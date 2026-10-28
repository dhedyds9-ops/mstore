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
                'alias' => ['fajar_lusio_muslim', 'Fajar', 'Bombom', 'bombom', 'BOMBOM'],
                'email' => 'flusiomuslim@gmail.com',
                'phone' => '6285719445761',
                'join_date' => '2026-10-28',
            ],
            [
                'nik' => '360207090',
                'name' => 'Irpan Candra Kurniawan',
                'alias' => ['IRFAN/GUNDUL', 'gundul', 'Irfan', 'Irpan', 'Gundul'],
                'email' => 'irfancandra435@gmail.com',
                'phone' => '6283830256854',
                'join_date' => '2026-10-27',
            ],
            [
                'nik' => '3602212006040002',
                'name' => 'IMRON SADEWO',
                'alias' => ['sadewo', 'Imron', 'Sadewo'],
                'email' => 'uu872964@gmail.com',
                'phone' => '6283823989017',
                'join_date' => '2026-10-26',
            ],
            [
                'nik' => '3602210909050004',
                'name' => 'RIPKY',
                'alias' => ['RIFKI', 'rifki', 'Ripky', 'Ripki', 'Rifky', 'rifky'],
                'email' => 'kiko92738@gmail.com',
                'phone' => '6285773497738',
                'join_date' => '2026-01-01',
            ],
            [
                'nik' => '3602011011030005',
                'name' => 'MUHAMAD DEDI MULYADI',
                'alias' => ['DEDI MULYADI', 'dedi_mulyadi', 'Dedi Mulyadi', 'Dedi', 'dedi'],
                'email' => 'dedi0812@gmail.com',
                'phone' => '6283807502062',
                'join_date' => '2026-10-26',
            ],
            [
                'nik' => '3602200000000',
                'name' => 'Sarip',
                'alias' => ['SARIP', 'sarip', 'Sarif', 'sarif'],
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

            // 1. Kumpulkan semua employee yang cocok (berdasarkan NIK, Phone, atau Nama/Alias)
            $matchingEmployees = Employee::where('nik', $ds['nik'])
                ->orWhere(function ($q) use ($last8) {
                    if (!empty($last8)) {
                        $q->where('phone', 'like', "%{$last8}%");
                    }
                })
                ->orWhereRaw('LOWER(TRIM(full_name)) = ?', [strtolower(trim($ds['name']))])
                ->get();

            if (!empty($ds['alias'])) {
                foreach ($ds['alias'] as $alias) {
                    $aliasEmps = Employee::whereRaw('LOWER(TRIM(full_name)) = ?', [strtolower(trim($alias))])->get();
                    $matchingEmployees = $matchingEmployees->merge($aliasEmps)->unique('id');
                }
            }

            // 2. Kumpulkan semua user yang cocok (berdasarkan Email, Phone, Nama, Username, atau Alias)
            $matchingUsers = User::where('email', $ds['email'])
                ->orWhere(function ($q) use ($last8) {
                    if (!empty($last8)) {
                        $q->where('phone', 'like', "%{$last8}%");
                    }
                })
                ->orWhereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($ds['name']))])
                ->get();

            if (!empty($ds['alias'])) {
                foreach ($ds['alias'] as $alias) {
                    $aliasUsers = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($alias))])
                        ->orWhere('username', strtolower(trim($alias)))
                        ->get();
                    $matchingUsers = $matchingUsers->merge($aliasUsers)->unique('id');
                }
            }

            foreach ($matchingEmployees as $emp) {
                if ($emp->user_id) {
                    $u = User::find($emp->user_id);
                    if ($u) {
                        $matchingUsers = $matchingUsers->merge([$u])->unique('id');
                    }
                }
            }

            // 3. Tentukan Primary User dan lakukan merge jika ada duplikat
            $primaryUser = $matchingUsers->first(fn($u) => $u->email === $ds['email'])
                ?: ($matchingUsers->first(fn($u) => !empty($u->id)) ?: null);

            if (! $primaryUser) {
                $uname = User::generateUniqueUsername($ds['name'], $ds['email']);
                $primaryUser = User::create([
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
                foreach ($matchingUsers as $otherUser) {
                    if ($otherUser->id !== $primaryUser->id) {
                        $this->mergeUser($primaryUser, $otherUser);
                        $this->info(" √ Menggabungkan akun duplikat user '{$otherUser->name}' (ID: {$otherUser->id}) ke '{$primaryUser->name}' (ID: {$primaryUser->id})");
                    }
                }

                $primaryUser->name = $ds['name'];
                $primaryUser->email = $ds['email'];
                $primaryUser->phone = $ds['phone'];
                $primaryUser->job_title = 'Teknisi Lapangan';
                if ($techRole && empty($primaryUser->role_id)) {
                    $primaryUser->role_id = $techRole->id;
                }
                $primaryUser->saveQuietly();
            }

            // 4. Tentukan Primary Employee dan lakukan merge jika ada duplikat
            $primaryEmp = $matchingEmployees->first(fn($e) => $e->nik === $ds['nik'] && !str_starts_with($e->nik, 'AUTO-'))
                ?: ($matchingEmployees->first(fn($e) => $e->user_id === $primaryUser->id)
                    ?: $matchingEmployees->first());

            if (! $primaryEmp) {
                $primaryEmp = Employee::create([
                    'nik' => $ds['nik'],
                    'full_name' => $ds['name'],
                    'position' => 'Teknisi Lapangan',
                    'department' => 'Teknis & Jaringan',
                    'employment_status' => 'Tetap',
                    'join_date' => $ds['join_date'],
                    'email' => $ds['email'],
                    'phone' => $ds['phone'],
                    'user_id' => $primaryUser->id,
                ]);
                $updatedDsCount++;
                $this->info(" + Tambahkan data dsBilling baru: {$ds['name']} (NIK: {$ds['nik']})");
            } else {
                foreach ($matchingEmployees as $otherEmp) {
                    if ($otherEmp->id !== $primaryEmp->id) {
                        $this->mergeEmployee($primaryEmp, $otherEmp);
                        $this->info(" √ Menggabungkan data duplikat karyawan '{$otherEmp->full_name}' (ID: {$otherEmp->id}) ke '{$primaryEmp->full_name}' (ID: {$primaryEmp->id})");
                    }
                }

                $primaryEmp->nik = $ds['nik'];
                $primaryEmp->full_name = $ds['name'];
                $primaryEmp->email = $ds['email'];
                $primaryEmp->phone = $ds['phone'];
                $primaryEmp->position = 'Teknisi Lapangan';
                $primaryEmp->department = 'Teknis & Jaringan';
                $primaryEmp->join_date = $ds['join_date'];
                $primaryEmp->user_id = $primaryUser->id;
                $primaryEmp->saveQuietly();
                $updatedDsCount++;
                $this->info(" √ Sinkronkan data dsBilling: {$ds['name']} (NIK: {$ds['nik']})");
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

    private function mergeUser(User $primaryUser, User $secondaryUser): void
    {
        if ($primaryUser->id === $secondaryUser->id) {
            return;
        }

        $sourceId = $secondaryUser->id;
        $targetId = $primaryUser->id;

        // 1. Absensi Teknisi
        \App\Models\TechnicianAttendance::where('user_id', $sourceId)->update([
            'user_id' => $targetId,
        ]);

        // 2. Tiket Penugasan
        if (\Illuminate\Support\Facades\Schema::hasTable('ticket_user')) {
            $existingTickets = \Illuminate\Support\Facades\DB::table('ticket_user')
                ->where('user_id', $targetId)
                ->pluck('ticket_id')
                ->toArray();

            \Illuminate\Support\Facades\DB::table('ticket_user')
                ->where('user_id', $sourceId)
                ->whereNotIn('ticket_id', $existingTickets)
                ->update(['user_id' => $targetId]);

            \Illuminate\Support\Facades\DB::table('ticket_user')
                ->where('user_id', $sourceId)
                ->delete();
        }

        // 3. Pemasangan Modem (Installations)
        if (\Illuminate\Support\Facades\Schema::hasTable('installations')) {
            \App\Models\Installation::where('technician_id', $sourceId)->update(['technician_id' => $targetId]);
        }

        // 4. Kasbon & Penyesuaian Gaji
        \App\Models\SalaryAdjustment::where('user_id', $sourceId)->update(['user_id' => $targetId]);

        // 5. Kasbon Loans (Pinjaman Angsuran)
        \App\Models\KasbonLoan::where('user_id', $sourceId)->update(['user_id' => $targetId]);

        // 6. Jadwal Kerja
        if (\Illuminate\Support\Facades\Schema::hasTable('technician_schedules')) {
            \Illuminate\Support\Facades\DB::table('technician_schedules')->where('user_id', $sourceId)->update(['user_id' => $targetId]);
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('technician_daily_schedules')) {
            \Illuminate\Support\Facades\DB::table('technician_daily_schedules')->where('user_id', $sourceId)->update(['user_id' => $targetId]);
        }

        // 7. Cuti & Izin
        if (\Illuminate\Support\Facades\Schema::hasTable('leave_requests')) {
            \App\Models\LeaveRequest::where('user_id', $sourceId)->update(['user_id' => $targetId]);
        }

        // 8. Audit Log
        if (\Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
            \Illuminate\Support\Facades\DB::table('audit_logs')->where('user_id', $sourceId)->update(['user_id' => $targetId]);
        }

        // 9. Update employee yang mereferensikan secondaryUser
        Employee::where('user_id', $sourceId)->update(['user_id' => $targetId]);

        $secondaryUser->delete();
    }

    private function mergeEmployee(Employee $primaryEmp, Employee $secondaryEmp): void
    {
        if ($primaryEmp->id === $secondaryEmp->id) {
            return;
        }

        // Salin berkas & foto jika di primary masih kosong
        if (empty($primaryEmp->document_path) && ! empty($secondaryEmp->document_path)) {
            $primaryEmp->document_path = $secondaryEmp->document_path;
        }
        if (empty($primaryEmp->id_card_photo_path) && ! empty($secondaryEmp->id_card_photo_path)) {
            $primaryEmp->id_card_photo_path = $secondaryEmp->id_card_photo_path;
        }

        $primaryEmp->saveQuietly();
        $secondaryEmp->forceDelete();
    }
}
