<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Setting;
use App\Services\EmployeeSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EmployeePublicRegisterController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran calon karyawan baru.
     */
    public function create()
    {
        $groupedPositions = EmployeeSyncService::groupedPositions();
        $positions = EmployeeSyncService::allPositions();
        $departments = EmployeeSyncService::allDepartments();
        $positionDeptMap = collect(EmployeeSyncService::positionRoleDepartmentMap())
            ->mapWithKeys(fn ($item) => [$item['position'] => $item['department']])
            ->unique()
            ->all();

        $storeName = Setting::getValue('store_name') ?: config('app.name', 'MStore');
        $storeLogo = Setting::getValue('store_logo');
        if (! $storeLogo) {
            $storeLogo = asset('img/logo.png');
        } elseif (! str_starts_with($storeLogo, 'http')) {
            $storeLogo = asset($storeLogo);
        }

        return view('employees.public_register', compact(
            'groupedPositions',
            'positions',
            'departments',
            'positionDeptMap',
            'storeName',
            'storeLogo'
        ));
    }

    /**
     * Simpan data pendaftaran calon karyawan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:32'],
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'date_of_birth' => ['required', 'date'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string'],
            'position' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'join_date' => ['nullable', 'date'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'id_card_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'gender.required' => 'Pilih jenis kelamin Anda.',
            'date_of_birth.required' => 'Tanggal lahir wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / HP aktif wajib diisi.',
            'address.required' => 'Alamat tempat tinggal saat ini wajib diisi.',
            'position.required' => 'Pilih posisi pekerjaan yang ingin Anda lamar.',
            'id_card_photo.max' => 'Ukuran foto KTP maksimal 5MB.',
            'document.max' => 'Ukuran berkas CV/Lamaran maksimal 10MB.',
        ]);

        // Normalisasi nomor HP / WhatsApp
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $validated['phone']);
        if (str_starts_with($cleanPhone, '08')) {
            $cleanPhone = '628' . substr($cleanPhone, 2);
        }
        $validated['phone'] = $cleanPhone ?: $validated['phone'];

        // 1. Cek apakah Nama Lengkap sudah terdaftar di sistem
        $trimmedName = trim((string) $validated['full_name']);
        $existingByName = Employee::query()
            ->whereRaw('LOWER(TRIM(full_name)) = ?', [strtolower($trimmedName)])
            ->first();

        if ($existingByName) {
            return back()
                ->withInput()
                ->withErrors([
                    'full_name' => "Nama \"{$existingByName->full_name}\" sudah terdaftar di sistem dengan jabatan {$existingByName->position} (Status: {$existingByName->employment_status}). Jika ini data diri Anda, tidak perlu mendaftar ulang atau silakan hubungi tim HRD.",
                ]);
        }

        // 2. Cek apakah NIK sudah terdaftar di sistem
        $cleanNik = trim((string) $validated['nik']);
        $existingByNik = Employee::query()
            ->where('nik', $cleanNik)
            ->first();

        if ($existingByNik) {
            return back()
                ->withInput()
                ->withErrors([
                    'nik' => "Nomor KTP/NIK \"{$cleanNik}\" sudah terdaftar atas nama {$existingByNik->full_name}. Pendaftaran tidak dapat diduplikasi.",
                ]);
        }

        // 3. Cek apakah Nomor HP / WhatsApp sudah terdaftar di sistem
        $existingByPhone = Employee::query()
            ->where('phone', $validated['phone'])
            ->first();

        if ($existingByPhone) {
            return back()
                ->withInput()
                ->withErrors([
                    'phone' => "Nomor WhatsApp/HP \"{$validated['phone']}\" sudah terdaftar atas nama {$existingByPhone->full_name}.",
                ]);
        }

        // 4. Cek apakah Email sudah terdaftar di sistem jika diisi
        if (! empty($validated['email'])) {
            $existingByEmail = Employee::query()
                ->where('email', strtolower(trim($validated['email'])))
                ->first();

            if ($existingByEmail) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'email' => "Alamat email ini sudah terdaftar atas nama {$existingByEmail->full_name}.",
                    ]);
            }
        }

        // Lengkapi departemen jika tidak terisi
        if (empty($validated['department'])) {
            $positionDeptMap = collect(EmployeeSyncService::positionRoleDepartmentMap())
                ->mapWithKeys(fn ($item) => [$item['position'] => $item['department']])
                ->all();
            $validated['department'] = $positionDeptMap[$validated['position']] ?? 'Operasional';
        }

        // Tanggal siap mulai kerja default adalah hari ini jika kosong
        if (empty($validated['join_date'])) {
            $validated['join_date'] = now()->toDateString();
        }

        // Status awal pendaftar melalui link publik
        $validated['employment_status'] = 'Pendaftar';

        // Simpan Berkas CV / Lamaran jika diunggah
        if ($request->hasFile('document')) {
            $validated['document_path'] = $request->file('document')->store('employee-documents', 'public');
        }

        // Simpan Foto KTP / Pas Foto jika diunggah
        if ($request->hasFile('id_card_photo')) {
            $validated['id_card_photo_path'] = $request->file('id_card_photo')->store('employee-id-cards', 'public');
        }

        // Hapus key yang tidak ada di model Employee
        unset($validated['document'], $validated['id_card_photo']);

        $employee = Employee::create($validated);

        return redirect()->route('employees.public.register.success')->with([
            'registered_name' => $employee->full_name,
            'registered_position' => $employee->position,
            'registered_phone' => $employee->phone,
            'registered_date' => now()->translatedFormat('d F Y - H:i'),
        ]);
    }

    /**
     * Halaman konfirmasi sukses setelah calon karyawan mendaftar.
     */
    public function success()
    {
        $storeName = Setting::getValue('store_name') ?: config('app.name', 'MStore');
        $storeLogo = Setting::getValue('store_logo');
        if (! $storeLogo) {
            $storeLogo = asset('img/logo.png');
        } elseif (! str_starts_with($storeLogo, 'http')) {
            $storeLogo = asset($storeLogo);
        }

        return view('employees.public_success', compact('storeName', 'storeLogo'));
    }

    /**
     * Cek apakah nama atau NIK calon karyawan sudah pernah terdaftar (AJAX / Real-time).
     */
    public function checkDuplicate(Request $request)
    {
        $name = trim((string) $request->query('name', ''));
        $nik = trim((string) $request->query('nik', ''));

        if ($name !== '' && mb_strlen($name) >= 3) {
            $existing = Employee::query()
                ->whereRaw('LOWER(TRIM(full_name)) = ?', [strtolower($name)])
                ->first();

            if ($existing) {
                return response()->json([
                    'exists' => true,
                    'field' => 'full_name',
                    'message' => "Nama \"{$existing->full_name}\" sudah terdaftar di sistem sebagai {$existing->position} ({$existing->employment_status}).",
                ]);
            }
        }

        if ($nik !== '' && strlen($nik) >= 10) {
            $existingNik = Employee::query()->where('nik', $nik)->first();
            if ($existingNik) {
                return response()->json([
                    'exists' => true,
                    'field' => 'nik',
                    'message' => "Nomor KTP/NIK ini sudah terdaftar atas nama {$existingNik->full_name}.",
                ]);
            }
        }

        return response()->json(['exists' => false]);
    }
}
