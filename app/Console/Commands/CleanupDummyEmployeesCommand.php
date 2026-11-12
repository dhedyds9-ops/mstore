<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\TechnicianAttendance;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDummyEmployeesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:cleanup-dummy {--date= : Tanggal format Ymd, default 20261112} {--force : Eksekusi penghapusan tanpa konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan data karyawan dummy / tes yang masuk dari sinkronisasi absensi dsBilling';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateParam = $this->option('date') ?: '20261112';
        $nikPattern = "AUTO-{$dateParam}-%";

        $dummyEmployees = Employee::where('nik', 'like', $nikPattern)->get();

        if ($dummyEmployees->isEmpty()) {
            $this->info("Tidak ditemukan data karyawan dummy dengan pola NIK {$nikPattern}.");
            return Command::SUCCESS;
        }

        $this->warn("Ditemukan {$dummyEmployees->count()} karyawan dummy dengan pola NIK '{$nikPattern}':");
        foreach ($dummyEmployees as $emp) {
            $this->line("- [ID: {$emp->id}] {$emp->full_name} ({$emp->nik}) - Posisi: {$emp->position}");
        }

        if (! $this->option('force') && ! $this->confirm('Apakah Anda yakin ingin menghapus data karyawan dummy di atas beserta akun user & absensi terkait?', true)) {
            $this->info('Pembersihan dibatalkan.');
            return Command::SUCCESS;
        }

        DB::transaction(function () use ($dummyEmployees) {
            $deletedCount = 0;
            foreach ($dummyEmployees as $employee) {
                $userId = $employee->user_id;

                // 1. Hapus absensi terkait
                if ($userId) {
                    TechnicianAttendance::where('user_id', $userId)->delete();
                }

                // 2. Hapus employee
                $employee->forceDelete();

                // 3. Hapus user terkait jika user tersebut dibuat otomatis oleh sync
                if ($userId) {
                    $user = User::find($userId);
                    if ($user) {
                        // Pastikan bukan user penting / admin
                        $roleName = strtolower((string) ($user->role?->name ?? ''));
                        if (! in_array($roleName, ['admin', 'super-admin', 'manager', 'owner'])) {
                            $user->delete();
                        }
                    }
                }

                $deletedCount++;
            }

            $this->info("Berhasil membersihkan {$deletedCount} data karyawan dummy beserta akun dan data absensi terkait.");
        });

        return Command::SUCCESS;
    }
}
