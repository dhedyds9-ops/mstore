@extends('layouts.app')

@section('title', __('Dasbor'))

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold text-primary mb-1"><i class="fa-solid fa-chart-pie me-2"></i>{{ __('Dasbor Eksekutif & Bisnis') }}</h4>
        <p class="text-muted small mb-0">{{ __('Konsolidasi Finansial Terpadu: Toko & Retail, Jasa & Layanan, serta Booking & Reservasi') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('accounting.income_statement') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-file-invoice-dollar me-1"></i> {{ __('Laporan Laba Rugi') }}
        </a>
        <a href="{{ route('accounting.journals.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-book-journal-whills me-1"></i> {{ __('Buku Jurnal Umum') }}
        </a>
    </div>
</div>

<!-- Executive Financial KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Likuiditas (Kas & Bank) -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-body-secondary small fw-bold">{{ __('Total Kas & Bank') }}</span>
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-success">Rp {{ number_format($executiveMetrics['total_cash_and_bank'] ?? 0, 0, ',', '.') }}</h3>
                <div class="small text-muted d-flex justify-content-between pt-1 border-top">
                    <span>Kas: Rp {{ number_format($executiveMetrics['cash_total'] ?? 0, 0, ',', '.') }}</span>
                    <span>Bank: Rp {{ number_format($executiveMetrics['bank_total'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Omset Konsolidasian -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-body-secondary small fw-bold">{{ __('Omset Bulan Ini') }}</span>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-money-bill-trend-up"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-primary">Rp {{ number_format($executiveMetrics['consolidated_month_revenue'] ?? 0, 0, ',', '.') }}</h3>
                <div class="small text-muted pt-1 border-top text-truncate" title="Toko/Retail + Jasa/Layanan + Booking/Reservasi">
                    <span>Toko: {{ number_format($executiveMetrics['atk_month_revenue'] ?? 0, 0, ',', '.') }} | Jasa: {{ number_format($executiveMetrics['wash_month_revenue'] ?? 0, 0, ',', '.') }} | Booking: {{ number_format($executiveMetrics['wedding_month_revenue'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Piutang & Kasbon -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-body-secondary small fw-bold">{{ __('Piutang & Kasbon') }}</span>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-warning">Rp {{ number_format($executiveMetrics['total_receivables'] ?? 0, 0, ',', '.') }}</h3>
                <div class="small text-muted d-flex justify-content-between pt-1 border-top">
                    <span>Usaha: Rp {{ number_format($executiveMetrics['trade_receivables'] ?? 0, 0, ',', '.') }}</span>
                    <span>Kasbon: Rp {{ number_format($executiveMetrics['staff_receivables'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Estimasi Laba Bersih GL PSAK -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase text-body-secondary small fw-bold">{{ __('Laba Bersih Bulan Ini') }}</span>
                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1 text-info">Rp {{ number_format($executiveMetrics['month_net_profit'] ?? 0, 0, ',', '.') }}</h3>
                <div class="small text-muted d-flex justify-content-between pt-1 border-top">
                    <span>{{ __('Margin Laba') }}: <strong class="text-info">{{ $executiveMetrics['net_profit_margin'] ?? 0 }}%</strong></span>
                    <span>PSAK GL</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts Bar -->
<div class="card border-0 shadow-sm mb-4 bg-light">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary px-3 py-2 text-uppercase"><i class="fa-solid fa-bolt me-1"></i> {{ __('Akses Cepat') }}</span>
                <span class="text-muted small fw-medium d-none d-md-inline">{{ __('Operasional kasir & pembukuan akuntansi terintegrasi') }}</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('atk.pos') }}" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-store me-1"></i> Kasir Toko
                </a>
                <a href="{{ route('wash.pos') }}" class="btn btn-sm btn-info text-white">
                    <i class="fa-solid fa-hand-sparkles me-1"></i> Kasir Layanan
                </a>
                <a href="{{ route('wedding.bookings.index') }}" class="btn btn-sm btn-danger">
                    <i class="fa-solid fa-calendar-check me-1"></i> Booking & Reservasi
                </a>
                <a href="{{ route('accounting.income_statement') }}" class="btn btn-sm btn-success">
                    <i class="fa-solid fa-chart-column me-1"></i> Laba Rugi Segmented
                </a>
                <a href="{{ route('accounting.periods.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fa-solid fa-calendar-check me-1"></i> Tutup Buku
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Business Unit Breakdown & Financial Chart -->
<div class="row g-4 mb-4">
    <!-- Komposisi Kontribusi Unit Bisnis -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="fa-solid fa-layer-group text-primary me-2"></i>{{ __('Kontribusi Omset Unit Bisnis') }}</h6>
                <small class="text-muted">{{ __('Akumulasi penjualan bulan ini') }}</small>
            </div>
            <div class="card-body">
                @foreach($unitBreakdown as $key => $unit)
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">
                            <i class="fa-solid {{ $unit['icon'] }} me-1 text-{{ $unit['badge'] }}"></i> {{ $unit['label'] }}
                        </span>
                        <div class="text-end">
                            <span class="small fw-bold">Rp {{ number_format($unit['revenue'], 0, ',', '.') }}</span>
                            <span class="badge bg-{{ $unit['badge'] }}-subtle text-{{ $unit['badge'] }} ms-1">{{ $unit['percentage'] }}%</span>
                        </div>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-{{ $unit['badge'] }}" role="progressbar" style="width: {{ $unit['percentage'] }}%" aria-valuenow="{{ $unit['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                @endforeach

                <hr class="my-3">

                <!-- Ringkasan Transaksi Hari Ini -->
                <h6 class="small fw-bold text-uppercase text-muted mb-2">{{ __('Aktivitas Hari Ini') }}</h6>
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <small class="text-muted d-block">Toko</small>
                            <span class="fw-bold fs-6 text-primary">{{ $stats['atk_today'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <small class="text-muted d-block">Jasa</small>
                            <span class="fw-bold fs-6 text-info">{{ $stats['wash_today'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded bg-light">
                            <small class="text-muted d-block">Booking</small>
                            <span class="fw-bold fs-6 text-danger">{{ $stats['wedding_today'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Chart (Real-time PSAK General Ledger) -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-chart-column text-success me-2"></i>{{ __('Tren Pendapatan & Beban GL') }} ({{ date('Y') }})</h6>
                    <small class="text-muted">{{ __('Data akuntansi riil dari Buku Jurnal Umum PSAK') }}</small>
                </div>
                <div class="badge bg-success-subtle text-success border border-success-subtle">
                    <i class="fa-solid fa-circle-check me-1"></i> Real-time GL
                </div>
            </div>
            <div class="card-body">
                <div style="height: 280px; width: 100%;">
                    <canvas id="financialChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live General Ledger Feed (Audit Trail Terkini) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="mb-0 fw-bold"><i class="fa-solid fa-receipt text-primary me-2"></i>{{ __('Mutasi Jurnal Umum Terkini (Live GL Feed)') }}</h6>
            <small class="text-muted">{{ __('Audit trail transaksi otomatis dari kasir Toko, Jasa Layanan, Booking Reservasi, dan Payroll') }}</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(!empty($glIsBalanced))
                <span class="badge bg-success-subtle text-success border border-success">
                    <i class="fa-solid fa-check-double me-1"></i> {{ __('Status GL: Balanced (Seimbang)') }}
                </span>
            @else
                <span class="badge bg-danger-subtle text-danger border border-danger">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ __('Status GL: Unbalanced') }}
                </span>
            @endif
            <a href="{{ route('accounting.journals.index') }}" class="btn btn-sm btn-outline-primary">
                {{ __('Lihat Semua Jurnal') }} <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-3 text-uppercase small text-muted border-0">{{ __('No. Jurnal') }}</th>
                    <th class="text-uppercase small text-muted border-0">{{ __('Tanggal') }}</th>
                    <th class="text-uppercase small text-muted border-0">{{ __('Unit') }}</th>
                    <th class="text-uppercase small text-muted border-0">{{ __('Akun Perkiraan') }}</th>
                    <th class="text-uppercase small text-muted border-0">{{ __('Keterangan / Memo') }}</th>
                    <th class="text-end text-uppercase small text-muted border-0">{{ __('Debit') }}</th>
                    <th class="text-end pe-3 text-uppercase small text-muted border-0">{{ __('Kredit') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestJournalEntries ?? [] as $entry)
                <tr>
                    <td class="ps-3 font-monospace small fw-bold text-primary">
                        {{ $entry->journal->journal_no ?? ('#' . $entry->journal_id) }}
                    </td>
                    <td class="small text-muted">
                        {{ $entry->journal?->date ? \Carbon\Carbon::parse($entry->journal->date)->format('d/m/Y') : '-' }}
                    </td>
                    <td>
                        @php
                            $unitBadge = match($entry->unit) {
                                'ATK' => 'primary',
                                'WASH' => 'info',
                                'EVENT' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $unitBadge }}-subtle text-{{ $unitBadge }} fw-semibold">{{ $entry->unit ?? 'GEN' }}</span>
                    </td>
                    <td>
                        <span class="font-monospace small text-muted me-1">{{ $entry->account->code ?? '-' }}</span>
                        <span class="fw-medium">{{ $entry->account->name ?? '-' }}</span>
                    </td>
                    <td class="small text-muted">{{ Str::limit($entry->memo ?: ($entry->journal->description ?? '-'), 35) }}</td>
                    <td class="text-end fw-semibold {{ $entry->debit > 0 ? 'text-body-emphasis' : 'text-muted' }}">
                        {{ $entry->debit > 0 ? 'Rp ' . number_format($entry->debit, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-end pe-3 fw-semibold {{ $entry->credit > 0 ? 'text-body-emphasis' : 'text-muted' }}">
                        {{ $entry->credit > 0 ? 'Rp ' . number_format($entry->credit, 0, ',', '.') : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">{{ __('Belum ada entri jurnal.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(\App\Support\ModuleGate::isEnabled('isp') || \App\Support\ModuleGate::isEnabled('ticketing'))
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <!-- Customers -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-body-secondary small fw-bold mb-0">{{ __('Pelanggan') }}</h6>
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-2">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['total_customers'] }}</h3>
                <div class="small text-success">
                    <i class="fa-solid fa-arrow-trend-up me-1"></i>
                    <span>+{{ $stats['new_customers_this_month'] }} {{ __('bulan ini') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-body-secondary small fw-bold mb-0">{{ __('Tiket Aktif') }}</h6>
                    <div class="bg-warning bg-opacity-10 text-warning rounded p-2">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['open_tickets'] }}</h3>
                <div class="small text-body-secondary">
                    <span>{{ $stats['tickets_today'] }} {{ __('baru hari ini') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Installations -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-body-secondary small fw-bold mb-0">{{ __('Instalasi Tertunda') }}</h6>
                    <div class="bg-info bg-opacity-10 text-info rounded p-2">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-1">{{ $stats['pending_installations'] }}</h3>
                <div class="small text-body-secondary">
                    <span>{{ __('Dijadwalkan minggu ini') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitor Summary -->
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-muted small fw-bold mb-0">{{ __('Ringkasan Monitor') }}</h6>
                    <div class="bg-success bg-opacity-10 text-success rounded p-2">
                        <i class="fa-solid fa-server"></i>
                    </div>
                </div>
                @if($monitorSummary)
                <h3 class="fw-bold mb-1">{{ number_format((int) ($monitorSummary['checked'] ?? 0)) }}</h3>
                <div class="small text-body-secondary mb-1">
                    <span>{{ __('Perangkat yang dicek') }}</span>
                </div>
                <div class="small text-body-secondary">
                    <span>{{ __('Gangguan') }}: {{ number_format((int) ($monitorSummary['down'] ?? 0)) }}</span>
                    <span class="mx-1">•</span>
                    <span>{{ __('Tiket') }}: {{ number_format((int) ($monitorSummary['tickets_created'] ?? 0)) }}</span>
                    <span class="mx-1">•</span>
                    <span>{{ __('Kesalahan') }}: {{ number_format((int) ($monitorSummary['errors'] ?? 0)) }}</span>
                </div>
                @if(!empty($monitorTrend))
                <div class="mt-2">
                    <div class="small text-muted mb-1">{{ __('Tren gangguan 7 hari') }}</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($monitorTrend as $trend)
                        <span class="badge {{ ($trend['down'] ?? 0) > 0 ? 'bg-warning text-dark' : 'bg-light text-muted' }}">
                            {{ $trend['label'] }}: {{ (int) ($trend['down'] ?? 0) }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if(!empty($monitorSummary['ran_at']))
                <div class="small text-muted mt-1">
                    <span>{{ __('Diperbarui') }} {{ \Carbon\Carbon::parse($monitorSummary['ran_at'])->diffForHumans() }}</span>
                </div>
                @endif
                @else
                <h3 class="fw-bold mb-1">0</h3>
                <div class="small text-body-secondary">
                    <span>{{ __('Belum ada data monitor') }}</span>
                </div>
                @if(!empty($monitorTrend))
                <div class="mt-2">
                    <div class="small text-muted mb-1">{{ __('Tren gangguan 7 hari') }}</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($monitorTrend as $trend)
                        <span class="badge {{ ($trend['down'] ?? 0) > 0 ? 'bg-warning text-dark' : 'bg-light text-muted' }}">
                            {{ $trend['label'] }}: {{ (int) ($trend['down'] ?? 0) }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Inventory & Finance Section -->
@if(\App\Support\ModuleGate::isEnabled('inventory'))
<!-- Inventory Section -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>{{ __('Stok Inventaris') }}</h6>
                <a href="{{ route('inventory.index') }}" class="btn btn-link btn-sm text-decoration-none">{{ __('Lihat Semua Inventaris') }}</a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-6 text-center border-end">
                        <h3 class="fw-bold text-primary mb-0">{{ $inventoryItems->count() }}</h3>
                        <small class="text-muted">{{ __('Barang') }}</small>
                    </div>
                    <div class="col-sm-6 text-center">
                        <h4 class="fw-bold text-success mb-0">Rp {{ number_format($totalInventoryValue, 0, ',', '.') }}</h4>
                        <small class="text-muted">{{ __('Total Nilai Aset Inventaris') }}</small>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase small text-muted border-0">{{ __('Barang') }}</th>
                                <th class="text-end text-uppercase small text-muted border-0">{{ __('Stok') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryItems as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded p-1 me-2">
                                            @if($item->type_group == 'tool')
                                                <i class="fa-solid fa-screwdriver-wrench text-warning"></i>
                                            @else
                                                <i class="fa-solid fa-box text-info"></i>
                                            @endif
                                        </div>
                                        <span class="small fw-medium">{{ Str::limit($item->name, 25) }}</span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ $item->stock < 5 ? 'bg-danger' : 'bg-success' }}">{{ $item->stock }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(\App\Support\ModuleGate::isEnabled('isp'))
<!-- POS & Car Wash Summary -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-body-secondary small fw-bold mb-0">{{ __('Pendapatan Toko & Retail') }}</h6>
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-2">
                        <i class="fa-solid fa-store"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">{{ __('Transaksi Hari Ini') }}</div>
                        <h4 class="fw-bold mb-0">{{ $stats['atk_today'] }}</h4>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">{{ __('Pendapatan Bulan Ini') }}</div>
                        <h4 class="fw-bold text-success mb-0">{{ number_format($stats['atk_month_revenue'], 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="text-uppercase text-body-secondary small fw-bold mb-0">{{ __('Pendapatan Jasa & Layanan') }}</h6>
                    <div class="bg-info bg-opacity-10 text-info rounded p-2">
                        <i class="fa-solid fa-hand-sparkles"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">{{ __('Transaksi Hari Ini') }}</div>
                        <h4 class="fw-bold mb-0">{{ $stats['wash_today'] }}</h4>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">{{ __('Pendapatan Bulan Ini') }}</div>
                        <h4 class="fw-bold text-success mb-0">{{ number_format($stats['wash_month_revenue'], 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(\App\Support\ModuleGate::isEnabled('ticketing') || \App\Support\ModuleGate::isEnabled('isp'))
<div class="row g-4 mb-4">
    <!-- Recent Tickets -->
    <div class="col-lg-8">
        <!-- Monthly Recap Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="mb-0 fw-bold">{{ __('Rekap Tiket Bulanan') }} ({{ date('Y') }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 text-uppercase small text-muted border-0">{{ __('Bulan') }}</th>
                                <th class="text-center text-uppercase small text-muted border-0">{{ __('Total') }}</th>
                                <th class="text-center text-uppercase small text-muted border-0">{{ __('Selesai') }}</th>
                                <th class="text-center text-uppercase small text-muted border-0">{{ __('Buka/Tertunda') }}</th>
                                <th class="text-end pe-3 text-uppercase small text-muted border-0">{{ __('Tingkat Penyelesaian') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ticketRecap as $recap)
                                @if($recap['total'] > 0 || $loop->iteration <= date('n'))
                                <tr>
                                    <td class="ps-3 fw-medium">{{ $recap['month'] }}</td>
                                    <td class="text-center">{{ $recap['total'] }}</td>
                                    <td class="text-center text-success">{{ $recap['resolved'] }}</td>
                                    <td class="text-center text-warning">{{ $recap['open'] }}</td>
                                    <td class="text-end pe-3">
                                        @if($recap['total'] > 0)
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <div class="progress" style="width: 60px; height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($recap['resolved'] / $recap['total']) * 100 }}%"></div>
                                                </div>
                                                <small class="text-body-secondary">{{ round(($recap['resolved'] / $recap['total']) * 100) }}%</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">{{ __('Tiket Dukungan Terbaru') }}</h6>
                <a href="{{ route('tickets.index') }}" class="btn btn-link btn-sm text-decoration-none">{{ __('Lihat Semua') }}</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-uppercase small text-muted border-0">{{ __('ID Tiket') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Pelanggan') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Subject') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Status') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Tanggal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTickets as $ticket)
                        <tr>
                            <td class="ps-4"><span class="fw-medium">#{{ $ticket->ticket_number }}</span></td>
                            <td>{{ $ticket->customer?->name ?? __('Tanpa Pelanggan') }}</td>
                            <td>{{ Str::limit($ticket->subject, 30) }}</td>
                            <td>
                                @if($ticket->status === 'open')
                                    <span class="badge bg-danger-subtle text-danger">{{ __('Buka') }}</span>
                                @elseif($ticket->status === 'closed')
                                    <span class="badge bg-success-subtle text-success">{{ __('Tutup') }}</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">{{ ucfirst($ticket->status) }}</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $ticket->created_at->format('d M H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">{{ __('Belum ada tiket terbaru.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Info & Installations -->
    <div class="col-lg-4">
        <!-- Info Card -->
        <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">{{ __('Informasi Sistem') }}</h5>
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3 opacity-75"><i class="fa-regular fa-clock fa-2x"></i></div>
                    <div>
                        <div class="small opacity-75 text-uppercase">{{ __('Waktu Server') }}</div>
                        <div class="fw-bold">{{ now()->format('H:i:s') }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="me-3 opacity-75"><i class="fa-regular fa-calendar fa-2x"></i></div>
                    <div>
                        <div class="small opacity-75 text-uppercase">{{ __('Tanggal') }}</div>
                        <div class="fw-bold">{{ now()->format('l, d F Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Installations -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="mb-0 fw-bold">{{ __('Instalasi Mendatang') }}</h6>
            </div>
            <div class="list-group list-group-flush">
                @forelse($upcomingInstallations as $install)
                <div class="list-group-item px-4 py-3 border-0 d-flex align-items-start">
                    <div class="bg-body-secondary rounded-circle p-2 me-3 text-center" style="width: 40px; height: 40px;">
                        <span class="fw-bold text-primary">{{ $install->plan_date ? $install->plan_date->format('d') : '-' }}</span>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-body-emphasis fw-semibold">{{ $install->customer->name }}</h6>
                        <p class="mb-1 small text-body-secondary"><i class="fa-solid fa-location-dot me-1"></i> {{ Str::limit($install->customer->address, 30) }}</p>
                        <span class="badge bg-info-subtle text-info">{{ $install->plan_date ? $install->plan_date->format('H:i') : __('Belum Dijadwalkan') }} WIB</span>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted small">
                    {{ __('Tidak ada instalasi mendatang.') }}
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif

@if(\App\Support\ModuleGate::isEnabled('inventory'))
<!-- Deployed Tools Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="mb-0 fw-bold">{{ __('Alat yang Dipakai Teknisi & Pengurus') }}</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-uppercase small text-muted border-0">{{ __('Kode Aset') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Nama Barang') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Pemegang') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Peran') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Status') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Kondisi') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deployedAssets as $asset)
                        <tr>
                            <td class="ps-4 font-monospace small">{{ $asset->asset_code }}</td>
                            <td class="fw-medium">{{ $asset->item->name ?? '-' }}</td>
                            <td>
                                @if($asset->holder)
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            {{ substr($asset->holder->name, 0, 1) }}
                                        </div>
                                        <span>{{ $asset->holder->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($asset->holder_type == 'App\Models\User')
                                    <span class="badge bg-info-subtle text-info">{{ __('Teknisi') }}</span>
                                @elseif($asset->holder_type == 'App\Models\Coordinator')
                                    <span class="badge bg-warning-subtle text-warning">{{ __('Pengurus') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Tidak Diketahui') }}</span>
                                @endif
                            </td>
                            <td><span class="badge bg-success">{{ ucfirst($asset->status) }}</span></td>
                            <td>{{ ucfirst($asset->condition) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">{{ __('Belum ada alat yang dipinjamkan ke staf.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('financialChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($financialData['labels']),
                datasets: [
                    {
                        label: '{{ __("Pendapatan") }}',
                        data: @json($financialData['income']),
                        backgroundColor: 'rgba(25, 135, 84, 0.7)',
                        borderColor: 'rgba(25, 135, 84, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.6,
                    },
                    {
                        label: '{{ __("Pengeluaran") }}',
                        data: @json($financialData['expense']),
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumSignificantDigits: 3 }).format(value);
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush

@if(\App\Support\ModuleGate::isEnabled('isp'))
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh MixRADIUS status every 15s
        const badge = document.getElementById('mixradius-badge');
        const meta = document.getElementById('mixradius-meta');
        async function refreshMixRadius() {
            if (!badge) return;
            try {
                const res = await fetch("{{ route('health.mixradius') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
                if (!res.ok) throw new Error('network');
                const data = await res.json();
                const ok = !!data.ok;
                badge.textContent = ok ? 'Terhubung' : 'Terputus';
                badge.className = 'badge ' + (ok ? 'bg-success' : 'bg-danger');
                if (meta) {
                    const ts = data.checked_at ? new Date(data.checked_at) : null;
                    const timeStr = ts ? ts.toLocaleTimeString() : '';
                    const authStr = (data.ok_auth === true) ? 'auth: OK' : (data.ok_auth === false ? 'auth: FAIL' : 'auth: -');
                    if (typeof data.latency_ms === 'number') {
                        meta.style.display = '';
                        meta.textContent = `• ${data.latency_ms} ms • ${authStr} • ${timeStr}`;
                    } else {
                        meta.style.display = '';
                        meta.textContent = `• ${authStr} • ${timeStr}`;
                    }
                }
            } catch (e) {
                badge.textContent = 'Terputus';
                badge.className = 'badge bg-danger';
                if (meta) {
                    meta.style.display = '';
                    meta.textContent = '• auth: -';
                }
            }
        }
        setInterval(refreshMixRadius, 15000);
        // initial slight delay to avoid blocking page load
        setTimeout(refreshMixRadius, 2000);

    });
</script>
@endpush
@endif

@endsection
