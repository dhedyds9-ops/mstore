<?php

namespace App\Services\Atk;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class AtkSpreadsheetService
{
    /**
     * Download data as XLSX spreadsheet response.
     */
    public function downloadXlsx(string $filename, array $headers, array $rows): Response
    {
        if (! str_ends_with(strtolower($filename), '.xlsx')) {
            $filename .= '.xlsx';
        }

        $content = $this->generateXlsx($headers, $rows);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename),
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    /**
     * Download data as CSV response with UTF-8 BOM.
     */
    public function downloadCsv(string $filename, array $headers, array $rows): StreamedResponse
    {
        if (! str_ends_with(strtolower($filename), '.csv')) {
            $filename .= '.csv';
        }

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fwrite($out, "\xEF\xBB\xBF");

            if (! empty($headers)) {
                fputcsv($out, $headers, ';');
            }

            foreach ($rows as $row) {
                fputcsv($out, (array) $row, ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    /**
     * Download file with format selection ('xlsx' or 'csv').
     */
    public function download(string $filename, array $headers, array $rows, string $format = 'xlsx')
    {
        if (strtolower($format) === 'csv') {
            return $this->downloadCsv($filename, $headers, $rows);
        }

        try {
            return $this->downloadXlsx($filename, $headers, $rows);
        } catch (\Throwable $e) {
            // Fallback to CSV if zip archive creation failed
            \Illuminate\Support\Facades\Log::warning('XLSX export failed, falling back to CSV: ' . $e->getMessage());
            return $this->downloadCsv(str_replace('.xlsx', '.csv', $filename), $headers, $rows);
        }
    }

    /**
     * Build XLSX binary content using ZipArchive.
     */
    public function generateXlsx(array $headers, array $rows): string
    {
        $tempFile = @tempnam(sys_get_temp_dir(), 'mstore_xlsx_');
        if (! $tempFile) {
            $tempDir = storage_path('framework/cache');
            if (! is_dir($tempDir)) {
                @mkdir($tempDir, 0777, true);
            }
            $tempFile = $tempDir . '/xlsx_' . uniqid('', true) . '.tmp';
        }

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file XLSX di server.');
        }

        // Shared strings
        $strings = [];
        $stringIndex = 0;
        $getStringIndex = function ($str) use (&$strings, &$stringIndex) {
            $str = (string) $str;
            if (! isset($strings[$str])) {
                $strings[$str] = $stringIndex++;
            }
            return $strings[$str];
        };

        // Prepare sheet XML
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $sheetXml .= '<sheetData>' . "\n";

        $allRows = [];
        if (! empty($headers)) {
            $allRows[] = $headers;
        }
        foreach ($rows as $r) {
            $allRows[] = (array) $r;
        }

        $rowNum = 1;
        foreach ($allRows as $row) {
            $sheetXml .= '<row r="' . $rowNum . '">' . "\n";
            $colNum = 0;
            foreach ($row as $val) {
                $colLetter = self::columnLetter($colNum);
                $cellRef = $colLetter . $rowNum;

                if (is_numeric($val) && ! is_string($val)) {
                    $sheetXml .= '<c r="' . $cellRef . '"><v>' . $val . '</v></c>' . "\n";
                } elseif (is_int($val) || (is_string($val) && ctype_digit($val) && strlen($val) < 12 && ! str_starts_with($val, '0'))) {
                    $sheetXml .= '<c r="' . $cellRef . '"><v>' . $val . '</v></c>' . "\n";
                } elseif ($val === null || $val === '') {
                    // Empty cell, do not output to save space
                } else {
                    $sIdx = $getStringIndex((string) $val);
                    $sheetXml .= '<c r="' . $cellRef . '" t="s"><v>' . $sIdx . '</v></c>' . "\n";
                }
                $colNum++;
            }
            $sheetXml .= '</row>' . "\n";
            $rowNum++;
        }

        $sheetXml .= '</sheetData></worksheet>';

        // Prepare sharedStrings XML
        $sstXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sstXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">' . "\n";
        foreach ($strings as $str => $idx) {
            $escaped = htmlspecialchars($str, ENT_XML1, 'UTF-8');
            $sstXml .= '<si><t>' . $escaped . '</t></si>' . "\n";
        }
        $sstXml .= '</sst>';

        // Standard OpenXML structures
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';

        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>
</workbook>';

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/workbook.xml', $wb);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->addFromString('xl/sharedStrings.xml', $sstXml);
        $zip->close();

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content ?: '';
    }

    /**
     * Parse an uploaded spreadsheet file (.xlsx, .xls, .csv, .txt) into an array of rows.
     * Preserves column indices and eliminates sparse cell column shifts.
     */
    public function parseFile(string $filePath, ?string $originalExtension = null): array
    {
        $ext = strtolower($originalExtension ?: pathinfo($filePath, PATHINFO_EXTENSION));

        // 1. Try reading as XLSX if extension is xlsx or if zip header is detected
        if ($ext === 'xlsx' || $this->isZipFile($filePath)) {
            try {
                return $this->parseXlsx($filePath);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Direct XLSX parse failed, trying OpenSpout: ' . $e->getMessage());
                // Fallback to OpenSpout Reader if installed
                return $this->parseWithOpenSpout($filePath);
            }
        }

        // 2. CSV / TXT or disguised text files
        return $this->parseCsv($filePath);
    }

    /**
     * Check if file starts with ZIP magic bytes PK\x03\x04
     */
    private function isZipFile(string $filePath): bool
    {
        $h = @fopen($filePath, 'rb');
        if (! $h) {
            return false;
        }
        $header = fread($h, 4);
        fclose($h);
        return $header === "PK\x03\x04";
    }

    /**
     * Parse XLSX using ZipArchive & SimpleXML without external dependencies.
     */
    public function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Tidak dapat membuka file Excel (.xlsx).');
        }

        // 1. Read shared strings
        $sharedStrings = [];
        $sstData = $zip->getFromName('xl/sharedStrings.xml');
        if ($sstData !== false) {
            $sstXml = @simplexml_load_string($sstData);
            if ($sstXml && isset($sstXml->si)) {
                foreach ($sstXml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Locate worksheet
        $sheetData = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetData === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_starts_with($name, 'xl/worksheets/') && str_ends_with($name, '.xml')) {
                    $sheetData = $zip->getFromIndex($i);
                    break;
                }
            }
        }
        $zip->close();

        if ($sheetData === false) {
            throw new \RuntimeException('Tidak ditemukan lembar kerja (worksheet) di dalam file Excel.');
        }

        $xml = @simplexml_load_string($sheetData);
        if (! $xml || ! isset($xml->sheetData)) {
            return [];
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            $maxCol = 0;

            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                preg_match('/([A-Z]+)(\d+)/', $ref, $matches);
                $colIndex = isset($matches[1]) ? (self::columnNumber($matches[1]) - 1) : 0;
                $maxCol = max($maxCol, $colIndex);

                $val = (string) ($c->v ?? '');
                $type = (string) ($c['t'] ?? '');

                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $rowData[$colIndex] = trim($sharedStrings[(int) $val]);
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $rowData[$colIndex] = trim((string) $c->is->t);
                } else {
                    $rowData[$colIndex] = trim($val);
                }
            }

            // Fill empty gaps to preserve column positioning
            $finalRow = [];
            for ($i = 0; $i <= $maxCol; $i++) {
                $finalRow[$i] = $rowData[$i] ?? '';
            }

            // Skip row if completely empty
            if (! empty(array_filter($finalRow, fn($v) => $v !== ''))) {
                $rows[] = $finalRow;
            }
        }

        return $rows;
    }

    /**
     * Fallback to OpenSpout Reader if available.
     */
    private function parseWithOpenSpout(string $filePath): array
    {
        if (! class_exists(\OpenSpout\Reader\XLSX\Reader::class)) {
            return [];
        }

        $reader = new \OpenSpout\Reader\XLSX\Reader();
        $reader->open($filePath);
        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->getCells();
                if (empty($cells)) {
                    continue;
                }

                $maxIndex = max(array_keys($cells));
                $rowVals = array_fill(0, $maxIndex + 1, '');

                foreach ($cells as $idx => $cell) {
                    $val = $cell->getValue();
                    if ($val instanceof \DateTimeInterface) {
                        $val = $val->format('Y-m-d H:i:s');
                    }
                    $rowVals[$idx] = trim((string) ($val ?? ''));
                }

                if (! empty(array_filter($rowVals, fn($v) => $v !== ''))) {
                    $rows[] = $rowVals;
                }
            }
            break; // First sheet only
        }

        $reader->close();
        return $rows;
    }

    /**
     * Parse CSV / TSV / TXT with auto-delimiter detection and UTF-8 BOM removal.
     */
    public function parseCsv(string $filePath): array
    {
        $handle = @fopen($filePath, 'r');
        if (! $handle) {
            throw new \RuntimeException('Tidak dapat membaca file CSV.');
        }

        // Read first line to detect delimiter and check BOM
        $firstLine = fgets($handle) ?: '';
        // Strip BOM if present
        if (str_starts_with($firstLine, "\xEF\xBB\xBF")) {
            $firstLine = substr($firstLine, 3);
        }

        $delimiters = [';', ',', "\t", '|'];
        $counts = [];
        foreach ($delimiters as $delim) {
            $counts[$delim] = substr_count($firstLine, $delim);
        }
        arsort($counts);
        $delimiter = key($counts) ?: ',';
        if ($counts[$delimiter] === 0) {
            $delimiter = ',';
        }

        rewind($handle);

        $rows = [];
        $isFirst = true;

        while (($data = fgetcsv($handle, 8000, $delimiter)) !== false) {
            if ($isFirst) {
                // Remove BOM from first column if present
                if (isset($data[0]) && str_starts_with($data[0], "\xEF\xBB\xBF")) {
                    $data[0] = substr($data[0], 3);
                }
                $isFirst = false;
            }

            $cleaned = array_map(fn($v) => trim((string) $v), $data);
            if (! empty(array_filter($cleaned, fn($v) => $v !== ''))) {
                $rows[] = $cleaned;
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Helper: Convert 0-based column index to letter (0 -> A, 1 -> B, 26 -> AA).
     */
    public static function columnLetter(int $colIndex): string
    {
        $letter = '';
        $colIndex++;
        while ($colIndex > 0) {
            $mod = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIndex = (int) (($colIndex - $mod) / 26);
        }
        return $letter;
    }

    /**
     * Helper: Convert column letter to 1-based number (A -> 1, B -> 2, AA -> 27).
     */
    public static function columnNumber(string $col): int
    {
        $col = strtoupper($col);
        $num = 0;
        $len = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($col[$i]) - 64);
        }
        return $num;
    }
}
