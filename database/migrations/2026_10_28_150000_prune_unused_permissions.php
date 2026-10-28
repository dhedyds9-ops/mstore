<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Assign proper group to accounting.report if empty
        DB::table('permissions')
            ->where('name', 'accounting.report')
            ->where(function ($q) {
                $q->whereNull('group')->orWhere('group', '');
            })
            ->update(['group' => 'Accounting']);

        // 2. Update friendly Indonesian labels for core Car Wash permissions
        $washLabels = [
            'wash.view' => 'Lihat Dashboard Cuci',
            'wash.pos' => 'Akses Kasir Cuci (POS)',
            'wash.manage' => 'Kelola Layanan & Tarif Cuci',
            'wash.report' => 'Laporan Pendapatan & Riwayat Transaksi',
            'wash.shift.view' => 'Kelola Shift & Kas Kasir',
            'wash.expense.view' => 'Kelola Biaya & Pengeluaran',
            'wash.stock.view' => 'Kelola Stok Bahan Cuci',
        ];

        foreach ($washLabels as $name => $label) {
            DB::table('permissions')
                ->where('name', $name)
                ->update(['label' => $label, 'group' => 'Car Wash']);
        }

        // 3. Remove obsolete/unused Car Wash permissions
        $pruneWashPerms = [
            'wash.member.view',
            'wash.member.manage',
            'wash.loyalty.view',
            'wash.loyalty.manage',
            'wash.reward.view',
            'wash.reward.manage',
            'wash.transaction.view',
            'wash.transaction.create',
            'wash.transaction.update',
            'wash.transaction.delete',
            'wash.expense.create',
            'wash.expense.update',
            'wash.expense.delete',
            'wash.expense.approve',
            'wash.shift.open',
            'wash.shift.close',
            'wash.shift.manage',
            'wash.cash.view',
            'wash.cash.manage',
            'wash.closing.view',
            'wash.closing.create',
            'wash.closing.approve',
            'wash.supplier.view',
            'wash.supplier.manage',
            'wash.stock.manage',
            'wash.package.view',
            'wash.package.manage',
        ];

        $permIds = DB::table('permissions')->whereIn('name', $pruneWashPerms)->pluck('id');

        if ($permIds->isNotEmpty()) {
            DB::table('permission_role')->whereIn('permission_id', $permIds)->delete();
            DB::table('permissions')->whereIn('id', $permIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback
    }
};
