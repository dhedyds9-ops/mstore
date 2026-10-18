@extends('layouts.app')

@section('title', __('Dasbor Toko ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Dasbor --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-store me-1"></i>{{ __('Retail & Layanan Toko') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-calendar-days me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Dasbor Toko ATK') }}</h1>
            <p class="text-muted small mb-0">{{ __('Pantau penjualan harian, perputaran kas, pendapatan fee, dan produk terlaris.') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('atk.products.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center shadow-2xs">
                <i class="fa-solid fa-boxes-stacked me-1 text-secondary"></i> {{ __('Katalog Produk') }}
            </a>
            <a href="{{ route('atk.transactions.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center shadow-2xs">
                <i class="fa-solid fa-clock-rotate-left me-1 text-secondary"></i> {{ __('Riwayat Transaksi') }}
            </a>
            <a href="{{ route('atk.pos') }}" class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center shadow-sm">
                <i class="fa-solid fa-cash-register me-2"></i> {{ __('Buka Kasir (POS)') }}
            </a>
        </div>
    </div>

    {{-- Widget Absensi Kasir --}}
    @if(Auth::user()->hasPermission('attendance.view') || Auth::user()->hasPermission('attendance.create'))
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden border-start border-4 border-primary">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary fs-4 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="fw-bold mb-0 text-gray-800">{{ __('Status Absensi Kasir') }}</h6>
                            @if($todayAttendance)
                                <span class="badge rounded-pill bg-{{ $todayAttendance->status == 'present' ? 'success' : ($todayAttendance->status == 'late' ? 'warning' : 'secondary') }}-subtle text-{{ $todayAttendance->status == 'present' ? 'success' : ($todayAttendance->status == 'late' ? 'warning' : 'secondary') }} border border-{{ $todayAttendance->status == 'present' ? 'success' : ($todayAttendance->status == 'late' ? 'warning' : 'secondary') }}-subtle px-2 py-1 small fw-semibold">
                                    <i class="fa-solid fa-circle-check me-1"></i>{{ __(ucfirst($todayAttendance->status)) }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small fw-semibold">
                                    {{ __('Belum Hadir') }}
                                </span>
                            @endif
                        </div>
                        <div class="small text-muted d-flex flex-wrap align-items-center gap-3">
                            @if($todayAttendance)
                                <span><i class="fa-solid fa-arrow-right-to-bracket text-success me-1"></i>{{ __('Masuk') }}: <strong>{{ \Carbon\Carbon::parse($todayAttendance->clock_in)->format('H:i') }}</strong></span>
                                @if($todayAttendance->clock_out)
                                    <span><i class="fa-solid fa-arrow-right-from-bracket text-danger me-1"></i>{{ __('Pulang') }}: <strong>{{ \Carbon\Carbon::parse($todayAttendance->clock_out)->format('H:i') }}</strong></span>
                                @endif
                            @else
                                <span>{{ __('Anda belum melakukan presensi masuk hari ini.') }}</span>
                            @endif
                            @if(isset($attendanceOverview['total']) && $attendanceOverview['total'] > 0)
                                <span class="border-start ps-3">
                                    <i class="fa-solid fa-users text-primary me-1"></i>{{ __('Kasir Hadir') }}: <strong>{{ $attendanceOverview['present'] }} / {{ $attendanceOverview['total'] }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('attendance.create') }}" class="btn btn-sm {{ !$todayAttendance ? 'btn-primary' : (!$todayAttendance->clock_out ? 'btn-danger' : 'btn-outline-primary') }} px-3">
                        @if(!$todayAttendance)
                            <i class="fa-solid fa-fingerprint me-1"></i> {{ __('Absen Masuk') }}
                        @elseif(!$todayAttendance->clock_out)
                            <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> {{ __('Absen Pulang') }}
                        @else
                            <i class="fa-solid fa-fingerprint me-1"></i> {{ __('Lihat Absensi') }}
                        @endif
                    </a>
                    <a href="{{ route('attendance.daily') }}" class="btn btn-sm btn-outline-secondary px-3">
                        <i class="fa-solid fa-clipboard-user me-1"></i> {{ __('Presensi Harian') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Ringkasan Kinerja Utama (Core KPIs) --}}
    <div class="row g-3 mb-4">
        {{-- Penjualan Harian --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('atk.transactions.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 atk-kpi-card hover-lift">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small fw-medium">{{ __('Penjualan Hari Ini') }}</span>
                            <div class="rounded-3 p-2 bg-primary-subtle text-primary fs-5">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                        </div>
                        <div class="fs-4 fw-bold text-gray-900 mb-1">
                            Rp {{ number_format($dailySales, 0, ',', '.') }}
                        </div>
                        <div class="d-flex align-items-center justify-content-between small text-muted">
                            <span><i class="fa-solid fa-cart-shopping me-1 text-primary"></i><strong>{{ $transactionCount }}</strong> {{ __('transaksi') }}</span>
                            <span class="text-primary fw-medium">{{ __('Detail') }} &rarr;</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Penjualan Bulanan --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('atk.reports.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 atk-kpi-card hover-lift">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small fw-medium">{{ __('Penjualan Bulan Ini') }}</span>
                            <div class="rounded-3 p-2 bg-success-subtle text-success fs-5">
                                <i class="fa-solid fa-money-bill-trend-up"></i>
                            </div>
                        </div>
                        <div class="fs-4 fw-bold text-success mb-1">
                            Rp {{ number_format($monthlySales, 0, ',', '.') }}
                        </div>
                        <div class="d-flex align-items-center justify-content-between small text-muted">
                            <span><i class="fa-solid fa-calendar-check me-1 text-success"></i>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM Y') }}</span>
                            <span class="text-success fw-medium">{{ __('Laporan') }} &rarr;</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Fee Hari Ini & Bulan Ini --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 atk-kpi-card">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-medium">{{ __('Total Fee Hari Ini') }}</span>
                        <div class="rounded-3 p-2 bg-warning-subtle text-warning-emphasis fs-5">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold text-gray-900 mb-1">
                        Rp {{ number_format($todayFees, 0, ',', '.') }}
                    </div>
                    <div class="small text-muted">
                        <i class="fa-solid fa-chart-pie me-1 text-warning"></i>{{ __('Bulanan') }}: <strong>Rp {{ number_format($monthlyFees, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pengeluaran Operasional Hari Ini --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('atk.expenses.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 atk-kpi-card hover-lift">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small fw-medium">{{ __('Pengeluaran Hari Ini') }}</span>
                            <div class="rounded-3 p-2 bg-danger-subtle text-danger fs-5">
                                <i class="fa-solid fa-arrow-trend-down"></i>
                            </div>
                        </div>
                        <div class="fs-4 fw-bold text-danger mb-1">
                            Rp {{ number_format($todayExpenses, 0, ',', '.') }}
                        </div>
                        <div class="d-flex align-items-center justify-content-between small text-muted">
                            <span><i class="fa-solid fa-receipt me-1 text-danger"></i>{{ __('Biaya operasional toko') }}</span>
                            <span class="text-danger fw-medium">{{ __('Kelola') }} &rarr;</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Ringkasan Likuiditas & Saldo Kas --}}
    <div class="row g-3 mb-4">
        {{-- Kas Utama --}}
        <div class="col-12 col-md-4">
            <a href="{{ route('atk.cash-movements.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-gradient-light hover-lift">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-4 p-3 bg-success text-white fs-4 shadow-sm">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted small fw-medium">{{ __('Kas Utama (Laci/Brankas)') }}</div>
                                <div class="fs-5 fw-bold text-gray-900">Rp {{ number_format($cash->balance, 0, ',', '.') }}</div>
                                <span class="small text-success fw-medium"><i class="fa-solid fa-clock-rotate-left me-1"></i>{{ __('Lihat Mutasi Kas') }} &rarr;</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Saldo Akun Float --}}
        <div class="col-12 col-md-4">
            <a href="{{ route('atk.float-accounts.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-gradient-light hover-lift">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-4 p-3 bg-info text-white fs-4 shadow-sm">
                                <i class="fa-solid fa-credit-card"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted small fw-medium">{{ __('Saldo Akun Float / Digital') }}</div>
                                <div class="fs-5 fw-bold text-gray-900">Rp {{ number_format($floatAccounts->sum('current_balance'), 0, ',', '.') }}</div>
                                <span class="small text-info fw-medium"><i class="fa-solid fa-list-check me-1"></i>{{ $floatAccounts->count() }} {{ __('Akun Aktif') }} &rarr;</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Dana Talangan Owner --}}
        <div class="col-12 col-md-4">
            <a href="{{ route('atk.owner-funds.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-gradient-light hover-lift">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-4 p-3 bg-warning text-dark fs-4 shadow-sm">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted small fw-medium">{{ __('Dana Talangan Owner') }}</div>
                                <div class="fs-5 fw-bold text-gray-900">Rp {{ number_format($currentOwnerBalance, 0, ',', '.') }}</div>
                                <span class="small text-warning-emphasis fw-medium"><i class="fa-solid fa-money-bill-transfer me-1"></i>{{ __('Kelola Talangan') }} &rarr;</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Aktivitas Layanan Digital & Perbankan Hari Ini --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-network-wired text-primary"></i>
                        <span>{{ __('Perputaran Layanan Digital Hari Ini') }}</span>
                    </h6>
                    <small class="text-muted">{{ __('Rincian volume transaksi dan fee yang dihasilkan hari ini') }}</small>
                </div>
                <span class="badge bg-light text-dark border px-2 py-1 small">
                    <i class="fa-regular fa-clock me-1"></i>{{ __('Hari Ini') }}
                </span>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="row g-3">
                {{-- Transfer Bank --}}
                <div class="col-6 col-lg-3">
                    <div class="p-3 rounded-4 bg-light border border-light-subtle h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-semibold text-muted">{{ __('Transfer Bank') }}</span>
                            <div class="rounded-circle p-2 bg-primary-subtle text-primary">
                                <i class="fa-solid fa-money-bill-transfer"></i>
                            </div>
                        </div>
                        <div class="fs-5 fw-bold text-dark mb-1">Rp {{ number_format($todayTransfer, 0, ',', '.') }}</div>
                        <div class="small text-success fw-medium">
                            <i class="fa-solid fa-coins me-1"></i>Fee: Rp {{ number_format($todayFeesBank, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                {{-- Top Up E-Wallet --}}
                <div class="col-6 col-lg-3">
                    <div class="p-3 rounded-4 bg-light border border-light-subtle h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-semibold text-muted">{{ __('Top Up Saldo') }}</span>
                            <div class="rounded-circle p-2 bg-success-subtle text-success">
                                <i class="fa-solid fa-arrow-up-right-dots"></i>
                            </div>
                        </div>
                        <div class="fs-5 fw-bold text-dark mb-1">Rp {{ number_format($todayTopUp, 0, ',', '.') }}</div>
                        <div class="small text-success fw-medium">
                            <i class="fa-solid fa-coins me-1"></i>Fee: Rp {{ number_format($todayFeesTopUp, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                {{-- PPOB & Tagihan --}}
                <div class="col-6 col-lg-3">
                    <div class="p-3 rounded-4 bg-light border border-light-subtle h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-semibold text-muted">{{ __('PPOB & Tagihan') }}</span>
                            <div class="rounded-circle p-2 bg-warning-subtle text-warning-emphasis">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                            </div>
                        </div>
                        <div class="fs-5 fw-bold text-dark mb-1">Rp {{ number_format($todayPpob, 0, ',', '.') }}</div>
                        <div class="small text-success fw-medium">
                            <i class="fa-solid fa-coins me-1"></i>Fee: Rp {{ number_format($todayFeesPpob, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                {{-- Tarik Tunai --}}
                <div class="col-6 col-lg-3">
                    <div class="p-3 rounded-4 bg-light border border-light-subtle h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-semibold text-muted">{{ __('Tarik Tunai') }}</span>
                            <div class="rounded-circle p-2 bg-secondary-subtle text-secondary">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                        </div>
                        <div class="fs-5 fw-bold text-dark mb-1">Rp {{ number_format($todayWithdrawal, 0, ',', '.') }}</div>
                        <div class="small text-success fw-medium">
                            <i class="fa-solid fa-coins me-1"></i>Fee: Rp {{ number_format($todayFeesCashOut ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bagian Bawah: Produk Terlaris & Pintasan Aksi Cepat --}}
    <div class="row g-4">
        {{-- Produk Terlaris --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-fire text-danger"></i>
                            <span>{{ __('5 Produk ATK Terlaris') }}</span>
                        </h6>
                        <small class="text-muted">{{ __('Berdasarkan akumulasi jumlah unit terjual') }}</small>
                    </div>
                    <a href="{{ route('atk.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                        {{ __('Lihat Semua') }}
                    </a>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($topProducts->count() > 0)
                        <div class="list-group list-group-flush gap-2">
                            @foreach($topProducts as $index => $product)
                                <div class="list-group-item border rounded-3 p-2 px-3 d-flex justify-content-between align-items-center bg-light-subtle">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold {{ $index === 0 ? 'bg-warning text-dark' : ($index === 1 ? 'bg-secondary-subtle text-dark' : ($index === 2 ? 'bg-warning-subtle text-dark' : 'bg-light text-muted border')) }}" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                            @if($index === 0)
                                                <i class="fa-solid fa-trophy"></i>
                                            @else
                                                #{{ $index + 1 }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-gray-900">{{ $product->product_name }}</div>
                                            <small class="text-muted">{{ __('Total Terjual:') }} <strong>{{ number_format($product->total_qty, 0, ',', '.') }}</strong> unit</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold">
                                        {{ $product->total_qty }}x
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-box-open text-secondary-subtle display-4 mb-3 d-block"></i>
                            <h6 class="fw-bold mb-1 text-gray-700">{{ __('Belum Ada Data Penjualan') }}</h6>
                            <p class="text-muted small mb-3">{{ __('Catat transaksi kasir pertama Anda hari ini.') }}</p>
                            <a href="{{ route('atk.pos') }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-cash-register me-1"></i>{{ __('Buka POS Sekarang') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pintasan Aksi Cepat --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-bolt text-warning"></i>
                            <span>{{ __('Pintasan Aksi Cepat') }}</span>
                        </h6>
                        <small class="text-muted">{{ __('Akses cepat ke menu dan fitur utama kasir ATK') }}</small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="{{ route('atk.pos') }}" class="card text-decoration-none border rounded-4 p-3 h-100 hover-lift bg-primary-subtle border-primary-subtle text-primary">
                                <div class="fs-3 mb-2"><i class="fa-solid fa-cash-register"></i></div>
                                <div class="fw-bold fs-6">{{ __('Kasir POS') }}</div>
                                <small class="text-muted">{{ __('Mulai transaksi jual beli') }}</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('atk.products.create') }}" class="card text-decoration-none border rounded-4 p-3 h-100 hover-lift bg-light border-light-subtle text-dark">
                                <div class="fs-3 mb-2 text-primary"><i class="fa-solid fa-plus-circle"></i></div>
                                <div class="fw-bold fs-6">{{ __('Tambah Produk') }}</div>
                                <small class="text-muted">{{ __('Input produk baru ke katalog') }}</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('atk.cash-movements.index') }}" class="card text-decoration-none border rounded-4 p-3 h-100 hover-lift bg-light border-light-subtle text-dark">
                                <div class="fs-3 mb-2 text-success"><i class="fa-solid fa-money-bill-transfer"></i></div>
                                <div class="fw-bold fs-6">{{ __('Mutasi Kas') }}</div>
                                <small class="text-muted">{{ __('Catat setor & tarik kas') }}</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('atk.expenses.create') }}" class="card text-decoration-none border rounded-4 p-3 h-100 hover-lift bg-light border-light-subtle text-dark">
                                <div class="fs-3 mb-2 text-danger"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                                <div class="fw-bold fs-6">{{ __('Catat Beban') }}</div>
                                <small class="text-muted">{{ __('Input biaya operasional') }}</small>
                            </a>
                        </div>
                        @if(Auth::user()->hasPermission('atk.manage'))
                        <div class="col-6">
                            <a href="{{ route('atk.float-accounts.index') }}" class="btn btn-outline-info w-100 py-2 rounded-3 text-start d-flex align-items-center gap-2">
                                <i class="fa-solid fa-credit-card"></i>
                                <span class="fw-semibold small">{{ __('Kelola Rekening Float') }}</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('atk.owner-funds.index') }}" class="btn btn-outline-warning w-100 py-2 rounded-3 text-start d-flex align-items-center gap-2">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                                <span class="fw-semibold small">{{ __('Kelola Dana Talangan') }}</span>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .atk-kpi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
    .bg-gradient-light {
        background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
    }
</style>
@endpush
@endsection
