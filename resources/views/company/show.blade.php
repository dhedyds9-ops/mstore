@extends('layouts.app')

@section('title', __('Detail Perusahaan - ') . $company->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header Halaman -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-building text-primary me-2"></i>{{ __('Detail Profil Perusahaan') }}</h4>
            <p class="text-secondary small mb-0">{{ __('Informasi lengkap badan usaha, konfigurasi tenant, dan lisensi langganan.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Daftar') }}
            </a>
            @can('company.manage')
            <a href="{{ route('companies.edit', $company) }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fa-solid fa-pen-to-square me-1"></i> {{ __('Ubah Perusahaan') }}
            </a>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Profil Utama -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-circle-info text-primary me-2"></i>{{ __('Informasi Dasar Perusahaan') }}</h6>
                    @if($company->is_active)
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-semibold">
                            <i class="fa-solid fa-circle-check me-1 small"></i>{{ __('Status Aktif') }}
                        </span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1 fw-semibold">{{ __('Non-Aktif') }}</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 60px; height: 60px;">
                            {{ strtoupper(substr($company->name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="fw-bold text-dark mb-1">{{ $company->name }}</h4>
                            <div class="text-secondary small">
                                <span class="badge bg-light text-dark border font-monospace me-2">{{ $company->code }}</span>
                                <i class="fa-solid fa-calendar-alt me-1 text-muted"></i> Terdaftar sejak {{ $company->created_at->format('d M Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-secondary fw-semibold mb-1">{{ __('Nomor NPWP / Pajak') }}</label>
                            <div class="fs-6 fw-medium font-monospace text-dark">{{ $company->tax_id ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary fw-semibold mb-1">{{ __('Mata Uang Default') }}</label>
                            <div class="fs-6 fw-medium text-dark"><span class="badge bg-light text-dark border px-2 py-1">{{ $company->currency ?? 'IDR' }}</span></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-secondary fw-semibold mb-1">{{ __('Negara Domisili') }}</label>
                            <div class="fs-6 fw-medium text-dark">{{ $company->country === 'ID' ? 'Indonesia (ID)' : ($company->country ?? 'ID') }}</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-secondary fw-semibold mb-1">{{ __('Alamat Lengkap Kantor / Cabang Utama') }}</label>
                            <div class="p-3 bg-light rounded-3 text-secondary small">
                                <i class="fa-solid fa-location-dot me-2 text-primary"></i>{{ $company->address ?: 'Alamat kantor belum dilengkapi.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Paket Langganan & Kuota Tenant -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-crown text-warning me-2"></i>{{ __('Paket & Kuota Lisensi') }}</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <span class="text-secondary small fw-semibold text-uppercase d-block mb-1">Paket UMKM Saat Ini</span>
                        @php
                            $planLabels = [
                                'starter' => ['label' => 'Toko & Retail (Starter)', 'color' => 'primary'],
                                'service' => ['label' => 'Jasa & Layanan (Service)', 'color' => 'info'],
                                'pro' => ['label' => 'Multi-Bisnis (Pro)', 'color' => 'success'],
                                'enterprise' => ['label' => 'Enterprise (All Modules)', 'color' => 'dark'],
                            ];
                            $planData = $planLabels[$company->subscription_plan ?? 'starter'] ?? ['label' => ucfirst($company->subscription_plan ?? 'Starter'), 'color' => 'secondary'];
                        @endphp
                        <span class="fs-5 fw-bold text-{{ $planData['color'] }}">{{ $planData['label'] }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="text-secondary small fw-semibold text-uppercase d-block mb-1">Status Langganan</span>
                        @if($company->subscription_status === 'active')
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-semibold">
                                <i class="fa-solid fa-circle-check me-1 small"></i> Berlangganan Aktif
                            </span>
                        @elseif($company->subscription_status === 'trial')
                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis rounded-pill px-3 py-1 fw-semibold">
                                <i class="fa-solid fa-hourglass-half me-1 small"></i> Masa Uji Coba (Trial)
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 fw-semibold">
                                <i class="fa-solid fa-circle-xmark me-1 small"></i> Kedaluwarsa
                            </span>
                        @endif
                    </div>

                    @if($company->subscription_ends_at)
                    <div class="mb-3">
                        <span class="text-secondary small fw-semibold text-uppercase d-block mb-1">Berlaku Sampai</span>
                        <div class="fw-bold text-dark"><i class="fa-regular fa-calendar-check me-1 text-primary"></i>{{ $company->subscription_ends_at->translatedFormat('d F Y') }}</div>
                    </div>
                    @endif

                    <div class="pt-3 border-top mt-3">
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-secondary"><i class="fa-solid fa-users me-1"></i> Batas Maks Pengguna:</span>
                            <span class="fw-bold font-monospace text-dark">{{ $company->max_users ?? 5 }} User</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-secondary"><i class="fa-solid fa-code-branch me-1"></i> Batas Maks Cabang:</span>
                            <span class="fw-bold font-monospace text-dark">{{ $company->max_branches ?? 1 }} Cabang</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
