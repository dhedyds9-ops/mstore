@extends('layouts.app')
@section('title', 'Periode Akuntansi')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-calendar-check text-primary me-2"></i>Periode Akuntansi & Tutup Buku</h4>
            <p class="text-secondary small mb-0">Kelola siklus periode pembukuan, pengisian saldo awal, dan proses tutup buku.</p>
        </div>
        <div>
            <a href="{{ route('accounting.periods.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus me-1"></i> Buat Periode Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center mb-4">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3 d-flex align-items-center mb-4">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list text-primary me-2"></i>Daftar Periode Pembukuan</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 small text-uppercase text-body-secondary fw-semibold">Nama Periode</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold">Tanggal Mulai</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold">Tanggal Selesai</th>
                        <th class="text-center small text-uppercase text-body-secondary fw-semibold">Status</th>
                        <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $p)
                    <tr>
                        <td class="ps-4 fw-bold text-dark">{{ $p->name }}</td>
                        <td class="text-secondary">{{ \Carbon\Carbon::parse($p->start_date)->translatedFormat('d M Y') }}</td>
                        <td class="text-secondary">{{ \Carbon\Carbon::parse($p->end_date)->translatedFormat('d M Y') }}</td>
                        <td class="text-center">
                            @if($p->status === 'open')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-semibold">
                                    <i class="fas fa-circle-check me-1 small"></i> Terbuka
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1 fw-semibold">
                                    <i class="fas fa-lock me-1 small"></i> Ditutup
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('accounting.periods.opening', $p) }}" class="btn btn-outline-primary btn-sm shadow-sm" title="Kelola Saldo Awal">
                                    <i class="fas fa-book-open me-1"></i> Saldo Awal
                                </a>
                                @if($p->status === 'open')
                                <form method="post" action="{{ route('accounting.periods.close', $p) }}" onsubmit="return confirm('Tutup periode {{ $p->name }}? Transaksi pada periode ini akan dikunci.')" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm shadow-sm" title="Tutup Buku Periode Ini">
                                        <i class="fas fa-lock me-1"></i> Tutup
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-3 text-secondary opacity-50 d-block"></i>
                            Belum ada periode akuntansi yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
