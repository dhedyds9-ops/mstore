@extends('layouts.app')

@section('title', __('Laporan Dana Talangan ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman & Ekspor --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-hand-holding-dollar me-1"></i>{{ __('Laporan Modal Talangan') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-calendar-days me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Laporan Dana Talangan Owner') }}</h1>
            <p class="text-muted small mb-0">{{ __('Rekapitulasi suntikan modal kasir dan pengembalian dana talangan pemilik toko.') }}</p>
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
                        <a class="dropdown-item py-2" href="{{ route('atk.reports.owner-funds.pdf', request()->query()) }}">
                            <i class="fa-solid fa-file-pdf text-danger me-2"></i>{{ __('Unduh Berkas PDF') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('atk.reports.owner-funds.excel', request()->query()) }}">
                            <i class="fa-solid fa-file-excel text-success me-2"></i>{{ __('Unduh Spreadsheet Excel') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('atk.reports.owner-funds') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-6 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted small">{{ __('Dari') }}</span>
                        <input type="date" name="start_date" class="form-control bg-light" value="{{ old('start_date', (is_object($start) ? $start->format('Y-m-d') : $start)) }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted small">{{ __('Sampai') }}</span>
                        <input type="date" name="end_date" class="form-control bg-light" value="{{ old('end_date', (is_object($end) ? $end->format('Y-m-d') : $end)) }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm bg-light">
                        <option value="">{{ __('Semua Status') }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved (Disetujui)') }}</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-sm btn-dark px-3 flex-fill flex-md-grow-0">
                        <i class="fa-solid fa-filter me-1"></i>{{ __('Filter Data') }}
                    </button>
                    @if(request('start_date') || request('end_date') || request('status'))
                        <a href="{{ route('atk.reports.owner-funds') }}" class="btn btn-sm btn-outline-secondary px-2" title="{{ __('Reset Filter') }}">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- 3 Stat Cards Ringkasan Saldo --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success-subtle text-success fs-4">
                        <i class="fa-solid fa-circle-arrow-down"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Dana Masuk (Pinjaman)') }}</div>
                        <div class="fs-5 fw-bold text-success">+Rp {{ number_format($incoming, 0, ',', '.') }}</div>
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
                        <div class="text-muted small fw-medium">{{ __('Total Pengembalian ke Owner') }}</div>
                        <div class="fs-5 fw-bold text-danger">-Rp {{ number_format($outgoing, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-warning">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning-subtle text-warning-emphasis fs-4">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Saldo Talangan Aktif') }}</div>
                        <div class="fs-5 fw-bold text-gray-900">Rp {{ number_format($currentBalance, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Tabel Laporan Dana Talangan --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary"></i>
                <span>{{ __('Detail Transaksi Mutasi Talangan') }}</span>
            </h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">
                {{ $funds->total() }} {{ __('Data Transaksi') }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="min-width: 130px;" class="py-3">{{ __('Tanggal') }}</th>
                        <th style="min-width: 140px;" class="py-3">{{ __('Kode Transaksi') }}</th>
                        <th style="min-width: 120px;" class="py-3 text-center">{{ __('Jenis Mutasi') }}</th>
                        <th style="min-width: 140px;" class="py-3 text-end">{{ __('Jumlah Nominal') }}</th>
                        <th style="min-width: 140px;" class="py-3 text-end">{{ __('Saldo Berjalan') }}</th>
                        <th style="min-width: 200px;" class="py-3">{{ __('Deskripsi / Catatan') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($funds as $fund)
                    <tr>
                        <td>
                            <div class="fw-semibold text-gray-800 small">{{ optional($fund->transaction_date)->translatedFormat('d M Y') ?? '-' }}</div>
                            <small class="text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ $fund->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <span class="font-monospace fw-bold text-primary">{{ $fund->transaction_code }}</span>
                        </td>
                        <td class="text-center">
                            @if($fund->type === 'loan')
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-semibold">
                                    <i class="fa-solid fa-arrow-down me-1"></i>{{ __('Pinjaman') }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-2 py-1 small fw-semibold">
                                    <i class="fa-solid fa-arrow-up me-1"></i>{{ __('Pengembalian') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-bold {{ $fund->type === 'loan' ? 'text-success' : 'text-danger' }}">
                                {{ $fund->type === 'loan' ? '+' : '-' }}Rp {{ number_format($fund->amount, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-gray-900 fs-6">Rp {{ number_format($fund->balance, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="small text-dark">{{ $fund->description ?: '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-hand-holding-dollar fs-1 text-secondary-subtle mb-3 d-block"></i>
                            <h6 class="fw-bold mb-1">{{ __('Tidak Ada Data Talangan') }}</h6>
                            <p class="small mb-0">{{ __('Tidak ada catatan transaksi dana talangan pada rentang tanggal ini.') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Card Footer & Paginasi --}}
        <div class="card-footer bg-white border-top py-3 px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <div class="text-muted small">
                {{ __('Menampilkan') }} <span class="fw-semibold text-dark">{{ $funds->firstItem() ?? 0 }}</span> - <span class="fw-semibold text-dark">{{ $funds->lastItem() ?? 0 }}</span> {{ __('dari') }} <span class="fw-semibold text-dark">{{ $funds->total() }}</span> {{ __('transaksi') }}
            </div>
            <div>
                {{ $funds->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
