@extends('layouts.app')

@section('title', __('Barang Masuk & Mutasi Stok ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman & Aksi Cepat --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1 fw-bold text-gray-800 d-flex align-items-center gap-2">
                <i class="fa-solid fa-boxes-packing text-primary"></i>
                <span>{{ __('Barang Masuk & Mutasi Stok ATK') }}</span>
            </h1>
            <p class="text-muted small mb-0">{{ __('Riwayat penerimaan stok masuk dari supplier, penjualan kasir, dan penyesuaian inventaris.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-excel text-success me-1"></i>
                    <span>{{ __('Ekspor Data') }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('atk.stock-movements.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}">
                            <i class="fa-solid fa-file-excel text-success me-2"></i>{{ __('Ekspor ke Excel (.xlsx)') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('atk.stock-movements.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
                            <i class="fa-solid fa-file-csv text-info me-2"></i>{{ __('Ekspor ke CSV (.csv)') }}
                        </a>
                    </li>
                </ul>
            </div>

            <a href="{{ route('atk.products.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-boxes-stacked me-1"></i>
                <span>{{ __('Katalog Produk') }}</span>
            </a>

            <a href="{{ route('atk.stock-movements.create') }}" class="btn btn-sm btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-1"></i>
                <span>{{ __('Catat Barang Masuk') }}</span>
            </a>
        </div>
    </div>

    {{-- Mini Metric Stat Cards (Statistik Bulan Ini) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success-subtle text-success fs-5">
                        <i class="fa-solid fa-arrow-down"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Barang Masuk (Bulan Ini)') }}</div>
                        <div class="fs-5 fw-bold text-success">+{{ number_format($stats['total_in_month'] ?? 0, 0, ',', '.') }} <span class="small fw-normal text-muted">item</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-primary">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-5">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Belanja Stok (Bulan Ini)') }}</div>
                        <div class="fs-5 fw-bold text-primary">Rp {{ number_format($stats['total_cost_month'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-info">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info-subtle text-info fs-5">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Transaksi Restok Masuk') }}</div>
                        <div class="fs-5 fw-bold text-gray-800">{{ number_format($stats['total_in_count'] ?? 0, 0, ',', '.') }} <span class="small fw-normal text-muted">kali</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-warning">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning-subtle text-warning-emphasis fs-5">
                        <i class="fa-solid fa-arrow-up"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Barang Keluar (Bulan Ini)') }}</div>
                        <div class="fs-5 fw-bold text-warning-emphasis">-{{ number_format($stats['total_out_month'] ?? 0, 0, ',', '.') }} <span class="small fw-normal text-muted">item</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card Tabel & Filter --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        {{-- Filter Bar --}}
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <form method="GET" action="{{ route('atk.stock-movements.index') }}" id="filterForm">
                <div class="row g-2 align-items-center atk-filter-bar">
                    {{-- Pencarian --}}
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted ps-3">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 border-end-0" placeholder="{{ __('Cari produk, faktur, supplier...') }}" value="{{ request('search') }}">
                            @if(request('search'))
                                <a href="{{ route('atk.stock-movements.index', array_merge(request()->except(['search', 'page']))) }}" class="btn btn-outline-secondary border-start-0 border-end-0 text-muted px-2" title="{{ __('Hapus Pencarian') }}">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            @endif
                            <button type="submit" class="btn btn-primary px-3" title="{{ __('Cari') }}">
                                <span class="fw-semibold">{{ __('Cari') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tipe Mutasi --}}
                    <div class="col-6 col-sm-4 col-md-2 col-lg-2">
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">{{ __('Semua Tipe Mutasi') }}</option>
                            <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>{{ __('Barang Masuk (Restok)') }}</option>
                            <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>{{ __('Barang Keluar (POS)') }}</option>
                            <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>{{ __('Penyesuaian Stok') }}</option>
                        </select>
                    </div>

                    {{-- Produk --}}
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <select name="product_id" class="form-select" onchange="this.form.submit()">
                            <option value="">{{ __('Semua Produk') }}</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ Str::limit($p->name, 25) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Periode Tanggal Dari - Sampai --}}
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted small py-1 border-end-0">{{ __('Dari') }}</span>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" onchange="this.form.submit()">
                        </div>
                    </div>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted small py-1 border-end-0">{{ __('S/d') }}</span>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" onchange="this.form.submit()">
                        </div>
                    </div>

                    {{-- Reset Filter Button --}}
                    <div class="col-12 col-md-auto ms-auto d-flex justify-content-end">
                        @if(request('search') || request('type') || request('product_id') || request('supplier_id') || request('start_date') || request('end_date'))
                            <a href="{{ route('atk.stock-movements.index') }}" class="btn btn-outline-danger d-inline-flex align-items-center gap-1 text-nowrap" style="height: 38px;" title="{{ __('Reset Filter') }}">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>{{ __('Reset') }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Table Content --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="min-width: 140px;" class="py-3 ps-4">{{ __('Tanggal & Waktu') }}</th>
                        <th style="min-width: 220px;" class="py-3">{{ __('Produk ATK') }}</th>
                        <th style="min-width: 130px;" class="py-3 text-center">{{ __('Tipe Mutasi') }}</th>
                        <th style="min-width: 110px;" class="py-3 text-center">{{ __('Perubahan (Qty)') }}</th>
                        <th style="min-width: 140px;" class="py-3 text-end">{{ __('Harga Satuan / Total') }}</th>
                        <th style="min-width: 140px;" class="py-3 text-center">{{ __('Stok Fisik') }}</th>
                        <th style="min-width: 150px;" class="py-3">{{ __('Supplier / Faktur') }}</th>
                        <th style="min-width: 180px;" class="py-3 pe-4">{{ __('Petugas & Catatan') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($movements as $m)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold text-dark">{{ $m->created_at ? $m->created_at->format('d/m/Y') : '-' }}</div>
                            <small class="text-muted font-monospace">{{ $m->created_at ? $m->created_at->format('H:i') : '' }} WIB</small>
                        </td>
                        <td>
                            @if($m->product)
                                <div class="fw-bold text-gray-900">{{ $m->product->name }}</div>
                                <div class="small text-muted font-monospace">{{ $m->product->code }}</div>
                            @else
                                <span class="text-muted fst-italic">{{ __('(Produk telah dihapus)') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($m->type === 'in')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 rounded-pill fs-7 fw-semibold">
                                    <i class="fa-solid fa-arrow-down me-1"></i>{{ __('Barang Masuk') }}
                                </span>
                            @elseif($m->type === 'out')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5 rounded-pill fs-7 fw-semibold">
                                    <i class="fa-solid fa-arrow-up me-1"></i>{{ __('Barang Keluar') }}
                                </span>
                            @else
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2.5 py-1.5 rounded-pill fs-7 fw-semibold">
                                    <i class="fa-solid fa-sliders me-1"></i>{{ __('Penyesuaian') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($m->type === 'in')
                                <span class="fw-bold text-success fs-6">+{{ number_format($m->quantity, 0, ',', '.') }}</span>
                            @elseif($m->type === 'out')
                                <span class="fw-bold text-danger fs-6">-{{ number_format($m->quantity, 0, ',', '.') }}</span>
                            @else
                                <span class="fw-bold text-primary fs-6">{{ number_format($m->quantity, 0, ',', '.') }}</span>
                            @endif
                            <small class="text-muted d-block" style="font-size: 0.75rem;">{{ $m->product?->unit ?: 'Pcs' }}</small>
                        </td>
                        <td class="text-end">
                            @if($m->total_cost > 0)
                                <div class="fw-bold text-dark">Rp {{ number_format($m->total_cost, 0, ',', '.') }}</div>
                                <small class="text-muted" style="font-size: 0.75rem;">@ Rp {{ number_format($m->unit_price, 0, ',', '.') }}</small>
                            @elseif($m->unit_price > 0)
                                <div class="text-muted small">@ Rp {{ number_format($m->unit_price, 0, ',', '.') }}</div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1.5 px-2 py-1 bg-light rounded-3 border small">
                                <span class="text-muted">{{ $m->balance_before }}</span>
                                <i class="fa-solid fa-arrow-right text-muted" style="font-size: 0.65rem;"></i>
                                <span class="fw-bold text-dark">{{ $m->balance_after }}</span>
                            </div>
                        </td>
                        <td>
                            @if($m->supplier)
                                <div class="fw-semibold text-dark"><i class="fa-solid fa-truck-field me-1 text-secondary"></i>{{ $m->supplier->name }}</div>
                            @endif
                            @if($m->invoice_number)
                                <div class="small text-muted font-monospace"><i class="fa-solid fa-file-invoice me-1"></i>{{ $m->invoice_number }}</div>
                            @endif
                            @if(!$m->supplier && !$m->invoice_number)
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="pe-4">
                            <div class="text-muted small">
                                <i class="fa-solid fa-user me-1 text-secondary"></i>{{ $m->user?->name ?: 'Sistem' }}
                            </div>
                            @if($m->notes)
                                <div class="text-dark small text-truncate" style="max-width: 220px;" title="{{ $m->notes }}">
                                    {{ $m->notes }}
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-boxes-packing fs-1 text-secondary-subtle mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark mb-1">{{ __('Belum Ada Riwayat Mutasi / Barang Masuk') }}</h6>
                                <p class="small text-muted mb-3">{{ __('Catat barang masuk dari supplier atau pembelian grosir baru untuk menambah stok.') }}</p>
                                <a href="{{ route('atk.stock-movements.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-plus me-1"></i>{{ __('Catat Barang Masuk Sekarang') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($movements->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
                <small class="text-muted">
                    {{ __('Menampilkan') }} <span class="fw-semibold">{{ $movements->firstItem() ?? 0 }}</span> - <span class="fw-semibold">{{ $movements->lastItem() ?? 0 }}</span> {{ __('dari') }} <span class="fw-semibold">{{ $movements->total() }}</span> {{ __('riwayat') }}
                </small>
                <div>
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
