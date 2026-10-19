@extends('layouts.app')

@section('title', __('Ubah Perusahaan - ') . $company->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header Halaman -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>{{ __('Ubah Data Perusahaan') }}</h4>
            <p class="text-secondary small mb-0">{{ __('Perbarui informasi profil badan usaha, pengaturan langganan, kuota, dan modul aktif.') }}</p>
        </div>
        <div>
            <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Daftar') }}
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-edit text-primary me-2"></i>{{ __('Formulir Pembaruan Perusahaan') }}</h6>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('companies.update', $company) }}">
                        @csrf
                        @method('PUT')

                        <h6 class="fw-bold text-dark text-uppercase small mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i>{{ __('Informasi Dasar Perusahaan') }}</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label small text-secondary fw-semibold mb-1">{{ __('Nama Perusahaan') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required class="form-control form-control-sm @error('name') is-invalid @enderror" placeholder="{{ __('Masukkan nama perusahaan') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="code" class="form-label small text-secondary fw-semibold mb-1">{{ __('Kode Perusahaan') }} <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" value="{{ old('code', $company->code) }}" required class="form-control form-control-sm font-monospace @error('code') is-invalid @enderror" placeholder="{{ __('Masukkan kode perusahaan') }}">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="tax_id" class="form-label small text-secondary fw-semibold mb-1">{{ __('Nomor NPWP') }}</label>
                                <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id', $company->tax_id) }}" class="form-control form-control-sm font-monospace @error('tax_id') is-invalid @enderror" placeholder="{{ __('Masukkan NPWP') }}">
                                @error('tax_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="currency" class="form-label small text-secondary fw-semibold mb-1">{{ __('Mata Uang') }}</label>
                                <select name="currency" id="currency" class="form-select form-select-sm @error('currency') is-invalid @enderror">
                                    <option value="IDR" {{ old('currency', $company->currency ?? 'IDR') == 'IDR' ? 'selected' : '' }}>IDR (Rupiah Indonesia)</option>
                                    <option value="USD" {{ old('currency', $company->currency) == 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                    <option value="EUR" {{ old('currency', $company->currency) == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                    <option value="SGD" {{ old('currency', $company->currency) == 'SGD' ? 'selected' : '' }}>SGD (Singapore Dollar)</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="country" class="form-label small text-secondary fw-semibold mb-1">{{ __('Negara') }}</label>
                                <select name="country" id="country" class="form-select form-select-sm @error('country') is-invalid @enderror">
                                    <option value="ID" {{ old('country', $company->country ?? 'ID') == 'ID' ? 'selected' : '' }}>Indonesia</option>
                                    <option value="US" {{ old('country', $company->country) == 'US' ? 'selected' : '' }}>United States</option>
                                    <option value="SG" {{ old('country', $company->country) == 'SG' ? 'selected' : '' }}>Singapore</option>
                                    <option value="MY" {{ old('country', $company->country) == 'MY' ? 'selected' : '' }}>Malaysia</option>
                                </select>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch p-2 bg-light rounded-3 d-flex align-items-center gap-3">
                                    <input class="form-check-input ms-0" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $company->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark mb-0" for="is_active">{{ __('Status Perusahaan Aktif') }}</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label small text-secondary fw-semibold mb-1">{{ __('Alamat Lengkap Kantor') }}</label>
                                <textarea name="address" id="address" rows="2" class="form-control form-control-sm @error('address') is-invalid @enderror" placeholder="{{ __('Masukkan alamat lengkap') }}">{{ old('address', $company->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold text-dark text-uppercase small mb-3">
                            <i class="fa-solid fa-crown text-warning me-2"></i>{{ __('Paket Langganan SaaS & Kuota Tenant') }}
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="subscription_plan" class="form-label small text-secondary fw-semibold mb-1">{{ __('Pilihan Paket Usaha') }}</label>
                                <select name="subscription_plan" id="subscription_plan" class="form-select form-select-sm @error('subscription_plan') is-invalid @enderror">
                                    <option value="starter" {{ old('subscription_plan', $company->subscription_plan) == 'starter' ? 'selected' : '' }}>Toko & Retail (Starter)</option>
                                    <option value="service" {{ old('subscription_plan', $company->subscription_plan) == 'service' ? 'selected' : '' }}>Jasa & Layanan (Service)</option>
                                    <option value="pro" {{ old('subscription_plan', $company->subscription_plan) == 'pro' ? 'selected' : '' }}>Multi-Bisnis (Pro)</option>
                                    <option value="enterprise" {{ old('subscription_plan', $company->subscription_plan) == 'enterprise' ? 'selected' : '' }}>Enterprise (Semua Fitur)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="subscription_status" class="form-label small text-secondary fw-semibold mb-1">{{ __('Status Langganan') }}</label>
                                <select name="subscription_status" id="subscription_status" class="form-select form-select-sm @error('subscription_status') is-invalid @enderror">
                                    <option value="trial" {{ old('subscription_status', $company->subscription_status) == 'trial' ? 'selected' : '' }}>Trial (Masa Uji Coba)</option>
                                    <option value="active" {{ old('subscription_status', $company->subscription_status) == 'active' ? 'selected' : '' }}>Aktif (Berlangganan)</option>
                                    <option value="expired" {{ old('subscription_status', $company->subscription_status) == 'expired' ? 'selected' : '' }}>Expired (Kedaluwarsa)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="trial_ends_at" class="form-label small text-secondary fw-semibold mb-1">{{ __('Batas Masa Trial') }}</label>
                                <input type="date" name="trial_ends_at" id="trial_ends_at" class="form-control form-control-sm" value="{{ old('trial_ends_at', $company->trial_ends_at ? $company->trial_ends_at->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="subscription_ends_at" class="form-label small text-secondary fw-semibold mb-1">{{ __('Batas Masa Aktif Langganan') }}</label>
                                <input type="date" name="subscription_ends_at" id="subscription_ends_at" class="form-control form-control-sm" value="{{ old('subscription_ends_at', $company->subscription_ends_at ? $company->subscription_ends_at->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="max_users" class="form-label small text-secondary fw-semibold mb-1">{{ __('Batas Maksimal Pengguna / Staf') }}</label>
                                <input type="number" min="1" name="max_users" id="max_users" class="form-control form-control-sm font-monospace" value="{{ old('max_users', $company->max_users ?? 5) }}">
                            </div>

                            <div class="col-md-6">
                                <label for="max_branches" class="form-label small text-secondary fw-semibold mb-1">{{ __('Batas Maksimal Cabang Usaha') }}</label>
                                <input type="number" min="1" name="max_branches" id="max_branches" class="form-control form-control-sm font-monospace" value="{{ old('max_branches', $company->max_branches ?? 1) }}">
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label small text-secondary fw-semibold mb-2">{{ __('Kustomisasi Modul Aktif (Kosongkan untuk mengikuti modul default paket)') }}</label>
                                <div class="row g-3 p-3 bg-light rounded-3 border">
                                    @php
                                        $activeMods = old('enabled_modules', $company->enabled_modules ?? []);
                                        if (!is_array($activeMods)) { $activeMods = []; }
                                        $availableModules = [
                                            'pos' => ['label' => 'Toko & Retail (POS, Barcode, Kasir Toko)', 'icon' => 'fa-store'],
                                            'wash' => ['label' => 'Jasa & Layanan (Kasir Jasa, Shift, Paket Member, Loyalty)', 'icon' => 'fa-hand-sparkles'],
                                            'wedding' => ['label' => 'Booking & Reservasi (Pemesanan Acara, Sewa, Jadwal)', 'icon' => 'fa-calendar-check'],
                                            'accounting' => ['label' => 'Akuntansi PSAK (COA, Jurnal Umum, Neraca, Laba Rugi)', 'icon' => 'fa-book-journal-whills'],
                                            'finance' => ['label' => 'Keuangan & Kas (Kasbon, Bagi Hasil Investor)', 'icon' => 'fa-wallet'],
                                            'hr' => ['label' => 'SDM & Presensi (Kiosk QR, Slip Gaji, Payroll)', 'icon' => 'fa-fingerprint'],
                                            'cctv' => ['label' => 'Integrasi CCTV & Monitoring Keamanan', 'icon' => 'fa-video'],
                                        ];
                                    @endphp
                                    @foreach($availableModules as $modKey => $modData)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="enabled_modules[]" value="{{ $modKey }}" id="mod_{{ $modKey }}" {{ in_array($modKey, $activeMods, true) ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-medium text-dark" for="mod_{{ $modKey }}">
                                                    <i class="fa-solid {{ $modData['icon'] }} me-1 text-primary"></i> {{ $modData['label'] }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('companies.index') }}" class="btn btn-secondary btn-sm">{{ __('Batal') }}</a>
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4">
                                <i class="fa-solid fa-save me-1"></i> {{ __('Perbarui Perusahaan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
