@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10 px-3 px-lg-0">
        <div class="card shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-body-emphasis fs-6 fs-md-5">{{ __('Ubah Perusahaan') }}</h5>
                <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> <span class="d-none d-sm-inline">{{ __('Kembali') }}</span>
                </a>
            </div>

            <div class="card-body p-3 p-md-4">
                <form method="POST" action="{{ route('companies.update', $company) }}">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-bold text-body-secondary text-uppercase small mb-3">{{ __('Informasi Perusahaan') }}</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="name" class="form-label small text-muted fw-bold">{{ __('Nama Perusahaan') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Masukkan nama perusahaan') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="code" class="form-label small text-muted fw-bold">{{ __('Kode Perusahaan') }}</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $company->code) }}" required class="form-control @error('code') is-invalid @enderror" placeholder="{{ __('Masukkan kode perusahaan') }}">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="tax_id" class="form-label small text-muted fw-bold">{{ __('NPWP') }}</label>
                            <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id', $company->tax_id) }}" class="form-control @error('tax_id') is-invalid @enderror" placeholder="{{ __('Masukkan NPWP') }}">
                            @error('tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="currency" class="form-label small text-muted fw-bold">{{ __('Mata Uang') }}</label>
                            <select name="currency" id="currency" class="form-select @error('currency') is-invalid @enderror">
                                <option value="IDR" {{ old('currency', $company->currency ?? 'IDR') == 'IDR' ? 'selected' : '' }}>IDR</option>
                                <option value="USD" {{ old('currency', $company->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('currency', $company->currency) == 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="SGD" {{ old('currency', $company->currency) == 'SGD' ? 'selected' : '' }}>SGD</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="country" class="form-label small text-muted fw-bold">{{ __('Negara') }}</label>
                            <select name="country" id="country" class="form-select @error('country') is-invalid @enderror">
                                <option value="ID" {{ old('country', $company->country ?? 'ID') == 'ID' ? 'selected' : '' }}>Indonesia</option>
                                <option value="US" {{ old('country', $company->country) == 'US' ? 'selected' : '' }}>United States</option>
                                <option value="SG" {{ old('country', $company->country) == 'SG' ? 'selected' : '' }}>Singapore</option>
                            </select>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $company->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">{{ __('Aktif') }}</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label small text-muted fw-bold">{{ __('Alamat') }}</label>
                            <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="{{ __('Masukkan alamat lengkap') }}">{{ old('address', $company->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold text-primary text-uppercase small mb-3">
                        <i class="fa-solid fa-crown me-1"></i> {{ __('Paket Langganan SaaS & Kuota Tenant') }}
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="subscription_plan" class="form-label small text-muted fw-bold">{{ __('Pilihan Paket') }}</label>
                            <select name="subscription_plan" id="subscription_plan" class="form-select @error('subscription_plan') is-invalid @enderror">
                                <option value="starter" {{ old('subscription_plan', $company->subscription_plan) == 'starter' ? 'selected' : '' }}>Toko & Retail (Starter)</option>
                                <option value="service" {{ old('subscription_plan', $company->subscription_plan) == 'service' ? 'selected' : '' }}>Jasa & Layanan (Service)</option>
                                <option value="pro" {{ old('subscription_plan', $company->subscription_plan) == 'pro' ? 'selected' : '' }}>Multi-Bisnis (Pro)</option>
                                <option value="enterprise" {{ old('subscription_plan', $company->subscription_plan) == 'enterprise' ? 'selected' : '' }}>Enterprise (Semua Fitur)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="subscription_status" class="form-label small text-muted fw-bold">{{ __('Status Langganan') }}</label>
                            <select name="subscription_status" id="subscription_status" class="form-select @error('subscription_status') is-invalid @enderror">
                                <option value="trial" {{ old('subscription_status', $company->subscription_status) == 'trial' ? 'selected' : '' }}>Trial (Masa Uji Coba)</option>
                                <option value="active" {{ old('subscription_status', $company->subscription_status) == 'active' ? 'selected' : '' }}>Aktif (Berlangganan)</option>
                                <option value="expired" {{ old('subscription_status', $company->subscription_status) == 'expired' ? 'selected' : '' }}>Expired (Kedaluwarsa)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="trial_ends_at" class="form-label small text-muted fw-bold">{{ __('Batas Masa Trial') }}</label>
                            <input type="date" name="trial_ends_at" id="trial_ends_at" class="form-control" value="{{ old('trial_ends_at', $company->trial_ends_at ? $company->trial_ends_at->format('Y-m-d') : '') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="subscription_ends_at" class="form-label small text-muted fw-bold">{{ __('Batas Masa Aktif Langganan') }}</label>
                            <input type="date" name="subscription_ends_at" id="subscription_ends_at" class="form-control" value="{{ old('subscription_ends_at', $company->subscription_ends_at ? $company->subscription_ends_at->format('Y-m-d') : '') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="max_users" class="form-label small text-muted fw-bold">{{ __('Batas Maksimal Pengguna / Staf') }}</label>
                            <input type="number" min="1" name="max_users" id="max_users" class="form-control" value="{{ old('max_users', $company->max_users ?? 5) }}">
                        </div>

                        <div class="col-md-6">
                            <label for="max_branches" class="form-label small text-muted fw-bold">{{ __('Batas Maksimal Cabang') }}</label>
                            <input type="number" min="1" name="max_branches" id="max_branches" class="form-control" value="{{ old('max_branches', $company->max_branches ?? 1) }}">
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label small text-muted fw-bold mb-2">{{ __('Kustomisasi Modul Aktif (Opsional: Kosongkan untuk mengikuti modul paket standar)') }}</label>
                            <div class="row g-2 p-3 bg-light rounded border">
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
                                            <label class="form-check-label small" for="mod_{{ $modKey }}">
                                                <i class="fa-solid {{ $modData['icon'] }} me-1 text-primary"></i> {{ $modData['label'] }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column-reverse flex-md-row justify-content-end gap-2 border-top pt-4 mobile-sticky-footer">
                        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary w-100 w-md-auto">{{ __('Batal') }}</a>
                        <button type="submit" class="btn btn-primary w-100 w-md-auto px-4">{{ __('Perbarui Perusahaan') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
