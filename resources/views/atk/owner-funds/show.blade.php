@extends('layouts.app')

@section('title', __('Detail Dana Talangan ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Detail Mutasi Dana Talangan') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Rincian informasi transaksi dan status rekonsiliasi kas.') }}</p>
                </div>
                <a href="{{ route('atk.owner-funds.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali') }}
                </a>
            </div>

            {{-- Detail Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <span class="font-monospace fw-bold text-primary">{{ $fund->transaction_code }}</span>
                    @if($fund->type === 'loan')
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-semibold">
                            <i class="fa-solid fa-arrow-down me-1"></i>{{ __('Pinjaman Masuk') }}
                        </span>
                    @else
                        <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-2 py-1 small fw-semibold">
                            <i class="fa-solid fa-arrow-up me-1"></i>{{ __('Pengembalian Dana') }}
                        </span>
                    @endif
                </div>

                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="text-muted small">{{ __('Jumlah Nominal') }}</div>
                            <div class="fs-4 fw-bold {{ $fund->type === 'loan' ? 'text-success' : 'text-danger' }}">
                                {{ $fund->type === 'loan' ? '+' : '-' }}Rp {{ number_format($fund->amount, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">{{ __('Saldo Akhir Talangan') }}</div>
                            <div class="fs-4 fw-bold text-gray-900">
                                Rp {{ number_format($fund->balance, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            <tr class="border-top">
                                <th class="text-muted small fw-medium py-2" style="width: 35%;">{{ __('Tanggal Transaksi') }}</th>
                                <td class="py-2 fw-semibold text-dark">{{ optional($fund->transaction_date)->translatedFormat('d F Y') ?? '-' }}</td>
                            </tr>
                            <tr class="border-top">
                                <th class="text-muted small fw-medium py-2">{{ __('Keterangan / Catatan') }}</th>
                                <td class="py-2 text-dark">{{ $fund->description ?: '-' }}</td>
                            </tr>
                            <tr class="border-top">
                                <th class="text-muted small fw-medium py-2">{{ __('Petugas Pencatat') }}</th>
                                <td class="py-2 text-dark">{{ $fund->creator->name ?? '-' }}</td>
                            </tr>
                            <tr class="border-top">
                                <th class="text-muted small fw-medium py-2">{{ __('Status Verifikasi') }}</th>
                                <td class="py-2">
                                    <span class="badge rounded-pill {{ $fund->status === 'approved' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary' }} px-2 py-1 small">
                                        {{ ucfirst($fund->status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-light border-top py-3 px-4 d-flex justify-content-between align-items-center">
                    <form action="{{ route('atk.owner-funds.destroy', $fund) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fa-solid fa-trash-can me-1"></i>{{ __('Hapus Transaksi') }}
                        </button>
                    </form>
                    <a href="{{ route('atk.owner-funds.index') }}" class="btn btn-sm btn-secondary px-3">
                        {{ __('Kembali ke Riwayat') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
