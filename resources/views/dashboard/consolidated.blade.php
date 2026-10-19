@extends('layouts.app')

@section('title', 'Dashboard Konsolidasi')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-chart-pie text-primary me-2"></i>Dashboard Konsolidasi
            </h4>
            <p class="text-body-secondary small mb-0">Ringkasan performa finansial, laba rugi, dan transaksi lintas unit usaha.</p>
        </div>
        <form action="{{ route('dashboard.consolidated.generate') }}" method="POST" class="d-flex flex-wrap gap-2 align-items-center">
            @csrf
            <select name="type" class="form-select form-select-sm" style="width: auto;" required>
                <option value="daily">Rekap Harian (Daily)</option>
                <option value="monthly">Rekap Bulanan (Monthly)</option>
                <option value="yearly">Rekap Tahunan (Yearly)</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                <i class="fa-solid fa-rotate me-1"></i> Generate
            </button>
        </form>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('dashboard.consolidated') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="business_unit_id" class="form-label small fw-semibold text-body-secondary mb-1">Unit Usaha</label>
                    <select name="business_unit_id" id="business_unit_id" class="form-select form-select-sm">
                        <option value="">Semua Unit Usaha</option>
                        @foreach($businessUnits as $bu)
                            <option value="{{ $bu->id }}" {{ (request('business_unit_id') == $bu->id) ? 'selected' : '' }}>
                                {{ $bu->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="year" class="form-label small fw-semibold text-body-secondary mb-1">Tahun</label>
                    <select name="year" id="year" class="form-select form-select-sm">
                        @for($y = now()->year - 5; $y <= now()->year; $y++)
                            <option value="{{ $y }}" {{ (request('year') ?? now()->year) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="month" class="form-label small fw-semibold text-body-secondary mb-1">Bulan</label>
                    <select name="month" id="month" class="form-select form-select-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (request('month') ?? now()->month) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label for="date" class="form-label small fw-semibold text-body-secondary mb-1">Tanggal Spesifik</label>
                    <input type="date" name="date" id="date" class="form-control form-control-sm"
                           value="{{ request('date') ?? now()->toDateString() }}">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">
                        <i class="fa-solid fa-filter me-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ringkasan Hari Ini -->
    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-calendar-day text-primary me-2"></i>Performa Hari Ini ({{ \Carbon\Carbon::parse(request('date') ?? now()->toDateString())->translatedFormat('d F Y') }})</h6>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-primary shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">Pendapatan Hari Ini</span>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-primary mb-1">
                        Rp {{ number_format($dailySummary?->total_income ?? 0, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">Total arus kas masuk harian</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-success shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">Laba Hari Ini</span>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-success mb-1">
                        Rp {{ number_format($dailySummary?->total_profit ?? 0, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">Estimasi margin laba harian</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-warning shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">Transaksi Hari Ini</span>
                        <div class="bg-warning bg-opacity-10 text-warning-emphasis rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-dark mb-1">
                        {{ number_format($dailySummary?->total_transactions ?? 0, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">Jumlah transaksi berhasil</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Bulanan -->
    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-calendar-week text-primary me-2"></i>Performa Bulanan ({{ \Carbon\Carbon::create()->month(request('month') ?? now()->month)->translatedFormat('F') }} {{ request('year') ?? now()->year }})</h6>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-primary shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">Pendapatan Bulan Ini</span>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-primary mb-1">
                        Rp {{ number_format($monthlySummary?->total_income ?? 0, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">Total akumulasi omset bulan ini</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-success shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">Laba Bulan Ini</span>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-success mb-1">
                        Rp {{ number_format($monthlySummary?->total_profit ?? 0, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">Akumulasi laba kotor bulan ini</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-warning shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">Transaksi Bulan Ini</span>
                        <div class="bg-warning bg-opacity-10 text-warning-emphasis rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-dark mb-1">
                        {{ number_format($monthlySummary?->total_transactions ?? 0, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">Total transaksi berhasil bulan ini</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Trend -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark">
                <i class="fa-solid fa-chart-area text-primary me-2"></i>Tren Pendapatan, Pengeluaran & Laba Tahun {{ $selectedYear }}
            </h6>
        </div>
        <div class="card-body p-4 border-top">
            <div class="chart-area" style="min-height: 350px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendChart').getContext('2d');
    const chartData = @json($chartData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Pendapatan',
                    data: chartData.income,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.08)',
                    fill: true,
                    tension: 0.35,
                },
                {
                    label: 'Pengeluaran',
                    data: chartData.expense,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.08)',
                    fill: true,
                    tension: 0.35,
                },
                {
                    label: 'Laba',
                    data: chartData.profit,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.08)',
                    fill: true,
                    tension: 0.35,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush