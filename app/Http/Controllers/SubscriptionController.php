<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Katalog Paket Langganan UMKM
     */
    public static function getPlans(): array
    {
        return [
            Company::PLAN_STARTER => [
                'name' => 'Toko & Retail (Starter)',
                'tagline' => 'Solusi tepat untuk toko kelontong, retail, atk, distro & warung',
                'price_monthly' => 99000,
                'price_yearly' => 990000,
                'badge' => 'primary',
                'icon' => 'fa-store',
                'features' => [
                    'Kasir POS Toko & Barcode Scanner',
                    'Cetak Struk Thermal & Kirim via WhatsApp',
                    'Shift Kasir, Mutasi Kas & Laci Kasir',
                    'Manajemen Produk, Stok & Harga Bertingkat',
                    'Laporan Penjualan & Pengeluaran Toko',
                    'Pembukuan Kas Ringkas & Presensi Karyawan',
                    'Maksimal 5 Pengguna / Staf Kasir',
                    '1 Lokasi / Toko',
                ],
                'popular' => false,
            ],
            Company::PLAN_SERVICE => [
                'name' => 'Jasa & Layanan (Service)',
                'tagline' => 'Solusi ideal untuk car wash, salon, barbershop, laundry & bengkel',
                'price_monthly' => 129000,
                'price_yearly' => 1290000,
                'badge' => 'info',
                'icon' => 'fa-hand-sparkles',
                'features' => [
                    'Kasir Layanan Cepat & Kustom Antrean',
                    'Hitung Otomatis Komisi Pegawai / Cuci',
                    'Paket Berlangganan Member & Diskon Pelanggan',
                    'Loyalty Points & Otomatis Reward Kunjungan',
                    'Shift Kerja Karyawan & Laci Kasir',
                    'Laporan Pendapatan Jasa & Beban Bahan',
                    'Maksimal 5 Pengguna / Staf',
                    '1 Lokasi Usaha',
                ],
                'popular' => false,
            ],
            Company::PLAN_PRO => [
                'name' => 'Multi-Bisnis (Pro)',
                'tagline' => 'Pilihan terpopuler untuk usaha campuran ritel, jasa & reservasi sewa',
                'price_monthly' => 199000,
                'price_yearly' => 1990000,
                'badge' => 'success',
                'icon' => 'fa-crown',
                'features' => [
                    'SEMUA Fitur Toko & Retail Modern',
                    'SEMUA Fitur Jasa, Layanan & Komisi',
                    'Modul Booking & Reservasi / Event Organizer',
                    'Akuntansi Standar PSAK (Jurnal, Buku Besar, Neraca, Laba Rugi)',
                    'Laporan Bagi Hasil Investor & Laba Bersih Konsolidasi',
                    'Presensi QR Kiosk & Penggajian Payroll Terpadu',
                    'Payment Gateway QRIS, Transfer & E-Wallet (Midtrans/Duitku)',
                    'Maksimal 15 Pengguna & Mendukung Multi Cabang',
                ],
                'popular' => true,
            ],
            Company::PLAN_ENTERPRISE => [
                'name' => 'Enterprise & Franchise',
                'tagline' => 'Untuk jaringan bisnis berkembang dengan cabang & integrasi lengkap',
                'price_monthly' => 399000,
                'price_yearly' => 3990000,
                'badge' => 'dark',
                'icon' => 'fa-building-columns',
                'features' => [
                    'Semua Fitur Paket Pro Tanpa Batas',
                    'Multi Cabang / Multi Outlet Unlimited',
                    'Unlimited Pengguna / Staf Kasir',
                    'Integrasi CCTV Monitoring Keamanan Terpusat',
                    'Akses Full API Keys & Webhook Notifikasi',
                    'Prioritas Bantuan Teknis & Pelatihan Sistem',
                    'Audit Trail Keamanan & Log Aktivitas Lengkap',
                ],
                'popular' => false,
            ],
        ];
    }

    /**
     * Halaman Dashboard Langganan UMKM
     */
    public function index()
    {
        $user = auth()->user();
        $company = $user->company;

        if (!$company) {
            // Jika akun belum terikat ke company, ambil default atau buat referensi
            $company = Company::first();
        }

        $plans = self::getPlans();
        $currentPlanKey = $company ? ($company->subscription_plan ?? Company::PLAN_STARTER) : Company::PLAN_STARTER;
        $currentPlan = $plans[$currentPlanKey] ?? $plans[Company::PLAN_STARTER];

        // Hitung kuota staf yang terpakai
        $totalUsers = $company ? User::where('company_id', $company->id)->count() : User::count();
        $maxUsers = $company ? ($company->max_users ?? 5) : 5;

        // Hitung kuota cabang
        $totalBranches = $company ? $company->branches()->count() : 1;
        $maxBranches = $company ? ($company->max_branches ?? 1) : 1;

        // Status dan sisa hari
        $daysRemaining = 0;
        $isTrial = ($company && $company->subscription_status === Company::STATUS_TRIAL);
        $isActive = ($company && $company->subscription_status === Company::STATUS_ACTIVE);
        $isExpired = !$company || !$company->hasActiveSubscription();

        if ($company) {
            $endDate = $isTrial ? $company->trial_ends_at : $company->subscription_ends_at;
            if ($endDate) {
                $daysRemaining = max(0, now()->diffInDays($endDate, false));
            }
        }

        return view('subscription.index', compact(
            'company',
            'plans',
            'currentPlanKey',
            'currentPlan',
            'totalUsers',
            'maxUsers',
            'totalBranches',
            'maxBranches',
            'daysRemaining',
            'isTrial',
            'isActive',
            'isExpired'
        ));
    }

    /**
     * Konfirmasi Perpanjangan / Upgrade Paket
     */
    public function upgrade(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|string|in:starter,service,pro,enterprise',
            'period' => 'required|string|in:1_month,12_month',
        ]);

        $user = auth()->user();
        $company = $user->company ?? Company::first();

        if (!$company) {
            return back()->with('error', 'Profil usaha tidak ditemukan.');
        }

        $monthsToAdd = ($validated['period'] === '12_month') ? 12 : 1;
        $baseDate = ($company->subscription_ends_at && $company->subscription_ends_at->isFuture())
            ? $company->subscription_ends_at
            : now();

        $company->update([
            'subscription_plan' => $validated['plan'],
            'subscription_status' => Company::STATUS_ACTIVE,
            'subscription_ends_at' => (clone $baseDate)->addMonths($monthsToAdd),
            'enabled_modules' => null, // Reset ke paket default baru
        ]);

        $plans = self::getPlans();
        $planName = $plans[$validated['plan']]['name'] ?? $validated['plan'];

        return redirect()->route('subscription.index')->with('success', "Selamat! Paket usaha Anda telah berhasil diaktifkan ke {$planName} untuk {$monthsToAdd} bulan ke depan.");
    }
}
