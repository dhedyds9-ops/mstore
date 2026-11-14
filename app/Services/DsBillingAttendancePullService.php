<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Role;
use App\Models\TechnicianAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DsBillingAttendancePullService
{
    /**
     * Tarik data absensi teknisi dari dsBilling (mstore.mstore.id) dan simpan ke database MStore.
     */
    public function pullFromDsBilling(?string $date = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $targetDate = $date ?: now()->toDateString();

        $pullUrl = config('services.dsbilling.attendance_pull_url');
        if (empty($pullUrl)) {
            $baseApiUrl = config('services.dsbilling.url', env('DSBILLING_API_URL', 'https://mstore.mstore.id/api/v1/employees/sync'));
            $parsed = parse_url($baseApiUrl);
            $scheme = $parsed['scheme'] ?? 'https';
            $host = $parsed['host'] ?? 'mstore.mstore.id';
            $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
            $pullUrl = "{$scheme}://{$host}{$port}/api/v1/attendance/pull";
        }

        $apiToken = trim((string) (
            config('services.dsbilling.token')
            ?: env('DSBILLING_API_TOKEN')
            ?: env('MSTORE_SYNC_TOKEN')
            ?: 'mstore-sync-secret-2026'
        ));

        $params = array_filter([
            'date' => $targetDate,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'token' => $apiToken,
            'sync_token' => $apiToken,
        ]);

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'X-DSBILLING-TOKEN' => $apiToken,
                    'X-MSTORE-TOKEN' => $apiToken,
                    'Accept' => 'application/json',
                ])
                ->get($pullUrl, $params);

            if (! $response->successful()) {
                $status = $response->status();
                $body = $response->json();
                $errMsg = $body['message'] ?? $response->body() ?? 'Gagal menghubungkan ke server dsBilling';

                Log::error('Failed to pull attendance from dsBilling', [
                    'url' => $pullUrl,
                    'status' => $status,
                    'response' => $body,
                ]);

                return [
                    'success' => false,
                    'message' => "Gagal menarik data dari dsBilling (HTTP {$status}): {$errMsg}",
                    'synced_count' => 0,
                ];
            }

            $records = $response->json('records') ?? [];
            if (empty($records)) {
                return [
                    'success' => true,
                    'message' => "Tidak ada data absensi teknisi di dsBilling untuk tanggal {$targetDate}.",
                    'synced_count' => 0,
                    'created_count' => 0,
                    'updated_count' => 0,
                ];
            }

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($records as $item) {
                try {
                    $res = $this->saveAttendanceRecord($item);
                    if ($res['status'] === 'created') {
                        $createdCount++;
                    } elseif ($res['status'] === 'updated') {
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } catch (\Throwable $e) {
                    $skippedCount++;
                    Log::warning('Error saving pulled attendance record in MStore', [
                        'record' => $item,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $total = $createdCount + $updatedCount;
            $detailMsg = "Berhasil menarik {$total} data absensi dari dsBilling untuk tanggal {$targetDate} ({$createdCount} baru, {$updatedCount} diperbarui).";

            return [
                'success' => true,
                'message' => $detailMsg,
                'synced_count' => $total,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount,
            ];
        } catch (\Throwable $e) {
            Log::error('Exception while pulling attendance from dsBilling', [
                'url' => $pullUrl,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan koneksi ke server dsBilling: ' . $e->getMessage(),
                'synced_count' => 0,
            ];
        }
    }

    /**
     * Simpan / perbarui satu record absensi ke tabel technician_attendances di MStore.
     */
    public function saveAttendanceRecord(array $payload): array
    {
        $cleanNik = ! empty($payload['nik']) ? trim((string) $payload['nik']) : null;
        $cleanName = ! empty($payload['name']) ? trim((string) $payload['name']) : null;
        $cleanEmail = ! empty($payload['email']) ? strtolower(trim((string) $payload['email'])) : null;
        $cleanPhone = ! empty($payload['phone']) ? trim((string) $payload['phone']) : null;

        // 1. Resolusi Karyawan & Akun User di MStore
        $employee = null;
        if ($cleanNik && ! str_starts_with($cleanNik, 'AUTO-')) {
            $employee = Employee::where('nik', $cleanNik)->first();
        }

        if (! $employee && $cleanName) {
            $employee = Employee::whereRaw('LOWER(TRIM(full_name)) = ?', [strtolower($cleanName)])->first();
        }

        if (! $employee && $cleanEmail) {
            $employee = Employee::where('email', $cleanEmail)->first();
        }

        // Cari atau buat User terkait
        $user = $employee?->user;

        if (! $user && $cleanEmail) {
            $user = User::where('email', $cleanEmail)->first();
        }

        if (! $user && $cleanPhone) {
            $digits = preg_replace('/\D+/', '', $cleanPhone);
            $user = User::where('phone', $cleanPhone)
                ->when(strlen($digits) >= 8, fn ($q) => $q->orWhere('phone', 'like', '%'.substr($digits, -8)))
                ->first();
        }

        if (! $user && $cleanName) {
            $user = User::where('name', $cleanName)->first();
        }

        // Jika Employee ada tapi User belum ada, buatkan User otomatis
        if (! $user) {
            $displayName = $cleanName ?: ($employee?->full_name ?: 'Karyawan ' . ($cleanNik ?: rand(100, 999)));
            $userEmail = $cleanEmail ?: ($employee?->email ?: (strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $displayName)) . rand(10, 99) . '@mstore.local'));
            $userPhone = $cleanPhone ?: ($employee?->phone ?: '');

            $role = Role::whereIn('name', ['field-technician', 'technician', 'wash-operator', 'operator', 'staff'])->first();

            $user = User::create([
                'name' => $displayName,
                'email' => $userEmail,
                'phone' => $userPhone,
                'username' => User::generateUniqueUsername($displayName, $userEmail),
                'password' => Hash::make('Password123!'),
                'role_id' => $role?->id,
                'is_active' => true,
            ]);

            if ($employee && empty($employee->user_id)) {
                $employee->user_id = $user->id;
                $employee->saveQuietly();
            }
        } elseif (empty($user->role_id)) {
            $defaultRole = Role::whereIn('name', ['field-technician', 'technician', 'wash-operator'])->first();
            if ($defaultRole) {
                $user->role_id = $defaultRole->id;
                $user->saveQuietly();
            }
        }

        // Jika User ada tapi Employee belum tercatat, buatkan record Employee
        if (! $employee) {
            $employee = Employee::where('user_id', $user->id)->first();
        }

        if (! $employee) {
            $employee = Employee::create([
                'nik' => $cleanNik ?: ('AUTO-' . date('Ymd') . '-' . rand(100, 999)),
                'full_name' => $user->name,
                'position' => 'Teknisi Lapangan',
                'department' => 'Teknis & Jaringan',
                'employment_status' => 'Tetap',
                'join_date' => now(),
                'email' => $user->email,
                'phone' => $user->phone,
                'user_id' => $user->id,
            ]);
        } else {
            if ($cleanNik && ! str_starts_with($cleanNik, 'AUTO-') && ($employee->nik !== $cleanNik || str_starts_with($employee->nik, 'AUTO-'))) {
                $employee->nik = $cleanNik;
            }
            if ($cleanName && $employee->full_name !== $cleanName) {
                $employee->full_name = $cleanName;
            }
            if (empty($employee->user_id)) {
                $employee->user_id = $user->id;
            }
            $employee->saveQuietly();
        }

        // 2. Upsert ke tabel technician_attendances
        $workDate = Carbon::parse($payload['work_date'] ?? now()->toDateString())->toDateString();
        $attendance = TechnicianAttendance::where('user_id', $user->id)
            ->whereDate('work_date', $workDate)
            ->first();

        $isNew = false;
        if (! $attendance) {
            $attendance = new TechnicianAttendance();
            $attendance->user_id = $user->id;
            $attendance->work_date = $workDate;
            $attendance->generated_type = 'manual';
            $isNew = true;
        }

        // Set / Update Jam Masuk
        if (! empty($payload['clock_in'])) {
            $attendance->clock_in = Carbon::parse($payload['clock_in']);
        } elseif (empty($attendance->clock_in)) {
            $attendance->clock_in = now();
        }

        // Set / Update Jam Pulang
        if (! empty($payload['clock_out'])) {
            $attendance->clock_out = Carbon::parse($payload['clock_out']);
        }

        // Koordinat Lokasi
        if (isset($payload['lat_clock_in']) && $payload['lat_clock_in'] !== null) {
            $attendance->lat_clock_in = (float) $payload['lat_clock_in'];
        }
        if (isset($payload['lng_clock_in']) && $payload['lng_clock_in'] !== null) {
            $attendance->lng_clock_in = (float) $payload['lng_clock_in'];
        }
        if (isset($payload['lat_clock_out']) && $payload['lat_clock_out'] !== null) {
            $attendance->lat_clock_out = (float) $payload['lat_clock_out'];
        }
        if (isset($payload['lng_clock_out']) && $payload['lng_clock_out'] !== null) {
            $attendance->lng_clock_out = (float) $payload['lng_clock_out'];
        }

        // Status Kehadiran
        $attendance->status = $payload['status'] ?? ($attendance->status ?? 'present');

        // Catatan
        $sourceNote = 'Absen via dsBilling';
        $extraNote = ! empty($payload['notes']) ? ' | ' . trim((string) $payload['notes']) : '';
        $attendance->notes = $sourceNote . $extraNote;

        $attendance->save();

        return [
            'success' => true,
            'status' => $isNew ? 'created' : 'updated',
            'attendance' => $attendance,
            'user' => $user,
        ];
    }
}
