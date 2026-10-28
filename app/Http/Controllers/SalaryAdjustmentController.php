<?php

namespace App\Http\Controllers;

use App\Models\KasbonInstallment;
use App\Models\KasbonLoan;
use App\Models\Role;
use App\Models\SalaryAdjustment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->hasPermission('kasbon.view') && ! Auth::user()->hasPermission('kasbon.manage')) {
            abort(403, 'Unauthorized');
        }

        // Ambil data kasbon
        $kasbonQuery = SalaryAdjustment::with('user')->where('type', 'kasbon');
        // Ambil data bonus
        $bonusQuery = SalaryAdjustment::with('user')->where('type', 'bonus');

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate && strtotime($startDate) > strtotime($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        if ($request->filled('user_id')) {
            $kasbonQuery->where('user_id', $request->user_id);
            $bonusQuery->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $kasbonQuery->where('status', $request->status);
            $bonusQuery->where('status', $request->status);
        }

        if ($startDate && $endDate) {
            $kasbonQuery->whereBetween('date', [$startDate, $endDate]);
            $bonusQuery->whereBetween('date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $kasbonQuery->where('date', '>=', $startDate);
            $bonusQuery->where('date', '>=', $startDate);
        } elseif ($endDate) {
            $kasbonQuery->where('date', '<=', $endDate);
            $bonusQuery->where('date', '<=', $endDate);
        }

        $kasbonAdjustments = $kasbonQuery->latest('date')->latest('id')->paginate(15, ['*'], 'kasbon_page')->withQueryString();
        $bonusAdjustments = $bonusQuery->latest('date')->latest('id')->paginate(15, ['*'], 'bonus_page')->withQueryString();

        $loanQuery = KasbonLoan::query()
            ->with(['user', 'installments' => function ($q) {
                $q->latest('date');
            }])
            ->withSum('installments', 'amount')
            ->latest('start_date')
            ->latest('id');
        
        if ($request->filled('user_id')) {
            $loanQuery->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $loanQuery->where('status', 'active');
            } elseif ($request->status === 'processed') {
                $loanQuery->where('status', 'closed');
            }
        }

        if ($startDate && $endDate) {
            $loanQuery->whereBetween('start_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $loanQuery->where('start_date', '>=', $startDate);
        } elseif ($endDate) {
            $loanQuery->where('start_date', '<=', $endDate);
        }

        $loans = $loanQuery->get();

        // Pastikan data teknisi dsBilling & karyawan terhubung ke user account
        if (\App\Models\Employee::where('position', 'Teknisi Lapangan')->count() < 7) {
            try {
                \Illuminate\Support\Facades\Artisan::call('attendance:sync-employees');
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $employeesWithoutUser = \App\Models\Employee::whereNull('user_id')->get();
        if ($employeesWithoutUser->isNotEmpty()) {
            $techRole = Role::whereIn('name', ['field-technician', 'technician'])->first()
                ?: Role::whereIn('name', ['wash-operator', 'operator', 'staff'])->first();

            foreach ($employeesWithoutUser as $emp) {
                $digits = preg_replace('/\D+/', '', (string) $emp->phone);
                $last8 = strlen($digits) >= 8 ? substr($digits, -8) : $digits;

                $user = null;
                if (!empty($emp->email)) {
                    $user = User::where('email', $emp->email)->first();
                }
                if (!$user && !empty($last8)) {
                    $user = User::where('phone', 'like', "%{$last8}%")->first();
                }
                if (!$user) {
                    $user = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($emp->full_name))])->first();
                }

                if (!$user) {
                    $uname = User::generateUniqueUsername($emp->full_name, $emp->email);
                    $user = User::create([
                        'name' => $emp->full_name,
                        'username' => $uname,
                        'email' => !empty($emp->email) ? $emp->email : ($uname . '@mstore.id'),
                        'phone' => $emp->phone,
                        'job_title' => $emp->position ?: 'Teknisi Lapangan',
                        'role_id' => $techRole?->id,
                        'password' => bcrypt('Password123!'),
                        'is_active' => true,
                    ]);
                }

                $emp->user_id = $user->id;
                $emp->saveQuietly();
            }
        }

        $employeeUserIds = \App\Models\Employee::query()
            ->where(function ($q) {
                $q->whereNull('employment_status')
                  ->orWhereNotIn('employment_status', ['Resigned', 'Keluar', 'Nonaktif']);
            })
            ->pluck('user_id')
            ->filter()
            ->unique();

        $allUsers = User::query()
            ->whereIn('id', $employeeUserIds)
            ->where('is_active', true)
            ->with(['role', 'employee'])
            ->orderBy('name')
            ->get();

        $recapUsers = $allUsers;
        if ($request->filled('user_id')) {
            $recapUsers = $allUsers->where('id', (int) $request->user_id);
        }

        $statusFilter = $request->input('status');

        // Recap per teknisi / karyawan
        $recap = $recapUsers->map(function ($user) use ($startDate, $endDate, $request) {
            $kasbonQuery = SalaryAdjustment::where('user_id', $user->id)
                ->where('type', 'kasbon');
            
            if ($request->filled('status')) {
                $kasbonQuery->where('status', $request->status);
            } elseif (! $request->has('status')) {
                // Default awal: kasbon yang masih pending (belum dipotong di slip gaji)
                $kasbonQuery->where('status', 'pending');
            }
            // Jika user memilih 'Semua Status' (status='', request->has('status')), tampilkan seluruh kasbon

            // Jika ada filter tanggal, filter nominal rekap kasbon sesuai rentang tanggal tersebut
            if ($startDate && $endDate) {
                $kasbonQuery->whereBetween('date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $kasbonQuery->where('date', '>=', $startDate);
            } elseif ($endDate) {
                $kasbonQuery->where('date', '<=', $endDate);
            }

            $totalKasbonBiasa = $kasbonQuery->sum('amount');
            
            $totalPinjamanAktif = KasbonLoan::where('user_id', $user->id)
                ->where('status', 'active')
                ->sum('principal_amount');
            
            $totalCicilan = KasbonInstallment::whereHas('kasbonLoan', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->sum('amount');
            
            $sisaPinjaman = KasbonLoan::where('user_id', $user->id)
                ->where('status', 'active')
                ->get()
                ->sum(fn($loan) => $loan->remaining);

            // Total kewajiban nyata karyawan = Kasbon biasa pending + sisa pokok pinjaman angsuran
            $totalOutstanding = (float) $totalKasbonBiasa + (float) $sisaPinjaman;

            return [
                'user' => $user,
                'total_kasbon_biasa' => (float) $totalKasbonBiasa,
                'total_pinjaman_aktif' => (float) $totalPinjamanAktif,
                'total_cicilan' => (float) $totalCicilan,
                'sisa_pinjaman' => (float) $sisaPinjaman,
                'total_outstanding' => (float) $totalOutstanding,
            ];
        });

        $users = $allUsers;

        return view('technicians.kasbon.index', compact('kasbonAdjustments', 'bonusAdjustments', 'loans', 'users', 'recap'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance'])) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:bonus,kasbon',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $adjustment = SalaryAdjustment::create($request->all());

        // Auto-journal for kasbon disbursement: Debit Piutang Karyawan, Credit Kas
        if ($request->type === 'kasbon') {
            try {
                $kasbonAccId = \App\Models\Account::where('code', '1103')->value('id');
                $cashAccId = \App\Models\Account::where('code', '1001')->value('id');
                $userTarget = \App\Models\User::find($request->user_id);
                $userName = $userTarget ? $userTarget->name : ('User #' . $request->user_id);
                $desc = 'Pencairan Kasbon: ' . $userName . ($request->description ? (' (' . $request->description . ')') : '');

                if ($kasbonAccId && $cashAccId) {
                    $poster = app(\App\Services\AccountingPoster::class);
                    $poster->post(
                        'KASBON-' . $adjustment->id . '-' . date('Ymd'),
                        $request->date ?: now()->toDateString(),
                        $desc,
                        [
                            ['account_id' => $kasbonAccId, 'debit' => $request->amount, 'credit' => 0, 'unit' => 'MSTORE'],
                            ['account_id' => $cashAccId, 'debit' => 0, 'credit' => $request->amount, 'unit' => 'MSTORE'],
                        ],
                        null,
                        SalaryAdjustment::class,
                        $adjustment->id
                    );

                    // Update cash balance
                    $cash = \App\Models\Cash::firstOrCreate(['name' => 'Kas Utama'], ['balance' => 0]);
                    $cash->balance = (float) $cash->balance - (float) $request->amount;
                    $cash->save();
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Kasbon journal error: ' . $e->getMessage());
            }
        }

        return back()->with('success', __(ucfirst($request->type).' added successfully.'));
    }

    public function update(Request $request, SalaryAdjustment $salaryAdjustment)
    {
        if (! Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance'])) {
            abort(403, 'Unauthorized');
        }

        if ($salaryAdjustment->status === 'processed') {
            return back()->with('error', __('Cannot edit processed adjustment.'));
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $salaryAdjustment->update($request->all());

        return back()->with('success', __('Adjustment updated successfully.'));
    }

    public function destroy(SalaryAdjustment $salaryAdjustment)
    {
        if (! Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance'])) {
            abort(403, 'Unauthorized');
        }

        if ($salaryAdjustment->status === 'processed') {
            return back()->with('error', __('Cannot delete processed adjustment.'));
        }

        $salaryAdjustment->delete();

        return back()->with('success', __('Adjustment deleted successfully.'));
    }
}
