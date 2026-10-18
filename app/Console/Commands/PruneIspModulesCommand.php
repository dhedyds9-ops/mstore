<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Support\ModuleGate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PruneIspModulesCommand extends Command
{
    protected $signature = 'modules:prune-isp
        {--force : Hapus permanen tanpa konfirmasi}
        {--dry-run : Hanya tampilkan rencana (default bila --force tidak diberikan)}';

    protected $description = 'Cabut permission & bersihkan data modul ISP/Jaringan (RADIUS, MikroTik, OLT, GenieACS, Tiket) sesuai config/module_gating.php';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run') || ! $this->option('force');

        $prefixes = collect(config('module_gating.permission_prefixes', []))
            ->filter(fn ($p, $module) => ! ModuleGate::isEnabled($module))
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        $permissions = Permission::query()
            ->when($prefixes, function ($query) use ($prefixes) {
                $query->where(function ($sub) use ($prefixes) {
                    foreach ($prefixes as $prefix) {
                        $sub->orWhere('name', 'like', $prefix.'%');
                    }
                });
            })
            ->get();

        $tables = collect(config('module_gating.cleanup_tables', []))
            ->filter(fn (string $table): bool => Schema::hasTable($table))
            ->values();

        $this->info('Modul aktif: '.implode(', ', ModuleGate::enabled()));
        $this->newLine();
        $this->line('Permission ISP yang akan dicabut & dihapus: <comment>'.$permissions->count().'</comment>');

        $rows = $tables->map(fn (string $table): array => [$table, DB::table($table)->count()])->all();
        $this->line('Tabel data ISP yang akan dikosongkan: <comment>'.$tables->count().'</comment>');
        if ($rows !== []) {
            $this->table(['Tabel', 'Jumlah Baris'], $rows);
        }

        if ($dryRun) {
            $this->warn('DRY-RUN: tidak ada data yang diubah. Jalankan `php artisan modules:prune-isp --force` untuk eksekusi.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Hapus PERMANEN semua data di atas? Pastikan Anda sudah backup database.', false)) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($permissions, $tables): void {
            foreach ($permissions as $permission) {
                $permission->roles()->detach();
            }

            if ($permissions->isNotEmpty()) {
                Permission::whereIn('id', $permissions->pluck('id'))->delete();
            }

            Schema::disableForeignKeyConstraints();
            try {
                foreach ($tables as $table) {
                    DB::table($table)->delete();
                }
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });

        Cache::forget('sidebar.menu.tree.v14');
        Cache::forget('sidebar.menu.tree.v15');
        Cache::forget('sidebar.menu.tree.v16');
        foreach (Role::query()->pluck('id') as $roleId) {
            Cache::forget("sidebar.permission_map.role.{$roleId}");
        }

        $this->info('Selesai. Permission & data modul ISP telah dibersihkan.');

        return self::SUCCESS;
    }
}
