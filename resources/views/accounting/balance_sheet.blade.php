@extends('layouts.app')
@section('title', 'Neraca')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-landmark text-primary me-2"></i>Laporan Posisi Keuangan (Neraca PSAK)</h4>
            <p class="text-secondary small mb-0">Posisi aset (aktiva), kewajiban (liabilitas), dan modal (ekuitas) perusahaan.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-danger shadow-sm" href="{{ route('accounting.balance_sheet.pdf', request()->all()) }}">
                <i class="fas fa-file-pdf me-1"></i> Ekspor PDF
            </a>
            <a class="btn btn-sm btn-outline-success shadow-sm" href="{{ route('accounting.balance_sheet.excel', request()->all()) }}">
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

    <div class="row g-4 mb-4">
        <!-- Kolom Aset -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <div class="card-header bg-primary bg-opacity-10 py-3 border-0">
                    <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-wallet me-2"></i>Aset / Aktiva</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 small text-uppercase text-body-secondary fw-semibold">Kode</th>
                                <th class="small text-uppercase text-body-secondary fw-semibold">Nama Akun</th>
                                <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $a)
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-primary">{{ $a->code }}</td>
                                <td class="fw-medium text-dark">{{ $a->name }}</td>
                                <td class="text-end pe-4 font-monospace">Rp {{ number_format($a->amount,0,',','.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">Tidak ada aset tercatat</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold border-top-2">
                            <tr>
                                <td colspan="2" class="ps-4 text-end text-uppercase small">Total Aset:</td>
                                <td class="text-end pe-4 font-monospace text-primary">Rp {{ number_format($totalAssets,0,',','.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kewajiban & Ekuitas -->
        <div class="col-lg-6 d-flex flex-column gap-4">
            <!-- Kewajiban -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-warning bg-opacity-10 py-3 border-0">
                    <h6 class="mb-0 fw-bold text-warning-emphasis"><i class="fas fa-file-invoice-dollar me-2"></i>Kewajiban / Liabilitas</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 small text-uppercase text-body-secondary fw-semibold">Kode</th>
                                <th class="small text-uppercase text-body-secondary fw-semibold">Nama Akun</th>
                                <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($liabilities as $l)
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-warning-emphasis">{{ $l->code }}</td>
                                <td class="fw-medium text-dark">{{ $l->name }}</td>
                                <td class="text-end pe-4 font-monospace">Rp {{ number_format($l->amount,0,',','.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted">Tidak ada kewajiban</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold border-top-2">
                            <tr>
                                <td colspan="2" class="ps-4 text-end text-uppercase small">Total Kewajiban:</td>
                                <td class="text-end pe-4 font-monospace text-warning-emphasis">Rp {{ number_format($totalLiabilities,0,',','.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Ekuitas -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-info bg-opacity-10 py-3 border-0">
                    <h6 class="mb-0 fw-bold text-info"><i class="fas fa-coins me-2"></i>Ekuitas / Modal</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 small text-uppercase text-body-secondary fw-semibold">Kode</th>
                                <th class="small text-uppercase text-body-secondary fw-semibold">Nama Akun</th>
                                <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equity as $e)
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-info">{{ $e->code }}</td>
                                <td class="fw-medium text-dark">{{ $e->name }}</td>
                                <td class="text-end pe-4 font-monospace">Rp {{ number_format($e->amount,0,',','.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted">Tidak ada ekuitas</td></tr>
                            @endforelse
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-info">3201</td>
                                <td class="fw-medium text-dark">Laba Bersih Periode Berjalan</td>
                                <td class="text-end pe-4 font-monospace">Rp {{ number_format($netIncome,0,',','.') }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light fw-bold border-top-2">
                            <tr>
                                <td colspan="2" class="ps-4 text-end text-uppercase small">Total Ekuitas:</td>
                                <td class="text-end pe-4 font-monospace text-info">Rp {{ number_format($totalEquity,0,',','.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Check Comparison -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 bg-primary bg-opacity-10 border-start border-4 border-primary">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-uppercase fw-semibold text-primary">Total Aset (Aktiva)</div>
                        <h4 class="fw-bold mb-0 font-monospace text-primary mt-1">Rp {{ number_format($totalAssets,0,',','.') }}</h4>
                    </div>
                    <i class="fas fa-wallet fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 bg-info bg-opacity-10 border-start border-4 border-info">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-uppercase fw-semibold text-info">Total Kewajiban + Ekuitas (Pasiva)</div>
                        <h4 class="fw-bold mb-0 font-monospace text-info mt-1">Rp {{ number_format($rhs,0,',','.') }}</h4>
                    </div>
                    <i class="fas fa-scale-balanced fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    @if(round($totalAssets) == round($rhs))
    <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center mt-3 mb-0">
        <i class="fas fa-check-circle fa-lg me-2"></i>
        <div>
            <strong>Neraca Seimbang:</strong> Nilai Aktiva sama dengan nilai Pasiva (Kewajiban + Ekuitas).
        </div>
    </div>
    @else
    <div class="alert alert-warning border-0 shadow-sm rounded-3 d-flex align-items-center mt-3 mb-0">
        <i class="fas fa-exclamation-triangle fa-lg me-2"></i>
        <div>
            <strong>Neraca Belum Seimbang:</strong> Selisih Aktiva dan Pasiva adalah Rp {{ number_format(abs($totalAssets - $rhs),0,',','.') }}.
        </div>
    </div>
    @endif
</div>
@endsection
