@extends('layouts.app')

@section('title', __('Rincian Shift Kasir - ') . $register->name)

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('atk.cash-registers.index') }}" class="text-muted text-decoration-none small">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Daftar Shift Kasir') }}
                </a>
                <span class="text-muted small">/</span>
                <span class="badge {{ $register->status === 'open' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid {{ $register->status === 'open' ? 'fa-circle-dot' : 'fa-lock' }} me-1"></i>
                    {{ $register->status === 'open' ? __('Shift Sedang Aktif') : __('Shift Ditutup') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ $register->name }}</h1>
            <p class="text-muted small mb-0">
                <i class="fa-regular fa-user me-1"></i>{{ __('Kasir:') }} <strong>{{ $register->user->name ?? '-' }}</strong> &bull;
                <i class="fa-regular fa-clock ms-2 me-1"></i>{{ __('Dibuka:') }} {{ $register->opened_at ? $register->opened_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                @if($register->closed_at)
                    &bull; <i class="fa-solid fa-door-closed ms-2 me-1"></i>{{ __('Ditutup:') }} {{ $register->closed_at->translatedFormat('d M Y, H:i') }} WIB
                @endif
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm shadow-2xs" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i>{{ __('Cetak Laporan') }}
            </button>
            @if($register->status === 'open')
                <a href="{{ route('atk.pos') }}" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="fa-solid fa-cash-register me-1"></i>{{ __('Buka POS') }}
                </a>
                <button type="button" class="btn btn-danger btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
                    <i class="fa-solid fa-door-closed me-1"></i>{{ __('Tutup Shift Ini') }}
                </button>
            @endif
        </div>
    </div>

    {{-- Ringkasan Keuangan Shift --}}
    <div class="row g-3 mb-4">
        {{-- Saldo Awal --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-medium">{{ __('Saldo Awal Laci') }}</span>
                        <div class="rounded-3 p-2 bg-primary-subtle text-primary">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold text-gray-900">
                        Rp {{ number_format($register->opening_balance, 0, ',', '.') }}
                    </div>
                    <small class="text-muted">{{ __('Modal kas saat buka shift') }}</small>
                </div>
            </div>
        </div>

        {{-- Penjualan Tunai --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-medium">{{ __('Penjualan Tunai') }}</span>
                        <div class="rounded-3 p-2 bg-success-subtle text-success">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold text-success">
                        Rp {{ number_format($totalCashSales, 0, ',', '.') }}
                    </div>
                    <small class="text-muted">{{ __('Masuk ke laci kasir') }}</small>
                </div>
            </div>
        </div>

        {{-- Penjualan Non-Tunai --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-medium">{{ __('Non-Tunai (QRIS/Bank)') }}</span>
                        <div class="rounded-3 p-2 bg-info-subtle text-info">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold text-info">
                        Rp {{ number_format($totalNonCashSales, 0, ',', '.') }}
                    </div>
                    <small class="text-muted">{{ __('Masuk ke rekening/float') }}</small>
                </div>
            </div>
        </div>

        {{-- Saldo Kas Akhir --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 {{ $register->status === 'open' ? 'border-primary' : 'border-success' }}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-medium">{{ $register->status === 'open' ? __('Saldo Kas Saat Ini') : __('Saldo Kas Penutupan') }}</span>
                        <div class="rounded-3 p-2 {{ $register->status === 'open' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success' }}">
                            <i class="fa-solid fa-cash-register"></i>
                        </div>
                    </div>
                    <div class="fs-4 fw-bold text-gray-900">
                        Rp {{ number_format($register->closing_balance, 0, ',', '.') }}
                    </div>
                    <small class="text-muted">{{ $register->status === 'open' ? __('Tercatat di sistem') : __('Fisik saat tutup shift') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs: Riwayat Transaksi Shift & Mutasi Kas Shift --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <ul class="nav nav-pills gap-2" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-3 py-2 small fw-semibold" id="trans-tab" data-bs-toggle="tab" data-bs-target="#trans-pane" type="button">
                        <i class="fa-solid fa-receipt me-1"></i>{{ __('Transaksi Shift') }} ({{ $transactionCount }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-3 py-2 small fw-semibold" id="mutasi-tab" data-bs-toggle="tab" data-bs-target="#mutasi-pane" type="button">
                        <i class="fa-solid fa-arrows-rotate me-1"></i>{{ __('Mutasi Kas Shift') }} ({{ $register->movements->count() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content">
                {{-- Pane Transaksi --}}
                <div class="tab-pane fade show active" id="trans-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                                <tr>
                                    <th class="py-3 ps-3">{{ __('Waktu') }}</th>
                                    <th class="py-3">{{ __('No. Transaksi') }}</th>
                                    <th class="py-3">{{ __('Rincian Produk') }}</th>
                                    <th class="py-3 text-end">{{ __('Total') }}</th>
                                    <th class="py-3 text-center">{{ __('Metode') }}</th>
                                    <th class="py-3 text-center pe-3">{{ __('Aksi') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($shiftTransactions as $trx)
                                <tr>
                                    <td class="ps-3">
                                        <div class="small fw-semibold text-dark">{{ $trx->created_at->format('H:i') }} WIB</div>
                                        <small class="text-muted">{{ $trx->created_at->translatedFormat('d M Y') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('atk.transactions.show', $trx) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                            {{ $trx->transaction_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="small fw-medium text-dark text-truncate" style="max-width: 250px;">
                                            {{ $trx->product_names['display'] }}
                                        </div>
                                        <small class="text-muted">{{ $trx->product_names['total'] }} item</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-gray-900">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1 small">
                                            {{ strtoupper($trx->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <a href="{{ route('atk.transactions.receipt', $trx) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2" title="{{ __('Cetak Struk') }}">
                                            <i class="fa-solid fa-receipt"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">
                                        <i class="fa-solid fa-receipt display-6 d-block mb-2 text-secondary-subtle"></i>
                                        {{ __('Belum ada transaksi pada shift ini.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pane Mutasi --}}
                <div class="tab-pane fade" id="mutasi-pane" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                                <tr>
                                    <th class="py-3 ps-3">{{ __('Waktu') }}</th>
                                    <th class="py-3">{{ __('Tipe Mutasi') }}</th>
                                    <th class="py-3">{{ __('Keterangan') }}</th>
                                    <th class="py-3 text-end">{{ __('Jumlah') }}</th>
                                    <th class="py-3 text-end pe-3">{{ __('Saldo Akhir') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($register->movements as $m)
                                <tr>
                                    <td class="ps-3">
                                        <span class="small font-monospace">{{ $m->created_at->format('H:i') }} WIB</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $m->direction === 'in' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} px-2 py-1 rounded-pill small">
                                            <i class="fa-solid {{ $m->direction === 'in' ? 'fa-arrow-down' : 'fa-arrow-up' }} me-1"></i>
                                            {{ strtoupper($m->movement_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-gray-800">{{ $m->description ?? '-' }}</div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold {{ $m->direction === 'in' ? 'text-success' : 'text-danger' }}">
                                            {{ $m->direction === 'in' ? '+' : '-' }} Rp {{ number_format($m->amount, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <span class="small fw-semibold text-gray-900 font-monospace">Rp {{ number_format($m->balance_after, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        {{ __('Belum ada mutasi tercatat pada shift ini.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tutup Shift --}}
@if($register->status === 'open')
<div class="modal fade" id="closeShiftModal" tabindex="-1" aria-labelledby="closeShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold" id="closeShiftModalLabel">
                    <i class="fa-solid fa-door-closed me-2"></i>{{ __('Tutup Shift Kasir') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('atk.cash-registers.close', $register) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-light border rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">{{ __('Saldo Awal:') }}</span>
                            <strong class="small">Rp {{ number_format($register->opening_balance, 0, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">{{ __('Total Penjualan Tunai:') }}</span>
                            <strong class="small text-success">+ Rp {{ number_format($totalCashSales, 0, ',', '.') }}</strong>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold small">{{ __('Saldo Sistem yang Diharapkan:') }}</span>
                            <strong class="text-primary fs-6">Rp {{ number_format($register->closing_balance, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="closing_balance" class="form-label fw-bold small text-gray-800">
                            {{ __('Hitung Kas Fisik Aktual di Laci (Rp)') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" name="closing_balance" id="closing_balance" class="form-control form-control-lg fw-bold text-gray-900" placeholder="0" value="{{ $register->closing_balance }}" required min="0" step="100">
                        </div>
                        <small class="text-muted">{{ __('Masukkan total uang tunai riil yang dihitung di laci sebelum shift ditutup.') }}</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold">
                        <i class="fa-solid fa-check me-1"></i>{{ __('Konfirmasi Tutup Shift') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
