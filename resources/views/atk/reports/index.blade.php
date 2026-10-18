@extends('layouts.app')

@section('title', __('Laporan Keuangan & Penjualan ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman & Ekspor --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-chart-line me-1"></i>{{ __('Laporan Finansial') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-calendar-days me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Laporan Keuangan & Penjualan ATK') }}</h1>
            <p class="text-muted small mb-0">{{ __('Rekapitulasi omzet pemasukan, pengeluaran operasional, dan kalkulasi laba/rugi toko ATK.') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('atk.dashboard') }}" class="btn btn-sm btn-outline-secondary shadow-2xs">
                <i class="fa-solid fa-gauge me-1"></i>{{ __('Dasbor') }}
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle shadow-2xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-export me-1 text-secondary"></i>
                    <span>{{ __('Ekspor Laporan') }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li>
                        <a class="dropdown-item py-2" id="btnExportPdfAtk" href="#">
                            <i class="fa-solid fa-file-pdf text-danger me-2"></i>{{ __('Unduh Berkas PDF') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" id="btnExportExcelAtk" href="#">
                            <i class="fa-solid fa-file-excel text-success me-2"></i>{{ __('Unduh Spreadsheet Excel') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Navigasi Tab Modern --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-2 p-md-3">
            <ul class="nav nav-pills gap-2" id="atkReportTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 py-2 fw-semibold" id="daily-tab" data-bs-toggle="tab" data-bs-target="#daily-content" type="button">
                        <i class="fa-solid fa-calendar-day me-1"></i>{{ __('Laporan Harian') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 py-2 fw-semibold" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly-content" type="button">
                        <i class="fa-solid fa-calendar-week me-1"></i>{{ __('Laporan Bulanan') }}
                    </button>
                </li>
            </ul>
        </div>
    </div>

    {{-- Konten Tab --}}
    <div class="tab-content" id="atkReportTabContent">
        {{-- ======================= --}}
        {{-- TAB HARIAN --}}
        {{-- ======================= --}}
        <div class="tab-pane fade show active" id="daily-content" role="tabpanel">
            {{-- Filter Tanggal Harian --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('atk.reports.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="view" value="daily">
                        <div class="col-12 col-md-auto">
                            <span class="fw-semibold text-muted small"><i class="fa-solid fa-filter me-1"></i>{{ __('Pilih Tanggal Laporan:') }}</span>
                        </div>
                        <div class="col-12 col-md-3">
                            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm bg-light">
                        </div>
                        <div class="col-12 col-md-auto">
                            <button type="submit" class="btn btn-sm btn-primary px-3">
                                <i class="fa-solid fa-magnifying-glass me-1"></i>{{ __('Tampilkan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3 Kartu Ringkasan Harian --}}
            @php
                $dailyNet = $dailyIncome - $dailyExpense;
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 p-3 bg-success-subtle text-success fs-4">
                                <i class="fa-solid fa-circle-arrow-down"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">{{ __('Total Pemasukan Harian') }}</div>
                                <div class="fs-4 fw-bold text-success">+Rp {{ number_format($dailyIncome, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 p-3 bg-danger-subtle text-danger fs-4">
                                <i class="fa-solid fa-circle-arrow-up"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">{{ __('Total Pengeluaran Harian') }}</div>
                                <div class="fs-4 fw-bold text-danger">-Rp {{ number_format($dailyExpense, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 {{ $dailyNet >= 0 ? 'border-primary' : 'border-danger' }}">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 p-3 {{ $dailyNet >= 0 ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' }} fs-4">
                                <i class="fa-solid fa-scale-balanced"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">{{ __('Laba / Rugi Bersih') }}</div>
                                <div class="fs-4 fw-bold {{ $dailyNet >= 0 ? 'text-primary' : 'text-danger' }}">
                                    Rp {{ number_format($dailyNet, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rincian Pemasukan Harian --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-arrow-down text-success"></i>
                        <span>{{ __('A. Rincian Pemasukan Kasir') }}</span>
                    </h6>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-semibold">
                        {{ count($dailyIncomeRows) }} {{ __('Transaksi') }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                        <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                            <tr>
                                <th style="width: 50px;" class="text-center py-3">#</th>
                                <th style="min-width: 100px;" class="py-3">{{ __('Waktu') }}</th>
                                <th style="min-width: 150px;" class="py-3">{{ __('No. Transaksi') }}</th>
                                <th style="min-width: 220px;" class="py-3">{{ __('Item Produk Terjual') }}</th>
                                <th style="min-width: 120px;" class="py-3 text-center">{{ __('Metode Bayar') }}</th>
                                <th style="min-width: 140px;" class="py-3 text-end">{{ __('Nominal') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($dailyIncomeRows as $index => $r)
                            <tr>
                                <td class="text-center text-muted small">{{ $index + 1 }}</td>
                                <td class="font-monospace small text-muted"><i class="fa-regular fa-clock me-1"></i>{{ $r->created_at->format('H:i') }}</td>
                                <td>
                                    <span class="font-monospace fw-bold text-primary">{{ $r->transaction_number }}</span>
                                </td>
                                <td>
                                    <div class="text-truncate fw-medium text-dark" style="max-width: 300px;">{{ $r->product_names['display'] }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1 small">{{ strtoupper($r->payment_method) }}</span>
                                </td>
                                <td class="text-end fw-bold text-success fs-6">
                                    Rp {{ number_format($r->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted small">{{ __('Tidak ada transaksi pemasukan pada tanggal ini.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light border-top">
                            <tr class="fw-bold">
                                <td colspan="5" class="text-end py-3 text-muted small text-uppercase">{{ __('Total Pemasukan Harian:') }}</td>
                                <td class="text-end py-3 text-success fs-6">Rp {{ number_format($dailyIncome, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Rincian Pengeluaran Harian & Analisis Pembayaran --}}
            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-arrow-up text-danger"></i>
                                <span>{{ __('B. Rincian Pengeluaran Toko') }}</span>
                            </h6>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small fw-semibold">
                                {{ count($dailyExpenseRows) }} {{ __('Catatan') }}
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                                    <tr>
                                        <th style="width: 50px;" class="text-center py-3">#</th>
                                        <th style="min-width: 200px;" class="py-3">{{ __('Deskripsi Pengeluaran') }}</th>
                                        <th style="min-width: 130px;" class="py-3 text-end">{{ __('Nominal') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse($dailyExpenseRows as $index => $r)
                                    <tr>
                                        <td class="text-center text-muted small">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $r->description }}</div>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            Rp {{ number_format($r->amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted small">{{ __('Tidak ada pengeluaran pada tanggal ini.') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light border-top">
                                    <tr class="fw-bold">
                                        <td colspan="2" class="text-end py-3 text-muted small text-uppercase">{{ __('Total Pengeluaran:') }}</td>
                                        <td class="text-end py-3 text-danger">Rp {{ number_format($dailyExpense, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
                            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-chart-pie text-primary"></i>
                                <span>{{ __('C. Analisis Per Metode Bayar') }}</span>
                            </h6>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                                        <tr>
                                            <th>{{ __('Metode Pembayaran') }}</th>
                                            <th class="text-end">{{ __('Total Nominal') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @forelse($dailyByPayment as $r)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2 py-1 small">{{ strtoupper($r->payment_method) }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-gray-900">
                                                Rp {{ number_format($r->amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="2" class="text-center py-3 text-muted small">-</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================= --}}
        {{-- TAB BULANAN --}}
        {{-- ======================= --}}
        <div class="tab-pane fade" id="monthly-content" role="tabpanel">
            {{-- Filter Bulan --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('atk.reports.index') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="view" value="monthly">
                        <div class="col-12 col-md-auto">
                            <span class="fw-semibold text-muted small"><i class="fa-solid fa-filter me-1"></i>{{ __('Pilih Periode Bulan:') }}</span>
                        </div>
                        <div class="col-12 col-md-3">
                            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm bg-light">
                        </div>
                        <div class="col-12 col-md-auto">
                            <button type="submit" class="btn btn-sm btn-primary px-3">
                                <i class="fa-solid fa-magnifying-glass me-1"></i>{{ __('Tampilkan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3 Kartu Ringkasan Bulanan --}}
            @php
                $monthlyNet = $monthlyIncome - $monthlyExpense;
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 p-3 bg-success-subtle text-success fs-4">
                                <i class="fa-solid fa-circle-arrow-down"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">{{ __('Pemasukan Bulanan') }}</div>
                                <div class="fs-4 fw-bold text-success">+Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 p-3 bg-danger-subtle text-danger fs-4">
                                <i class="fa-solid fa-circle-arrow-up"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">{{ __('Pengeluaran Bulanan') }}</div>
                                <div class="fs-4 fw-bold text-danger">-Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 {{ $monthlyNet >= 0 ? 'border-primary' : 'border-danger' }}">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="rounded-3 p-3 {{ $monthlyNet >= 0 ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' }} fs-4">
                                <i class="fa-solid fa-scale-balanced"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium">{{ __('Laba Bersih Bulanan') }}</div>
                                <div class="fs-4 fw-bold {{ $monthlyNet >= 0 ? 'text-primary' : 'text-danger' }}">
                                    Rp {{ number_format($monthlyNet, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rekap Harian Bulanan & Analisis Metode Bayar --}}
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
                            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-table-list text-primary"></i>
                                <span>{{ __('Rekap Arus Kas Harian (Periode: ') }} {{ $month }})</span>
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                                    <tr>
                                        <th style="min-width: 120px;" class="py-3">{{ __('Tanggal') }}</th>
                                        <th style="min-width: 130px;" class="py-3 text-end">{{ __('Pemasukan') }}</th>
                                        <th style="min-width: 130px;" class="py-3 text-end">{{ __('Pengeluaran') }}</th>
                                        <th style="min-width: 140px;" class="py-3 text-end">{{ __('Laba / Rugi') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @php
                                        $incomeMap = collect($monthlyDailyIncome)->keyBy('d');
                                        $expenseMap = collect($monthlyDailyExpense)->keyBy('d');
                                        $days = collect($incomeMap->keys())->merge($expenseMap->keys())->unique()->sort();
                                    @endphp
                                    @forelse($days as $d)
                                    @php
                                        $inc = (float)($incomeMap[$d]->total ?? 0);
                                        $exp = (float)($expenseMap[$d]->total ?? 0);
                                        $net = $inc - $exp;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold text-gray-800">{{ $d }}</td>
                                        <td class="text-end text-success fw-medium">+Rp {{ number_format($inc, 0, ',', '.') }}</td>
                                        <td class="text-end text-danger fw-medium">-Rp {{ number_format($exp, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold {{ $net >= 0 ? 'text-primary' : 'text-danger' }}">
                                            Rp {{ number_format($net, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted small">{{ __('Tidak ada data pada bulan ini.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
                            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-chart-pie text-primary"></i>
                                <span>{{ __('Analisis Metode Pembayaran (Bulanan)') }}</span>
                            </h6>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                                        <tr>
                                            <th>{{ __('Metode') }}</th>
                                            <th class="text-end">{{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @forelse($monthlyByPayment as $r)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2 py-1 small">{{ strtoupper($r->payment_method) }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-gray-900">
                                                Rp {{ number_format($r->amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="2" class="text-center py-3 text-muted small">-</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        function q(name) { return new URLSearchParams(window.location.search).get(name); }
        var pdf = document.getElementById('btnExportPdfAtk');
        var xls = document.getElementById('btnExportExcelAtk');
        if (pdf) {
            var params = new URLSearchParams();
            if (q('date')) params.set('date', q('date'));
            if (q('month')) params.set('month', q('month'));
            pdf.href = '{{ route('atk.reports.pdf') }}' + (params.toString() ? ('?' + params.toString()) : '');
        }
        if (xls) {
            var params2 = new URLSearchParams();
            if (q('date')) params2.set('date', q('date'));
            if (q('month')) params2.set('month', q('month'));
            xls.href = '{{ route('atk.reports.excel') }}' + (params2.toString() ? ('?' + params2.toString()) : '');
        }
        
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('month') && !urlParams.has('date')) {
                const monthlyTab = document.querySelector('#monthly-tab');
                if (monthlyTab && window.bootstrap) {
                    new bootstrap.Tab(monthlyTab).show();
                }
            }
        });
    })();
</script>
@endpush
@endsection
