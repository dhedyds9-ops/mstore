@extends('layouts.app')

@section('title', __('Dana Talangan Pemilik ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-hand-holding-dollar me-1"></i>{{ __('Modal & Talangan') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-calendar-days me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Dana Talangan Kasir & Pemilik') }}</h1>
            <p class="text-muted small mb-0">{{ __('Pencatatan modal talangan pribadi dari pemilik/owner untuk modal transaksi kasir dan pengembaliannya.') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('atk.dashboard') }}" class="btn btn-sm btn-outline-secondary shadow-2xs">
                <i class="fa-solid fa-gauge me-1"></i>{{ __('Dasbor') }}
            </a>
            <a href="{{ route('atk.owner-funds.create') }}" class="btn btn-sm btn-primary px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i>{{ __('Catat Talangan Baru') }}
            </a>
        </div>
    </div>

    {{-- Ringkasan Metrik Dana Talangan --}}
    @php
        $totalLoan = \App\Models\OwnerFund::where('type', 'loan')->sum('amount');
        $totalRepay = \App\Models\OwnerFund::where('type', 'repayment')->sum('amount');
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-warning">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning-subtle text-warning-emphasis fs-4">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Saldo Talangan Aktif') }}</div>
                        <div class="fs-4 fw-bold text-gray-900">Rp {{ number_format($currentBalance, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success-subtle text-success fs-4">
                        <i class="fa-solid fa-arrow-down-long"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Masuk (Pinjaman)') }}</div>
                        <div class="fs-5 fw-bold text-success">+Rp {{ number_format($totalLoan, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info-subtle text-info fs-4">
                        <i class="fa-solid fa-arrow-up-long"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Pengembalian ke Owner') }}</div>
                        <div class="fs-5 fw-bold text-info">-Rp {{ number_format($totalRepay, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Riwayat Transaksi --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary"></i>
                <span>{{ __('Riwayat Mutasi Dana Talangan') }}</span>
            </h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">
                {{ $funds->total() }} {{ __('Data') }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="min-width: 140px;" class="py-3">{{ __('Kode Transaksi') }}</th>
                        <th style="min-width: 130px;" class="py-3">{{ __('Tanggal') }}</th>
                        <th style="min-width: 120px;" class="py-3 text-center">{{ __('Tipe Mutasi') }}</th>
                        <th style="min-width: 140px;" class="py-3 text-end">{{ __('Jumlah Nominal') }}</th>
                        <th style="min-width: 140px;" class="py-3 text-end">{{ __('Saldo Berjalan') }}</th>
                        <th style="min-width: 100px;" class="py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($funds as $fund)
                    <tr>
                        <td>
                            <a href="{{ route('atk.owner-funds.show', $fund) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                {{ $fund->transaction_code }}
                            </a>
                        </td>
                        <td>
                            <div class="small text-gray-800">{{ optional($fund->transaction_date)->translatedFormat('d M Y') ?? '-' }}</div>
                            <small class="text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ $fund->created_at->format('H:i') }}</small>
                        </td>
                        <td class="text-center">
                            @if($fund->type === 'loan')
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-semibold">
                                    <i class="fa-solid fa-arrow-down me-1"></i>{{ __('Pinjaman Masuk') }}
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
                            <span class="fw-bold text-gray-900 fs-6">
                                Rp {{ number_format($fund->balance, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('atk.owner-funds.show', $fund) }}" class="btn btn-outline-secondary" title="{{ __('Lihat Detail') }}">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                </a>
                                <form action="{{ route('atk.owner-funds.destroy', $fund) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi talangan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary text-danger" title="{{ __('Hapus') }}">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-hand-holding-dollar fs-1 text-secondary-subtle mb-3 d-block"></i>
                                <h6 class="fw-bold mb-1">{{ __('Belum Ada Transaksi Dana Talangan') }}</h6>
                                <p class="small mb-3">{{ __('Catat dana talangan baru jika owner menyuntikkan modal kas untuk transaksi kasir.') }}</p>
                                <a href="{{ route('atk.owner-funds.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-plus me-1"></i>{{ __('Catat Talangan Baru') }}
                                </a>
                            </div>
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
