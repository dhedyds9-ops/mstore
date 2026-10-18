@extends('layouts.app')
@section('title', 'Laporan Arus Kas')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-money-bill-wave text-primary me-2"></i>Laporan Arus Kas (PSAK)</h4>
            <p class="text-secondary small mb-0">Laporan arus kas metode langsung (Aktivitas Operasi, Investasi, dan Pendanaan).</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-danger shadow-sm" href="{{ route('accounting.cash_flow.pdf', request()->all()) }}">
                <i class="fas fa-file-pdf me-1"></i> Ekspor PDF
            </a>
            <a class="btn btn-sm btn-outline-success shadow-sm" href="{{ route('accounting.cash_flow.excel', request()->all()) }}">
                <i class="fas fa-file-excel me-1"></i> Ekspor Excel
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $start }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $end }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">
                        <i class="fas fa-filter me-1"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Integrasi Finance KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase">Pendapatan Operasional</div>
                    <div class="fs-5 fw-bold text-success mt-1 font-monospace">Rp {{ number_format(($financeSummary['revenue'] ?? 0),0,',','.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-danger rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase">Beban Operasional</div>
                    <div class="fs-5 fw-bold text-danger mt-1 font-monospace">Rp {{ number_format(($financeSummary['expense'] ?? 0),0,',','.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase">Setoran Transfer</div>
                    <div class="fs-5 fw-bold text-warning-emphasis mt-1 font-monospace">Rp {{ number_format(($financeSummary['transfer'] ?? 0),0,',','.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase">Net Operasional Finance</div>
                    <div class="fs-5 fw-bold text-primary mt-1 font-monospace">Rp {{ number_format(($financeSummary['net_operational'] ?? 0),0,',','.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Flow Statement Table Card -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <tbody>
                    <!-- Aktivitas Operasi -->
                    <tr class="table-light">
                        <th colspan="2" class="ps-4 text-primary fw-bold text-uppercase small py-3">
                            <i class="fas fa-briefcase me-2"></i>1. Arus Kas dari Aktivitas Operasi
                        </th>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">Penerimaan dari pelanggan / penjualan</td>
                        <td class="text-end pe-4 font-monospace text-success fw-medium">+ Rp {{ number_format($operatingIn,0,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">Pembayaran beban operasional & pemasok</td>
                        <td class="text-end pe-4 font-monospace text-danger fw-medium">- Rp {{ number_format($operatingOut,0,',','.') }}</td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td class="ps-4">Arus Kas Bersih dari Aktivitas Operasi</td>
                        <td class="text-end pe-4 font-monospace {{ $netOperating >= 0 ? 'text-primary' : 'text-danger' }}">
                            Rp {{ number_format($netOperating,0,',','.') }}
                        </td>
                    </tr>

                    <!-- Aktivitas Investasi -->
                    <tr class="table-light border-top-2">
                        <th colspan="2" class="ps-4 text-primary fw-bold text-uppercase small py-3">
                            <i class="fas fa-building me-2"></i>2. Arus Kas dari Aktivitas Investasi
                        </th>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">Penerimaan dari pelepasan / penjualan aset</td>
                        <td class="text-end pe-4 font-monospace text-success fw-medium">+ Rp {{ number_format($investingIn,0,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">Pembelian aset tetap / perlengkapan</td>
                        <td class="text-end pe-4 font-monospace text-danger fw-medium">- Rp {{ number_format($investingOut,0,',','.') }}</td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td class="ps-4">Arus Kas Bersih dari Aktivitas Investasi</td>
                        <td class="text-end pe-4 font-monospace {{ $netInvesting >= 0 ? 'text-primary' : 'text-danger' }}">
                            Rp {{ number_format($netInvesting,0,',','.') }}
                        </td>
                    </tr>

                    <!-- Aktivitas Pendanaan -->
                    <tr class="table-light border-top-2">
                        <th colspan="2" class="ps-4 text-primary fw-bold text-uppercase small py-3">
                            <i class="fas fa-hand-holding-dollar me-2"></i>3. Arus Kas dari Aktivitas Pendanaan
                        </th>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">Penerimaan pendanaan modal / setoran investor</td>
                        <td class="text-end pe-4 font-monospace text-success fw-medium">+ Rp {{ number_format($financingIn,0,',','.') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">Pengembalian pokok pinjaman / distribusi ekuitas</td>
                        <td class="text-end pe-4 font-monospace text-danger fw-medium">- Rp {{ number_format($financingOut,0,',','.') }}</td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td class="ps-4">Arus Kas Bersih dari Aktivitas Pendanaan</td>
                        <td class="text-end pe-4 font-monospace {{ $netFinancing >= 0 ? 'text-primary' : 'text-danger' }}">
                            Rp {{ number_format($netFinancing,0,',','.') }}
                        </td>
                    </tr>

                    <!-- Total Kenaikan/Penurunan Kas -->
                    <tr class="bg-primary bg-opacity-10 fw-bold border-top-2">
                        <td class="ps-4 py-3 text-primary">Kenaikan (Penurunan) Bersih Kas</td>
                        <td class="text-end pe-4 py-3 font-monospace fs-6 text-primary">
                            Rp {{ number_format($netChange,0,',','.') }}
                        </td>
                    </tr>

                    @if(!is_null($openingCash))
                    <tr>
                        <td class="ps-4 text-muted">Saldo Awal Kas</td>
                        <td class="text-end pe-4 font-monospace text-muted">Rp {{ number_format($openingCash,0,',','.') }}</td>
                    </tr>
                    @endif
                    <tr class="table-light fw-bold">
                        <td class="ps-4 py-3">Saldo Akhir Kas & Setara Kas</td>
                        <td class="text-end pe-4 py-3 font-monospace fs-6 text-dark">
                            Rp {{ number_format($closingCash,0,',','.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
