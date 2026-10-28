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
        $this->info('Memulai sinkronisasi data teknisi dari dsBilling & riwayat absensi...');

        $techRole = Role::whereIn('name', ['field-technician', 'technician'])->first()
            ?: Role::whereIn('name', ['wash-operator', 'operator', 'staff'])->first();

        // 1. Master Data Teknisi Resmi dari dsBilling (https://mstore.mstore.id/admin/employee)
        $dsBillingEmployees = [
            [
                'nik' => '3602212302060001',
                'name' => 'Fajar Lusio Muslim',
                'alias' => ['fajar_lusio_muslim', 'Fajar'],
                'email' => 'flusiomuslim@gmail.com',
                'phone' => '6285719445761',
                'join_date' => '2026-10-28',
            ],
            [
                'nik' => '360207090',
                'name' => 'Irpan Candra Kurniawan',
                'alias' => ['IRFAN/GUNDUL', 'gundul', 'Irfan', 'Irpan'],
                'email' => 'irfancandra435@gmail.com',
                'phone' => '6283830256854',
                'join_date' => '2026-10-27',
            ],
            [
                'nik' => '3602212006040002',
                'name' => 'IMRON SADEWO',
                'alias' => ['sadewo', 'Imron'],
                'email' => 'uu872964@gmail.com',
                'phone' => '6283823989017',
                'join_date' => '2026-10-26',
            ],
            [
                'nik' => '3602210909050004',
                'name' => 'RIPKY',
                'alias' => ['RIFKI', 'rifki', 'Ripky', 'Ripki'],
                'email' => 'kiko92738@gmail.com',
                'phone' => '6285773497738',
                'join_date' => '2026-01-01',
            ],
            [
                'nik' => '3602011011030005',
                'name' => 'MUHAMAD DEDI MULYADI',
                'alias' => ['DEDI MULYADI', 'dedi_mulyadi', 'Dedi Mulyadi'],
                'email' => 'dedi0812@gmail.com',
                'phone' => '6283807502062',
                'join_date' => '2026-10-26',
            ],
            [
                'nik' => '3602200000000',
                'name' => 'Sarip',
                'alias' => ['SARIP', 'sarip', 'Sarif'],
                'email' => 'sarif@gmail.com',
                'phone' => '6281400398753',
                'join_date' => '2026-10-26',
            ],
            [
                'nik' => '3602211508060001',
                'name' => 'MUHAMAD ROUP',
                'alias' => ['roup', 'Muhamad Roup', 'Roup'],
                'email' => 'roupdd@gmail.com',
                'phone' => '6285692253390',
                'join_date' => '2026-10-26',
            ],
        ];

        $updatedDsCount = 0;
        foreach ($dsBillingEmployees as $ds) {
            $digits = preg_replace('/\D+/', '', $ds['phone']);
            $last8 = strlen($digits) >= 8 ? substr($digits, -8) : $digits;

            // Cari employee di MStore berdasarkan NIK, Phone, atau Nama/Alias
            $employee = Employee::where('nik', $ds['nik'])
                ->orWhere(function ($q) use ($last8) {
                    $q->where('phone', 'like', "%{$last8}%");
                })
                ->orWhereRaw('LOWER(TRIM(full_name)) = ?', [strtolower(trim($ds['name']))])
                ->first();

            if (! $employee && ! empty($ds['alias'])) {
                foreach ($ds['alias'] as $alias) {
                    $employee = Employee::whereRaw('LOWER(TRIM(full_name)) = ?', [strtolower(trim($alias))])->first();
                    if ($employee) break;
                }
            }

            // Cari atau buat User terkait
            $user = $employee?->user;
            if (! $user) {
                $user = User::where('email', $ds['email'])
                    ->orWhere(function ($q) use ($last8) {
                        $q->where('phone', 'like', "%{$last8}%");
                    })
                    ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($ds['name']))])
                    ->first();

                if (! $user && ! empty($ds['alias'])) {
                    foreach ($ds['alias'] as $alias) {
                        $user = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($alias))])->first();
                        if ($user) break;
                    }
                }
            }

            if (! $user) {
                $uname = User::generateUniqueUsername($ds['name'], $ds['email']);
                $user = User::create([
                    'name' => $ds['name'],
                    'username' => $uname,
                    'email' => $ds['email'],
                    'phone' => $ds['phone'],
                    'job_title' => 'Teknisi Lapangan',
                    'role_id' => $techRole?->id,
                    'password' => bcrypt('Password123!'),
                    'is_active' => true,
                ]);
            } else {
                $user->name = $ds['name'];
                $user->email = $ds['email'];
                $user->phone = $ds['phone'];
                $user->job_title = 'Teknisi Lapangan';
                if ($techRole && empty($user->role_id)) {
                    $user->role_id = $techRole->id;
                }
                $user->saveQuietly();
            }

            if ($employee) {
                $employee->nik = $ds['nik'];
                $employee->full_name = $ds['name'];
                $employee->email = $ds['email'];
                $employee->phone = $ds['phone'];
                $employee->position = 'Teknisi Lapangan';
                $employee->department = 'Teknis & Jaringan';
                $employee->join_date = $ds['join_date'];
                if ($user) {
                    $employee->user_id = $user->id;
                }
                $employee->saveQuietly();
                $updatedDsCount++;
                $this->info(" √ Sinkronkan data dsBilling: {$ds['name']} (NIK: {$ds['nik']})");
            } else {
                $newEmployee = Employee::create([
                    'nik' => $ds['nik'],
                    'full_name' => $ds['name'],
                    'position' => 'Teknisi Lapangan',
                    'department' => 'Teknis & Jaringan',
                    'employment_status' => 'Tetap',
                    'join_date' => $ds['join_date'],
                    'email' => $ds['email'],
                    'phone' => $ds['phone'],
                    'user_id' => $user->id,
                ]);
                $updatedDsCount++;
                $this->info(" + Tambahkan data dsBilling baru: {$ds['name']} (NIK: {$ds['nik']})");
            }
        }

        // 2. Ambil semua user_id unik yang pernah absen dan pastikan punya employee record
        $userIdsWithAttendance = TechnicianAttendance::whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $this->line("Memeriksa {$userIdsWithAttendance->count()} karyawan dari riwayat absensi...");

        $createdEmployees = 0;
        $updatedRoles = 0;

        foreach ($userIdsWithAttendance as $userId) {
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            // Pastikan User memiliki role yang valid (bukan null)
            if (empty($user->role_id) && $techRole) {
                $user->role_id = $techRole->id;
                $user->saveQuietly();
                $updatedRoles++;
            }

            // Pastikan Karyawan tercatat di tabel `employees`
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
            } elseif (empty($employee->user_id)) {
                $employee->user_id = $user->id;
                $employee->saveQuietly();
            }
        }

        $this->newLine();
        $this->info("Sinkronisasi dsBilling & Karyawan selesai!");
        $this->line(" - Data Teknisi dsBilling disinkronkan: <info>{$updatedDsCount}</info>");
        $this->line(" - Data Karyawan Absensi Baru dibuat: <info>{$createdEmployees}</info>");
        $this->line(" - Role User diperbarui: <info>{$updatedRoles}</info>");

        return Command::SUCCESS;
    }
}
