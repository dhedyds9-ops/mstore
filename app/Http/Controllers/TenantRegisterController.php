<?php

namespace App\Http\Controllers;

use App\Models\AtkCashRegister;
use App\Models\Company;
use App\Models\CompanyBranch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantRegisterController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran toko baru
     */
    public function create(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $selectedPlan = $request->query('plan', 'starter');
        if (!in_array($selectedPlan, [Company::PLAN_STARTER, Company::PLAN_SERVICE, Company::PLAN_PRO, Company::PLAN_ENTERPRISE], true)) {
            $selectedPlan = Company::PLAN_STARTER;
        }

        return view('auth.register-tenant', compact('selectedPlan'));
    }

    /**
     * Simpan pendaftaran toko baru dan otomatis aktifkan akun
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|min:3|max:100',
            'owner_name' => 'required|string|min:3|max:100',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'business_type' => 'required|string|in:starter,service,pro,enterprise',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'store_name.required' => 'Nama toko / bisnis wajib diisi.',
            'owner_name.required' => 'Nama pemilik usaha wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.',
            'phone.required' => 'Nomor WhatsApp / telepon wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            // Generate kode toko unik (3 huruf dari nama + random angka)
            $cleanName = preg_replace('/[^A-Za-z]/', '', $validated['store_name']);
            $prefix = strtoupper(substr($cleanName, 0, 3));
            if (strlen($prefix) < 3) {
                $prefix = str_pad($prefix, 3, 'X');
            }
            $code = $prefix . '-' . mt_rand(1000, 9999);
            while (Company::where('code', $code)->exists()) {
                $code = $prefix . '-' . mt_rand(1000, 9999);
            }

            // 1. Buat Data Perusahaan / Toko Tenant
            $company = Company::create([
                'name' => $validated['store_name'],
                'code' => $code,
                'currency' => 'IDR',
                'country' => 'ID',
                'is_active' => true,
                'subscription_plan' => $validated['business_type'],
                'subscription_status' => Company::STATUS_TRIAL,
                'trial_ends_at' => now()->addDays(14),
                'max_branches' => ($validated['business_type'] === Company::PLAN_ENTERPRISE) ? 99 : (($validated['business_type'] === Company::PLAN_PRO) ? 3 : 1),
                'max_users' => ($validated['business_type'] === Company::PLAN_ENTERPRISE) ? 999 : (($validated['business_type'] === Company::PLAN_PRO) ? 15 : 5),
            ]);

            // 2. Buat Cabang Utama
            $branch = CompanyBranch::create([
                'company_id' => $company->id,
                'name' => 'Outlet Utama',
                'code' => 'PST',
                'is_active' => true,
            ]);

            // 3. Dapatkan Role Admin
            $adminRole = Role::where('name', Role::ADMIN)->first() ?? Role::first();

            // 4. Buat Akun Pengguna Pemilik
            $newUser = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'company_id' => $company->id,
                'company_branch_id' => $branch->id,
                'role_id' => $adminRole ? $adminRole->id : 1,
                'is_active' => true,
            ]);

            // 5. Buat Laci Kasir Awal
            try {
                AtkCashRegister::create([
                    'user_id' => $newUser->id,
                    'name' => 'Laci Kasir Utama',
                    'opening_balance' => 0,
                    'status' => 'open',
                    'opened_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Ignore register creation if optional
            }

            return $newUser;
        });

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Selamat datang di MStore! Toko Anda (' . $user->company->name . ') berhasil didaftarkan dengan Masa Uji Coba Gratis 14 Hari.');
    }
}
