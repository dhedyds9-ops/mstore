@extends('layouts.app')

@section('title', $investor->name . ' - Detail Investor')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-hand-holding-dollar text-primary me-2"></i>{{ $investor->name }}
            </h4>
            <p class="text-body-secondary small mb-0">{{ __('Detail profil penanam modal, saldo portofolio, dan riwayat transaksi.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('investors.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali') }}
            </a>
            <a href="{{ route('investors.edit', $investor) }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fa-solid fa-pen-to-square me-1"></i> {{ __('Edit Investor') }}
            </a>
        </div>
    </div>

    <!-- Ringkasan KPI Finansial -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-primary shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">{{ __('Saldo Bersih / Modal Aktif') }}</span>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-primary mb-1">
                        Rp {{ number_format($balance, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">{{ __('Modal aktif saat ini & akumulasi bagi hasil') }}</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-success shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">{{ __('Total Modal Masuk') }}</span>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-arrow-down-long"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-success mb-1">
                        Rp {{ number_format($totalCapital, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">{{ __('Akumulasi penyertaan investasi yang disetor') }}</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 border-start border-4 border-danger shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-body-secondary text-uppercase">{{ __('Total Penarikan / Bagi Hasil') }}</span>
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-arrow-up-long"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold font-monospace text-danger mb-1">
                        Rp {{ number_format($totalWithdrawal, 0, ',', '.') }}
                    </div>
                    <small class="text-body-secondary">{{ __('Penarikan modal atau pembagian hasil keuntungan') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Detail Investor -->
    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark">
                <i class="fa-solid fa-id-card text-primary me-2"></i>{{ __('Informasi Profil Investor') }}
            </h6>
        </div>
        <div class="card-body p-3 p-md-4 border-top">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <span class="text-body-secondary small d-block">{{ __('Nama Lengkap') }}</span>
                            <span class="fw-semibold text-body">{{ $investor->name }}</span>
                        </div>
                        <div>
                            <span class="text-body-secondary small d-block">{{ __('Pengurus / Koordinator') }}</span>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                <i class="fa-solid fa-user-tie me-1"></i>{{ $investor->coordinator->name ?? 'Pusat' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-body-secondary small d-block">{{ __('Nomor Telepon / WhatsApp') }}</span>
                            <span class="fw-medium text-body">{{ $investor->phone ?: '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <span class="text-body-secondary small d-block">{{ __('Tanggal Bergabung') }}</span>
                            <span class="fw-medium text-body">{{ $investor->created_at ? $investor->created_at->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-body-secondary small d-block">{{ __('Keterangan / Catatan') }}</span>
                            <span class="fw-medium text-body">{{ $investor->description ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="text-body-secondary small d-block">{{ __('Akun Terhubung Sistem') }}</span>
                            @if($investor->user)
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                    <i class="fa-solid fa-user-check me-1"></i>{{ $investor->user->name }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Transaksi -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>{{ __('Riwayat Transaksi Investasi & Bagi Hasil') }}
            </h6>
            <span class="badge bg-light text-secondary border">Total: {{ $transactions->total() }} Catatan</span>
        </div>
        <div class="card-body p-0 border-top">
            <div class="table-responsive table-responsive-mobile">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase text-body-secondary">
                            <th class="ps-3 py-3">{{ __('Tanggal') }}</th>
                            <th class="py-3">{{ __('Tipe') }}</th>
                            <th class="py-3">{{ __('Kategori') }}</th>
                            <th class="py-3">{{ __('Keterangan') }}</th>
                            <th class="text-end pe-3 py-3">{{ __('Jumlah') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="ps-3 fw-medium">
                                    {{ $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    @if($transaction->type === 'income')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fa-solid fa-arrow-down me-1"></i>{{ __('Modal Masuk') }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="fa-solid fa-arrow-up me-1"></i>{{ __('Penarikan / Bagi Hasil') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        {{ $transaction->category ?: '-' }}
                                    </span>
                                </td>
                                <td class="text-body-secondary">{{ $transaction->description ?: '-' }}</td>
                                <td class="text-end pe-3 fw-bold font-monospace {{ $transaction->type === 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type === 'income' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-body-secondary">
                                    <i class="fa-solid fa-receipt fs-1 mb-2 d-block text-secondary opacity-50"></i>
                                    {{ __('Belum ada data riwayat transaksi untuk investor ini.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="p-3 border-top">
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
