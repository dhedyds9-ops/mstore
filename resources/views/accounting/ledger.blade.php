@extends('layouts.app')
@section('title', 'Buku Besar')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h4 mb-1 fw-bold text-body"><i class="fa-solid fa-book-open text-primary me-2"></i>Buku Besar (General Ledger)</h1>
            <p class="text-body-secondary small mb-0">Rincian mutasi debit, kredit, dan saldo berjalan per akun akuntansi.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-sm btn-outline-danger shadow-sm" href="{{ route('accounting.ledger.pdf', request()->all()) }}" target="_blank">
                <i class="fa-solid fa-file-pdf me-1"></i> PDF
            </a>
            <a class="btn btn-sm btn-outline-success shadow-sm" href="{{ route('accounting.ledger.excel', request()->all()) }}">
                <i class="fa-solid fa-file-excel me-1"></i> Excel
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4">
            <form method="get" class="row g-3 align-items-end mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-body-secondary mb-1">Akun Perkiraan</label>
                    <select name="account_id" class="form-select form-select-sm">
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $a)
                            <option value="{{ $a->id }}" {{ (request('account_id')==$a->id)?'selected':'' }}>{{ $a->code }} - {{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <label class="form-label small fw-semibold text-body-secondary mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $start }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-auto">
                    <label class="form-label small fw-semibold text-body-secondary mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $end }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-auto">
                    <button class="btn btn-sm btn-primary px-3 shadow-sm">
                        <i class="fa-solid fa-filter me-1"></i> Terapkan
                    </button>
                    @if(request('account_id') || request('start_date') || request('end_date'))
                        <a href="{{ route('accounting.ledger') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
                    @endif
                </div>
            </form>
            @if($selected)
            <div class="mb-3 d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 font-monospace fs-6">
                    {{ $selected->code }} - {{ $selected->name }}
                </span>
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                    {{ strtoupper($selected->type) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0 table-responsive-mobile">
                    <thead class="table-light">
                        <tr class="small text-uppercase text-body-secondary">
                            <th style="width: 15%;">Tanggal</th>
                            <th style="width: 15%;">No. Jurnal</th>
                            <th class="text-end" style="width: 18%;">Debit</th>
                            <th class="text-end" style="width: 18%;">Kredit</th>
                            <th>Keterangan / Memo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $balance = 0; @endphp
                        @forelse($entries as $e)
                        @php $balance += ($e->debit - $e->credit); @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}</td>
                            <td class="font-monospace fw-bold text-primary">{{ $e->journal_no }}</td>
                            <td class="text-end font-monospace">{{ $e->debit > 0 ? number_format($e->debit,0,',','.') : '-' }}</td>
                            <td class="text-end font-monospace">{{ $e->credit > 0 ? number_format($e->credit,0,',','.') : '-' }}</td>
                            <td>{{ $e->memo }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-body-secondary">
                                <i class="fa-regular fa-folder-open fa-2x opacity-25 d-block mb-2"></i>
                                Tidak ada transaksi pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5 text-body-secondary border rounded-3 bg-body-tertiary">
                <i class="fa-solid fa-book-open fa-2x opacity-25 d-block mb-2 text-primary"></i>
                Silakan pilih akun perkiraan terlebih dahulu untuk menampilkan buku besar.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
