<?php

namespace App\Http\Controllers;

use App\Models\WashStockItem;
use App\Models\WashStockMovement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;

class WashStockController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:wash.view', only: ['index', 'show', 'export']),
            new Middleware('permission:wash.manage', only: ['create', 'store', 'edit', 'update', 'destroy', 'stockIn', 'storeStockIn', 'import', 'downloadTemplate']),
        ];
    }

    public function index(Request $request)
    {
        $defaultCategories = ['Shampo', 'Semir Ban', 'Lap & Busa', 'Chemical / Obat', 'Pewangi', 'Perlengkapan', 'Sparepart'];
        $dbCategories = WashStockItem::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();
        $categories = array_values(array_unique(array_filter(array_merge($defaultCategories, $dbCategories))));
        sort($categories);

        $query = WashStockItem::query();

        // 1. Pencarian
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // 2. Kategori
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        // 3. Status Stok
        if ($request->filled('stock_status')) {
            $stockStatus = $request->get('stock_status');
            if ($stockStatus === 'out') {
                $query->where('current_stock', '<=', 0);
            } elseif ($stockStatus === 'low') {
                $query->where('current_stock', '>', 0)
                    ->whereColumn('current_stock', '<=', 'minimum_stock');
            } elseif ($stockStatus === 'in') {
                $query->whereColumn('current_stock', '>', 'minimum_stock');
            }
        }

        // 4. Status Aktif / Nonaktif
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // 5. Urutan
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('current_stock', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('current_stock', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('last_buy_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('last_buy_price', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $items = $query->customPaginate()->appends($request->query());

        $stats = [
            'total_items' => WashStockItem::count(),
            'total_stock' => (float) WashStockItem::sum('current_stock'),
            'low_stock' => WashStockItem::where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'minimum_stock')->count(),
            'out_of_stock' => WashStockItem::where('current_stock', '<=', 0)->count(),
        ];

        return view('wash.stock.index', compact('items', 'categories', 'stats'));
    }

    public function export()
    {
        $items = WashStockItem::orderBy('name')->get();

        return response()->streamDownload(function () use ($items) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'Nama Item',
                'Kategori',
                'Satuan',
                'Stok Saat Ini',
                'Stok Minimum',
                'Harga Beli Terakhir',
                'Status',
            ]));

            foreach ($items as $item) {
                $writer->addRow(Row::fromValues([
                    $item->name,
                    $item->category ?? '',
                    $item->unit,
                    (float) $item->current_stock,
                    (float) ($item->minimum_stock ?? 0),
                    (float) ($item->last_buy_price ?? 0),
                    $item->is_active ? 'Aktif' : 'Nonaktif',
                ]));
            }

            $writer->close();
        }, 'daftar_stok_wash_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'Nama Item',
                'Kategori',
                'Satuan',
                'Stok Awal',
                'Stok Minimum',
                'Harga Beli Terakhir',
                'Status',
            ]));

            // Data Contoh Cuci Motor & Mobil
            $writer->addRow(Row::fromValues([
                'Shampo Snow Wash Premium Touchless',
                'Shampo',
                'Liter',
                50,
                10,
                35000,
                'Aktif',
            ]));
            $writer->addRow(Row::fromValues([
                'Semir Ban Silikon Glossy Black',
                'Semir Ban',
                'Liter',
                20,
                5,
                45000,
                'Aktif',
            ]));
            $writer->addRow(Row::fromValues([
                'Lap Microfiber 40x40 380GSM',
                'Lap & Busa',
                'Pcs',
                30,
                10,
                15000,
                'Aktif',
            ]));
            $writer->addRow(Row::fromValues([
                'Pembersih Jamur Kaca & Waterspot Remover',
                'Chemical / Obat',
                'Botol',
                15,
                3,
                55000,
                'Aktif',
            ]));
            $writer->addRow(Row::fromValues([
                'Sponge Busa Tebal Cuci Mobil',
                'Lap & Busa',
                'Pcs',
                25,
                5,
                8000,
                'Aktif',
            ]));

            $writer->close();
        }, 'template_import_stok_wash.xlsx');
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

        $str = preg_replace('/[^\d.,-]/', '', $str);

        if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
            if (strrpos($str, '.') > strrpos($str, ',')) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            }
        } elseif (strpos($str, '.') !== false) {
            if (substr_count($str, '.') > 1) {
                $str = str_replace('.', '', $str);
            } elseif (preg_match('/\.\d{3}$/', $str)) {
                $str = str_replace('.', '', $str);
            }
        } elseif (strpos($str, ',') !== false) {
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
            return back()->withErrors(['file' => 'Format file tidak didukung. Harap unggah file berformat .xlsx, .xls, atau .csv.']);
        }

        try {
            $rowsData = [];

            if (in_array($ext, ['csv', 'txt'])) {
                $firstLine = '';
                $handle = fopen($realPath, 'r');
                if ($handle) {
                    $firstLine = fgets($handle) ?: '';
                    fclose($handle);
                }
                $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

                if (($h = fopen($realPath, 'r')) !== false) {
                    while (($data = fgetcsv($h, 4000, $delimiter)) !== false) {
                        $rowsData[] = array_map(fn ($v) => trim((string) $v), $data);
                    }
                    fclose($h);
                }
            } else {
                $reader = new Reader;
                $reader->open($realPath);
                foreach ($reader->getSheetIterator() as $sheet) {
                    foreach ($sheet->getRowIterator() as $row) {
                        $cells = $row->getCells();
                        $rowVals = [];
                        foreach ($cells as $cell) {
                            $rowVals[] = trim((string) ($cell->getValue() ?? ''));
                        }
                        $rowsData[] = $rowVals;
                    }
                    break;
                }
                $reader->close();
            }

            if (empty($rowsData)) {
                return back()->withErrors(['file' => 'File Excel/CSV kosong.']);
            }

            $headerMap = [
                'name' => null,
                'category' => null,
                'unit' => null,
                'stock' => null,
                'minimum_stock' => null,
                'price' => null,
                'status' => null,
            ];

            $hasHeader = false;
            $firstRow = $rowsData[0] ?? [];

            foreach ($firstRow as $colIdx => $colName) {
                $norm = strtolower(trim($colName));
                if ($norm === '') continue;

                if (str_contains($norm, 'nama') || str_contains($norm, 'name') || str_contains($norm, 'barang') || str_contains($norm, 'item')) {
                    $headerMap['name'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'kategori') || str_contains($norm, 'category')) {
                    $headerMap['category'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'satuan') || str_contains($norm, 'unit')) {
                    $headerMap['unit'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'min') || str_contains($norm, 'batas')) {
                    $headerMap['minimum_stock'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'stok') || str_contains($norm, 'stock') || str_contains($norm, 'qty') || str_contains($norm, 'jumlah')) {
                    $headerMap['stock'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'harga') || str_contains($norm, 'beli') || str_contains($norm, 'hpp') || str_contains($norm, 'price') || str_contains($norm, 'cost')) {
                    $headerMap['price'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'status') || str_contains($norm, 'aktif') || str_contains($norm, 'active')) {
                    $headerMap['status'] = $colIdx;
                    $hasHeader = true;
                }
            }

            if (! $hasHeader || $headerMap['name'] === null) {
                $headerMap = [
                    'name' => 0,
                    'category' => 1,
                    'unit' => 2,
                    'stock' => 3,
                    'minimum_stock' => 4,
                    'price' => 5,
                    'status' => 6,
                ];
                $dataRows = $rowsData;
            } else {
                $dataRows = array_slice($rowsData, 1);
            }

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($dataRows as $row) {
                if (empty(array_filter($row, fn ($v) => $v !== ''))) {
                    continue;
                }

                $name = $headerMap['name'] !== null && isset($row[$headerMap['name']]) ? trim($row[$headerMap['name']]) : '';
                if ($name === '') {
                    $skippedCount++;
                    continue;
                }

                $category = $headerMap['category'] !== null && isset($row[$headerMap['category']]) && trim($row[$headerMap['category']]) !== ''
                    ? trim($row[$headerMap['category']])
                    : 'Bahan Cuci';
                $unit = $headerMap['unit'] !== null && isset($row[$headerMap['unit']]) && trim($row[$headerMap['unit']]) !== ''
                    ? trim($row[$headerMap['unit']])
                    : 'Pcs';
                $stock = $headerMap['stock'] !== null && isset($row[$headerMap['stock']])
                    ? $this->cleanNumeric($row[$headerMap['stock']])
                    : 0.0;
                $minimumStock = $headerMap['minimum_stock'] !== null && isset($row[$headerMap['minimum_stock']])
                    ? $this->cleanNumeric($row[$headerMap['minimum_stock']])
                    : 0.0;
                $buyPrice = $headerMap['price'] !== null && isset($row[$headerMap['price']])
                    ? $this->cleanNumeric($row[$headerMap['price']])
                    : 0.0;

                $isActive = true;
                if ($headerMap['status'] !== null && isset($row[$headerMap['status']])) {
                    $st = strtolower(trim($row[$headerMap['status']]));
                    if (in_array($st, ['nonaktif', 'tidak', 'inactive', 'false', '0', 'off'])) {
                        $isActive = false;
                    }
                }

                $existing = WashStockItem::where('name', $name)->first();
                if ($existing) {
                    $existing->update([
                        'category' => $category,
                        'unit' => $unit,
                        'current_stock' => $stock,
                        'minimum_stock' => $minimumStock,
                        'last_buy_price' => $buyPrice > 0 ? $buyPrice : $existing->last_buy_price,
                        'is_active' => $isActive,
                    ]);
                    $updatedCount++;
                } else {
                    $newItem = WashStockItem::create([
                        'name' => $name,
                        'category' => $category,
                        'unit' => $unit,
                        'current_stock' => $stock,
                        'minimum_stock' => $minimumStock,
                        'last_buy_price' => $buyPrice,
                        'is_active' => $isActive,
                    ]);

                    if ($stock > 0) {
                        WashStockMovement::create([
                            'wash_stock_item_id' => $newItem->id,
                            'movement_type' => 'in',
                            'quantity' => $stock,
                            'unit_price' => $buyPrice,
                            'total_amount' => $stock * $buyPrice,
                            'movement_date' => now(),
                            'notes' => 'Import awal stok bahan wash',
                            'user_id' => auth()->id(),
                        ]);
                    }
                    $createdCount++;
                }
            }

            return redirect()->route('wash.stock.index')->with(
                'success',
                "Impor data stok cuci berhasil! {$createdCount} item baru ditambahkan, {$updatedCount} item diperbarui" . ($skippedCount > 0 ? ", {$skippedCount} baris kosong dilewati." : '.')
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Import gagal: ' . $e->getMessage()]);
        }
    }

    public function create()
    {
        return view('wash.stock.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'last_buy_price' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $item = WashStockItem::create([
                'name' => $request->name,
                'category' => $request->category,
                'unit' => $request->unit,
                'current_stock' => $request->current_stock,
                'minimum_stock' => $request->minimum_stock ?? 0,
                'last_buy_price' => $request->last_buy_price,
                'is_active' => $request->has('is_active'),
            ]);
            
            if ($item->current_stock > 0) {
                WashStockMovement::create([
                    'wash_stock_item_id' => $item->id,
                    'movement_type' => 'in',
                    'quantity' => $item->current_stock,
                    'unit_price' => $item->last_buy_price,
                    'total_amount' => $item->current_stock * ($item->last_buy_price ?? 0),
                    'movement_date' => now(),
                    'notes' => 'Stok awal',
                    'user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('wash.stock.index')->with('success', 'Stok item berhasil ditambahkan!');
    }

    public function show(WashStockItem $stockItem)
    {
        $movements = $stockItem->movements()->latest()->customPaginate();
        return view('wash.stock.show', compact('stockItem', 'movements'));
    }

    public function edit(WashStockItem $stockItem)
    {
        return view('wash.stock.edit', compact('stockItem'));
    }

    public function update(Request $request, WashStockItem $stockItem)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'last_buy_price' => 'nullable|numeric|min:0',
        ]);

        $stockItem->update([
            'name' => $request->name,
            'category' => $request->category,
            'unit' => $request->unit,
            'current_stock' => $request->current_stock,
            'minimum_stock' => $request->minimum_stock ?? 0,
            'last_buy_price' => $request->last_buy_price,
            'is_active' => $request->has('is_active'),
        ]);
        return redirect()->route('wash.stock.index')->with('success', 'Stok item berhasil diupdate!');
    }

    public function destroy(WashStockItem $stockItem)
    {
        $stockItem->delete();
        return redirect()->route('wash.stock.index')->with('success', 'Stok item berhasil dihapus!');
    }

    public function stockIn(WashStockItem $stockItem)
    {
        return view('wash.stock.stock-in', compact('stockItem'));
    }

    public function storeStockIn(Request $request, WashStockItem $stockItem)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'movement_date' => 'required|date',
        ]);

        DB::transaction(function () use ($request, $stockItem) {
            $stockItem->increment('current_stock', $request->quantity);
            
            if ($request->unit_price > 0) {
                $stockItem->update(['last_buy_price' => $request->unit_price]);
            }

            WashStockMovement::create([
                'wash_stock_item_id' => $stockItem->id,
                'movement_type' => 'in',
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_amount' => $request->quantity * $request->unit_price,
                'movement_date' => $request->movement_date,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->route('wash.stock.show', $stockItem)->with('success', 'Stok masuk berhasil dicatat!');
    }
}
