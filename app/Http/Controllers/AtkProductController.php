<?php

namespace App\Http\Controllers;

use App\Models\AtkProduct;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;

class AtkProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:atk.manage'),
        ];
    }

    public function barcodes(Request $request)
    {
        $query = AtkProduct::query();
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }
        $products = $query->orderBy('name')->get();

        return view('atk.products.barcodes', compact('products'));
    }

    public function barcodesPdf(Request $request)
    {
        $query = AtkProduct::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        $products = $query->orderBy('name')->get();

        $htmlGen = new \Picqer\Barcode\BarcodeGeneratorHTML;
        $barcodes = [];

        foreach ($products as $product) {
            $code = $product->code ?: ('ITEM_'.substr(uniqid('', true), -6));
            $barcodes[$product->id] = $htmlGen->getBarcode(
                $code,
                $htmlGen::TYPE_CODE_128,
                2,
                60
            );
        }

        // 🔹 PILIH UKURAN KERTAS
        $paper = $request->get('paper');
        $paper = in_array($paper, ['a4', 'legal', 'letter']) ? $paper : 'a4';
        $mode = $request->get('mode', 'preview'); // preview | download | print

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'atk.products.barcodes_pdf',
            compact('products', 'barcodes')
        )->setPaper($paper, 'portrait');

        if ($mode === 'download') {
            return $pdf->download('atk_barcodes.pdf');
        }

        if ($mode === 'print') {
            return response(
                $pdf->output(),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="atk_barcodes.pdf"',
                ]
            );
        }

        // default preview
        return $pdf->stream('atk_barcodes.pdf');
    }

    public function barcodesPreview(Request $request)
    {
        $query = AtkProduct::query();
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }
        $products = $query->orderBy('name')->get();
        $htmlGen = new \Picqer\Barcode\BarcodeGeneratorHTML;
        $barcodes = [];
        foreach ($products as $product) {
            $code = $product->code ?: ('ITEM_'.substr(uniqid('', true), -6));
            $barcodes[$product->id] = $htmlGen->getBarcode($code, $htmlGen::TYPE_CODE_128, 1.5, 60);
        }
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('atk.products.barcodes_pdf', compact('products', 'barcodes'))->setPaper('a4', 'portrait');

        return $pdf->stream('atk_barcodes.pdf');
    }

    public function export()
    {
        $products = AtkProduct::all();

        return response()->streamDownload(function () use ($products) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues(['Kode Barang', 'Nama Produk', 'Kategori', 'Harga Jual', 'Harga Beli / HPP', 'Stok', 'Satuan', 'Deskripsi']));

            foreach ($products as $product) {
                $writer->addRow(Row::fromValues([
                    $product->code,
                    $product->name,
                    $product->category,
                    $product->price,
                    $product->cost_price,
                    $product->stock,
                    $product->unit,
                    $product->description,
                ]));
            }

            $writer->close();
        }, 'daftar_produk_atk_'.now()->format('Ymd_His').'.xlsx');
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $writer = new Writer;
            $writer->openToFile('php://output');

            // Header kolom resmi
            $writer->addRow(Row::fromValues([
                'Kode Barang',
                'Nama Produk',
                'Kategori',
                'Harga Jual',
                'Harga Beli / HPP',
                'Stok Awal',
                'Satuan',
                'Deskripsi / Keterangan',
            ]));

            // Data Contoh ATK Riil
            $writer->addRow(Row::fromValues([
                'ATK-PAP-001',
                'Kertas HVS A4 75gr PaperOne (1 Rim)',
                'ATK',
                55000,
                48000,
                50,
                'Rim',
                'Kertas HVS putih 75 gsm PaperOne isi 500 lembar',
            ]));
            $writer->addRow(Row::fromValues([
                'ATK-PEN-002',
                'Pulpen Standard AE7 0.5 Black',
                'ATK',
                3500,
                2500,
                120,
                'Pcs',
                'Pulpen tinta hitam mata pena 0.5mm',
            ]));
            $writer->addRow(Row::fromValues([
                'ATK-BK-003',
                'Buku Tulis Sinar Dunia 38 Lembar',
                'ATK',
                4500,
                3200,
                80,
                'Pcs',
                'Buku tulis SIDU 38 lembar pack/eceran',
            ]));
            $writer->addRow(Row::fromValues([
                'SVC-FCP-001',
                'Jasa Fotocopy A4 Hitam Putih',
                'JASA POTOCOPY',
                300,
                150,
                9999,
                'Lembar',
                'Jasa fotocopy bolak balik / biasa',
            ]));

            $writer->close();
        }, 'template_import_produk_atk.xlsx');
    }

    private function cleanNumeric($value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $str = trim((string) $value);
        if ($str === '') {
            return 0.0;
        }

        // Hapus teks/simbol selain angka, minus, titik, koma
        $str = preg_replace('/[^\d.,-]/', '', $str);

        // Ada titik DAN koma: 1.850.000,50 atau 1,850,000.50
        if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
            if (strrpos($str, '.') > strrpos($str, ',')) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            }
        }
        // Hanya ada titik
        elseif (strpos($str, '.') !== false) {
            if (substr_count($str, '.') > 1) {
                $str = str_replace('.', '', $str);
            } elseif (preg_match('/\.\d{3}$/', $str)) {
                $str = str_replace('.', '', $str);
            }
        }
        // Hanya ada koma
        elseif (strpos($str, ',') !== false) {
            if (substr_count($str, ',') > 1) {
                $str = str_replace(',', '', $str);
            } elseif (preg_match('/,\d{3}$/', $str)) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace(',', '.', $str);
            }
        }

        return is_numeric($str) ? (float) $str : 0.0;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $realPath = $file->getRealPath();

        if (! in_array($ext, ['xlsx', 'xls', 'csv', 'txt'])) {
            return back()->withErrors(['file' => __('Format file tidak didukung. Harap unggah file berformat .xlsx, .xls, atau .csv.')]);
        }

        try {
            $rowsData = [];

            if (in_array($ext, ['csv', 'txt'])) {
                // Deteksi delimiter (; atau ,)
                $firstLine = '';
                $handle = fopen($realPath, 'r');
                if ($handle) {
                    $firstLine = fgets($handle) ?: '';
                    fclose($handle);
                }
                $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

                if (($h = fopen($realPath, 'r')) !== false) {
                    while (($data = fgetcsv($h, 4000, $delimiter)) !== false) {
                        $rowsData[] = array_map(fn($v) => trim((string)$v), $data);
                    }
                    fclose($h);
                }
            } else {
                // XLSX / XLS
                $reader = new Reader;
                $reader->open($realPath);
                foreach ($reader->getSheetIterator() as $sheet) {
                    foreach ($sheet->getRowIterator() as $row) {
                        $cells = $row->getCells();
                        $rowVals = [];
                        foreach ($cells as $cell) {
                            $rowVals[] = trim((string)($cell->getValue() ?? ''));
                        }
                        $rowsData[] = $rowVals;
                    }
                    break; // Ambil sheet pertama saja
                }
                $reader->close();
            }

            if (empty($rowsData)) {
                return back()->withErrors(['file' => __('File Excel/CSV kosong.')]);
            }

            // Pemetaan Kolom Dinamis berdasarkan baris header
            $headerMap = [
                'code' => null,
                'name' => null,
                'category' => null,
                'price' => null,
                'cost_price' => null,
                'stock' => null,
                'unit' => null,
                'description' => null,
            ];

            $hasHeader = false;
            $firstRow = $rowsData[0] ?? [];

            foreach ($firstRow as $colIdx => $colName) {
                $norm = strtolower(trim($colName));
                if ($norm === '') continue;

                if (str_contains($norm, 'beli') || str_contains($norm, 'hpp') || str_contains($norm, 'modal') || str_contains($norm, 'cost') || str_contains($norm, 'pokok')) {
                    $headerMap['cost_price'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'jual') || str_contains($norm, 'price') || str_contains($norm, 'harga') || str_contains($norm, 'tarif')) {
                    $headerMap['price'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'stok') || str_contains($norm, 'stock') || str_contains($norm, 'qty') || str_contains($norm, 'jumlah') || str_contains($norm, 'kuantitas')) {
                    $headerMap['stock'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'kode') || str_contains($norm, 'code') || str_contains($norm, 'barcode') || str_contains($norm, 'sku')) {
                    $headerMap['code'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'kategori') || str_contains($norm, 'category') || str_contains($norm, 'kelompok')) {
                    $headerMap['category'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'satuan') || str_contains($norm, 'unit') || str_contains($norm, 'uom')) {
                    $headerMap['unit'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'deskripsi') || str_contains($norm, 'keterangan') || str_contains($norm, 'catatan') || str_contains($norm, 'note') || str_contains($norm, 'spesifikasi') || str_contains($norm, 'desc')) {
                    $headerMap['description'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'nama') || str_contains($norm, 'name') || str_contains($norm, 'produk') || str_contains($norm, 'barang') || str_contains($norm, 'item') || str_contains($norm, 'uraian')) {
                    $headerMap['name'] = $colIdx;
                    $hasHeader = true;
                }
            }

            // Fallback jika tidak ada header terdeteksi
            if (! $hasHeader || $headerMap['name'] === null) {
                // Cek kemungkinan posisi standar
                // Kolom 0: Code, Kolom 1: Name, dst...
                $headerMap = [
                    'code' => 0,
                    'name' => 1,
                    'category' => 2,
                    'price' => 3,
                    'cost_price' => 4,
                    'stock' => 5,
                    'unit' => 6,
                    'description' => 7,
                ];
                $dataRows = $rowsData;
            } else {
                $dataRows = array_slice($rowsData, 1);
            }

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($dataRows as $row) {
                if (empty(array_filter($row, fn($v) => $v !== ''))) {
                    continue; // Skip baris kosong
                }

                $name = $headerMap['name'] !== null && isset($row[$headerMap['name']]) ? trim($row[$headerMap['name']]) : '';
                if ($name === '') {
                    $skippedCount++;
                    continue;
                }

                $code = $headerMap['code'] !== null && isset($row[$headerMap['code']]) ? trim($row[$headerMap['code']]) : '';
                $category = $headerMap['category'] !== null && isset($row[$headerMap['category']]) ? trim($row[$headerMap['category']]) : 'ATK';
                $price = $headerMap['price'] !== null && isset($row[$headerMap['price']]) ? $this->cleanNumeric($row[$headerMap['price']]) : 0.0;
                $costPrice = $headerMap['cost_price'] !== null && isset($row[$headerMap['cost_price']]) ? $this->cleanNumeric($row[$headerMap['cost_price']]) : 0.0;
                $stock = $headerMap['stock'] !== null && isset($row[$headerMap['stock']]) ? (int) $this->cleanNumeric($row[$headerMap['stock']]) : 0;
                $unit = $headerMap['unit'] !== null && isset($row[$headerMap['unit']]) && trim($row[$headerMap['unit']]) !== '' ? trim($row[$headerMap['unit']]) : 'Pcs';
                $description = $headerMap['description'] !== null && isset($row[$headerMap['description']]) ? trim($row[$headerMap['description']]) : null;

                // Generate kode otomatis jika kosong
                if ($code === '') {
                    $prefix = 'ATK-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 4));
                    if (strlen($prefix) < 5) {
                        $prefix = 'ATK-ITEM';
                    }
                    $code = $prefix . '-' . str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    while (AtkProduct::where('code', $code)->exists()) {
                        $code = $prefix . '-' . str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    }
                }

                $existing = AtkProduct::where('code', $code)->first();

                if ($existing) {
                    $existing->update([
                        'name' => $name,
                        'category' => $category ?: ($existing->category ?? 'ATK'),
                        'price' => $price > 0 ? $price : $existing->price,
                        'selling_price' => $price > 0 ? $price : $existing->price,
                        'cost_price' => $costPrice > 0 ? $costPrice : $existing->cost_price,
                        'stock' => $stock,
                        'current_stock' => $stock,
                        'unit' => $unit ?: $existing->unit,
                        'description' => $description ?: $existing->description,
                        'barcode' => $code,
                    ]);
                    $updatedCount++;
                } else {
                    AtkProduct::create([
                        'code' => $code,
                        'barcode' => $code,
                        'name' => $name,
                        'category' => $category ?: 'ATK',
                        'price' => $price,
                        'selling_price' => $price,
                        'cost_price' => $costPrice,
                        'stock' => $stock,
                        'current_stock' => $stock,
                        'unit' => $unit ?: 'Pcs',
                        'description' => $description,
                    ]);
                    $createdCount++;
                }
            }

            $totalSuccess = $createdCount + $updatedCount;
            $msg = "Import stok & produk ATK selesai: {$totalSuccess} produk diproses ({$createdCount} produk baru ditambahkan, {$updatedCount} produk diperbarui).";
            if ($skippedCount > 0) {
                $msg .= " ({$skippedCount} baris kosong/tanpa nama dilewati).";
            }

            return redirect()->route('atk.products.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Import gagal: ' . $e->getMessage()]);
        }
    }

    public function index(Request $request)
    {
        $defaultCategories = ['ATK', 'JASA POTOCOPY', 'JASA TRANSFER BANK'];
        $dbCategories = AtkProduct::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();
        try {
            $catModelNames = \App\Models\AtkCategory::pluck('name')->toArray();
        } catch (\Throwable $e) {
            $catModelNames = [];
        }
        $categories = array_values(array_unique(array_filter(array_merge($defaultCategories, $dbCategories, $catModelNames))));
        sort($categories);

        $query = AtkProduct::query();

        // 1. Pencarian (Nama, Kode, Barcode, Deskripsi)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 2. Kategori
        if ($request->filled('category')) {
            $cat = trim($request->get('category'));
            $query->where(function ($q) use ($cat) {
                $q->where('category', $cat)
                    ->orWhereHas('categoryModel', fn ($cq) => $cq->where('name', $cat));
            });
        }

        // 3. Status Stok
        if ($request->filled('stock_status')) {
            $stockStatus = $request->get('stock_status');
            if ($stockStatus === 'out') {
                $query->where('stock', '<=', 0);
            } elseif ($stockStatus === 'low') {
                $query->where('stock', '<=', 5)->where('stock', '>', 0);
            } elseif ($stockStatus === 'in') {
                $query->where('stock', '>', 5);
            }
        }

        // 4. Pengurutan / Sort
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('stock', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // 5. Pagination / Per Page
        $per = $request->get('per_page', '10');
        if ($per === 'all') {
            $perPage = max(1, (int) (clone $query)->count());
        } else {
            $perPage = (int) $per;
            if (! in_array($perPage, [10, 25, 50, 100], true)) {
                $perPage = 10;
            }
        }

        $products = $query->paginate($perPage)->appends($request->query());

        $stats = [
            'total_items' => AtkProduct::count(),
            'total_stock' => (int) AtkProduct::sum('stock'),
            'low_stock' => AtkProduct::where('stock', '<=', 5)->where('stock', '>', 0)->count(),
            'out_of_stock' => AtkProduct::where('stock', '<=', 0)->count(),
        ];

        return view('atk.products.index', compact('products', 'categories', 'stats'));
    }

    public function create()
    {
        return view('atk.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:atk_products,code',
            'category' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if (empty($validated['code'])) {
            $base = strtoupper(\Illuminate\Support\Str::slug($validated['name'], '_'));
            $suffix = substr(uniqid('', true), -6);
            $code = $base ? ($base.'_'.$suffix) : ('ITEM_'.$suffix);
            // ensure unique
            while (\App\Models\AtkProduct::where('code', $code)->exists()) {
                $suffix = substr(uniqid('', true), -6);
                $code = $base ? ($base.'_'.$suffix) : ('ITEM_'.$suffix);
            }
            $validated['code'] = $code;
        }
        if (empty($validated['unit'])) {
            $validated['unit'] = 'pcs';
        }

        $validated['price'] = $validated['price'] ?? 0;
        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        $validated['stock'] = $validated['stock'] ?? 0;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('atk-products', 'public');
        }

        AtkProduct::create($validated);

        return redirect()->route('atk.products.index')->with('success', __('Product created successfully.'));
    }

    public function edit(AtkProduct $product)
    {
        return view('atk.products.edit', compact('product'));
    }

    public function show(AtkProduct $product)
    {
        return redirect()->route('atk.products.edit', $product);
    }

    public function update(Request $request, AtkProduct $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|unique:atk_products,code,'.$product->id,
            'category' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if (array_key_exists('code', $validated) && empty($validated['code'])) {
            // keep existing code if user cleared it
            $validated['code'] = $product->code;
        }
        $validated['price'] = $validated['price'] ?? $product->price ?? 0;
        $validated['cost_price'] = $validated['cost_price'] ?? $product->cost_price ?? 0;
        $validated['stock'] = $validated['stock'] ?? $product->stock ?? 0;
        if (empty($validated['unit'])) {
            $validated['unit'] = 'pcs';
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('atk-products', 'public');
        }

        $product->update($validated);

        return redirect()->route('atk.products.index')->with('success', __('Product updated successfully.'));
    }

    public function destroy(AtkProduct $product)
    {
        if ($product->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return redirect()->route('atk.products.index')->with('success', __('Product deleted successfully.'));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (! is_array($ids) || empty($ids)) {
            return redirect()
                ->route('atk.products.index')
                ->withErrors(['ids' => __('No products selected.')]);
        }

        \DB::transaction(function () use ($ids) {

            // Ambil hanya kolom yang diperlukan
            $products = AtkProduct::whereIn('id', $ids)
                ->select('id', 'image')
                ->get();

            // Kumpulkan file image
            $images = $products
                ->pluck('image')
                ->filter()
                ->toArray();

            if (! empty($images)) {
                \Storage::disk('public')->delete($images);
            }

            // Single query delete
            AtkProduct::whereIn('id', $ids)->delete();
        });

        return redirect()
            ->route('atk.products.index')
            ->with('success', __('Selected products deleted successfully.'));
    }
}
