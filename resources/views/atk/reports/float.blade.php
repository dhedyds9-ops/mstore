@extends('layouts.app')

@section('title', __('Laporan Akun Float ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman & Ekspor --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-credit-card me-1"></i>{{ __('Laporan Saldo Digital') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-calendar-days me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Laporan Akun Float Kasir') }}</h1>
            <p class="text-muted small mb-0">{{ __('Rekapitulasi pergerakan saldo rekening bank dan e-wallet modal transaksi.') }}</p>
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
                        <a class="dropdown-item py-2" href="{{ route('atk.reports.float.pdf', request()->query()) }}">
                            <i class="fa-solid fa-file-pdf text-danger me-2"></i>{{ __('Unduh Berkas PDF') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('atk.reports.float.excel', request()->query()) }}">
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
            <form action="{{ route('atk.reports.float') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted small">{{ __('Akun Float') }}</span>
                        <select name="account_id" class="form-select bg-light" required>
                            <option value="">{{ __('Pilih Rekening / Akun') }}</option>
                            @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }} ({{ $acc->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted small">{{ __('Dari') }}</span>
                        <input type="date" name="start_date" class="form-control bg-light" value="{{ request('start_date') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted small">{{ __('Sampai') }}</span>
                        <input type="date" name="end_date" class="form-control bg-light" value="{{ request('end_date') }}">
                    </div>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-sm btn-dark px-3 flex-fill flex-md-grow-0">
                        <i class="fa-solid fa-filter me-1"></i>{{ __('Tampilkan') }}
                    </button>
                    @if(request('account_id') || request('start_date') || request('end_date'))
                        <a href="{{ route('atk.reports.float') }}" class="btn btn-sm btn-outline-secondary px-2" title="{{ __('Reset Filter') }}">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($selectedAccount)
    {{-- 4 Stat Cards Saldo Float --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-4">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Saldo Awal Periode') }}</div>
                        <div class="fs-5 fw-bold text-gray-900">Rp {{ number_format($startBalance, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success-subtle text-success fs-4">
                        <i class="fa-solid fa-circle-arrow-down"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Masuk (+)') }}</div>
                        <div class="fs-5 fw-bold text-success">+Rp {{ number_format($totalIn, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-danger-subtle text-danger fs-4">
                        <i class="fa-solid fa-circle-arrow-up"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Keluar (-)') }}</div>
                        <div class="fs-5 fw-bold text-danger">-Rp {{ number_format($totalOut, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-info">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info-subtle text-info fs-4">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Saldo Akhir Periode') }}</div>
                        <div class="fs-5 fw-bold text-gray-900">Rp {{ number_format($endBalance, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Tabel Transaksi Float --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary"></i>
                <span>{{ __('Detail Transaksi Saldo: ') }} {{ $selectedAccount->name }}</span>
            </h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">
                {{ count($transactions) }} {{ __('Catatan Mutasi') }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="width: 50px;" class="text-center py-3">#</th>
                        <th style="min-width: 140px;" class="py-3">{{ __('Waktu & Tanggal') }}</th>
                        <th style="min-width: 120px;" class="py-3">{{ __('Jenis Mutasi') }}</th>
                        <th style="min-width: 220px;" class="py-3">{{ __('Keterangan / Deskripsi') }}</th>
                        <th style="min-width: 130px;" class="py-3 text-end">{{ __('Masuk (Debit)') }}</th>
                        <th style="min-width: 130px;" class="py-3 text-end">{{ __('Keluar (Kredit)') }}</th>
                        <th style="min-width: 140px;" class="py-3 text-end">{{ __('Saldo Berjalan') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($transactions as $index => $transaction)
                        @php
                            $isIncoming = in_array($transaction->transaction_type, ['deposit', 'transfer_in', 'adjustment', 'in']);
                        @endphp
                        <tr>
                            <td class="text-center text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="small fw-semibold text-gray-800">{{ $transaction->created_at->translatedFormat('d M Y') }}</div>
                                <small class="text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ $transaction->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 small">{{ ucfirst($transaction->transaction_type) }}</span>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $transaction->description ?: '-' }}</div>
                            </td>
                            <td class="text-end">
                                @if($isIncoming)
                                    <span class="fw-bold text-success">+Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$isIncoming)
                                    <span class="fw-bold text-danger">-Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-gray-900 fs-6">Rp {{ number_format($transaction->running_balance, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-credit-card fs-1 text-secondary-subtle mb-3 d-block"></i>
                                <h6 class="fw-bold mb-1">{{ __('Tidak Ada Data Mutasi Float') }}</h6>
                                <p class="small mb-0">{{ __('Belum ada catatan mutasi pada rentang tanggal yang dipilih.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm rounded-4 text-center py-5">
        <div class="card-body">
            <i class="fa-solid fa-credit-card fs-1 text-secondary-subtle mb-3 d-block"></i>
            <h5 class="fw-bold text-gray-800 mb-1">{{ __('Silakan Pilih Akun Float') }}</h5>
            <p class="text-muted small mb-0">{{ __('Pilih salah satu rekening bank atau e-wallet pada form filter di atas untuk melihat rincian laporan pergerakan saldo.') }}</p>
        </div>
    </div>
    @endif
</div>
@endsection
