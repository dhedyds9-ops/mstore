@extends('layouts.app')
@section('title', 'Neraca Saldo')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-balance-scale text-primary me-2"></i>Neraca Saldo</h4>
            <p class="text-secondary small mb-0">Laporan keseimbangan saldo debit dan kredit akun buku besar.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-danger shadow-sm" href="{{ route('accounting.trial_balance.pdf', request()->all()) }}">
                <i class="fas fa-file-pdf me-1"></i> Ekspor PDF
            </a>
            <a class="btn btn-sm btn-outline-success shadow-sm" href="{{ route('accounting.trial_balance.excel', request()->all()) }}">
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

    <!-- Table Card -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%;" class="ps-4 small text-uppercase text-body-secondary fw-semibold">Kode</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold">Nama Akun</th>
                        <th style="width: 20%;" class="text-end small text-uppercase text-body-secondary fw-semibold">Debit</th>
                        <th style="width: 20%;" class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                    <tr>
                        <td class="ps-4 font-monospace fw-bold text-primary">{{ $r->code }}</td>
                        <td class="fw-medium text-dark">{{ $r->name }}</td>
                        <td class="text-end font-monospace">{{ $r->debit > 0 ? 'Rp ' . number_format($r->debit,0,',','.') : '-' }}</td>
                        <td class="text-end pe-4 font-monospace">{{ $r->credit > 0 ? 'Rp ' . number_format($r->credit,0,',','.') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-balance-scale fa-2x mb-3 text-secondary opacity-50 d-block"></i>
                            Tidak ada data transaksi jurnal pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($rows) > 0)
                <tfoot class="table-light fw-bold border-top-2">
                    <tr>
                        <td colspan="2" class="ps-4 text-end text-uppercase small">Total Saldo:</td>
                        <td class="text-end font-monospace text-primary">Rp {{ number_format($totalDebit,0,',','.') }}</td>
                        <td class="text-end pe-4 font-monospace text-primary">Rp {{ number_format($totalCredit,0,',','.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if($totalDebit == $totalCredit)
    <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center mb-0">
        <i class="fas fa-check-circle fa-lg me-2"></i>
        <div>
            <strong>Status Seimbang:</strong> Total Debit dan Total Kredit telah seimbang (Rp {{ number_format($totalDebit,0,',','.') }}).
        </div>
    </div>
    @else
    <div class="alert alert-danger border-0 shadow-sm rounded-3 d-flex align-items-center mb-0">
        <i class="fas fa-exclamation-triangle fa-lg me-2"></i>
        <div>
            <strong>Peringatan Neraca Tidak Seimbang:</strong> Selisih Rp {{ number_format(abs($totalDebit - $totalCredit),0,',','.') }}. Harap periksa pencatatan jurnal umum Anda.
        </div>
    </div>
    @endif
</div>
@endsection
