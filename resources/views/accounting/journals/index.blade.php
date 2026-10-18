@extends('layouts.app')
@section('title', 'Jurnal Umum (General Journal)')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 fw-bold text-gray-800">Jurnal Umum (General Journal)</h1>
            <p class="text-muted small mb-0">Catatan transaksi keuangan sistematis dengan prinsip pembukuan ganda (Debit / Kredit).</p>
        </div>
        <a href="{{ route('accounting.journals.create') }}" class="btn btn-primary btn-lg shadow-sm">
            <i class="fas fa-plus me-1"></i> Buat Jurnal Manual
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end mb-4">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $start }}" class="form-control">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $end }}" class="form-control">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-muted">Cari No Jurnal / Deskripsi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Contoh: JU-2026 atau Gaji">
                    </div>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary px-3">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if($start || $end || $search)
                        <a href="{{ route('accounting.journals.index') }}" class="btn btn-outline-secondary ms-1">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 14%;">Tanggal</th>
                            <th style="width: 16%;">No. Jurnal</th>
                            <th>Keterangan / Deskripsi</th>
                            <th style="width: 12%;">Sumber</th>
                            <th style="width: 14%;" class="text-end">Total Nominal</th>
                            <th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 10%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $j)
                            @php
                                $totalDebit = $j->entries->sum('debit');
                            @endphp
                            <tr>
                                <td>{{ date('d/m/Y', strtotime($j->date)) }}</td>
                                <td class="font-monospace fw-bold text-primary">{{ $j->journal_no }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $j->description }}</div>
                                    <small class="text-muted">{{ $j->entries->count() }} baris akun berpasangan</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ strtoupper($j->source_type ?? 'MANUAL') }}</span>
                                </td>
                                <td class="text-end fw-bold font-monospace">
                                    Rp {{ number_format($totalDebit, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        {{ strtoupper($j->status ?? 'POSTED') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#journalDetail{{ $j->id }}">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </button>

                                    <!-- Modal Detail Jurnal -->
                                    <div class="modal fade text-start" id="journalDetail{{ $j->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Detail Jurnal: {{ $j->journal_no }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <div class="small text-muted">Tanggal Transaksi:</div>
                                                            <div class="fw-bold">{{ date('d F Y', strtotime($j->date)) }}</div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="small text-muted">Keterangan:</div>
                                                            <div class="fw-semibold">{{ $j->description }}</div>
                                                        </div>
                                                    </div>

                                                    <table class="table table-sm table-bordered">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Kode & Nama Akun</th>
                                                                <th>Memo / Catatan</th>
                                                                <th class="text-end" style="width: 22%;">Debit (Rp)</th>
                                                                <th class="text-end" style="width: 22%;">Kredit (Rp)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($j->entries as $e)
                                                            <tr>
                                                                <td>
                                                                    <span class="font-monospace fw-bold text-primary">{{ $e->account?->code }}</span>
                                                                    <div class="small">{{ $e->account?->name }}</div>
                                                                </td>
                                                                <td class="small text-muted">{{ $e->memo ?? '-' }}</td>
                                                                <td class="text-end font-monospace {{ $e->debit > 0 ? 'fw-bold' : 'text-muted' }}">
                                                                    {{ number_format($e->debit, 0, ',', '.') }}
                                                                </td>
                                                                <td class="text-end font-monospace {{ $e->credit > 0 ? 'fw-bold' : 'text-muted' }}">
                                                                    {{ number_format($e->credit, 0, ',', '.') }}
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                            <tr class="table-secondary fw-bold">
                                                                <td colspan="2" class="text-end">Total</td>
                                                                <td class="text-end font-monospace">{{ number_format($j->entries->sum('debit'), 0, ',', '.') }}</td>
                                                                <td class="text-end font-monospace">{{ number_format($j->entries->sum('credit'), 0, ',', '.') }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada jurnal umum yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $journals->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
