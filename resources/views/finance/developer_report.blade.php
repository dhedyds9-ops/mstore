@extends('layouts.app')

@section('title', __('Laporan Pembukuan Pengembang'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-code-branch text-primary me-2"></i>{{ __('Laporan Pembukuan Pengembang') }}
            </h4>
            <p class="text-body-secondary small mb-0">{{ __('Rekapitulasi pendapatan dan beban operasional divisi manajemen / pengembang.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Kas') }}
            </a>
            <a href="{{ route('finance.developer.pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm shadow-sm" target="_blank">
                <i class="fa-solid fa-file-pdf me-1"></i> {{ __('Ekspor PDF') }}
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('finance.developer') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="month" class="form-label small fw-semibold text-body-secondary mb-1">{{ __('Pilih Periode Bulan') }}</label>
                    <input type="month" id="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">
                        <i class="fa-solid fa-filter me-1"></i> {{ __('Terapkan Filter') }}
                    </button>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    @if($selectedMonth)
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2">
                            <i class="fa-regular fa-calendar me-1"></i> {{ \Carbon\Carbon::parse($selectedMonth.'-01')->translatedFormat('F Y') }}
                        </span>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-success shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">{{ __('Total Pendapatan') }}</span>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-arrow-down-long"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-success mb-1">
                        Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">{{ __('Akumulasi penerimaan dari seluruh unit operasional') }}</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-danger shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">{{ __('Total Pengeluaran') }}</span>
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-arrow-up-long"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-danger mb-1">
                        Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">{{ __('Beban operasional, internet, gaji, dan logistik') }}</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-primary shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">{{ __('Laba Bersih') }}</span>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }} mb-1">
                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">{{ __('Hasil bersih setelah dikurangi seluruh beban') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Rincian Pendapatan & Pengeluaran -->
    <div class="row g-4 mb-4">
        <!-- Rincian Pendapatan -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="fa-solid fa-circle-arrow-down text-success me-2"></i>{{ __('Rincian Pendapatan') }}
                    </h6>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        Pemasukan
                    </span>
                </div>
                <div class="card-body p-0 border-top">
                    <div class="table-responsive table-responsive-mobile">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-uppercase text-body-secondary">
                                    <th class="ps-3 py-3">{{ __('Sumber Pendapatan') }}</th>
                                    <th class="text-end pe-3 py-3">{{ __('Jumlah') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Pendapatan ISP</div>
                                        <small class="text-body-secondary">Bagi hasil 25% dari Koordinator</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-success">
                                        Rp {{ number_format($incomeISP, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Pendapatan Manajemen</div>
                                        <small class="text-body-secondary">Bagi hasil 20% dari Pengurus</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-success">
                                        Rp {{ number_format($incomeMgmt, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Penjualan Material Wifi</div>
                                        <small class="text-body-secondary">Material, kabel, dan router</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-success">
                                        Rp {{ number_format($incomeMaterial, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Jasa Cuci Kendaraan</div>
                                        <small class="text-body-secondary">Unit usaha car & motor wash</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-success">
                                        Rp {{ number_format($incomeWash, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Penjualan ATK & Fotokopi</div>
                                        <small class="text-body-secondary">Unit usaha alat tulis kantor</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-success">
                                        Rp {{ number_format($incomeATK, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td class="ps-3 py-3 text-uppercase small text-body-secondary">{{ __('Total Pendapatan') }}</td>
                                    <td class="text-end pe-3 py-3 font-monospace text-success fs-6">
                                        Rp {{ number_format($totalIncome, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rincian Pengeluaran -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="fa-solid fa-circle-arrow-up text-danger me-2"></i>{{ __('Rincian Pengeluaran') }}
                    </h6>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                        Beban Operasional
                    </span>
                </div>
                <div class="card-body p-0 border-top">
                    <div class="table-responsive table-responsive-mobile">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-uppercase text-body-secondary">
                                    <th class="ps-3 py-3">{{ __('Jenis Pengeluaran') }}</th>
                                    <th class="text-end pe-3 py-3">{{ __('Jumlah') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Pembayaran Internet (ISP)</div>
                                        <small class="text-body-secondary">Tagihan bandwidth uplink pusat</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-danger">
                                        Rp {{ number_format($expenseISP, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Gaji Karyawan & Staf</div>
                                        <small class="text-body-secondary">Payroll gaji tim pengembang & operasional</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-danger">
                                        Rp {{ number_format($expenseSalaries, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Pembelian Material</div>
                                        <small class="text-body-secondary">Restok barang & inventaris instalasi</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-danger">
                                        Rp {{ number_format($expenseMaterials, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Pembelian Alat Kerja & Tools</div>
                                        <small class="text-body-secondary">Peralatan teknisi & perlengkapan</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-danger">
                                        Rp {{ number_format($expenseTools, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Pengeluaran Unit Jasa Cuci</div>
                                        <small class="text-body-secondary">Sampo, semir, & operasional steam</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-danger">
                                        Rp {{ number_format($expenseWash, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-body">Pengeluaran Lain-lain</div>
                                        <small class="text-body-secondary">Biaya umum & kebutuhan tak terduga</small>
                                    </td>
                                    <td class="text-end pe-3 fw-bold font-monospace text-danger">
                                        Rp {{ number_format($expenseOthers, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td class="ps-3 py-3 text-uppercase small text-body-secondary">{{ __('Total Pengeluaran') }}</td>
                                    <td class="text-end pe-3 py-3 font-monospace text-danger fs-6">
                                        Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
