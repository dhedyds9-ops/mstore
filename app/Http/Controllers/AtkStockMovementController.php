<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AtkCashMovement;
use App\Models\AtkProduct;
use App\Models\AtkStockMovement;
use App\Models\AtkSupplier;
use App\Models\Cash;
use App\Models\Transaction;
use App\Services\AccountingPoster;
use App\Services\Atk\AtkSpreadsheetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AtkStockMovementController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:atk.view', only: ['index', 'export']),
            new Middleware('permission:atk.manage', only: ['create', 'store', 'update', 'destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = AtkStockMovement::with(['product.categoryModel', 'supplier', 'user']);

        // 1. Filter Tipe (in, out, adjustment)
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // 2. Filter Produk
        if ($request->filled('product_id')) {
            $query->where('atk_product_id', $request->get('product_id'));
        }

        // 3. Filter Supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }

        // 4. Pencarian (Nama produk, kode, barcode, no faktur, catatan, supplier)
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                })
                ->orWhereHas('supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('invoice_number', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // 5. Filter Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->get('end_date'));
        }

        // Pengurutan
        $movements = $query->latest('created_at')->paginate(20)->appends($request->query());

        // Statistik Bulan Ini
        $thisMonth = Carbon::now()->startOfMonth();
        $baseMonthQuery = AtkStockMovement::where('created_at', '>=', $thisMonth);

        $stats = [
            'total_in_month' => (int) (clone $baseMonthQuery)->where('type', 'in')->sum('quantity'),
            'total_cost_month' => (float) (clone $baseMonthQuery)->where('type', 'in')->sum('total_cost'),
            'total_in_count' => (int) (clone $baseMonthQuery)->where('type', 'in')->count(),
            'total_out_month' => (int) (clone $baseMonthQuery)->where('type', 'out')->sum('quantity'),
        ];

        $products = AtkProduct::orderBy('name')->get(['id', 'name', 'code', 'stock', 'unit', 'cost_price', 'price']);
        $suppliers = AtkSupplier::orderBy('name')->get(['id', 'name']);

        return view('atk.stock-movements.index', compact('movements', 'stats', 'products', 'suppliers'));
    }

    public function create(Request $request)
    {
        $products = AtkProduct::orderBy('name')->get(['id', 'name', 'code', 'barcode', 'stock', 'unit', 'cost_price', 'price']);
        $suppliers = AtkSupplier::orderBy('name')->get(['id', 'name', 'phone']);
        $selectedProductId = $request->get('product_id');

        return view('atk.stock-movements.create', compact('products', 'suppliers', 'selectedProductId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'atk_product_id' => 'required|exists:atk_products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable',
            'supplier_name' => 'nullable|string|max:255',
            'movement_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'pay_from_cash' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $product = AtkProduct::lockForUpdate()->findOrFail($validated['atk_product_id']);

            // Supplier: buat jika nama baru diketik
            $supplierId = ! empty($validated['supplier_id']) ? (int) $validated['supplier_id'] : null;
            if (empty($supplierId) && ! empty($validated['supplier_name'])) {
                try {
                    $sup = AtkSupplier::firstOrCreate(['name' => trim($validated['supplier_name'])]);
                    $supplierId = $sup->id;
                } catch (\Throwable $e) {}
            }

            $quantity = (int) $validated['quantity'];
            $unitPrice = isset($validated['unit_price']) && $validated['unit_price'] !== ''
                ? (float) $validated['unit_price']
                : (float) ($product->cost_price ?? 0);
            $totalCost = $quantity * $unitPrice;

            $before = (int) ($product->stock ?? 0);
            $after = $before + $quantity;

            // Update stok dan HPP modal produk
            $product->stock = $after;
            $product->current_stock = $after;
            if ($unitPrice > 0) {
                $product->cost_price = $unitPrice;
            }
            if ($supplierId) {
                $product->supplier_id = $supplierId;
            }
            $product->save();

            // Format tanggal mutasi
            $movementTime = now()->format('H:i:s');
            $createdAt = Carbon::parse($validated['movement_date'])->setTimeFromTimeString($movementTime);

            // Buat record mutasi stok masuk
            $movement = AtkStockMovement::create([
                'atk_product_id' => $product->id,
                'type' => 'in',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_cost' => $totalCost,
                'supplier_id' => $supplierId,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => 'purchase',
                'reference_id' => null,
                'notes' => $validated['notes'] ?: ('Barang masuk: +' . $quantity . ' ' . ($product->unit ?: 'Pcs')),
                'user_id' => Auth::id(),
                'created_at' => $createdAt,
            ]);

            // Jika dibayar tunai dari Kas Toko (Kas Utama)
            if ($request->boolean('pay_from_cash') && $totalCost > 0) {
                $refNumber = 'ATK-STK-' . now()->format('YmdHis');
                $expenseDesc = "Pembelian stok ATK: {$quantity} " . ($product->unit ?: 'Pcs') . " {$product->name}" . ($validated['invoice_number'] ? " (Faktur: {$validated['invoice_number']})" : "");

                // 1. Buat record transaksi expense
                $expense = Transaction::create([
                    'user_id' => Auth::id(),
                    'type' => 'expense',
                    'category' => 'Pembelian Stok ATK',
                    'amount' => $totalCost,
                    'description' => $expenseDesc,
                    'transaction_date' => $validated['movement_date'],
                    'reference_number' => $refNumber,
                ]);

                // 2. Potong Kas Utama
                $cash = Cash::firstOrCreate(['name' => 'Kas Utama'], ['balance' => 0]);
                $cashBalanceBefore = (float) $cash->balance;
                $cash->balance = $cashBalanceBefore - $totalCost;
                $cash->save();

                // 3. Catat di AtkCashMovement
                AtkCashMovement::create([
                    'atk_cash_register_id' => null,
                    'movement_type' => 'expense',
                    'amount' => $totalCost,
                    'balance_before' => $cashBalanceBefore,
                    'balance_after' => $cash->balance,
                    'reference_type' => 'transaction',
                    'reference_id' => $expense->id,
                    'description' => $expenseDesc,
                    'created_by' => Auth::id(),
                ]);

                // 4. Jurnal Akuntansi otomatis
                $expAccId = Account::where('code', '6004')->value('id');
                $cashAccId = Account::where('code', '1001')->value('id');
                if ($expAccId && $cashAccId) {
                    try {
                        $poster = app(AccountingPoster::class);
                        $poster->post(
                            $refNumber,
                            $validated['movement_date'],
                            $expenseDesc,
                            [
                                ['account_id' => $expAccId, 'debit' => $totalCost, 'credit' => 0, 'unit' => 'ATK'],
                                ['account_id' => $cashAccId, 'debit' => 0, 'credit' => $totalCost, 'unit' => 'ATK'],
                            ],
                            null,
                            false
                        );
                    } catch (\Throwable $e) {}
                }

                // Tautkan id expense ke mutasi stok masuk
                $movement->reference_type = 'transaction';
                $movement->reference_id = $expense->id;
                $movement->save();
            }

            $successMsg = "Stok masuk berhasil dicatat! {$product->name} bertambah +{$quantity} " . ($product->unit ?: 'Pcs') . " (Stok fisik saat ini: {$after} " . ($product->unit ?: 'Pcs') . ").";

            if ($request->has('redirect_to') && $request->get('redirect_to') === 'products') {
                return redirect()->route('atk.products.index')->with('success', $successMsg);
            }

            return redirect()->route('atk.stock-movements.index')->with('success', $successMsg);
        });
    }

    public function update(Request $request, AtkStockMovement $stockMovement)
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable',
            'supplier_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'movement_date' => 'nullable|date',
        ]);

        $supplierId = ! empty($validated['supplier_id']) ? (int) $validated['supplier_id'] : null;
        if (empty($supplierId) && ! empty($validated['supplier_name'])) {
            try {
                $sup = AtkSupplier::firstOrCreate(['name' => trim($validated['supplier_name'])]);
                $supplierId = $sup->id;
            } catch (\Throwable $e) {}
        }

        $stockMovement->supplier_id = $supplierId;
        $stockMovement->invoice_number = $validated['invoice_number'] ?? null;
        $stockMovement->notes = $validated['notes'] ?? null;

        if (! empty($validated['movement_date'])) {
            $movementTime = $stockMovement->created_at ? $stockMovement->created_at->format('H:i:s') : now()->format('H:i:s');
            $stockMovement->created_at = Carbon::parse($validated['movement_date'])->setTimeFromTimeString($movementTime);
        }

        $stockMovement->save();

        return redirect()->back()->with('success', 'Data mutasi barang masuk berhasil diperbarui.');
    }

    public function destroy(Request $request, AtkStockMovement $stockMovement)
    {
        if ($stockMovement->type !== 'in') {
            return redirect()->back()->with('error', 'Hanya data Barang Masuk yang dapat dibatalkan melalui menu ini.');
        }

        return DB::transaction(function () use ($stockMovement) {
            $product = AtkProduct::lockForUpdate()->find($stockMovement->atk_product_id);
            $qty = (int) $stockMovement->quantity;

            if ($product) {
                $currentStock = (int) ($product->stock ?? 0);
                if ($currentStock < $qty) {
                    return redirect()->back()->with('error', "Gagal membatalkan: Sisa stok saat ini ({$currentStock}) lebih kecil dari jumlah masuk yang ingin dibatalkan ({$qty}). Sebagian barang mungkin sudah terjual di POS. Harap lakukan penyesuaian stok manual.");
                }

                $newStock = $currentStock - $qty;
                $product->stock = $newStock;
                $product->current_stock = $newStock;
                $product->save();
            }

            // Rollback Kas jika ada transaksi pengeluaran kas toko terkait
            $expense = null;
            if ($stockMovement->reference_type === 'transaction' && $stockMovement->reference_id) {
                $expense = Transaction::find($stockMovement->reference_id);
            } elseif ($stockMovement->total_cost > 0) {
                // Fallback pencarian transaksi kas lama jika reference_id belum terikat
                $expense = Transaction::where('type', 'expense')
                    ->where('category', 'Pembelian Stok ATK')
                    ->where('amount', $stockMovement->total_cost)
                    ->whereDate('transaction_date', $stockMovement->created_at ? $stockMovement->created_at->toDateString() : now()->toDateString())
                    ->latest()
                    ->first();
            }

            $refundAmount = 0;
            if ($expense) {
                $refundAmount = (float) $expense->amount;
                $cash = Cash::where('name', 'Kas Utama')->first() ?? Cash::first();
                if ($cash) {
                    $cashBalanceBefore = (float) $cash->balance;
                    $cash->balance = $cashBalanceBefore + $refundAmount;
                    $cash->save();

                    // Catat mutasi pengembalian dana kas
                    AtkCashMovement::create([
                        'atk_cash_register_id' => null,
                        'movement_type' => 'income',
                        'amount' => $refundAmount,
                        'balance_before' => $cashBalanceBefore,
                        'balance_after' => $cash->balance,
                        'reference_type' => 'transaction_cancel',
                        'reference_id' => $expense->id,
                        'description' => "Pengembalian dana batal barang masuk: {$stockMovement->notes}",
                        'created_by' => Auth::id(),
                    ]);
                }

                // Jurnal pembalik akuntansi jika ada
                if (! empty($expense->reference_number)) {
                    $expAccId = Account::where('code', '6004')->value('id');
                    $cashAccId = Account::where('code', '1001')->value('id');
                    if ($expAccId && $cashAccId) {
                        try {
                            $poster = app(AccountingPoster::class);
                            $poster->post(
                                'REV-' . $expense->reference_number,
                                now()->toDateString(),
                                "Batal Pembelian Stok ATK (Ref: {$expense->reference_number})",
                                [
                                    ['account_id' => $cashAccId, 'debit' => $refundAmount, 'credit' => 0, 'unit' => 'ATK'],
                                    ['account_id' => $expAccId, 'debit' => 0, 'credit' => $refundAmount, 'unit' => 'ATK'],
                                ],
                                null,
                                false
                            );
                        } catch (\Throwable $e) {}
                    }
                }

                $expense->delete();
            }

            $productName = $product ? $product->name : 'Produk';
            $stockMovement->delete();

            $msg = "Transaksi barang masuk berhasil dibatalkan dan dihapus! Stok {$productName} telah dikurangi kembali -{$qty}.";
            if ($refundAmount > 0) {
                $msg .= " Saldo Kas Toko telah dikembalikan sebesar Rp " . number_format($refundAmount, 0, ',', '.') . ".";
            }

            return redirect()->route('atk.stock-movements.index')->with('success', $msg);
        });
    }

    public function export(Request $request, AtkSpreadsheetService $spreadsheet)
    {
        $query = AtkStockMovement::with(['product', 'supplier', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }
        if ($request->filled('product_id')) {
            $query->where('atk_product_id', $request->get('product_id'));
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->get('end_date'));
        }

        $movements = $query->latest('created_at')->get();
        $format = $request->get('format', 'xlsx');

        $headers = [
            'Tanggal',
            'Tipe Mutasi',
            'Kode Barang',
            'Nama Produk',
            'Qty Masuk/Keluar',
            'Satuan',
            'Harga Satuan (Rp)',
            'Total Nilai (Rp)',
            'Stok Sebelum',
            'Stok Sesudah',
            'Supplier',
            'No. Faktur / Nota',
            'Petugas / Kasir',
            'Catatan',
        ];

        $rows = [];
        foreach ($movements as $m) {
            $typeLabel = match ($m->type) {
                'in' => 'Barang Masuk',
                'out' => 'Barang Keluar (POS)',
                'adjustment' => 'Penyesuaian Stok',
                default => strtoupper($m->type),
            };

            $rows[] = [
                $m->created_at ? $m->created_at->format('d/m/Y H:i') : '',
                $typeLabel,
                $m->product?->code ?: '',
                $m->product?->name ?: 'Produk Telah Dihapus',
                (int) $m->quantity,
                $m->product?->unit ?: 'Pcs',
                (float) ($m->unit_price ?? 0),
                (float) ($m->total_cost ?? 0),
                (int) $m->balance_before,
                (int) $m->balance_after,
                $m->supplier?->name ?: '-',
                $m->invoice_number ?: '-',
                $m->user?->name ?: 'Sistem',
                $m->notes ?: '-',
            ];
        }

        $filename = 'riwayat_mutasi_barang_atk_' . now()->format('Ymd_His');
        return $spreadsheet->download($filename, $headers, $rows, $format);
    }
}
