@extends('layouts.app')

@section('title', __('Detail Akun Float: ') . $account->name)

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-credit-card me-1"></i>{{ strtoupper($account->account_type) }}
                </span>
                <span class="font-monospace text-muted small">{{ $account->code }}</span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ $account->name }}</h1>
            <p class="text-muted small mb-0">{{ __('Detail saldo dan seluruh riwayat mutasi kredit/debit akun float ini.') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('atk.float-accounts.index') }}" class="btn btn-sm btn-outline-secondary shadow-2xs">
                <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali') }}
            </a>
            <a href="{{ route('atk.float-accounts.create-transaction', $account) }}" class="btn btn-sm btn-success px-3 shadow-sm">
                <i class="fa-solid fa-plus-minus me-1"></i>{{ __('Catat Mutasi Saldo') }}
            </a>
            <a href="{{ route('atk.float-accounts.edit', $account) }}" class="btn btn-sm btn-outline-primary px-3 shadow-2xs">
                <i class="fa-solid fa-pen me-1"></i>{{ __('Ubah Akun') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Kartu Info Akun --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-info text-info"></i>
                        <span>{{ __('Informasi Rekening / Akun') }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 rounded-4 bg-light border border-light-subtle mb-3 text-center">
                        <div class="text-muted small fw-medium mb-1">{{ __('Saldo Saat Ini') }}</div>
                        <div class="fs-3 fw-bold text-gray-900">
                            Rp {{ number_format($account->current_balance, 0, ',', '.') }}
                        </div>
                    </div>

                    <table class="table table-borderless small align-middle mb-0">
                        <tbody>
                            <tr class="border-top">
                                <th class="text-muted fw-medium py-2" style="width: 40%;">{{ __('Kode Akun') }}</th>
                                <td class="py-2 fw-bold font-monospace text-dark">{{ $account->code }}</td>
                            </tr>
                            <tr class="border-top">
                                <th class="text-muted fw-medium py-2">{{ __('Nama Provider') }}</th>
                                <td class="py-2 fw-semibold text-dark">{{ $account->name }}</td>
                            </tr>
                            <tr class="border-top">
                                <th class="text-muted fw-medium py-2">{{ __('Nomor Rekening/HP') }}</th>
                                <td class="py-2 font-monospace text-dark">{{ $account->account_number ?: '-' }}</td>
                            </tr>
                            <tr class="border-top">
                                <th class="text-muted fw-medium py-2">{{ __('Tipe Akun') }}</th>
                                <td class="py-2 text-uppercase fw-semibold text-muted">{{ $account->account_type }}</td>
                            </tr>
                            <tr class="border-top">
                                <th class="text-muted fw-medium py-2">{{ __('Status') }}</th>
                                <td class="py-2">
                                    @if($account->status === 'active')
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-semibold">
                                            <i class="fa-solid fa-circle-check me-1"></i>{{ __('Aktif') }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small fw-semibold">
                                            {{ __('Nonaktif') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-top">
                                <th class="text-muted fw-medium py-2">{{ __('Keterangan') }}</th>
                                <td class="py-2 text-dark">{{ $account->description ?: '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tabel Riwayat Transaksi --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-primary"></i>
                        <span>{{ __('Riwayat Mutasi Saldo Float') }}</span>
                    </h6>
                    <span class="badge bg-light text-muted border px-2 py-1 small">
                        {{ $transactions->total() }} {{ __('Mutasi') }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="dataTable" width="100%" cellspacing="0">
                        <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                            <tr>
                                <th style="min-width: 120px;" class="py-3">{{ __('Waktu & Tanggal') }}</th>
                                <th style="min-width: 110px;" class="py-3 text-center">{{ __('Jenis Mutasi') }}</th>
                                <th style="min-width: 120px;" class="py-3 text-end">{{ __('Nominal') }}</th>
                                <th style="min-width: 120px;" class="py-3 text-end">{{ __('Saldo Awal') }}</th>
                                <th style="min-width: 120px;" class="py-3 text-end">{{ __('Saldo Akhir') }}</th>
                                <th style="min-width: 160px;" class="py-3">{{ __('Keterangan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    <div class="small fw-semibold text-gray-800">{{ $transaction->created_at->translatedFormat('d M Y') }}</div>
                                    <small class="text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ $transaction->created_at->format('H:i') }}</small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $type = strtolower($transaction->transaction_type ?? '');
                                        $isIncoming = in_array($type, ['deposit', 'topup', 'transfer', 'credit', 'in']);
                                    @endphp
                                    <span class="badge rounded-pill {{ $isIncoming ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} px-2 py-1 small fw-semibold">
                                        {{ ucfirst($transaction->transaction_type) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold {{ $isIncoming ? 'text-success' : 'text-danger' }}">
                                        {{ $isIncoming ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="text-muted small">Rp {{ number_format($transaction->balance_before, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-gray-900 fs-6">Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="small text-dark">{{ $transaction->description ?: '-' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fa-solid fa-receipt fs-1 text-secondary-subtle mb-3 d-block"></i>
                                        <h6 class="fw-bold mb-1">{{ __('Belum Ada Riwayat Mutasi') }}</h6>
                                        <p class="small mb-3">{{ __('Catat mutasi top up atau tarik saldo untuk akun ini.') }}</p>
                                        <a href="{{ route('atk.float-accounts.create-transaction', $account) }}" class="btn btn-sm btn-success">
                                            <i class="fa-solid fa-plus me-1"></i>{{ __('Catat Mutasi Saldo') }}
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
                        {{ __('Menampilkan') }} <span class="fw-semibold text-dark">{{ $transactions->firstItem() ?? 0 }}</span> - <span class="fw-semibold text-dark">{{ $transactions->lastItem() ?? 0 }}</span> {{ __('dari') }} <span class="fw-semibold text-dark">{{ $transactions->total() }}</span> {{ __('mutasi') }}
                    </div>
                    <div>
                        {{ $transactions->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
