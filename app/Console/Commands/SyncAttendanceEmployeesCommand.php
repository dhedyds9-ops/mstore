<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Role;
use App\Models\TechnicianAttendance;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAttendanceEmployeesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan data teknisi dari absensi ke Data Karyawan, Kasbon, dan Absensi Harian';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai sinkronisasi data teknisi dari riwayat absensi...');

        $techRole = Role::whereIn('name', ['field-technician', 'technician', 'wash-operator'])->first();

        // Ambil semua user_id unik yang pernah absen
        $userIdsWithAttendance = TechnicianAttendance::whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $this->line("Ditemukan {$userIdsWithAttendance->count()} karyawan dari data absensi.");

        $createdEmployees = 0;
        $updatedRoles = 0;

        foreach ($userIdsWithAttendance as $userId) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            // 1. Pastikan User memiliki role yang valid (bukan null)
            if (empty($user->role_id) && $techRole) {
                $user->role_id = $techRole->id;
                $user->saveQuietly();
                $updatedRoles++;
                $this->line(" - Perbarui role User: {$user->name} -> {$techRole->label} ({$techRole->name})");
            }

            // 2. Pastikan Karyawan tercatat di tabel `employees`
            $employee = Employee::where('user_id', $user->id)
                ->orWhereRaw('LOWER(TRIM(full_name)) = ?', [strtolower(trim($user->name))])
                ->first();

            if (! $employee) {
                $employee = Employee::create([
                    'nik' => 'AUTO-NIK-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'full_name' => $user->name,
                    'position' => 'Teknisi Lapangan',
                    'department' => 'Teknis & Jaringan',
                    'employment_status' => 'Tetap',
                    'join_date' => $user->created_at ?? now(),
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'user_id' => $user->id,
                ]);
                $createdEmployees++;
                $this->info(" + Tambahkan ke Data Karyawan: {$user->name} (Dept: Teknis & Jaringan)");
            } elseif (empty($employee->user_id)) {
                $employee->user_id = $user->id;
                $employee->saveQuietly();
                $this->line(" * Tautkan akun User ke Karyawan: {$employee->full_name}");
            }
        }

        $this->newLine();
        $this->info("Sinkronisasi selesai!");
        $this->line(" - Data Karyawan Baru dibuat: <info>{$createdEmployees}</info>");
        $this->line(" - Role User diperbarui: <info>{$updatedRoles}</info>");
        $this->line("Sekarang seluruh teknisi akan otomatis muncul di Data Karyawan, Absensi Harian, Kasbon, dan Slip Gaji.");

        return Command::SUCCESS;
    }
}
