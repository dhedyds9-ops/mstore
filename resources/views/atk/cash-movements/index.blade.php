@extends('layouts.app')

@section('title', __('Mutasi Kas Utama ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-wallet me-1"></i>{{ __('Kas Laci Fisik') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-calendar-days me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Mutasi Kas Utama (Laci Toko)') }}</h1>
            <p class="text-muted small mb-0">{{ __('Buku arus kas fisik toko: pencatatan kas masuk penjualan, pengeluaran kasir, setor kas, dan koreksi saldo.') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('atk.dashboard') }}" class="btn btn-sm btn-outline-secondary shadow-2xs">
                <i class="fa-solid fa-gauge me-1"></i>{{ __('Dasbor') }}
            </a>
            <a href="{{ route('atk.cash-movements.create') }}" class="btn btn-sm btn-warning text-dark px-3 shadow-sm">
                <i class="fa-solid fa-sliders me-1"></i>{{ __('Koreksi / Penyesuaian Kas') }}
            </a>
        </div>
    </div>

    {{-- Ringkasan Saldo Mutasi --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-4">
                        <i class="fa-solid fa-scale-balanced"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Saldo Awal Periode') }}</div>
                        <div class="fs-5 fw-bold text-gray-900">Rp {{ number_format($initialBalance, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success-subtle text-success fs-4">
                        <i class="fa-solid fa-circle-arrow-down"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Kas Masuk (+)') }}</div>
                        <div class="fs-5 fw-bold text-success">+Rp {{ number_format($movements->where('direction', 'in')->sum('amount'), 0, ',', '.') }}</div>
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
                        <div class="text-muted small fw-medium">{{ __('Total Kas Keluar (-)') }}</div>
                        <div class="fs-5 fw-bold text-danger">-Rp {{ number_format($movements->where('direction', 'out')->sum('amount'), 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Tabel Mutasi --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <form action="{{ route('atk.cash-movements.index') }}" method="GET" class="row g-2 align-items-center">
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

                <div class="col-6 col-md-2">
                    <select name="movement_type" class="form-select form-select-sm bg-light">
                        <option value="">{{ __('Semua Jenis') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('movement_type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="created_by" class="form-select form-select-sm bg-light">
                        <option value="">{{ __('Semua Kasir') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('created_by') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-sm btn-dark px-3 flex-fill flex-md-grow-0" title="{{ __('Terapkan Filter') }}">
                        <i class="fa-solid fa-filter me-1"></i>{{ __('Filter') }}
                    </button>
                    @if(request('start_date') || request('end_date') || request('movement_type') || request('created_by'))
                        <a href="{{ route('atk.cash-movements.index') }}" class="btn btn-sm btn-outline-secondary px-2" title="{{ __('Reset Filter') }}">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="min-width: 130px;" class="py-3">{{ __('Waktu & Tanggal') }}</th>
                        <th style="min-width: 120px;" class="py-3">{{ __('Jenis Mutasi') }}</th>
                        <th style="min-width: 200px;" class="py-3">{{ __('Deskripsi / Transaksi') }}</th>
                        <th style="min-width: 130px;" class="py-3 text-end">{{ __('Kas Masuk') }}</th>
                        <th style="min-width: 130px;" class="py-3 text-end">{{ __('Kas Keluar') }}</th>
                        <th style="min-width: 140px;" class="py-3 text-end">{{ __('Saldo Setelahnya') }}</th>
                        <th style="min-width: 120px;" class="py-3">{{ __('Petugas Kasir') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($movements as $move)
                    <tr>
                        <td>
                            <div class="fw-semibold text-gray-800 small">{{ $move->created_at->translatedFormat('d M Y') }}</div>
                            <small class="text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ $move->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            @php
                                $typeBadge = 'bg-primary-subtle text-primary border border-primary-subtle';
                                if($move->movement_type === 'sale') {
                                    $typeBadge = 'bg-success-subtle text-success border border-success-subtle';
                                } elseif($move->movement_type === 'expense') {
                                    $typeBadge = 'bg-danger-subtle text-danger border border-danger-subtle';
                                } elseif($move->movement_type === 'adjustment') {
                                    $typeBadge = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                }
                            @endphp
                            <span class="badge rounded-pill {{ $typeBadge }} px-2 py-1 small fw-medium">
                                {{ ucfirst($move->movement_type) }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-medium text-dark">{{ $move->description ?? '-' }}</div>
                            @if($move->atkTransaction && $move->atkTransaction->items->count() > 0)
                                <small class="text-muted d-block mt-1">
                                    <i class="fa-solid fa-basket-shopping me-1"></i>
                                    @foreach($move->atkTransaction->items->take(2) as $item)
                                        {{ $item->product_name ?? $item->atk_product->name }} ({{ $item->quantity }}){{ !$loop->last ? ',' : '' }}
                                    @endforeach
                                    @if($move->atkTransaction->items->count() > 2)
                                        <span class="text-primary">+{{ $move->atkTransaction->items->count() - 2 }} lainnya</span>
                                    @endif
                                </small>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($move->direction === 'in')
                                <span class="fw-bold text-success">+Rp {{ number_format($move->amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($move->direction === 'out')
                                <span class="fw-bold text-danger">-Rp {{ number_format($move->amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-gray-900 fs-6">Rp {{ number_format($move->balance_after, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="small text-gray-700">
                                <i class="fa-regular fa-user me-1 text-muted"></i>{{ $move->creator->name ?? '-' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-wallet fs-1 text-secondary-subtle mb-3 d-block"></i>
                                <h6 class="fw-bold mb-1">{{ __('Belum Ada Riwayat Mutasi Kas') }}</h6>
                                <p class="small mb-3">{{ __('Mutasi akan tercatat otomatis setiap kali ada transaksi POS, pengeluaran kasir, atau koreksi saldo.') }}</p>
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
                {{ __('Menampilkan') }} <span class="fw-semibold text-dark">{{ $movements->firstItem() ?? 0 }}</span> - <span class="fw-semibold text-dark">{{ $movements->lastItem() ?? 0 }}</span> {{ __('dari') }} <span class="fw-semibold text-dark">{{ $movements->total() }}</span> {{ __('mutasi kas') }}
            </div>
            <div>
                {{ $movements->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
