@extends('layouts.app')

@section('title', __('Manajemen Kasbon & Pinjaman Karyawan'))

@section('content')
@php
    // Hitung Summary Data
    $totalKasbonPending = 0;
    $totalBonusPending = 0;
    $totalPinjamanAktif = 0;
    $totalOutstanding = 0;
    $totalCicilanDibayar = 0;
    $karyawanMemilikiKasbon = 0;
    $loanSelesai = 0;

    foreach ($recap as $r) {
        $totalKasbonPending += $r['total_kasbon_biasa'];
        $totalPinjamanAktif += $r['total_pinjaman_aktif'];
        $totalCicilanDibayar += $r['total_cicilan'];
        $totalOutstanding += $r['sisa_pinjaman'];
        if ($r['total_kasbon_biasa'] > 0 || $r['total_pinjaman_aktif'] > 0) {
            $karyawanMemilikiKasbon++;
        }
    }
    
    foreach ($loans as $loan) {
        if ($loan->status === 'closed') {
            $loanSelesai++;
        }
    }
    
    foreach ($bonusAdjustments as $bonus) {
        if ($bonus->status === 'pending') {
            $totalBonusPending += $bonus->amount;
        }
    }
@endphp

<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Page Header --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1.5 rounded-pill fs-7 fw-bold">
                            <i class="fa-solid fa-hand-holding-dollar me-1"></i> SDM & Keuangan
                        </span>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ __('Manajemen Kasbon & Pinjaman Karyawan') }}</h3>
                    <p class="mb-0 text-muted small">
                        {{ __('Kelola pencatatan kasbon sekali potong, pinjaman angsuran berkala, dan bonus insentif karyawan.') }}
                    </p>
                </div>
                @if(Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']))
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#addKasbonModal">
                            <i class="fas fa-hand-holding-dollar me-1.5"></i>
                            {{ __('+ Kasbon Biasa') }}
                        </button>
                        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addKasbonLoanModal">
                            <i class="fas fa-file-invoice-dollar me-1.5"></i>
                            {{ __('+ Pinjaman Angsuran') }}
                        </button>
                        <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#addBonusModal">
                            <i class="fas fa-gift me-1.5"></i>
                            {{ __('+ Tambah Bonus') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 4 Kartu Metrik Utama --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-danger">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted fw-bold small">{{ __('Total Sisa Hutang (Outstanding)') }}</span>
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-coins fs-6"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-danger">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h3>
                    <small class="text-muted d-block">{{ $karyawanMemilikiKasbon }} {{ __('karyawan memiliki kewajiban') }}</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted fw-bold small">{{ __('Kasbon Biasa (Pending)') }}</span>
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-money-bill-wave fs-6"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-warning">Rp {{ number_format($totalKasbonPending, 0, ',', '.') }}</h3>
                    <small class="text-muted d-block">{{ $kasbonAdjustments->total() }} {{ __('transaksi siap potong gaji') }}</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted fw-bold small">{{ __('Pinjaman Angsuran Aktif') }}</span>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-credit-card fs-6"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-primary">Rp {{ number_format($totalPinjamanAktif, 0, ',', '.') }}</h3>
                    <small class="text-muted d-block">{{ $loans->where('status', 'active')->count() }} {{ __('pinjaman cicilan berjalan') }}</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-uppercase text-muted fw-bold small">{{ __('Bonus Pending') }}</span>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-gift fs-6"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-success">Rp {{ number_format($totalBonusPending, 0, ',', '.') }}</h3>
                    <small class="text-muted d-block">{{ $bonusAdjustments->total() }} {{ __('penambahan insentif gaji') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('technicians.kasbon.index') }}" method="GET" id="filterForm">
                <input type="hidden" name="tab" id="filterTabInput" value="{{ request('tab', 'recap') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-muted mb-1">{{ __('Filter Karyawan') }}</label>
                        <select name="user_id" class="form-select" id="filterTechnician">
                            <option value="">{{ __('Semua Karyawan') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label small text-muted mb-1">{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('Semua Status') }}</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Belum Diproses (Pending)') }}</option>
                            <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>{{ __('Sudah Diproses (Lunas/Dipotong)') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">{{ __('Tanggal Mulai') }}</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <label class="form-label small text-muted mb-1">{{ __('Tanggal Selesai') }}</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
                    </div>
                    <div class="col-lg-2 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('technicians.kasbon.index') }}" class="btn btn-outline-secondary" title="{{ __('Reset Filter') }}">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Navigasi Tab Modern --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom p-2 px-md-3">
            <ul class="nav nav-pills card-header-pills gap-1" id="kasbonTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-3 py-2 fw-semibold {{ request('tab', 'recap') === 'recap' ? 'active' : '' }}" 
                            id="tab-recap-btn" data-bs-toggle="pill" data-bs-target="#tab-recap" type="button" role="tab">
                        <i class="fa-solid fa-users me-1.5"></i>
                        {{ __('1. Rekap per Karyawan') }}
                        <span class="badge bg-secondary bg-opacity-25 text-dark rounded-pill ms-1">{{ $recap->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-3 py-2 fw-semibold {{ request('tab') === 'biasa' ? 'active' : '' }}" 
                            id="tab-biasa-btn" data-bs-toggle="pill" data-bs-target="#tab-biasa" type="button" role="tab">
                        <i class="fa-solid fa-money-bill-wave me-1.5"></i>
                        {{ __('2. Kasbon Sekali Potong') }}
                        <span class="badge bg-warning bg-opacity-25 text-dark rounded-pill ms-1">{{ $kasbonAdjustments->total() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-3 py-2 fw-semibold {{ request('tab') === 'loans' ? 'active' : '' }}" 
                            id="tab-loans-btn" data-bs-toggle="pill" data-bs-target="#tab-loans" type="button" role="tab">
                        <i class="fa-solid fa-file-invoice-dollar me-1.5"></i>
                        {{ __('3. Kasbon Angsuran (Pinjaman)') }}
                        <span class="badge bg-primary bg-opacity-25 text-dark rounded-pill ms-1">{{ $loans->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-3 py-2 fw-semibold {{ request('tab') === 'bonus' ? 'active' : '' }}" 
                            id="tab-bonus-btn" data-bs-toggle="pill" data-bs-target="#tab-bonus" type="button" role="tab">
                        <i class="fa-solid fa-gift me-1.5"></i>
                        {{ __('4. Bonus & Insentif') }}
                        <span class="badge bg-success bg-opacity-25 text-dark rounded-pill ms-1">{{ $bonusAdjustments->total() }}</span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-3 p-md-4">
            <div class="tab-content" id="kasbonTabsContent">
                
                {{-- TAB 1: REKAP PER KARYAWAN --}}
                <div class="tab-pane fade {{ request('tab', 'recap') === 'recap' ? 'show active' : '' }}" id="tab-recap" role="tabpanel">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ __('Ringkasan Saldo per Karyawan') }}</h5>
                            <small class="text-muted">{{ __('Rincian kasbon biasa dan sisa angsuran per masing-masing staf.') }}</small>
                        </div>
                        <div style="min-width: 250px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchTechnician" placeholder="{{ __('Ketik nama karyawan...') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3" id="userCards">
                        @forelse($recap as $item)
                            <div class="col-xl-3 col-lg-4 col-md-6 user-card" data-name="{{ strtolower($item['user']->name) }}">
                                <x-kasbon.user-summary 
                                    :user="$item['user']"
                                    :total-kasbon-biasa="$item['total_kasbon_biasa']"
                                    :total-pinjaman-aktif="$item['total_pinjaman_aktif']"
                                    :total-cicilan="$item['total_cicilan']"
                                    :sisa-pinjaman="$item['sisa_pinjaman']"
                                />
                            </div>
                        @empty
                            <div class="col-12">
                                <x-kasbon.empty-state 
                                    icon="fa-users"
                                    title="{{ __('Belum ada data karyawan') }}"
                                />
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- TAB 2: KASBON BIASA (SEKALI POTONG) --}}
                <div class="tab-pane fade {{ request('tab') === 'biasa' ? 'show active' : '' }}" id="tab-biasa" role="tabpanel">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ __('Daftar Kasbon Biasa (Sekali Potong)') }}</h5>
                            <small class="text-muted">{{ __('Kasbon harian yang otomatis dipotong penuh saat penggajian/slip gaji berikutnya.') }}</small>
                        </div>
                        @if(Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']))
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#addKasbonModal">
                                <i class="fas fa-plus me-1"></i>{{ __('Tambah Kasbon Biasa') }}
                            </button>
                        @endif
                    </div>

                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold text-center" style="width: 50px;">{{ __('No') }}</th>
                                    <th class="fw-semibold">{{ __('Nama Karyawan') }}</th>
                                    <th class="fw-semibold">{{ __('Tanggal Pengajuan') }}</th>
                                    <th class="fw-semibold">{{ __('Jumlah Kasbon') }}</th>
                                    <th class="fw-semibold">{{ __('Keterangan / Keperluan') }}</th>
                                    <th class="fw-semibold text-center">{{ __('Status') }}</th>
                                    <th class="fw-semibold text-end pe-3">{{ __('Aksi') }}</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($kasbonAdjustments as $index => $adjustment)
                                    <tr>
                                        <td class="text-center text-muted small">{{ $kasbonAdjustments->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2.5" style="width: 32px; height: 32px; font-weight: 600;">
                                                    {{ strtoupper(substr($adjustment->user->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $adjustment->user->name ?? '-' }}</div>
                                                    <small class="text-muted">{{ $adjustment->user->role->name ?? 'Staf' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            <i class="far fa-calendar-alt me-1 text-muted"></i>
                                            {{ $adjustment->date ? $adjustment->date->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td>
                                            <span class="fw-bold text-danger fs-6">
                                                Rp {{ number_format($adjustment->amount, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-secondary small">{{ $adjustment->description ?: '-' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <x-kasbon.status-badge :status="$adjustment->status" />
                                        </td>
                                        <td class="text-end pe-3">
                                            @if(Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']))
                                                @if($adjustment->status !== 'processed')
                                                    <div class="d-inline-flex gap-1">
                                                        <button type="button" class="btn btn-outline-primary btn-sm px-2" data-bs-toggle="modal" data-bs-target="#editKasbonModal-{{ $adjustment->id }}" title="{{ __('Edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('salary-adjustments.destroy', $adjustment) }}" method="POST" class="d-inline" data-no-loading="true" data-confirm="{{ __('Hapus kasbon ini?') }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm px-2" title="{{ __('Hapus') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary small">
                                                        <i class="fas fa-check-double me-1"></i>{{ __('Telah Dipotong') }}
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <x-kasbon.empty-state 
                                                icon="fa-inbox"
                                                title="{{ __('Belum ada data kasbon biasa') }}"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($kasbonAdjustments->hasPages())
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-2">
                            <small class="text-muted">
                                {{ __('Menampilkan') }} {{ $kasbonAdjustments->firstItem() }} - {{ $kasbonAdjustments->lastItem() }} {{ __('dari') }} {{ $kasbonAdjustments->total() }} {{ __('data') }}
                            </small>
                            <div>
                                {{ $kasbonAdjustments->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- TAB 3: KASBON ANGSURAN (PINJAMAN BERKALA) --}}
                <div class="tab-pane fade {{ request('tab') === 'loans' ? 'show active' : '' }}" id="tab-loans" role="tabpanel">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ __('Pinjaman Angsuran Berkala') }}</h5>
                            <small class="text-muted">{{ __('Pinjaman jumlah besar dengan skema cicilan bulanan yang dapat dilunasi bertahap.') }}</small>
                        </div>
                        @if(Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']))
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addKasbonLoanModal">
                                <i class="fas fa-plus me-1"></i>{{ __('Buat Pinjaman Angsuran') }}
                            </button>
                        @endif
                    </div>

                    @forelse($loans as $loan)
                        @php
                            $totalPaid = $loan->installments->sum('amount');
                            $remaining = $loan->remaining;
                            $percentage = $loan->principal_amount > 0 ? min(100, ($totalPaid / $loan->principal_amount) * 100) : 0;
                        @endphp
                        <div class="card border rounded-3 mb-3 shadow-none bg-light bg-opacity-25">
                            <div class="card-header bg-white border-bottom py-3">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="p-2.5 rounded-circle bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-user-tie fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $loan->user->name ?? '-' }}</h6>
                                            <small class="text-muted">
                                                <i class="far fa-calendar-alt me-1"></i>{{ __('Mulai:') }} {{ $loan->start_date ? $loan->start_date->translatedFormat('d M Y') : '-' }}
                                                @if($loan->description)
                                                    &bull; <span class="fst-italic">{{ $loan->description }}</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <x-kasbon.status-badge :status="$loan->status" text="{{ $loan->status === 'active' ? 'Aktif Berjalan' : 'Selesai (Lunas)' }}" />
                                        @if(Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']))
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editKasbonLoanModal-{{ $loan->id }}" title="{{ __('Edit Pinjaman') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if($loan->status === 'active')
                                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addInstallmentModal-{{ $loan->id }}">
                                                        <i class="fas fa-receipt me-1"></i>{{ __('Bayar Cicilan') }}
                                                    </button>
                                                @endif
                                                <form action="{{ route('kasbon-loans.destroy', $loan) }}" method="POST" class="d-inline" data-no-loading="true">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('{{ __('Hapus kasbon angsuran ini?') }}')" title="{{ __('Hapus') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3 p-md-4 bg-white">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-3 col-6">
                                        <div class="border rounded-3 p-2.5 bg-light bg-opacity-50">
                                            <small class="text-muted d-block">{{ __('Total Pokok Pinjaman') }}</small>
                                            <h6 class="fw-bold mb-0 text-dark">
                                                Rp {{ number_format($loan->principal_amount, 0, ',', '.') }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="border rounded-3 p-2.5 bg-light bg-opacity-50">
                                            <small class="text-muted d-block">{{ __('Total Terbayar') }}</small>
                                            <h6 class="fw-bold mb-0 text-success">
                                                Rp {{ number_format($totalPaid, 0, ',', '.') }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="border rounded-3 p-2.5 bg-light bg-opacity-50">
                                            <small class="text-muted d-block">{{ __('Sisa Kewajiban (Sisa Pokok)') }}</small>
                                            <h6 class="fw-bold mb-0 text-danger">
                                                Rp {{ number_format($remaining, 0, ',', '.') }}
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="border rounded-3 p-2.5 bg-light bg-opacity-50">
                                            <small class="text-muted d-block">{{ __('Tenor & Target Bulanan') }}</small>
                                            <h6 class="fw-bold mb-0 text-secondary">
                                                {{ $loan->tenor_months ? $loan->tenor_months . ' bln' : '-' }} &bull; {{ $loan->monthly_installment ? 'Rp ' . number_format($loan->monthly_installment, 0, ',', '.') : '-' }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>

                                {{-- Progress Pelunasan --}}
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>{{ __('Progress Pelunasan') }}</span>
                                        <span class="fw-bold text-success">{{ number_format($percentage, 1) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                {{-- Riwayat Cicilan Accordion --}}
                                @if($loan->installments->count() > 0)
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstallment-{{ $loan->id }}">
                                            <i class="fas fa-list-check me-1"></i>{{ __('Lihat Riwayat Cicilan') }} ({{ $loan->installments->count() }}x pembayaran) <i class="fas fa-chevron-down ms-1 small"></i>
                                        </button>
                                        <div class="collapse mt-2" id="collapseInstallment-{{ $loan->id }}">
                                            <div class="table-responsive border rounded-3 bg-white">
                                                <table class="table table-sm table-hover align-middle mb-0">
                                                    <thead class="table-light small">
                                                        <tr>
                                                            <th>{{ __('Tanggal Bayar') }}</th>
                                                            <th>{{ __('Nominal Cicilan') }}</th>
                                                            <th>{{ __('Keterangan') }}</th>
                                                            <th>{{ __('Metode') }}</th>
                                                            <th class="text-end pe-2">{{ __('Aksi') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="small">
                                                        @foreach($loan->installments as $installment)
                                                            <tr>
                                                                <td class="text-muted">{{ $installment->date ? $installment->date->translatedFormat('d M Y') : '-' }}</td>
                                                                <td class="fw-bold text-success">Rp {{ number_format($installment->amount, 0, ',', '.') }}</td>
                                                                <td>{{ $installment->description ?: '-' }}</td>
                                                                <td>
                                                                    @if($installment->salary_adjustment_id)
                                                                        <span class="badge bg-info-subtle text-info">{{ __('Potong Slip Gaji') }}</span>
                                                                    @else
                                                                        <span class="badge bg-secondary-subtle text-secondary">{{ __('Setor Tunai / Manual') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-end pe-2">
                                                                    @if(Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']))
                                                                        <form action="{{ route('kasbon-loans.installments.destroy', [$loan, $installment]) }}" method="POST" class="d-inline" data-no-loading="true">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('{{ __('Hapus cicilan ini?') }}')" title="{{ __('Hapus Cicilan') }}">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <x-kasbon.empty-state 
                            icon="fa-credit-card"
                            title="{{ __('Belum ada data pinjaman angsuran') }}"
                        />
                    @endforelse
                </div>

                {{-- TAB 4: BONUS & INSENTIF --}}
                <div class="tab-pane fade {{ request('tab') === 'bonus' ? 'show active' : '' }}" id="tab-bonus" role="tabpanel">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ __('Daftar Bonus & Insentif Karyawan') }}</h5>
                            <small class="text-muted">{{ __('Tambahan pendapatan yang akan diakumulasikan ke slip gaji karyawan.') }}</small>
                        </div>
                        @if(Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']))
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addBonusModal">
                                <i class="fas fa-plus me-1"></i>{{ __('Tambah Bonus Karyawan') }}
                            </button>
                        @endif
                    </div>

                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold text-center" style="width: 50px;">{{ __('No') }}</th>
                                    <th class="fw-semibold">{{ __('Nama Karyawan') }}</th>
                                    <th class="fw-semibold">{{ __('Tanggal Diberikan') }}</th>
                                    <th class="fw-semibold">{{ __('Nominal Bonus') }}</th>
                                    <th class="fw-semibold">{{ __('Keterangan / Alasan') }}</th>
                                    <th class="fw-semibold text-center">{{ __('Status') }}</th>
                                    <th class="fw-semibold text-end pe-3">{{ __('Aksi') }}</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($bonusAdjustments as $index => $bonus)
                                    <tr>
                                        <td class="text-center text-muted small">{{ $bonusAdjustments->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-2.5" style="width: 32px; height: 32px; font-weight: 600;">
                                                    {{ strtoupper(substr($bonus->user->name ?? 'U', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $bonus->user->name ?? '-' }}</div>
                                                    <small class="text-muted">{{ $bonus->user->role->name ?? 'Staf' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            <i class="far fa-calendar-alt me-1 text-muted"></i>
                                            {{ $bonus->date ? $bonus->date->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success fs-6">
                                                Rp {{ number_format($bonus->amount, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-secondary small">{{ $bonus->description ?: '-' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <x-kasbon.status-badge :status="$bonus->status" />
                                        </td>
                                        <td class="text-end pe-3">
                                            @if(Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']))
                                                @if($bonus->status !== 'processed')
                                                    <div class="d-inline-flex gap-1">
                                                        <button type="button" class="btn btn-outline-primary btn-sm px-2" data-bs-toggle="modal" data-bs-target="#editBonusModal-{{ $bonus->id }}" title="{{ __('Edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('salary-adjustments.destroy', $bonus) }}" method="POST" class="d-inline" data-no-loading="true" data-confirm="{{ __('Hapus bonus ini?') }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm px-2" title="{{ __('Hapus') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary small">
                                                        <i class="fas fa-check-double me-1"></i>{{ __('Telah Dibayarkan') }}
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">
                                            <x-kasbon.empty-state 
                                                icon="fa-gift"
                                                title="{{ __('Belum ada data bonus') }}"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($bonusAdjustments->hasPages())
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-2">
                            <small class="text-muted">
                                {{ __('Menampilkan') }} {{ $bonusAdjustments->firstItem() }} - {{ $bonusAdjustments->lastItem() }} {{ __('dari') }} {{ $bonusAdjustments->total() }} {{ __('data') }}
                            </small>
                            <div>
                                {{ $bonusAdjustments->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ==================== MODALS ==================== --}}

{{-- Modal Tambah Kasbon Biasa --}}
<div class="modal fade" id="addKasbonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning bg-opacity-10 border-bottom-0">
                <div class="d-flex align-items-center">
                    <div class="p-2 rounded-circle bg-warning text-dark me-2">
                        <i class="fas fa-hand-holding-dollar"></i>
                    </div>
                    <h5 class="modal-title mb-0 fw-bold">{{ __('Tambah Kasbon Biasa') }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                @if ($errors->any() && old('type') === 'kasbon')
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('salary-adjustments.store') }}" method="POST" id="addKasbonForm" data-no-loading="true">
                    @csrf
                    <input type="hidden" name="type" value="kasbon">
                    <div class="mb-3">
                        <label for="add_user_id" class="form-label fw-semibold small">{{ __('Pilih Karyawan') }} <span class="text-danger">*</span></label>
                        <select name="user_id" id="add_user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">{{ __('-- Pilih Karyawan --') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->role->name ?? 'Staf' }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_amount" class="form-label fw-semibold small">{{ __('Nominal Kasbon (Rp)') }} <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="add_amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Contoh: 100000" required min="1" value="{{ old('amount') }}">
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_date" class="form-label fw-semibold small">{{ __('Tanggal Kasbon') }} <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="add_date" class="form-control @error('date') is-invalid @enderror" required value="{{ old('date', date('Y-m-d')) }}">
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label fw-semibold small">{{ __('Keterangan / Keperluan') }}</label>
                        <textarea name="description" id="add_description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="{{ __('Tuliskan alasan kasbon...') }}">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                <button type="submit" class="btn btn-warning fw-semibold px-4" form="addKasbonForm">{{ __('Simpan Kasbon') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Kasbon Angsuran --}}
<div class="modal fade" id="addKasbonLoanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0">
                <div class="d-flex align-items-center">
                    <div class="p-2 rounded-circle bg-primary text-white me-2">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h5 class="modal-title mb-0 fw-bold">{{ __('Buat Pinjaman Angsuran') }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                @if ($errors->any() && !old('type'))
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('kasbon-loans.store') }}" method="POST" id="addKasbonLoanForm" data-no-loading="true">
                    @csrf
                    <div class="mb-3">
                        <label for="add_loan_user_id" class="form-label fw-semibold small">{{ __('Pilih Karyawan') }} <span class="text-danger">*</span></label>
                        <select name="user_id" id="add_loan_user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">{{ __('-- Pilih Karyawan --') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->role->name ?? 'Staf' }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_principal_amount" class="form-label fw-semibold small">{{ __('Total Pokok Pinjaman (Rp)') }} <span class="text-danger">*</span></label>
                        <input type="number" name="principal_amount" id="add_principal_amount" class="form-control @error('principal_amount') is-invalid @enderror" placeholder="Contoh: 1500000" required min="1" value="{{ old('principal_amount') }}">
                        @error('principal_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_start_date" class="form-label fw-semibold small">{{ __('Tanggal Mulai Pinjaman') }} <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="add_start_date" class="form-control @error('start_date') is-invalid @enderror" required value="{{ old('start_date', date('Y-m-d')) }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="add_tenor_months" class="form-label fw-semibold small">{{ __('Tenor (Bulan)') }}</label>
                            <input type="number" name="tenor_months" id="add_tenor_months" class="form-control @error('tenor_months') is-invalid @enderror" placeholder="Contoh: 5" min="1" value="{{ old('tenor_months') }}">
                            @error('tenor_months')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label for="add_monthly_installment" class="form-label fw-semibold small">{{ __('Cicilan/Bulan (Rp)') }}</label>
                            <input type="number" name="monthly_installment" id="add_monthly_installment" class="form-control @error('monthly_installment') is-invalid @enderror" placeholder="Contoh: 300000" min="0" value="{{ old('monthly_installment') }}">
                            @error('monthly_installment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_loan_description" class="form-label fw-semibold small">{{ __('Keterangan Pinjaman') }}</label>
                        <textarea name="description" id="add_loan_description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="{{ __('Tuliskan alasan/peruntukan pinjaman...') }}">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                <button type="submit" class="btn btn-primary fw-semibold px-4" form="addKasbonLoanForm">{{ __('Simpan Pinjaman') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Bonus --}}
<div class="modal fade" id="addBonusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success bg-opacity-10 border-bottom-0">
                <div class="d-flex align-items-center">
                    <div class="p-2 rounded-circle bg-success text-white me-2">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h5 class="modal-title mb-0 fw-bold">{{ __('Tambah Bonus Karyawan') }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                @if ($errors->any() && old('type') === 'bonus')
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('salary-adjustments.store') }}" method="POST" id="addBonusForm" data-no-loading="true">
                    @csrf
                    <input type="hidden" name="type" value="bonus">
                    <div class="mb-3">
                        <label for="add_bonus_user_id" class="form-label fw-semibold small">{{ __('Pilih Karyawan') }} <span class="text-danger">*</span></label>
                        <select name="user_id" id="add_bonus_user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">{{ __('-- Pilih Karyawan --') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->role->name ?? 'Staf' }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_bonus_amount" class="form-label fw-semibold small">{{ __('Nominal Bonus (Rp)') }} <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="add_bonus_amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Contoh: 250000" required min="1" value="{{ old('amount') }}">
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_bonus_date" class="form-label fw-semibold small">{{ __('Tanggal Bonus') }} <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="add_bonus_date" class="form-control @error('date') is-invalid @enderror" required value="{{ old('date', date('Y-m-d')) }}">
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="add_bonus_description" class="form-label fw-semibold small">{{ __('Keterangan / Alasan Bonus') }}</label>
                        <textarea name="description" id="add_bonus_description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="{{ __('Tuliskan alasan pemberian bonus...') }}">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                <button type="submit" class="btn btn-success fw-semibold px-4" form="addBonusForm">{{ __('Simpan Bonus') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Dynamic Edit Modals Kasbon Biasa --}}
@foreach($kasbonAdjustments as $adjustment)
    <div class="modal fade" id="editKasbonModal-{{ $adjustment->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">{{ __('Edit Kasbon') }} - {{ $adjustment->user->name ?? '' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('salary-adjustments.update', $adjustment) }}" method="POST" id="editKasbonForm-{{ $adjustment->id }}" data-no-loading="true">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">{{ __('Karyawan') }}</label>
                            <input type="text" class="form-control" value="{{ $adjustment->user->name ?? '' }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="edit_amount_{{ $adjustment->id }}" class="form-label fw-semibold small">{{ __('Jumlah (Rp)') }}</label>
                            <input type="number" name="amount" id="edit_amount_{{ $adjustment->id }}" class="form-control" required min="1" value="{{ $adjustment->amount }}">
                        </div>
                        <div class="mb-3">
                            <label for="edit_date_{{ $adjustment->id }}" class="form-label fw-semibold small">{{ __('Tanggal') }}</label>
                            <input type="date" name="date" id="edit_date_{{ $adjustment->id }}" class="form-control" required value="{{ $adjustment->date ? $adjustment->date->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label for="edit_description_{{ $adjustment->id }}" class="form-label fw-semibold small">{{ __('Keterangan') }}</label>
                            <textarea name="description" id="edit_description_{{ $adjustment->id }}" class="form-control" rows="3">{{ $adjustment->description }}</textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-primary" form="editKasbonForm-{{ $adjustment->id }}">{{ __('Perbarui') }}</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Dynamic Edit Modals Bonus --}}
@foreach($bonusAdjustments as $bonus)
    <div class="modal fade" id="editBonusModal-{{ $bonus->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">{{ __('Edit Bonus') }} - {{ $bonus->user->name ?? '' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('salary-adjustments.update', $bonus) }}" method="POST" id="editBonusForm-{{ $bonus->id }}" data-no-loading="true">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">{{ __('Karyawan') }}</label>
                            <input type="text" class="form-control" value="{{ $bonus->user->name ?? '' }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="edit_bonus_amount_{{ $bonus->id }}" class="form-label fw-semibold small">{{ __('Jumlah (Rp)') }}</label>
                            <input type="number" name="amount" id="edit_bonus_amount_{{ $bonus->id }}" class="form-control" required min="1" value="{{ $bonus->amount }}">
                        </div>
                        <div class="mb-3">
                            <label for="edit_bonus_date_{{ $bonus->id }}" class="form-label fw-semibold small">{{ __('Tanggal') }}</label>
                            <input type="date" name="date" id="edit_bonus_date_{{ $bonus->id }}" class="form-control" required value="{{ $bonus->date ? $bonus->date->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label for="edit_bonus_description_{{ $bonus->id }}" class="form-label fw-semibold small">{{ __('Keterangan') }}</label>
                            <textarea name="description" id="edit_bonus_description_{{ $bonus->id }}" class="form-control" rows="3">{{ $bonus->description }}</textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-success" form="editBonusForm-{{ $bonus->id }}">{{ __('Perbarui') }}</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Dynamic Edit & Installment Modals Loans --}}
@foreach($loans as $loan)
    {{-- Edit Loan Modal --}}
    <div class="modal fade" id="editKasbonLoanModal-{{ $loan->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">{{ __('Edit Pinjaman Angsuran') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('kasbon-loans.update', $loan) }}" method="POST" id="editKasbonLoanForm-{{ $loan->id }}" data-no-loading="true">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">{{ __('Karyawan') }}</label>
                            <input type="text" class="form-control" value="{{ $loan->user->name ?? '' }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="edit_principal_amount_{{ $loan->id }}" class="form-label fw-semibold small">{{ __('Pokok Pinjaman (Rp)') }}</label>
                            <input type="number" name="principal_amount" id="edit_principal_amount_{{ $loan->id }}" class="form-control" required min="1" value="{{ $loan->principal_amount }}">
                        </div>
                        <div class="mb-3">
                            <label for="edit_start_date_{{ $loan->id }}" class="form-label fw-semibold small">{{ __('Tanggal Mulai') }}</label>
                            <input type="date" name="start_date" id="edit_start_date_{{ $loan->id }}" class="form-control" required value="{{ $loan->start_date ? $loan->start_date->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="edit_tenor_months_{{ $loan->id }}" class="form-label fw-semibold small">{{ __('Tenor (Bulan)') }}</label>
                                <input type="number" name="tenor_months" id="edit_tenor_months_{{ $loan->id }}" class="form-control" min="1" value="{{ $loan->tenor_months }}">
                            </div>
                            <div class="col-6">
                                <label for="edit_monthly_installment_{{ $loan->id }}" class="form-label fw-semibold small">{{ __('Cicilan/Bulan (Rp)') }}</label>
                                <input type="number" name="monthly_installment" id="edit_monthly_installment_{{ $loan->id }}" class="form-control" min="0" value="{{ $loan->monthly_installment }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_loan_description_{{ $loan->id }}" class="form-label fw-semibold small">{{ __('Keterangan') }}</label>
                            <textarea name="description" id="edit_loan_description_{{ $loan->id }}" class="form-control" rows="2">{{ $loan->description }}</textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-primary" form="editKasbonLoanForm-{{ $loan->id }}">{{ __('Perbarui') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Installment Modal --}}
    @php
        $loanRem = $loan->remaining;
    @endphp
    <div class="modal fade" id="addInstallmentModal-{{ $loan->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded-circle bg-success text-white me-2">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <h5 class="modal-title mb-0 fw-bold">{{ __('Catat Pembayaran Cicilan') }}</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <div class="d-flex justify-content-between">
                            <span>{{ __('Karyawan:') }} <strong>{{ $loan->user->name ?? '-' }}</strong></span>
                            <span>{{ __('Sisa Hutang:') }} <strong class="text-danger">Rp {{ number_format($loanRem, 0, ',', '.') }}</strong></span>
                        </div>
                    </div>
                    <form action="{{ route('kasbon-loans.installments.store', $loan) }}" method="POST" id="addInstallmentForm-{{ $loan->id }}" data-no-loading="true">
                        @csrf
                        <div class="mb-3">
                            <label for="installment_amount_{{ $loan->id }}" class="form-label fw-semibold small">{{ __('Nominal Cicilan (Rp)') }} <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="installment_amount_{{ $loan->id }}" class="form-control" required min="1" max="{{ $loanRem }}" value="{{ min($loanRem, $loan->monthly_installment ?: $loanRem) }}">
                        </div>
                        <div class="mb-3">
                            <label for="installment_date_{{ $loan->id }}" class="form-label fw-semibold small">{{ __('Tanggal Pembayaran') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="installment_date_{{ $loan->id }}" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label for="installment_description_{{ $loan->id }}" class="form-label fw-semibold small">{{ __('Keterangan / Catatan') }}</label>
                            <textarea name="description" id="installment_description_{{ $loan->id }}" class="form-control" rows="2" placeholder="Cicilan ke-..."></textarea>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" name="potong_gaji" id="potong_gaji_{{ $loan->id }}" class="form-check-input" checked>
                            <label for="potong_gaji_{{ $loan->id }}" class="form-check-label small fw-semibold">
                                {{ __('Otomatis catat sebagai pemotongan slip gaji') }}
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-success px-4" form="addInstallmentForm-{{ $loan->id }}">{{ __('Simpan Pembayaran') }}</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto open modal on validation errors
        @if ($errors->any() && old('type') === 'kasbon')
            const addKasbonModal = new bootstrap.Modal(document.getElementById('addKasbonModal'));
            addKasbonModal.show();
        @elseif ($errors->any() && old('type') === 'bonus')
            const addBonusModal = new bootstrap.Modal(document.getElementById('addBonusModal'));
            addBonusModal.show();
        @elseif ($errors->any() && !old('type'))
            const addKasbonLoanModal = new bootstrap.Modal(document.getElementById('addKasbonLoanModal'));
            addKasbonLoanModal.show();
        @endif

        // Tab Persistence & synchronization with filter form
        const filterTabInput = document.getElementById('filterTabInput');
        const tabButtons = document.querySelectorAll('#kasbonTabs button[data-bs-toggle="pill"]');
        
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function(event) {
                const targetId = event.target.getAttribute('data-bs-target').replace('#tab-', '');
                if (filterTabInput) {
                    filterTabInput.value = targetId;
                }
                // Update URL without reloading page
                const url = new URL(window.location);
                url.searchParams.set('tab', targetId);
                window.history.replaceState({}, '', url);
            });
        });

        // Search live filter on User Recap cards
        const searchInput = document.getElementById('searchTechnician');
        const userCards = document.querySelectorAll('.user-card');
        
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                const searchTerm = e.target.value.toLowerCase().trim();
                debounceTimer = setTimeout(() => {
                    userCards.forEach(card => {
                        const cardName = card.getAttribute('data-name');
                        if (searchTerm.length === 0 || cardName.includes(searchTerm)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }, 200);
            });
        }
    });
</script>
@endpush
