@extends('layouts.app')

@section('title', __('Manajemen Perusahaan'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header Halaman -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-building text-primary me-2"></i>{{ __('Manajemen Perusahaan') }}</h4>
            <p class="text-secondary small mb-0">{{ __('Kelola profil tenant perusahaan, cabang, paket langganan, dan status aktif.') }}</p>
        </div>

        <div class="d-flex gap-2">
            @can('company.manage')
            <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> {{ __('Tambah Perusahaan') }}
            </a>
            @endcan
        </div>
    </div>

    <!-- Tabel Daftar Perusahaan -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-list text-primary me-2"></i>{{ __('Daftar Perusahaan Terdaftar') }}</h6>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">Total: {{ $companies->count() }} Perusahaan</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 small text-uppercase text-body-secondary fw-semibold">{{ __('Nama & Alamat') }}</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold" style="width: 120px;">{{ __('Kode') }}</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold">{{ __('Paket Langganan') }}</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold">{{ __('Status Langganan') }}</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold text-center" style="width: 120px;">{{ __('Status') }}</th>
                        <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold" style="width: 140px;">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 38px; height: 38px;">
                                        {{ strtoupper(substr($company->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $company->name }}</div>
                                        <div class="small text-secondary"><i class="fa-solid fa-location-dot me-1 text-muted"></i>{{ Str::limit($company->address ?? 'Alamat belum diatur', 45) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ $company->code }}</span>
                            </td>
                            <td>
                                @php
                                    $planLabels = [
                                        'starter' => ['label' => 'Toko & Retail (Starter)', 'badge' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25'],
                                        'service' => ['label' => 'Jasa & Layanan (Service)', 'badge' => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25'],
                                        'pro' => ['label' => 'Multi-Bisnis (Pro)', 'badge' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25'],
                                        'enterprise' => ['label' => 'Enterprise (All Modules)', 'badge' => 'bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25'],
                                    ];
                                    $planInfo = $planLabels[$company->subscription_plan ?? 'starter'] ?? ['label' => ucfirst($company->subscription_plan ?? 'Starter'), 'badge' => 'bg-secondary bg-opacity-10 text-secondary border'];
                                @endphp
                                <span class="badge {{ $planInfo['badge'] }} rounded-pill px-3 py-1 fw-semibold">{{ $planInfo['label'] }}</span>
                                <div class="small text-secondary mt-1">
                                    <i class="fa-solid fa-users me-1"></i> Max {{ $company->max_users ?? 5 }} User
                                    @if(($company->max_branches ?? 1) > 1)
                                        | <i class="fa-solid fa-code-branch me-1"></i> {{ $company->max_branches }} Cabang
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($company->subscription_status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fa-solid fa-circle-check me-1 small"></i> {{ __('Aktif') }}
                                    </span>
                                    @if($company->subscription_ends_at)
                                        <div class="small text-secondary mt-1"><i class="fa-regular fa-calendar-check me-1"></i>s/d {{ $company->subscription_ends_at->format('d M Y') }}</div>
                                    @endif
                                @elseif($company->subscription_status === 'trial')
                                    <span class="badge bg-warning bg-opacity-10 text-warning-emphasis rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fa-solid fa-hourglass-half me-1 small"></i> {{ __('Trial (Uji Coba)') }}
                                    </span>
                                    @if($company->trial_ends_at)
                                        <div class="small text-secondary mt-1"><i class="fa-regular fa-clock me-1"></i>s/d {{ $company->trial_ends_at->format('d M Y') }}</div>
                                    @endif
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fa-solid fa-circle-xmark me-1 small"></i> {{ __('Expired') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($company->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-semibold">{{ __('Aktif') }}</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1 fw-semibold">{{ __('Non-Aktif') }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    @can('company.view')
                                    <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-outline-primary shadow-sm" title="{{ __('Lihat Detail') }}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('company.manage')
                                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-secondary shadow-sm" title="{{ __('Ubah') }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Yakin ingin menghapus perusahaan ini?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" title="{{ __('Hapus') }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-building fa-2x mb-3 text-secondary opacity-50 d-block"></i>
                                {{ __('Belum ada perusahaan yang terdaftar.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
