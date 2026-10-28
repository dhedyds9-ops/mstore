<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\WashEmployee;
use App\Services\EmployeeSyncService;
use App\Services\WhatsAppService;
use App\Traits\HasIdCard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class EmployeeController extends Controller implements HasMiddleware
{
    use HasIdCard;

    public function __construct(private readonly EmployeeSyncService $employeeSyncService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('permission:employee.view', only: ['index', 'idCard', 'printCards']),
            new Middleware('permission:employee.create', only: ['create', 'store']),
            new Middleware('permission:employee.edit', only: ['edit', 'update', 'syncToDsBilling', 'createUserAccount', 'sendWhatsAppCredentials']),
            new Middleware('permission:employee.delete', only: ['destroy']),
            new Middleware('permission:employee.sync', only: ['syncExisting']),
            new Middleware('permission:employee.export', only: ['exportCsv', 'exportPdf', 'exportExcel']),
        ];
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $department = trim((string) $request->query('department', ''));
        $position = trim((string) ($request->query('position') ?: $request->query('job_title', '')));
        $status = trim((string) $request->query('status', ''));
        $roleId = $request->filled('role_id') ? $request->integer('role_id') : null;

        $query = Employee::query()
            ->with(['user.role', 'washEmployee'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('nik', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('department', 'like', '%'.$search.'%')
                        ->orWhere('position', 'like', '%'.$search.'%');

                    $searchLower = strtolower(trim($search));
                    if (str_contains($searchLower, 'bombom')) {
                        $q->orWhere('full_name', 'like', '%fajar%');
                    }
                    if (str_contains($searchLower, 'fajar')) {
                        $q->orWhere('full_name', 'like', '%bombom%');
                    }
                    if (str_contains($searchLower, 'rifki') || str_contains($searchLower, 'rifky') || str_contains($searchLower, 'ripky')) {
                        $q->orWhere('full_name', 'like', '%ripky%')
                          ->orWhere('full_name', 'like', '%rifki%')
                          ->orWhere('full_name', 'like', '%rifky%');
                    }
                    if (str_contains($searchLower, 'gundul') || str_contains($searchLower, 'irfan') || str_contains($searchLower, 'irpan')) {
                        $q->orWhere('full_name', 'like', '%irpan%')
                          ->orWhere('full_name', 'like', '%gundul%');
                    }
                });
            })
            ->when($department !== '', function ($query) use ($department) {
                $query->where('department', $department);
            })
            ->when($position !== '', function ($query) use ($position) {
                $query->where('position', $position);
            })
            ->when($roleId !== null, function ($query) use ($roleId) {
                $query->whereHas('user', function ($uq) use ($roleId) {
                    $uq->where('role_id', $roleId);
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('employment_status', $status);
            })
            ->latest()
            ->customPaginate()
            ->withQueryString();

        $employees = $query;
        $departments = Employee::query()->select('department')->whereNotNull('department')->where('department', '!=', '')->distinct()->orderBy('department')->pluck('department');
        $positions = Employee::query()->select('position')->whereNotNull('position')->where('position', '!=', '')->distinct()->orderBy('position')->pluck('position');
        $roles = Role::whereNotIn('name', Role::hiddenInUserManagement())->orderBy('label')->get();
        $defaultStatuses = ['Tetap', 'Training', 'Pendaftar'];
        $dbStatuses = Employee::query()->select('employment_status')->whereNotNull('employment_status')->distinct()->pluck('employment_status')->toArray();
        $statuses = array_values(array_unique(array_filter(array_merge($defaultStatuses, $dbStatuses))));

        return view('employees.index', compact('employees', 'search', 'departments', 'positions', 'roles', 'statuses', 'department', 'position', 'status'));
    }

    public function create()
    {
        $users = $this->employeeUsers();
        $washEmployees = $this->washEmployees();
        $roles = \App\Services\EmployeeSyncService::employeeRolesQuery()->get();
        $roleLabels = $roles->pluck('label')->unique()->toArray();
        $positions = \App\Services\EmployeeSyncService::allPositions();
        $departments = \App\Services\EmployeeSyncService::allDepartments();
        $positionDeptMap = collect(\App\Services\EmployeeSyncService::positionRoleDepartmentMap())
            ->mapWithKeys(fn ($item) => [$item['position'] => $item['department']])
            ->unique()
            ->all();

        return view('employees.create', compact('users', 'washEmployees', 'roleLabels', 'roles', 'positions', 'departments', 'positionDeptMap'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateEmployee($request);
        $validated['document_path'] = $this->storeDocument($request);
        if ($this->hasIdCardPhotoColumn()) {
            $validated['id_card_photo_path'] = $this->storeIdCardPhoto($request);
        }
        if (! $this->hasIdCardExpiresColumn()) {
            unset($validated['id_card_expires_at']);
        }

        // Handle User Account Creation / Linking (single source of truth in users table)
        if ($request->boolean('create_user_account')) {
            $user = $this->resolveUserForEmployeeData($validated);
            $this->syncUserFromEmployeeData($user, $validated, true);
            $validated['user_id'] = $user->id;
        }

        $validated = $this->applyLinkedEmployee($validated);
        $employee = Employee::create($validated);

        if ($employee->user_id) {
            $user = User::find($employee->user_id);
            if ($user) {
                // Update user data to match employee data
                $user->update([
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'avatar' => $validated['id_card_photo_path'] ?? $user->avatar,
                ]);

                // Sync Employee.position → User.role (reverse direction, if position matches a role)
                $this->employeeSyncService->syncFromEmployee($employee->fresh());

                // Then sync to ensure other fields are correct
                $this->employeeSyncService->syncFromUser($user->load('role'));
            }
        }

        return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        $users = $this->employeeUsers($employee->id);
        $washEmployees = $this->washEmployees($employee->id);
        $roles = \App\Services\EmployeeSyncService::employeeRolesQuery($employee->user?->role_id)->get();
        $roleLabels = $roles->pluck('label')->unique()->toArray();
        $positions = \App\Services\EmployeeSyncService::allPositions();
        $departments = \App\Services\EmployeeSyncService::allDepartments();
        $positionDeptMap = collect(\App\Services\EmployeeSyncService::positionRoleDepartmentMap())
            ->mapWithKeys(fn ($item) => [$item['position'] => $item['department']])
            ->unique()
            ->all();

        return view('employees.edit', compact('employee', 'users', 'washEmployees', 'roleLabels', 'roles', 'positions', 'departments', 'positionDeptMap'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $this->validateEmployee($request, $employee->id);
        if (! $this->hasIdCardExpiresColumn()) {
            unset($validated['id_card_expires_at']);
        }
        $newDocPath = $this->storeDocument($request);
        if ($newDocPath) {
            if ($employee->document_path) {
                Storage::disk('public')->delete($employee->document_path);
            }
            $validated['document_path'] = $newDocPath;
        }
        if ($this->hasIdCardPhotoColumn()) {
            $newPhotoPath = $this->storeIdCardPhoto($request);
            if ($newPhotoPath) {
                if ($employee->id_card_photo_path) {
                    Storage::disk('public')->delete($employee->id_card_photo_path);
                }
                $validated['id_card_photo_path'] = $newPhotoPath;
            }
        }

        // Handle User Account Creation/Update/Linking consistently
        if (!empty($validated['user_id'])) {
            $employee->update(['user_id' => (int) $validated['user_id']]);
        } elseif ($request->boolean('create_user_account')) {
            if (empty($validated['user_id']) && $employee->user_id) {
                $validated['user_id'] = $employee->user_id;
            }
            $user = $this->resolveUserForEmployeeData($validated);
            $this->syncUserFromEmployeeData($user, $validated, false);
            $validated['user_id'] = $user->id;
        }

        $validated = $this->applyLinkedEmployee($validated);
        $employee->update($validated);

        if ($employee->user_id) {
            $user = User::find($employee->user_id);
            if ($user) {
                // Update user record to match basic info & sync username/password
                $userUpdate = [
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'avatar' => $validated['id_card_photo_path'] ?? $user->avatar,
                ];

                $inputUsername = trim((string) ($request->input('username') ?? ''));
                if ($inputUsername !== '') {
                    $taken = User::where('username', $inputUsername)->where('id', '!=', $user->id)->exists();
                    if (!$taken) {
                        $userUpdate['username'] = $inputUsername;
                    }
                }

                $inputPassword = trim((string) ($request->input('password') ?? ''));
                if ($inputPassword !== '') {
                    $userUpdate['password'] = Hash::make($inputPassword);
                }

                if (!empty($validated['role_id'])) {
                    $userUpdate['role_id'] = (int) $validated['role_id'];
                }

                $user->update($userUpdate);

                // Sync Employee.position → User.role (reverse direction, 2-way sync)
                $this->employeeSyncService->syncFromEmployee($employee->fresh());

                // Then sync user → employee to ensure consistency
                $this->employeeSyncService->syncFromUser($user->load('role'));
            }
        }

        return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        DB::transaction(function () use ($employee) {
            $employee->loadMissing(['user', 'washEmployee']);

            // Delete documents/photos
            if ($employee->document_path) {
                Storage::disk('public')->delete($employee->document_path);
            }
            if ($this->hasIdCardPhotoColumn() && $employee->id_card_photo_path) {
                Storage::disk('public')->delete($employee->id_card_photo_path);
            }

            $linkedUser = $employee->user;
            $linkedWashEmployee = $employee->washEmployee;

            // Delete Employee record first (force delete because SoftDeletes is used and we want it gone)
            $employee->forceDelete();

            // If there's a linked Wash Employee, we might want to delete it too or at least unlink
            if ($linkedWashEmployee) {
                // Check if wash employee is linked to this specific employee record
                // (Usually 1:1 in this context)
                $linkedWashEmployee->delete();
            }

            // If there's a linked User, delete the user account as well
            if ($linkedUser && $linkedUser->id !== Auth::id()) {
                // Optional: Check if user has other critical links before deleting
                // For now, following user expectation that deleting employee deletes the user account
                $linkedUser->delete();
            }
        });

        return redirect()->route('employees.index')->with('success', 'Data karyawan dan akun pengguna terkait berhasil dihapus.');
    }

    public function idCard(Employee $employee, Request $request)
    {
        $user = $employee->user ?? new User(['name' => $employee->full_name]);
        $idCardCode = $this->userIdCardCode($user);
        $printMode = $request->boolean('print');
        [$brandName, $logoUrl, $brandSlogan, $brandKey] = $this->resolveUserBrand($user);

        return view('employees.id-card', compact('employee', 'user', 'idCardCode', 'printMode', 'logoUrl', 'brandName', 'brandSlogan', 'brandKey'));
    }

    public function printCards(Request $request)
    {
        $selectedIds = collect((array) $request->query('selected_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $selectedPosition = trim((string) $request->query('position', ''));
        $positions = Employee::query()
            ->select('position')
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->distinct()
            ->orderBy('position')
            ->pluck('position');

        $employees = $selectedIds->isNotEmpty()
            ? Employee::query()
                ->whereIn('id', $selectedIds)
                ->when($selectedPosition !== '', fn ($q) => $q->where('position', $selectedPosition))
                ->orderBy('full_name')
                ->get()
            : $this->filteredEmployees($request)->orderBy('full_name')->get();

        $cards = $employees->map(function (Employee $employee) {
            $user = $employee->user ?? new User(['name' => $employee->full_name]);
            [$brandName, $logoUrl, $brandSlogan, $brandKey] = $this->resolveUserBrand($user);

            return [
                'employee' => $employee,
                'user' => $user,
                'code' => $this->userIdCardCode($user),
                'brand_name' => $brandName,
                'logo_url' => $logoUrl,
                'brand_slogan' => $brandSlogan,
                'brand_key' => $brandKey,
            ];
        });

        return view('employees.id-cards-print', compact('cards', 'positions', 'selectedPosition'));
    }

    public function exportCsv(Request $request)
    {
        $filename = 'data-karyawan-'.date('Ymd-His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $search = trim((string) $request->query('search', ''));
        $department = trim((string) $request->query('department', ''));
        $position = trim((string) ($request->query('position') ?: $request->query('job_title', '')));
        $status = trim((string) $request->query('status', ''));
        $roleId = $request->filled('role_id') ? $request->integer('role_id') : null;

        $rows = Employee::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('nik', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('department', 'like', '%'.$search.'%')
                        ->orWhere('position', 'like', '%'.$search.'%');

                    $searchLower = strtolower(trim($search));
                    if (str_contains($searchLower, 'bombom')) {
                        $q->orWhere('full_name', 'like', '%fajar%');
                    }
                    if (str_contains($searchLower, 'fajar')) {
                        $q->orWhere('full_name', 'like', '%bombom%');
                    }
                    if (str_contains($searchLower, 'rifki') || str_contains($searchLower, 'rifky') || str_contains($searchLower, 'ripky')) {
                        $q->orWhere('full_name', 'like', '%ripky%')
                          ->orWhere('full_name', 'like', '%rifki%')
                          ->orWhere('full_name', 'like', '%rifky%');
                    }
                    if (str_contains($searchLower, 'gundul') || str_contains($searchLower, 'irfan') || str_contains($searchLower, 'irpan')) {
                        $q->orWhere('full_name', 'like', '%irpan%')
                          ->orWhere('full_name', 'like', '%gundul%');
                    }
                });
            })
            ->when($department !== '', function ($query) use ($department) {
                $query->where('department', $department);
            })
            ->when($position !== '', function ($query) use ($position) {
                $query->where('position', $position);
            })
            ->when($roleId !== null, function ($query) use ($roleId) {
                $query->whereHas('user', function ($uq) use ($roleId) {
                    $uq->where('role_id', $roleId);
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('employment_status', $status);
            })
            ->orderBy('full_name')
            ->get([
                'full_name',
                'date_of_birth',
                'gender',
                'address',
                'phone',
                'email',
                'nik',
                'position',
                'department',
                'join_date',
                'employment_status',
            ]);

        $columns = ['Nama Lengkap', 'Tanggal Lahir', 'Jenis Kelamin', 'Alamat', 'No HP', 'Email', 'NIK', 'Jabatan', 'Departemen', 'Tanggal Masuk', 'Status'];

        $callback = function () use ($rows, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->full_name,
                    optional($r->date_of_birth)->format('Y-m-d'),
                    $r->gender,
                    $r->address,
                    $r->phone,
                    $r->email,
                    $r->nik,
                    $r->position,
                    $r->department,
                    optional($r->join_date)->format('Y-m-d'),
                    $r->employment_status,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $employees = $this->filteredEmployees($request)->orderBy('full_name')->get();
        $logo = Setting::getValue('store_logo');
        if (! $logo) {
            $logo = asset('img/logo.png');
        } elseif (! str_starts_with($logo, 'http')) {
            $logo = asset($logo);
        }

        $pdf = Pdf::loadView('employees.pdf', [
            'employees' => $employees,
            'logo' => $logo,
            'printedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('data_karyawan_'.now()->format('Ymd_His').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $employees = $this->filteredEmployees($request)->orderBy('full_name')->get();

        return response()->streamDownload(function () use ($employees) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues([
                'Nama Lengkap',
                'Tanggal Lahir',
                'Jenis Kelamin',
                'Alamat',
                'No HP',
                'Email',
                'NIK',
                'Jabatan',
                'Departemen',
                'Tanggal Masuk',
                'Status',
                'Tipe Integrasi',
            ]));

            foreach ($employees as $employee) {
                $integration = [];
                if ($employee->user_id) {
                    $integration[] = 'User';
                }
                if ($employee->wash_employee_id) {
                    $integration[] = 'Wash';
                }
                if ($integration === []) {
                    $integration[] = 'Manual';
                }

                $writer->addRow(Row::fromValues([
                    $employee->full_name,
                    optional($employee->date_of_birth)->format('Y-m-d'),
                    $employee->gender,
                    $employee->address,
                    $employee->phone,
                    $employee->email,
                    $employee->nik,
                    $employee->position,
                    $employee->department,
                    optional($employee->join_date)->format('Y-m-d'),
                    $employee->employment_status,
                    implode(', ', $integration),
                ]));
            }

            $writer->close();
        }, 'data-karyawan-'.now()->format('Ymd-His').'.xlsx');
    }

    public function syncExisting()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('attendance:sync-employees');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('attendance:sync-employees failed in syncExisting: ' . $e->getMessage());
        }

        $users = User::query()->with('role')->whereHas('role', function ($query) {
            $query->whereIn('name', $this->employeeSyncService->allowedRoles());
        })->get();

        foreach ($users as $user) {
            $this->employeeSyncService->syncFromUser($user);
        }

        $washEmployees = WashEmployee::query()->with('user')->get();
        foreach ($washEmployees as $washEmployee) {
            $this->employeeSyncService->syncFromWashEmployee($washEmployee);
        }

        return redirect()->route('employees.index')->with('success', 'Sinkronisasi data karyawan dari teknisi/wash/user berhasil.');
    }

    /**
     * Kirim & sinkronkan data karyawan ke dsBilling (https://mstore.mstore.id/admin/employee).
     */
    public function syncToDsBilling(Employee $employee)
    {
        $result = $this->employeeSyncService->syncToDsBilling($employee);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Buat dan tautkan akun User Login langsung untuk karyawan ini.
     */
    public function createUserAccount(Employee $employee)
    {
        if ($employee->user_id && $employee->user) {
            return back()->with('info', "Karyawan {$employee->full_name} sudah memiliki akun login (Username: {$employee->user->username}).");
        }

        $email = trim((string) $employee->email);
        $fullName = trim((string) $employee->full_name);
        $phone = trim((string) $employee->phone);

        // Cari atau buat User berdasarkan email, nomor telepon, atau nama
        $user = null;
        if ($email !== '') {
            $user = User::query()->where('email', $email)->first();
        }
        if (! $user && $phone !== '') {
            $digits = preg_replace('/\D+/', '', $phone);
            $user = User::query()->where('phone', $phone)
                ->when(strlen($digits) >= 8, fn ($q) => $q->orWhere('phone', 'like', '%'.substr($digits, -8)))
                ->first();
        }
        if (! $user && $fullName !== '') {
            $user = User::query()->where('name', $fullName)->first();
        }

        $defaultPassword = 'Password123!';

        if (! $user) {
            $username = User::generateUniqueUsername($fullName, $email);

            // Cari role sesuai jabatan karyawan
            $roleName = $this->employeeSyncService->roleNameFromPosition($employee->position);
            $roleId = null;
            if ($roleName) {
                $role = Role::query()->where('name', $roleName)->first();
                $roleId = $role?->id;
            }
            if (! $roleId) {
                $fallbackRole = Role::query()->whereIn('name', ['staff', 'employee', 'operator', 'atk-cashier', 'admin'])->first();
                $roleId = $fallbackRole?->id;
            }

            $user = User::create([
                'name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'username' => $username,
                'password' => Hash::make($defaultPassword),
                'role_id' => $roleId,
                'is_active' => true,
            ]);
        }

        $employee->update(['user_id' => $user->id]);

        // Sync Employee.position -> User.role
        $this->employeeSyncService->syncFromEmployee($employee->fresh());

        return back()->with('success', "Akun login berhasil dibuat & ditautkan untuk {$employee->full_name}! Username: {$user->username}, Password Default: {$defaultPassword}");
    }

    /**
     * Kirim data login (Nama, Jabatan, Username, Password) langsung ke nomor WhatsApp karyawan via Gateway.
     */
    public function sendWhatsAppCredentials(Employee $employee, WhatsAppService $wa)
    {
        $rawPhone = $employee->phone ?: '';
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $rawPhone);
        if (str_starts_with($cleanPhone, '08')) {
            $cleanPhone = '628' . substr($cleanPhone, 2);
        } elseif (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '628' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '6208')) {
            $cleanPhone = '628' . substr($cleanPhone, 4);
        }

        if (empty($cleanPhone) || !str_starts_with($cleanPhone, '628') || strlen($cleanPhone) < 10 || strlen($cleanPhone) > 16) {
            return back()->with('error', "Nomor WhatsApp karyawan ('{$rawPhone}') tidak valid. WhatsApp memerlukan nomor ponsel seluler Indonesia (contoh: 08123456789 atau 628123456789).");
        }

        // Jika belum memiliki user akun, buatkan otomatis terlebih dahulu
        if (! $employee->user_id || ! $employee->user) {
            $this->createUserAccount($employee);
            $employee->refresh();
        }

        $user = $employee->user;
        $username = $user?->username ?? ($user?->email ?? '-');
        $nama = $employee->full_name;
        $jabatan = $employee->position ?: ($employee->department ?: 'Staff');
        $loginUrl = route('login');
        $defaultPassword = 'Password123!';

        $msgText = "Halo *{$nama}*,\n\n"
            . "Berikut informasi akun login sistem Anda:\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "🏢 *Aplikasi:* MStore\n"
            . "👤 *Nama:* {$nama}\n"
            . "💼 *Jabatan:* {$jabatan}\n"
            . "🌐 *Link Login:* {$loginUrl}\n\n"
            . "🔐 *Kredensial Login:*\n"
            . "• Username: *{$username}*\n"
            . "• Password: *{$defaultPassword}*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "Silakan gunakan informasi di atas untuk masuk ke sistem. Demi keamanan, segera ganti password Anda setelah login pertama kali.\n\n"
            . "Terima kasih.";

        // Cek gateway WhatsApp di MStore
        $gwStatus = $wa->checkGatewayStatus();
        if (! ($gwStatus['ok'] ?? false) || ! ($gwStatus['connected'] ?? false)) {
            $errMsg = $gwStatus['message'] ?? 'Gateway WhatsApp belum diatur / belum aktif.';
            return back()->with('error', "Gagal mengirim via WhatsApp Gateway: {$errMsg}. Silakan periksa pengaturan di menu Pengaturan > Gateway WhatsApp.");
        }

        try {
            $res = $wa->sendMessage($cleanPhone, $msgText, 'employee_credentials');
            if ($res['success'] ?? false) {
                return back()->with('success', "✅ Kredensial login (Username & Password) berhasil dikirim via WhatsApp Gateway ke {$cleanPhone}!");
            }
            return back()->with('error', "Gagal mengirim via WhatsApp Gateway: " . ($res['error'] ?? ($res['message'] ?? 'Respon gateway tidak berhasil.')));
        } catch (\Throwable $e) {
            return back()->with('error', "Terjadi kesalahan saat menghubungi WhatsApp Gateway: " . $e->getMessage());
        }
    }

    private function validateEmployee(Request $request, ?int $id = null): array
    {
        $userId = $id ? Employee::find($id)?->user_id : null;

        $rules = [
            'full_name' => ['required', 'string', 'max:255', Rule::unique('employees', 'full_name')->ignore($id)],
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('employees', 'user_id')->ignore($id)],
            'wash_employee_id' => ['nullable', 'exists:wash_employees,id'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'nik' => ['required', 'string', 'max:32', Rule::unique('employees', 'nik')->ignore($id)],
            'position' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'join_date' => ['required', 'date'],
            'employment_status' => ['required', Rule::in(['Tetap', 'Training', 'Pendaftar', 'Kontrak'])],
            'monthly_salary' => ['nullable', 'numeric', 'min:0'],
            'daily_salary' => ['nullable', 'numeric', 'min:0'],
            'annual_leave_quota' => ['nullable', 'integer', 'min:0'],
            'annual_leave_used' => ['nullable', 'integer', 'min:0'],
            'sick_leave_quota' => ['nullable', 'integer', 'min:0'],
            'sick_leave_used' => ['nullable', 'integer', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'attendance_card_code' => ['nullable', 'string', 'max:255', Rule::unique('employees', 'attendance_card_code')->ignore($id)],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:2048'],
            'id_card_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'id_card_photo_base64' => ['nullable', 'string'],
            // User account fields
            'create_user_account' => ['nullable', 'boolean'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ];

        if ($this->hasIdCardExpiresColumn()) {
            $rules['id_card_expires_at'] = ['nullable', 'date'];
        }

        $messages = [
            'full_name.unique' => 'Nama karyawan ini sudah terdaftar di sistem.',
            'nik.unique' => 'Nomor KTP/NIK ini sudah terdaftar di sistem.',
        ];

        return $request->validate($rules, $messages);
    }

    private function storeDocument(Request $request): ?string
    {
        if (! $request->hasFile('document')) {
            return null;
        }

        return $request->file('document')->store('employee-documents', 'public');
    }

    private function storeIdCardPhoto(Request $request): ?string
    {
        if (! $this->hasIdCardPhotoColumn()) {
            return null;
        }
        if ($request->filled('id_card_photo_base64')) {
            $base64 = (string) $request->input('id_card_photo_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64 = substr($base64, strpos($base64, ',') + 1);
                $type = strtolower($type[1]);
                if (! in_array($type, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $type = 'jpg';
                }
                $data = base64_decode($base64);
                if ($data !== false) {
                    $filename = 'employee-id-cards/'.uniqid('idcard_', true).'.'.$type;
                    \Storage::disk('public')->put($filename, $data);

                    return $filename;
                }
            }
        }
        if (! $request->hasFile('id_card_photo')) {
            return null;
        }

        return $request->file('id_card_photo')->store('employee-id-cards', 'public');
    }

    private function employeeUsers(?int $excludeEmployeeId = null)
    {
        $linkedUserIds = Employee::query()
            ->when($excludeEmployeeId, fn ($q) => $q->where('id', '!=', $excludeEmployeeId))
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->toArray();

        return User::query()
            ->with('role')
            ->whereHas('role', function ($query) {
                $query->whereIn('name', $this->employeeSyncService->allowedRoles());
            })
            ->whereNotIn('id', $linkedUserIds)
            ->orderBy('name')
            ->get();
    }

    private function washEmployees(?int $excludeEmployeeId = null)
    {
        $linkedWashIds = Employee::query()
            ->when($excludeEmployeeId, fn ($q) => $q->where('id', '!=', $excludeEmployeeId))
            ->whereNotNull('wash_employee_id')
            ->pluck('wash_employee_id')
            ->toArray();

        return WashEmployee::query()
            ->whereNotIn('id', $linkedWashIds)
            ->orderBy('name')
            ->get();
    }

    private function applyLinkedEmployee(array $validated): array
    {
        if (! empty($validated['wash_employee_id'])) {
            $washEmployee = WashEmployee::query()->find($validated['wash_employee_id']);
            if ($washEmployee && empty($validated['user_id']) && $washEmployee->user_id) {
                $validated['user_id'] = $washEmployee->user_id;
            }
        }

        return $validated;
    }

    private function resolveUserForEmployeeData(array $validated): User
    {
        if (! empty($validated['user_id'])) {
            return User::query()->findOrFail((int) $validated['user_id']);
        }

        $email = trim((string) ($validated['email'] ?? ''));
        $username = trim((string) ($validated['username'] ?? ''));

        if ($email !== '') {
            $existingByEmail = User::query()->where('email', $email)->first();
            if ($existingByEmail) {
                return $existingByEmail;
            }
        }

        if ($username !== '') {
            $existingByUsername = User::query()->where('username', $username)->first();
            if ($existingByUsername) {
                return $existingByUsername;
            }
        }

        return new User;
    }

    private function syncUserFromEmployeeData(User $user, array $validated, bool $isNew): void
    {
        $username = trim((string) ($validated['username'] ?? ''));
        if ($username === '') {
            $username = $isNew
                ? User::generateUniqueUsername((string) ($validated['full_name'] ?? ''), (string) ($validated['email'] ?? ''))
                : (string) ($user->username ?: User::generateUniqueUsername((string) ($validated['full_name'] ?? ''), (string) ($validated['email'] ?? '')));
        }

        $data = [
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'username' => $username,
            'phone' => $validated['phone'],
            'is_active' => true,
        ];

        if (empty($user->company_id)) {
            $companyId = auth()->user()?->company_id
                ?? \App\Models\Company::query()->where('is_active', true)->value('id')
                ?? \App\Models\Company::query()->value('id');

            if (! $companyId) {
                $company = \App\Models\Company::create([
                    'code' => 'MSTORE',
                    'name' => \App\Models\Setting::getValue('store_name', config('app.name', 'MStore')),
                    'currency' => 'IDR',
                    'country' => 'ID',
                    'is_active' => true,
                    'subscription_status' => 'active',
                ]);
                $companyId = $company->id;
            }

            $data['company_id'] = $companyId;
        }

        if (! empty($validated['role_id'])) {
            $data['role_id'] = $validated['role_id'];
        }
        if (! empty($validated['id_card_photo_path'])) {
            $data['avatar'] = $validated['id_card_photo_path'];
        }
        if (! empty($validated['password'])) {
            $data['password'] = bcrypt((string) $validated['password']);
        } elseif ($isNew) {
            $data['password'] = bcrypt('Password123!');
        }

        $user->fill($data)->save();
    }

    private function filteredEmployees(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $department = trim((string) $request->query('department', ''));
        $status = trim((string) $request->query('status', ''));
        $position = trim((string) $request->query('position', ''));
        $roleId = $request->filled('role_id') ? $request->integer('role_id') : null;

        return Employee::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('nik', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('department', 'like', '%'.$search.'%')
                        ->orWhere('position', 'like', '%'.$search.'%');

                    $searchLower = strtolower(trim($search));
                    if (str_contains($searchLower, 'bombom')) {
                        $q->orWhere('full_name', 'like', '%fajar%');
                    }
                    if (str_contains($searchLower, 'fajar')) {
                        $q->orWhere('full_name', 'like', '%bombom%');
                    }
                    if (str_contains($searchLower, 'rifki') || str_contains($searchLower, 'rifky') || str_contains($searchLower, 'ripky')) {
                        $q->orWhere('full_name', 'like', '%ripky%')
                          ->orWhere('full_name', 'like', '%rifki%')
                          ->orWhere('full_name', 'like', '%rifky%');
                    }
                    if (str_contains($searchLower, 'gundul') || str_contains($searchLower, 'irfan') || str_contains($searchLower, 'irpan')) {
                        $q->orWhere('full_name', 'like', '%irpan%')
                          ->orWhere('full_name', 'like', '%gundul%');
                    }
                });
            })
            ->when($department !== '', function ($query) use ($department) {
                $query->where('department', $department);
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('employment_status', $status);
            })
            ->when($position !== '', function ($query) use ($position) {
                $query->where('position', $position);
            })
            ->when($roleId !== null, function ($query) use ($roleId) {
                $query->whereHas('user', function ($uq) use ($roleId) {
                    $uq->where('role_id', $roleId);
                });
            });
    }

    private function hasIdCardPhotoColumn(): bool
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('employees_id_card_photo_column', function () {
            return Schema::hasColumn('employees', 'id_card_photo_path');
        });
    }

    private function hasIdCardExpiresColumn(): bool
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('employees_id_card_expires_column', function () {
            return Schema::hasColumn('employees', 'id_card_expires_at');
        });
    }
}
