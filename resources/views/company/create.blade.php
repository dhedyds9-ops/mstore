@extends('layouts.app')

@section('title', __('Tambah Perusahaan'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header Halaman -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-building-circle-check text-primary me-2"></i>{{ __('Tambah Perusahaan Baru') }}</h4>
            <p class="text-secondary small mb-0">{{ __('Daftarkan profil tenant perusahaan baru beserta alokasi paket langganan dan cabang.') }}</p>
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
                    <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-edit text-primary me-2"></i>{{ __('Formulir Pendaftaran Perusahaan') }}</h6>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('companies.store') }}">
                        @csrf

                        <h6 class="fw-bold text-dark text-uppercase small mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i>{{ __('Informasi Dasar Perusahaan') }}</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label small text-secondary fw-semibold mb-1">{{ __('Nama Perusahaan') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="form-control form-control-sm @error('name') is-invalid @enderror" placeholder="{{ __('Contoh: PT Sumber Berkah Mandiri') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="code" class="form-label small text-secondary fw-semibold mb-1">{{ __('Kode Perusahaan (Unik)') }} <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}" required class="form-control form-control-sm font-monospace @error('code') is-invalid @enderror" placeholder="{{ __('Contoh: SBM-01') }}">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="tax_id" class="form-label small text-secondary fw-semibold mb-1">{{ __('Nomor NPWP') }}</label>
                                <input type="text" name="tax_id" id="tax_id" value="{{ old('tax_id') }}" class="form-control form-control-sm font-monospace @error('tax_id') is-invalid @enderror" placeholder="{{ __('Contoh: 01.234.567.8-901.000') }}">
                                @error('tax_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="currency" class="form-label small text-secondary fw-semibold mb-1">{{ __('Mata Uang') }}</label>
                                <select name="currency" id="currency" class="form-select form-select-sm @error('currency') is-invalid @enderror">
                                    <option value="IDR" {{ old('currency', 'IDR') == 'IDR' ? 'selected' : '' }}>IDR (Rupiah Indonesia)</option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                                    <option value="SGD" {{ old('currency') == 'SGD' ? 'selected' : '' }}>SGD (Singapore Dollar)</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="country" class="form-label small text-secondary fw-semibold mb-1">{{ __('Negara') }}</label>
                                <select name="country" id="country" class="form-select form-select-sm @error('country') is-invalid @enderror">
                                    <option value="ID" {{ old('country', 'ID') == 'ID' ? 'selected' : '' }}>Indonesia</option>
                                    <option value="US" {{ old('country') == 'US' ? 'selected' : '' }}>United States</option>
                                    <option value="SG" {{ old('country') == 'SG' ? 'selected' : '' }}>Singapore</option>
                                    <option value="MY" {{ old('country') == 'MY' ? 'selected' : '' }}>Malaysia</option>
                                </select>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label small text-secondary fw-semibold mb-1">{{ __('Alamat Lengkap Kantor') }}</label>
                                <textarea name="address" id="address" rows="2" class="form-control form-control-sm @error('address') is-invalid @enderror" placeholder="{{ __('Masukkan alamat jalan, nomor, kota, dan kode pos') }}">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold text-dark text-uppercase small mb-3">
                            <i class="fa-solid fa-crown text-warning me-2"></i>{{ __('Paket Langganan Awal & Kuota Tenant') }}
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="subscription_plan" class="form-label small text-secondary fw-semibold mb-1">{{ __('Pilihan Paket Usaha') }}</label>
                                <select name="subscription_plan" id="subscription_plan" class="form-select form-select-sm @error('subscription_plan') is-invalid @enderror">
                                    <option value="starter" {{ old('subscription_plan', 'starter') == 'starter' ? 'selected' : '' }}>Toko & Retail (Starter) - POS & Barcode</option>
                                    <option value="service" {{ old('subscription_plan') == 'service' ? 'selected' : '' }}>Jasa & Layanan (Service) - Kasir Cuci & Member</option>
                                    <option value="pro" {{ old('subscription_plan') == 'pro' ? 'selected' : '' }}>Multi-Bisnis (Pro) - Toko + Jasa + Booking</option>
                                    <option value="enterprise" {{ old('subscription_plan') == 'enterprise' ? 'selected' : '' }}>Enterprise (Semua Modul & Kuota Lengkap)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="subscription_status" class="form-label small text-secondary fw-semibold mb-1">{{ __('Status Langganan Awal') }}</label>
                                <select name="subscription_status" id="subscription_status" class="form-select form-select-sm @error('subscription_status') is-invalid @enderror">
                                    <option value="trial" {{ old('subscription_status', 'trial') == 'trial' ? 'selected' : '' }}>Trial (Masa Uji Coba 14 Hari)</option>
                                    <option value="active" {{ old('subscription_status') == 'active' ? 'selected' : '' }}>Aktif Langsung</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="max_users" class="form-label small text-secondary fw-semibold mb-1">{{ __('Batas Pengguna / Staf') }}</label>
                                <input type="number" min="1" name="max_users" id="max_users" class="form-control form-control-sm font-monospace" value="{{ old('max_users', 5) }}">
                            </div>

                            <div class="col-md-6">
                                <label for="max_branches" class="form-label small text-secondary fw-semibold mb-1">{{ __('Batas Cabang Usaha') }}</label>
                                <input type="number" min="1" name="max_branches" id="max_branches" class="form-control form-control-sm font-monospace" value="{{ old('max_branches', 1) }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="{{ route('companies.index') }}" class="btn btn-secondary btn-sm">{{ __('Batal') }}</a>
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4">
                                <i class="fa-solid fa-save me-1"></i> {{ __('Simpan Perusahaan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
