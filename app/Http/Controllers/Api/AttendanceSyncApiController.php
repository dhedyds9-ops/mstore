<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Role;
use App\Models\TechnicianAttendance;
use App\Models\User;
use App\Services\DsBillingAttendancePullService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AttendanceSyncApiController extends Controller
{
    /**
     * Terima data presensi dari dsBilling dan sinkronkan ke MStore.
     */
    public function sync(Request $request, DsBillingAttendancePullService $pullService): JsonResponse
    {
        // 1. Verifikasi Secret Token atau Jaringan Lokal
        $clientIp = $request->ip();
        $isLocalPrivateIp = str_starts_with($clientIp, '192.168.') 
            || str_starts_with($clientIp, '10.') 
            || str_starts_with($clientIp, '172.')
            || in_array($clientIp, ['127.0.0.1', '::1']);

        $providedToken = trim((string) (
            $request->header('X-DSBILLING-TOKEN') 
            ?: $request->header('x-dsbilling-token')
            ?: $request->header('X-MSTORE-TOKEN') 
            ?: $request->header('x-mstore-token')
            ?: $request->bearerToken() 
            ?: $request->input('sync_token')
            ?: $request->input('api_token')
            ?: $request->input('token')
        ));

        $candidateTokens = array_values(array_unique(array_filter([
            trim((string) config('services.dsbilling.token')),
            trim((string) env('DSBILLING_API_TOKEN')),
            trim((string) env('MSTORE_SYNC_TOKEN')),
            ...(! app()->isProduction() ? [
                'mstore-sync-secret-2026',
                'mstore sync secret 2026',
                'mstore',
            ] : []),
        ])));

        $isValid = false;
        if ($providedToken !== '') {
            foreach ($candidateTokens as $candidate) {
                if ($candidate !== '' && hash_equals($candidate, $providedToken)) {
                    $isValid = true;
                    break;
                }
            }
        } elseif ($isLocalPrivateIp && ! app()->isProduction()) {
            $isValid = true;
        }

        if (! $isValid) {
            Log::warning('Unauthorized attendance sync attempt from dsBilling', [
                'ip' => $request->ip(),
                'provided_token' => $providedToken,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Token autentikasi sinkronisasi absensi tidak valid.',
            ], 401);
        }

        // 2. Validasi Payload
        $validated = $request->validate([
            'nik' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'work_date' => ['required', 'date'],
            'clock_in' => ['nullable', 'string'],
            'clock_out' => ['nullable', 'string'],
            'lat_clock_in' => ['nullable', 'numeric'],
            'lng_clock_in' => ['nullable', 'numeric'],
            'lat_clock_out' => ['nullable', 'numeric'],
            'lng_clock_out' => ['nullable', 'numeric'],
            'status' => ['nullable', 'string', 'in:present,late,leave,permit,sick,alpha,off'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $cleanNik = ! empty($validated['nik']) ? trim((string) $validated['nik']) : null;
        $cleanName = ! empty($validated['name']) ? trim((string) $validated['name']) : null;
        $cleanEmail = ! empty($validated['email']) ? strtolower(trim((string) $validated['email'])) : null;
        $cleanPhone = ! empty($validated['phone']) ? trim((string) $validated['phone']) : null;

        // 3. Simpan / Perbarui Presensi via DsBillingAttendancePullService
        $res = $pullService->saveAttendanceRecord($validated);
        $attendance = $res['attendance'];
        $user = $res['user'];

        Log::info('Attendance synced from dsBilling to MStore successfully', [
            'user_id' => $user->id,
            'name' => $user->name,
            'work_date' => $workDate,
            'clock_in' => $attendance->clock_in?->format('H:i:s'),
            'clock_out' => $attendance->clock_out?->format('H:i:s'),
            'status' => $attendance->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Presensi untuk '{$user->name}' berhasil disinkronkan ke MStore.",
            'data' => [
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'work_date' => $attendance->work_date->format('Y-m-d'),
                'clock_in' => $attendance->clock_in?->format('H:i:s'),
                'clock_out' => $attendance->clock_out?->format('H:i:s'),
                'status' => $attendance->status,
            ],
        ], 200);
    }
}
