<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Coordinator;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;

class InventoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory.view', only: ['index', 'exportPdf', 'exportExcel', 'exportMovementExcel', 'downloadTemplate']),
            new Middleware('permission:inventory.manage', only: ['storeItem', 'storeStockIn', 'updateItem', 'destroyItem', 'updatePickup', 'destroyPickup', 'importExcel']),
            new Middleware('permission:inventory.pickup|inventory.manage', only: ['createPickup', 'storePickup']),
        ];
    }

    public function index(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        if ($request->has('type_group') && $request->type_group != '') {
            $query->where('type_group', $request->type_group);
        }

        // Default sorting: Tools first, then Materials, then by Name
        $query->orderBy('type_group', 'desc')->orderBy('name', 'asc');

        $items = (clone $query)
            ->select([
                'id',
                'name',
                'category',
                'type_group',
                'type',
                'brand',
                'model',
                'unit',
                'stock',
                'price',
                'selling_price',
                'description',
                'created_at',
            ])
            ->get();
        $categories = Cache::remember('inventory.categories', now()->addMinutes(10), function () {
            return InventoryItem::query()
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->orderBy('category')
                ->distinct()
                ->pluck('category');
        });

        // Dashboard Stats
        $totalStockValue = (clone $query)
            ->selectRaw('COALESCE(SUM(stock * price), 0) as total_stock_value')
            ->value('total_stock_value') ?? 0;
        $totalSellingValue = (clone $query)
            ->selectRaw('COALESCE(SUM(stock * selling_price), 0) as total_selling_value')
            ->value('total_selling_value') ?? 0;
        $totalItems = (clone $query)->count();

        // Total Pembelian (Purchases) separated by type_group
        $purchaseStats = Cache::remember('inventory.purchase_stats', now()->addMinutes(5), function () {
            return DB::table('inventory_transactions')
                ->join('inventory_items', 'inventory_transactions.inventory_item_id', '=', 'inventory_items.id')
                ->where('inventory_transactions.type', 'in')
                ->select('inventory_items.type_group', DB::raw('SUM(inventory_transactions.total_cost) as total'))
                ->groupBy('inventory_items.type_group')
                ->pluck('total', 'type_group');
        });

        $totalToolPurchases = $purchaseStats['tool'] ?? 0;
        $totalMaterialPurchases = $purchaseStats['material'] ?? 0;
        $totalPurchases = $totalToolPurchases + $totalMaterialPurchases;

        // Total Penjualan/Pemakaian (Sales) - Expense 'Pengeluaran Pengurus' linked to Inventory
        $totalSales = Cache::remember('inventory.total_sales', now()->addMinutes(5), function () {
            return Transaction::where('category', 'Pengeluaran Pengurus')
                ->where('reference_number', 'like', 'INV-OUT-%')
                ->sum('amount');
        });

        // Transaction History (Stock Movements: in/out)
        $transactions = $this->buildMovementQuery($request)
            ->latest()
            ->customPaginate()
            ->withQueryString();

        // My Assigned Assets (For Technicians/Coordinators)
        $user = Auth::user();
        $coordinatorId = Coordinator::where('user_id', $user->id)->value('id');
        $myAssetsQuery = Asset::query()
            ->with(['item:id,name,unit'])
            ->where(function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('holder_type', User::class)
                        ->where('holder_id', $user->id);
                });

            });
        if ($coordinatorId) {
            $myAssetsQuery->orWhere(function ($sub) use ($coordinatorId) {
                $sub->where('holder_type', Coordinator::class)
                    ->where('holder_id', $coordinatorId);
            });
        }

        $myAssets = $myAssetsQuery->get();

        return view('inventory.index', compact('items', 'transactions', 'totalStockValue', 'totalItems', 'totalPurchases', 'totalToolPurchases', 'totalMaterialPurchases', 'totalSales', 'categories', 'myAssets'));
    }

    private function buildMovementQuery(Request $request)
    {
        $query = InventoryTransaction::query()->with([
            'user:id,name',
            'item:id,name,unit,type_group',
            'coordinator:id,name',
        ]);

        if ($request->filled('type_group')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('type_group', $request->type_group);
            });
        }

        if ($request->filled('movement_type') && in_array($request->movement_type, ['in', 'out'], true)) {
            $query->where('type', $request->movement_type);
        }

        if ($request->input('movement_period') === 'day' && $request->filled('movement_day')) {
            try {
                $day = Carbon::createFromFormat('Y-m-d', $request->movement_day)->toDateString();
                $query->whereDate('created_at', $day);
            } catch (\Throwable $e) {
                // Ignore invalid filter format.
            }
        }

        if ($request->input('movement_period') === 'month' && $request->filled('movement_month')) {
            try {
                $month = Carbon::createFromFormat('Y-m', $request->movement_month);
                $query->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month);
            } catch (\Throwable $e) {
                // Ignore invalid filter format.
            }
        }

        if (! Auth::user()->hasPermission('inventory.view')) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public function createPickup(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->has('type_group') && in_array($request->type_group, ['tool', 'material'])) {
            $query->where('type_group', $request->type_group);
        }

        $items = $query->orderBy('type_group', 'desc')->orderBy('name')->get();
        $coordinators = Coordinator::orderBy('name')->get();

        return view('inventory.pickup', compact('items', 'coordinators'));
    }

    public function storePickup(Request $request)
    {
        if ($request->has('items')) {
            $data = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
                'items.*.quantity' => 'required|integer|min:1',
                'usage' => 'required|string|in:pemasangan_baru,perbaikan_maintenance,stok_tim,penggantian_material,penggantian_alat',
                'proof_image' => 'required|image|max:10240',
                'description' => 'nullable|string',
                'coordinator_id' => 'nullable|exists:coordinators,id',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);

            $path = $request->file('proof_image')->store('inventory_proofs', 'public');

            $usageLabels = [
                'pemasangan_baru' => 'Pemasangan Baru',
                'perbaikan_maintenance' => 'Perbaikan / Maintenance',
                'stok_tim' => 'Stok Tim / Coordinator',
                'penggantian_material' => 'Penggantian Material',
                'penggantian_alat' => 'Penggantian Alat',
            ];
            $usageLabel = $usageLabels[$data['usage']] ?? $data['usage'];
            $finalDescription = '['.$usageLabel.'] '.($data['description'] ?? '');

            $hasTools = false;
            DB::transaction(function () use ($data, $path, $finalDescription, &$hasTools) {
                $totals = [];
                foreach ($data['items'] as $row) {
                    $itemId = $row['inventory_item_id'];
                    $qty = $row['quantity'];
                    if (! isset($totals[$itemId])) {
                        $totals[$itemId] = 0;
                    }
                    $totals[$itemId] += $qty;
                }

                foreach ($totals as $itemId => $qty) {
                    $item = InventoryItem::find($itemId);
                    if (! $item || $item->stock < $qty) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'items' => [__('Not enough stock available.')],
                        ]);
                    }
                }

                foreach ($data['items'] as $row) {
                    $item = InventoryItem::find($row['inventory_item_id']);

                    $inventoryTransaction = InventoryTransaction::create([
                        'user_id' => Auth::id(),
                        'coordinator_id' => $data['coordinator_id'] ?? null,
                        'inventory_item_id' => $row['inventory_item_id'],
                        'type' => 'out',
                        'quantity' => $row['quantity'],
                        'unit_cost' => $item?->price ?? 0,
                        'total_cost' => ($item?->price ?? 0) * $row['quantity'],
                        'source_type' => 'pickup',
                        'proof_image' => $path,
                        'description' => $finalDescription,
                    ]);

                    if ($item) {
                        $item->decrement('stock', $row['quantity']);

                        if (! empty($data['coordinator_id']) && $item->price > 0) {
                            Transaction::create([
                            'user_id' => Auth::id(),
                            'coordinator_id' => $data['coordinator_id'],
                            'type' => 'expense',
                            'category' => 'Pengeluaran Pengurus',
                            'amount' => $item->price * $row['quantity'],
                            'transaction_date' => now()->toDateString(),
                            'description' => 'Pengurus mengambil '.$row['quantity'].' '.$item->unit.' '.$item->name,
                            'reference_number' => 'INV-OUT-'.$inventoryTransaction->id,
                        ]);
                        Cache::forget('inventory.total_sales');
                    }

                        // Auto-create Asset records for Tools
                        if ($item->type_group === 'tool') {
                            $hasTools = true;
                            $holderType = ! empty($data['coordinator_id']) ? Coordinator::class : User::class;
                            $holderId = $data['coordinator_id'] ?? Auth::id();

                            for ($i = 0; $i < $row['quantity']; $i++) {
                                Asset::create([
                                    'inventory_item_id' => $item->id,
                                    'asset_code' => 'TOOL-'.$item->id.'-'.time().'-'.uniqid(),
                                    'status' => 'deployed',
                                    'condition' => 'good',
                                    'holder_type' => $holderType,
                                    'holder_id' => $holderId,
                                    'latitude' => $data['latitude'] ?? null,
                                    'longitude' => $data['longitude'] ?? null,
                                    'purchase_date' => now(),
                                    'meta_data' => ['source_transaction_id' => $inventoryTransaction->id],
                                ]);
                            }
                        }
                    }
                }
            });
        } else {
            $request->validate([
                'inventory_item_id' => 'required|exists:inventory_items,id',
                'quantity' => 'required|integer|min:1',
                'usage' => 'required|string|in:pemasangan_baru,perbaikan_maintenance,stok_tim,penggantian_material,penggantian_alat',
                'proof_image' => 'required|image|max:10240',
                'description' => 'nullable|string',
                'coordinator_id' => 'nullable|exists:coordinators,id',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);

            $path = $request->file('proof_image')->store('inventory_proofs', 'public');

            $usageLabels = [
                'pemasangan_baru' => 'Pemasangan Baru',
                'perbaikan_maintenance' => 'Perbaikan / Maintenance',
                'stok_tim' => 'Stok Tim / Coordinator',
                'penggantian_material' => 'Penggantian Material',
                'penggantian_alat' => 'Penggantian Alat',
            ];
            $usageLabel = $usageLabels[$request->usage] ?? $request->usage;

            DB::transaction(function () use ($request, $path, $usageLabel) {
                $item = InventoryItem::findOrFail($request->inventory_item_id);

                if ($item->stock < $request->quantity) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'quantity' => [__('Not enough stock available.')],
                    ]);
                }

                $inventoryTransaction = InventoryTransaction::create([
                    'user_id' => Auth::id(),
                    'coordinator_id' => $request->coordinator_id,
                    'inventory_item_id' => $request->inventory_item_id,
                    'type' => 'out',
                    'quantity' => $request->quantity,
                    'unit_cost' => $item->price,
                    'total_cost' => $item->price * $request->quantity,
                    'source_type' => 'pickup',
                    'proof_image' => $path,
                    'description' => '['.$usageLabel.'] '.$request->description,
                ]);

                $item->decrement('stock', $request->quantity);

                // Auto-create Asset records for Tools
                if ($item->type_group === 'tool') {
                    $hasTools = true;
                    $holderType = $request->coordinator_id ? Coordinator::class : User::class;
                    $holderId = $request->coordinator_id ?? Auth::id();

                    for ($i = 0; $i < $request->quantity; $i++) {
                        Asset::create([
                            'inventory_item_id' => $item->id,
                            'asset_code' => 'TOOL-'.$item->id.'-'.time().'-'.uniqid(),
                            'status' => 'deployed',
                            'condition' => 'good',
                            'holder_type' => $holderType,
                            'holder_id' => $holderId,
                            'purchase_date' => now(),
                            'meta_data' => ['source_transaction_id' => $inventoryTransaction->id],
                        ]);
                    }
                }

                // Add to Finance Expense if Coordinator is selected
                if ($request->coordinator_id && $item->price > 0) {
                    Transaction::create([
                        'user_id' => Auth::id(),
                        'coordinator_id' => $request->coordinator_id,
                        'type' => 'expense',
                        'category' => 'Pengeluaran Pengurus',
                        'amount' => $item->price * $request->quantity,
                        'transaction_date' => now()->toDateString(),
                        'description' => 'Pengurus mengambil '.$request->quantity.' '.$item->unit.' '.$item->name,
                        'reference_number' => 'INV-OUT-'.$inventoryTransaction->id,
                    ]);
                    Cache::forget('inventory.total_sales');
                }
            });
        }

        return redirect()->route('inventory.index')->with('success', __('Items picked up successfully.'));
    }

    public function storeStockIn(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'supplier_name' => 'nullable|string|max:150',
            'reference_no' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'selling_price' => 'nullable|numeric|min:0',
        ]);

        $purchaseDate = $validated['purchase_date'] ?? now()->toDateString();

        $item = DB::transaction(function () use ($validated, $purchaseDate) {
            $item = InventoryItem::lockForUpdate()->findOrFail($validated['inventory_item_id']);
            $qty = (int) $validated['quantity'];
            $unitCost = (float) $validated['unit_cost'];
            $totalCost = $qty * $unitCost;

            $oldStock = (int) $item->stock;
            $oldPrice = (float) $item->price;
            $newStock = $oldStock + $qty;
            $newAveragePrice = $newStock > 0
                ? (($oldStock * $oldPrice) + $totalCost) / $newStock
                : $oldPrice;

            $item->update([
                'stock' => $newStock,
                'price' => $newAveragePrice,
                'selling_price' => $validated['selling_price'] ?? $item->selling_price,
            ]);

            $inventoryIn = InventoryTransaction::create([
                'user_id' => Auth::id(),
                'inventory_item_id' => $item->id,
                'type' => 'in',
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'source_type' => 'purchase',
                'supplier_name' => $validated['supplier_name'] ?? null,
                'reference_no' => $validated['reference_no'] ?? null,
                'description' => $validated['description'] ?? 'Barang masuk dari pembelian stok',
            ]);

            Transaction::create([
                'user_id' => Auth::id(),
                'type' => 'expense',
                'category' => 'Pembelian Alat',
                'amount' => $totalCost,
                'transaction_date' => $purchaseDate,
                'description' => 'Pembelian stok '.$item->name.' ('.$qty.' '.$item->unit.')',
                'reference_number' => 'INV-IN-'.$inventoryIn->id,
            ]);

            Cache::forget('inventory.purchase_stats');

            return $item;
        });

        return redirect()->route('inventory.index', ['type_group' => $item->type_group])
            ->with('success', __('Stock in recorded successfully.'));
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'type_group' => 'required|in:material,tool',
            'type' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
        ]);

        $item = InventoryItem::create($validated);

        if ($item->stock > 0) {
            InventoryTransaction::create([
                'user_id' => Auth::id(),
                'inventory_item_id' => $item->id,
                'type' => 'in',
                'quantity' => $item->stock,
                'unit_cost' => $item->price,
                'total_cost' => $item->stock * $item->price,
                'source_type' => 'opening_balance',
                'description' => 'Stok awal item dibuat',
            ]);
        }

        if ($item->stock > 0 && $item->price > 0) {
            Transaction::create([
                'user_id' => Auth::id(),
                'type' => 'expense',
                'category' => 'Pembelian Alat',
                'amount' => $item->stock * $item->price,
                'transaction_date' => now()->toDateString(),
                'description' => 'Pembelian stok awal '.$item->name,
                'reference_number' => 'INV-IN-'.$item->id,
            ]);
        }

        Cache::forget('inventory.purchase_stats');
        Cache::forget('inventory.categories');

        return redirect()->route('inventory.index', ['type_group' => $validated['type_group']])->with('success', __('Item added successfully.'));
    }

    public function updateItem(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'type_group' => 'required|in:material,tool',
            'type' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'stock' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
        ]);

        if (! array_key_exists('stock', $validated) || $validated['stock'] === null) {
            $validated['stock'] = $item->stock;
        }

        $oldStock = (int) $item->stock;
        $newStock = (int) $validated['stock'];

        DB::transaction(function () use ($item, $validated, $oldStock, $newStock) {
            $item->update($validated);

            if ($newStock > $oldStock && (float) $item->price > 0) {
                $diff = $newStock - $oldStock;
                $totalCost = $diff * (float) $item->price;

                $inventoryIn = InventoryTransaction::create([
                    'user_id' => Auth::id(),
                    'inventory_item_id' => $item->id,
                    'type' => 'in',
                    'quantity' => $diff,
                    'unit_cost' => (float) $item->price,
                    'total_cost' => $totalCost,
                    'source_type' => 'adjustment',
                    'description' => 'Penyesuaian stok (tambah) '.$item->name.' ('.$diff.' '.$item->unit.')',
                ]);

                Transaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'expense',
                    'category' => 'Pembelian Alat',
                    'amount' => $totalCost,
                    'transaction_date' => now()->toDateString(),
                    'description' => 'Pembelian stok tambahan '.$item->name.' ('.$diff.' '.$item->unit.')',
                    'reference_number' => 'INV-IN-'.$inventoryIn->id,
                ]);
            }
        });

        Cache::forget('inventory.purchase_stats');
        Cache::forget('inventory.categories');

        return redirect()->route('inventory.index', ['type_group' => $validated['type_group']])->with('success', __('Item updated successfully.'));
    }

    public function destroyItem(InventoryItem $item)
    {
        $item->delete();

        return redirect()->route('inventory.index')->with('success', __('Item deleted successfully.'));
    }

    public function updatePickup(Request $request, InventoryTransaction $transaction)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // Revert old stock
        $item = $transaction->item;
        $item->increment('stock', $transaction->quantity);

        // Check new stock availability (old + current stock)
        if ($item->stock < $validated['quantity']) {
            $item->decrement('stock', $transaction->quantity); // Re-revert if failed

            return back()->withErrors(['quantity' => __('Not enough stock available.')]);
        }

        // Apply new stock
        $item->decrement('stock', $validated['quantity']);

        $transaction->update([
            'quantity' => $validated['quantity'],
            'description' => $validated['description'],
        ]);

        // Update Finance Transaction if exists
        $financeTx = Transaction::where('reference_number', 'INV-OUT-'.$transaction->id)->first();
        if ($financeTx && $item->price > 0) {
            $financeTx->update([
                'amount' => $validated['quantity'] * $item->price,
                'description' => 'Pengurus mengambil '.$validated['quantity'].' '.$item->unit.' '.$item->name,
            ]);
        }

        return redirect()->route('inventory.index')->with('success', __('Pickup updated successfully.'));
    }

    public function destroyPickup(InventoryTransaction $transaction)
    {
        // Return stock
        $transaction->item->increment('stock', $transaction->quantity);

        // Delete auto-created assets
        Asset::whereJsonContains('meta_data->source_transaction_id', $transaction->id)->delete();

        // Delete related finance transaction if exists
        Transaction::where('reference_number', 'INV-OUT-'.$transaction->id)->delete();

        $transaction->delete();

        return redirect()->route('inventory.index')->with('success', __('Pickup deleted and stock returned.'));
    }

    public function exportPdf()
    {
        $items = InventoryItem::all();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('inventory.pdf', compact('items'));

        return $pdf->download('inventory_report.pdf');
    }

    public function exportExcel()
    {
        return response()->streamDownload(function () {
            $writer = new Writer;
            $writer->openToFile('php://output');

            // Header
            $writer->addRow(Row::fromValues(['Name', 'Category', 'Type', 'Brand', 'Model', 'Description', 'Stock', 'Unit', 'Price']));

            // Data
            InventoryItem::chunk(100, function ($items) use ($writer) {
                foreach ($items as $item) {
                    $writer->addRow(Row::fromValues([
                        $item->name,
                        $item->category,
                        $item->type,
                        $item->brand,
                        $item->model,
                        $item->description,
                        $item->stock,
                        $item->unit,
                        $item->price,
                    ]));
                }
            });

            $writer->close();
        }, 'inventory_report.xlsx');
    }

    public function exportMovementExcel(Request $request)
    {
        return response()->streamDownload(function () use ($request) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'Tanggal',
                'Jenis',
                'Tipe Item',
                'User',
                'Item',
                'Qty',
                'Unit',
                'Unit Cost',
                'Total Cost',
                'Sumber',
                'Supplier',
                'Ref No',
                'Deskripsi',
            ]));

            $this->buildMovementQuery($request)
                ->orderBy('id')
                ->chunk(200, function ($movements) use ($writer) {
                    foreach ($movements as $movement) {
                        $writer->addRow(Row::fromValues([
                            optional($movement->created_at)->format('Y-m-d H:i:s'),
                            strtoupper((string) $movement->type),
                            $movement->item?->type_group,
                            $movement->user?->name,
                            $movement->item?->name,
                            (int) $movement->quantity,
                            $movement->item?->unit,
                            (float) ($movement->unit_cost ?? 0),
                            (float) ($movement->total_cost ?? 0),
                            $movement->source_type,
                            $movement->supplier_name,
                            $movement->reference_no,
                            $movement->description,
                        ]));
                    }
                });

            $writer->close();
        }, 'inventory_stock_movements_'.now()->format('Ymd_His').'.xlsx');
    }

    public function downloadTemplate()
    {
        if (! Auth::user()->hasPermission('inventory.view')) {
            abort(403, 'Unauthorized action.');
        }

        return response()->streamDownload(function () {
            $writer = new Writer;
            $writer->openToFile('php://output');

            // Header resmi inventaris
            $writer->addRow(Row::fromValues([
                'Nama Barang',
                'Kategori',
                'Tipe',
                'Merek',
                'Model',
                'Deskripsi / Spesifikasi',
                'Stok Awal',
                'Satuan',
                'Harga Beli / Nilai Aset',
            ]));

            // Data Contoh Perlengkapan & Inventaris Riil Toko/Kantor
            $writer->addRow(Row::fromValues([
                'Printer Kasir Epson TM-U220',
                'Equipment',
                'Printer Kasir',
                'Epson',
                'TM-U220B',
                'Printer kasir struk dot matrix auto-cutter',
                3,
                'Unit',
                2150000,
            ]));
            $writer->addRow(Row::fromValues([
                'Barcode Scanner 2D Honeywell',
                'Equipment',
                'Scanner',
                'Honeywell',
                'HF680',
                'Scanner barcode 1D/2D desktop handsfree USB',
                2,
                'Unit',
                1250000,
            ]));
            $writer->addRow(Row::fromValues([
                'Mesin Laminating A3 Joyko',
                'Equipment',
                'Laminator',
                'Joyko',
                'LM-01',
                'Mesin laminating dokumen & foto hingga ukuran A3',
                2,
                'Unit',
                850000,
            ]));
            $writer->addRow(Row::fromValues([
                'Kertas Struk Kasir Thermal 80x80',
                'Material',
                'Consumable',
                'Generic',
                'Thermal 80',
                'Roll thermal paper kasir POS 80mm x 80mm',
                100,
                'Roll',
                12000,
            ]));

            $writer->close();
        }, 'template_import_inventaris.xlsx');
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

    public function importExcel(Request $request)
    {
        if (! Auth::user()->hasPermission('inventory.view')) {
            abort(403, 'Unauthorized action.');
        }

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
                    break;
                }
                $reader->close();
            }

            if (empty($rowsData)) {
                return back()->withErrors(['file' => __('File inventaris kosong.')]);
            }

            $headerMap = [
                'name' => null,
                'category' => null,
                'type' => null,
                'brand' => null,
                'model' => null,
                'description' => null,
                'stock' => null,
                'unit' => null,
                'price' => null,
            ];

            $hasHeader = false;
            $firstRow = $rowsData[0] ?? [];

            foreach ($firstRow as $colIdx => $colName) {
                $norm = strtolower(trim($colName));
                if ($norm === '') continue;

                if (str_contains($norm, 'nilai') || str_contains($norm, 'aset') || str_contains($norm, 'harga') || str_contains($norm, 'price') || str_contains($norm, 'beli')) {
                    $headerMap['price'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'stok') || str_contains($norm, 'stock') || str_contains($norm, 'qty') || str_contains($norm, 'jumlah')) {
                    $headerMap['stock'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'kategori') || str_contains($norm, 'category') || str_contains($norm, 'kelompok')) {
                    $headerMap['category'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'satuan') || str_contains($norm, 'unit') || str_contains($norm, 'uom')) {
                    $headerMap['unit'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'merek') || str_contains($norm, 'merk') || str_contains($norm, 'brand')) {
                    $headerMap['brand'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'model') || str_contains($norm, 'seri')) {
                    $headerMap['model'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'tipe') || str_contains($norm, 'type') || str_contains($norm, 'jenis')) {
                    $headerMap['type'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'deskripsi') || str_contains($norm, 'spesifikasi') || str_contains($norm, 'keterangan') || str_contains($norm, 'desc')) {
                    $headerMap['description'] = $colIdx;
                    $hasHeader = true;
                } elseif (str_contains($norm, 'nama') || str_contains($norm, 'name') || str_contains($norm, 'barang') || str_contains($norm, 'item')) {
                    $headerMap['name'] = $colIdx;
                    $hasHeader = true;
                }
            }

            if (! $hasHeader || $headerMap['name'] === null) {
                $headerMap = [
                    'name' => 0,
                    'category' => 1,
                    'type' => 2,
                    'brand' => 3,
                    'model' => 4,
                    'description' => 5,
                    'stock' => 6,
                    'unit' => 7,
                    'price' => 8,
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
                    continue;
                }

                $name = $headerMap['name'] !== null && isset($row[$headerMap['name']]) ? trim($row[$headerMap['name']]) : '';
                if ($name === '') {
                    $skippedCount++;
                    continue;
                }

                $category = $headerMap['category'] !== null && isset($row[$headerMap['category']]) ? trim($row[$headerMap['category']]) : 'Equipment';
                $type = $headerMap['type'] !== null && isset($row[$headerMap['type']]) ? trim($row[$headerMap['type']]) : null;
                $brand = $headerMap['brand'] !== null && isset($row[$headerMap['brand']]) ? trim($row[$headerMap['brand']]) : null;
                $model = $headerMap['model'] !== null && isset($row[$headerMap['model']]) ? trim($row[$headerMap['model']]) : null;
                $description = $headerMap['description'] !== null && isset($row[$headerMap['description']]) ? trim($row[$headerMap['description']]) : null;
                $stock = $headerMap['stock'] !== null && isset($row[$headerMap['stock']]) ? (int) $this->cleanNumeric($row[$headerMap['stock']]) : 0;
                $unit = $headerMap['unit'] !== null && isset($row[$headerMap['unit']]) && trim($row[$headerMap['unit']]) !== '' ? trim($row[$headerMap['unit']]) : 'Unit';
                $price = $headerMap['price'] !== null && isset($row[$headerMap['price']]) ? $this->cleanNumeric($row[$headerMap['price']]) : 0.0;

                // Tentukan type_group: tool (aset/alat kerja) vs material (habis pakai)
                $catLower = strtolower($category);
                $typeGroup = (str_contains($catLower, 'tool') || str_contains($catLower, 'equipment') || str_contains($catLower, 'alat') || str_contains($catLower, 'mesin') || str_contains($catLower, 'kendaraan') || str_contains($catLower, 'vehicle') || str_contains($catLower, 'aset')) ? 'tool' : 'material';

                $existing = InventoryItem::where('name', $name)->first();

                if ($existing) {
                    $existing->update([
                        'category' => $category ?: $existing->category,
                        'type_group' => $typeGroup,
                        'type' => $type ?: $existing->type,
                        'brand' => $brand ?: $existing->brand,
                        'model' => $model ?: $existing->model,
                        'description' => $description ?: $existing->description,
                        'stock' => $stock,
                        'unit' => $unit ?: $existing->unit,
                        'price' => $price > 0 ? $price : $existing->price,
                    ]);
                    $updatedCount++;
                } else {
                    InventoryItem::create([
                        'name' => $name,
                        'category' => $category,
                        'type_group' => $typeGroup,
                        'type' => $type,
                        'brand' => $brand,
                        'model' => $model,
                        'description' => $description,
                        'stock' => $stock,
                        'unit' => $unit,
                        'price' => $price,
                    ]);
                    $createdCount++;
                }
            }

            $totalSuccess = $createdCount + $updatedCount;
            $msg = "Import data & stok inventaris selesai: {$totalSuccess} item diproses ({$createdCount} item baru ditambahkan, {$updatedCount} item diperbarui).";
            if ($skippedCount > 0) {
                $msg .= " ({$skippedCount} baris kosong dilewati).";
            }

            return redirect()->route('inventory.index')->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Import gagal: ' . $e->getMessage()]);
        }
    }
}
