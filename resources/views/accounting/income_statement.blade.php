@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-chart-line text-primary me-2"></i>Laporan Laba Rugi (PSAK)</h4>
            <p class="text-secondary small mb-0">Rincian pendapatan operasional, beban usaha, dan laba bersih periode berjalan.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-danger shadow-sm" href="{{ route('accounting.income_statement.pdf', request()->all()) }}">
                <i class="fas fa-file-pdf me-1"></i> Ekspor PDF
            </a>
            <a class="btn btn-sm btn-outline-success shadow-sm" href="{{ route('accounting.income_statement.excel', request()->all()) }}">
                <i class="fas fa-file-excel me-1"></i> Ekspor Excel
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $start }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $end }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Unit Bisnis</label>
                    <select name="unit" class="form-select form-select-sm">
                        <option value="">Semua Unit (Konsolidasi)</option>
                        @foreach($unitOptions ?? [] as $code => $label)
                            <option value="{{ $code }}" {{ ($unit ?? '') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">
                        <i class="fas fa-filter me-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Integrasi Transaksi Finance KPI -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-info rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase">Pendapatan Koordinator</div>
                    <div class="fs-5 fw-bold text-dark mt-1 font-monospace">Rp {{ number_format(($financeSummary['member_voucher_income'] ?? 0),0,',','.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase">Total Pendapatan (Kas)</div>
                    <div class="fs-5 fw-bold text-success mt-1 font-monospace">Rp {{ number_format(($financeSummary['revenue'] ?? 0),0,',','.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-danger rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase">Total Beban (Kas)</div>
                    <div class="fs-5 fw-bold text-danger mt-1 font-monospace">Rp {{ number_format(($financeSummary['expense'] ?? 0),0,',','.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase">Setoran ke Kantor</div>
                    <div class="fs-5 fw-bold text-warning-emphasis mt-1 font-monospace">Rp {{ number_format(($financeSummary['transfer'] ?? 0),0,',','.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Pendapatan Akuntansi -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <div class="card-header bg-success bg-opacity-10 py-3 border-0">
                    <h6 class="mb-0 fw-bold text-success"><i class="fas fa-arrow-down me-2"></i>Pendapatan (PSAK)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 small text-uppercase text-body-secondary fw-semibold">Kode</th>
                                <th class="small text-uppercase text-body-secondary fw-semibold">Nama Akun</th>
                                <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revenues as $r)
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-success">{{ $r->code }}</td>
                                <td class="fw-medium text-dark">{{ $r->name }}</td>
                                <td class="text-end pe-4 font-monospace">Rp {{ number_format($r->amount,0,',','.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">Tidak ada pendapatan pada periode ini</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold border-top-2">
                            <tr>
                                <td colspan="2" class="ps-4 text-end text-uppercase small">Total Pendapatan:</td>
                                <td class="text-end pe-4 font-monospace text-success">Rp {{ number_format($totalRevenue,0,',','.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Beban Akuntansi -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <div class="card-header bg-danger bg-opacity-10 py-3 border-0">
                    <h6 class="mb-0 fw-bold text-danger"><i class="fas fa-arrow-up me-2"></i>Beban Usaha & Operasional (PSAK)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 small text-uppercase text-body-secondary fw-semibold">Kode</th>
                                <th class="small text-uppercase text-body-secondary fw-semibold">Nama Akun</th>
                                <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $e)
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-danger">{{ $e->code }}</td>
                                <td class="fw-medium text-dark">{{ $e->name }}</td>
                                <td class="text-end pe-4 font-monospace">Rp {{ number_format($e->amount,0,',','.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">Tidak ada beban pada periode ini</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold border-top-2">
                            <tr>
                                <td colspan="2" class="ps-4 text-end text-uppercase small">Total Beban:</td>
                                <td class="text-end pe-4 font-monospace text-danger">Rp {{ number_format($totalExpense,0,',','.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Laba Bersih -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden {{ $netIncome >= 0 ? 'bg-primary bg-opacity-10 border-start border-4 border-primary' : 'bg-danger bg-opacity-10 border-start border-4 border-danger' }}">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-3 {{ $netIncome >= 0 ? 'bg-primary text-white' : 'bg-danger text-white' }}">
                    <i class="fas {{ $netIncome >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} fa-2x"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 {{ $netIncome >= 0 ? 'text-primary' : 'text-danger' }}">
                        {{ $netIncome >= 0 ? 'Laba Bersih Periode Berjalan' : 'Rugi Bersih Periode Berjalan' }}
                    </h5>
                    <p class="text-secondary small mb-0">Total Pendapatan dikurangi Total Beban Usaha.</p>
                </div>
            </div>
            <div class="text-md-end">
                <span class="fs-3 fw-bold font-monospace {{ $netIncome >= 0 ? 'text-primary' : 'text-danger' }}">
                    Rp {{ number_format($netIncome, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
