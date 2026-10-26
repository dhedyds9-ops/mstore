@extends('layouts.app')

@section('title', __('Stok Bahan & Operasional Jasa Cuci'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman & Aksi Cepat --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1 fw-bold text-gray-800 d-flex align-items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-primary"></i>
                <span>{{ __('Stok Bahan & Operasional Cuci') }}</span>
            </h1>
            <p class="text-muted small mb-0">{{ __('Kelola bahan operasional, shampo, semir ban, chemical, stok minimum, serta impor/ekspor data.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#helpBoxContent" title="{{ __('Bantuan / Petunjuk') }}">
                <i class="fa-solid fa-circle-question me-1"></i><span class="d-none d-sm-inline">{{ __('Petunjuk') }}</span>
            </button>

            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-excel text-success me-1"></i>
                    <span>{{ __('Impor / Ekspor') }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#importWashStockModal">
                            <i class="fa-solid fa-file-import text-primary me-2"></i>{{ __('Impor Stok Bahan (.xlsx / .csv)') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('wash.stock.template') }}">
                            <i class="fa-solid fa-download text-success me-2"></i>{{ __('Unduh Template Excel') }}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('wash.stock.export') }}">
                            <i class="fa-solid fa-file-export text-secondary me-2"></i>{{ __('Ekspor Semua Data (.xlsx)') }}
                        </a>
                    </li>
                </ul>
            </div>

            <a href="{{ route('wash.stock.create') }}" class="btn btn-sm btn-primary shadow-sm" title="{{ __('Tambah Item Baru') }}">
                <i class="fa-solid fa-plus me-1"></i>
                <span>{{ __('Tambah Item') }}</span>
            </a>
        </div>
    </div>

    {{-- Collapsible Help Box --}}
    <div class="collapse mb-4" id="helpBoxContent">
        <div class="card border-0 shadow-sm border-start border-4 border-info">
            <div class="card-body py-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="text-info fs-4 mt-1"><i class="fa-solid fa-circle-info"></i></div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1 text-gray-800">{{ __('Petunjuk Stok Bahan & Operasional Cuci') }}</h6>
                        <ul class="mb-0 small text-muted ps-3">
                            <li><strong>{{ __('Tambah & Catat Stok:') }}</strong> Gunakan "Tambah Item" untuk mendaftarkan bahan baru, atau tombol (+) hijau pada baris item untuk mencatat transaksi stok masuk (pembelian baru).</li>
                            <li><strong>{{ __('Impor & Ekspor:') }}</strong> Gunakan menu "Impor / Ekspor" untuk mengunduh template resmi dan mengunggah stok dalam jumlah banyak sekaligus via Excel (.xlsx) atau CSV.</li>
                            <li><strong>{{ __('Batas Stok Minimum:') }}</strong> Tetapkan batas minimum stok pada setiap bahan agar sistem otomatis memberi peringatan warna kuning saat persediaan mulai menipis.</li>
                            <li><strong>{{ __('Filter Instan:') }}</strong> Klik kartu indikator (Stok Menipis / Stok Habis) untuk melihat bahan yang butuh segera dibeli kembali.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mini Metric Stat Cards (Interaktif Filter) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('wash.stock.index', array_merge(request()->except(['stock_status', 'page']))) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 {{ !request('stock_status') ? 'border-start border-4 border-primary' : '' }}">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-5">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ __('Total Item Bahan') }}</div>
                            <div class="fs-5 fw-bold text-gray-800">{{ number_format($stats['total_items'] ?? $items->total(), 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('wash.stock.index', array_merge(request()->except(['page']), ['stock_status' => 'in'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 {{ request('stock_status') === 'in' ? 'border-start border-4 border-success' : '' }}">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 bg-success-subtle text-success fs-5">
                            <i class="fa-solid fa-cubes"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ __('Stok Aman (> Min)') }}</div>
                            <div class="fs-5 fw-bold text-success">{{ number_format($stats['total_stock'] ?? 0, 0, ',', '.') }} <span class="small fw-normal text-muted">unit</span></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('wash.stock.index', array_merge(request()->except(['page']), ['stock_status' => 'low'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 {{ request('stock_status') === 'low' ? 'border-start border-4 border-warning' : '' }}">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 bg-warning-subtle text-warning-emphasis fs-5">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ __('Stok Menipis (<= Min)') }}</div>
                            <div class="fs-5 fw-bold text-warning-emphasis">{{ number_format($stats['low_stock'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('wash.stock.index', array_merge(request()->except(['page']), ['stock_status' => 'out'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 {{ request('stock_status') === 'out' ? 'border-start border-4 border-danger' : '' }}">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 bg-danger-subtle text-danger fs-5">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ __('Stok Habis (0)') }}</div>
                            <div class="fs-5 fw-bold text-danger">{{ number_format($stats['out_of_stock'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Card Tabel Stok & Filter --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        {{-- Filter Bar --}}
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <form method="GET" action="{{ route('wash.stock.index') }}" id="filterForm">
                <div class="row g-2 align-items-center">
                    {{-- Pencarian --}}
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" id="stockSearch" class="form-control bg-light border-start-0 border-end-0" placeholder="{{ __('Cari nama bahan, kategori...') }}" value="{{ request('search') }}">
                            @if(request('search'))
                                <a href="{{ route('wash.stock.index', array_merge(request()->except(['search', 'page']))) }}" class="btn btn-light border-top border-bottom border-0 text-muted" title="{{ __('Hapus Pencarian') }}">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Kategori --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small"><i class="fa-solid fa-layer-group"></i></span>
                            <select name="category" class="form-select bg-light" onchange="this.form.submit()">
                                <option value="">{{ __('Semua Kategori') }}</option>
                                @foreach($categories as $opt)
                                    <option value="{{ $opt }}" {{ request('category') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Status Stok --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small"><i class="fa-solid fa-boxes-stacked"></i></span>
                            <select name="stock_status" class="form-select bg-light" onchange="this.form.submit()">
                                <option value="">{{ __('Semua Status Stok') }}</option>
                                <option value="in" {{ request('stock_status') === 'in' ? 'selected' : '' }}>{{ __('Stok Aman (> Min)') }}</option>
                                <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>{{ __('Stok Menipis (<= Min)') }}</option>
                                <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>{{ __('Stok Habis (0)') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Status Aktif / Nonaktif --}}
                    <div class="col-6 col-md-2 col-lg-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small"><i class="fa-solid fa-toggle-on"></i></span>
                            <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                                <option value="">{{ __('Semua Status') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Nonaktif') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Urutkan / Sort --}}
                    <div class="col-6 col-md-2 col-lg-1">
                        <select name="sort" class="form-select bg-light" onchange="this.form.submit()" title="{{ __('Urutkan data') }}">
                            <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>{{ __('Terbaru') }}</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>{{ __('Nama A-Z') }}</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>{{ __('Nama Z-A') }}</option>
                            <option value="stock_asc" {{ request('sort') === 'stock_asc' ? 'selected' : '' }}>{{ __('Stok Min') }}</option>
                            <option value="stock_desc" {{ request('sort') === 'stock_desc' ? 'selected' : '' }}>{{ __('Stok Max') }}</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('Harga Min') }}</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('Harga Max') }}</option>
                        </select>
                    </div>

                    {{-- Per Page & Action --}}
                    <div class="col-12 col-md-4 col-lg-2 d-flex gap-2 justify-content-md-end">
                        <select name="per_page" id="per_page" class="form-select bg-light" onchange="this.form.submit()" title="{{ __('Jumlah per halaman') }}" style="max-width: 90px;">
                            @php $pp = request('per_page', '10'); @endphp
                            <option value="10" {{ $pp == '10' ? 'selected' : '' }}>10 / hal</option>
                            <option value="25" {{ $pp == '25' ? 'selected' : '' }}>25 / hal</option>
                            <option value="50" {{ $pp == '50' ? 'selected' : '' }}>50 / hal</option>
                            <option value="100" {{ $pp == '100' ? 'selected' : '' }}>100 / hal</option>
                            <option value="all" {{ $pp == 'all' ? 'selected' : '' }}>Semua</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm px-3" title="{{ __('Terapkan Filter') }}">
                            <i class="fa-solid fa-filter me-1"></i>{{ __('Filter') }}
                        </button>
                        @if(request('search') || request('category') || request('stock_status') || request('status') || (request('sort') && request('sort') !== 'latest') || (request('per_page') && request('per_page') != '10'))
                            <a href="{{ route('wash.stock.index') }}" class="btn btn-outline-secondary btn-sm px-2" title="{{ __('Reset Semua Filter') }}">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Filter Aktif Indicators --}}
                @if(request('search') || request('category') || request('stock_status') || request('status') || (request('sort') && request('sort') !== 'latest'))
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-2 border-top">
                        <span class="small text-muted me-1"><i class="fa-solid fa-tags me-1"></i>{{ __('Filter Aktif:') }}</span>
                        @if(request('search'))
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                                {{ __('Cari:') }} "{{ request('search') }}"
                                <a href="{{ route('wash.stock.index', array_merge(request()->except(['search', 'page']))) }}" class="text-danger ms-1 text-decoration-none fw-bold">&times;</a>
                            </span>
                        @endif
                        @if(request('category'))
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                                {{ __('Kategori:') }} {{ request('category') }}
                                <a href="{{ route('wash.stock.index', array_merge(request()->except(['category', 'page']))) }}" class="text-danger ms-1 text-decoration-none fw-bold">&times;</a>
                            </span>
                        @endif
                        @if(request('stock_status'))
                            @php
                                $statusLabels = ['in' => 'Stok Aman (> Min)', 'low' => 'Stok Menipis (<= Min)', 'out' => 'Stok Habis (0)'];
                            @endphp
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                                {{ __('Status Stok:') }} {{ $statusLabels[request('stock_status')] ?? request('stock_status') }}
                                <a href="{{ route('wash.stock.index', array_merge(request()->except(['stock_status', 'page']))) }}" class="text-danger ms-1 text-decoration-none fw-bold">&times;</a>
                            </span>
                        @endif
                        @if(request('status'))
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                                {{ __('Status:') }} {{ request('status') === 'active' ? 'Aktif' : 'Nonaktif' }}
                                <a href="{{ route('wash.stock.index', array_merge(request()->except(['status', 'page']))) }}" class="text-danger ms-1 text-decoration-none fw-bold">&times;</a>
                            </span>
                        @endif
                        @if(request('sort') && request('sort') !== 'latest')
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                                {{ __('Urutan:') }} {{ request('sort') }}
                                <a href="{{ route('wash.stock.index', array_merge(request()->except(['sort', 'page']))) }}" class="text-danger ms-1 text-decoration-none fw-bold">&times;</a>
                            </span>
                        @endif
                        <a href="{{ route('wash.stock.index') }}" class="btn btn-link btn-sm p-0 text-decoration-none text-danger small ms-auto">
                            <i class="fa-solid fa-xmark me-1"></i>{{ __('Reset Semua Filter') }}
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Table Content --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="min-width: 220px;" class="py-3 px-3">{{ __('Nama Item Bahan') }}</th>
                        <th style="min-width: 140px;" class="py-3">{{ __('Kategori') }}</th>
                        <th style="min-width: 90px;" class="py-3">{{ __('Satuan') }}</th>
                        <th style="min-width: 130px;" class="py-3 text-center">{{ __('Stok Saat Ini') }}</th>
                        <th style="min-width: 110px;" class="py-3 text-center">{{ __('Min. Stok') }}</th>
                        <th style="min-width: 150px;" class="py-3 text-end">{{ __('Harga Beli Terakhir') }}</th>
                        <th style="min-width: 100px;" class="py-3 text-center">{{ __('Status') }}</th>
                        <th style="min-width: 140px;" class="py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($items as $item)
                    <tr>
                        <td class="px-3">
                            <div class="fw-bold text-gray-900">{{ $item->name }}</div>
                            @if($item->supplier)
                                <div class="small text-muted"><i class="fa-solid fa-truck-field me-1"></i>{{ $item->supplier->name }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $catClass = 'bg-secondary-subtle text-secondary';
                                $catUpper = strtoupper($item->category ?? '');
                                if (str_contains($catUpper, 'SHAMPO')) {
                                    $catClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                } elseif (str_contains($catUpper, 'SEMIR')) {
                                    $catClass = 'bg-dark-subtle text-dark border border-dark-subtle';
                                } elseif (str_contains($catUpper, 'CHEMICAL') || str_contains($catUpper, 'OBAT')) {
                                    $catClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                } elseif (str_contains($catUpper, 'LAP') || str_contains($catUpper, 'BUSA')) {
                                    $catClass = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                                } elseif (str_contains($catUpper, 'PEWANGI')) {
                                    $catClass = 'bg-success-subtle text-success border border-success-subtle';
                                }
                            @endphp
                            <span class="badge rounded-pill {{ $catClass }} fw-medium px-2 py-1">
                                {{ $item->category ?: __('Bahan Cuci') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $item->unit }}</span>
                        </td>
                        <td class="text-center">
                            @if($item->current_stock <= 0)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-7">
                                    <i class="fa-solid fa-circle-xmark me-1"></i>{{ __('Habis (0)') }}
                                </span>
                            @elseif($item->minimum_stock > 0 && $item->current_stock <= $item->minimum_stock)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fs-7">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ number_format($item->current_stock, 2, ',', '.') }} {{ $item->unit }}
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-7">
                                    <i class="fa-solid fa-circle-check me-1"></i>{{ number_format($item->current_stock, 2, ',', '.') }} {{ $item->unit }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center text-muted small">
                            {{ $item->minimum_stock ? number_format($item->minimum_stock, 2, ',', '.') . ' ' . $item->unit : '-' }}
                        </td>
                        <td class="text-end">
                            <span class="fw-semibold text-dark">
                                {{ $item->last_buy_price ? 'Rp ' . number_format($item->last_buy_price, 0, ',', '.') : '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill {{ $item->is_active ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary' }} px-2 py-1">
                                {{ $item->is_active ? __('Aktif') : __('Nonaktif') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('wash.stock.show', $item) }}" class="btn btn-outline-secondary" title="{{ __('Detail & Riwayat Mutasi') }}">
                                    <i class="fa-solid fa-clock-rotate-left text-info"></i>
                                </a>
                                <a href="{{ route('wash.stock.stock-in', $item) }}" class="btn btn-outline-secondary" title="{{ __('Catat Stok Masuk') }}">
                                    <i class="fa-solid fa-plus text-success"></i>
                                </a>
                                <a href="{{ route('wash.stock.edit', $item) }}" class="btn btn-outline-secondary" title="{{ __('Ubah Item') }}">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>
                                <form method="POST" action="{{ route('wash.stock.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus item {{ addslashes($item->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary text-danger" title="{{ __('Hapus Item') }}">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-boxes-stacked fs-1 text-secondary-subtle mb-3 d-block"></i>
                                <h6 class="fw-bold mb-1">{{ __('Tidak ada data stok ditemukan') }}</h6>
                                <p class="small mb-3">{{ __('Coba sesuaikan kata kunci pencarian atau tambahkan item bahan baru.') }}</p>
                                @if(request('search') || request('category') || request('stock_status') || request('status'))
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('wash.stock.index') }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa-solid fa-rotate-left me-1"></i>{{ __('Reset Filter') }}
                                        </a>
                                        <a href="{{ route('wash.stock.create') }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-plus me-1"></i>{{ __('Tambah Item Baru') }}
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('wash.stock.create') }}" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-plus me-1"></i>{{ __('Tambah Item Baru') }}
                                    </a>
                                @endif
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
                {{ __('Menampilkan') }} <span class="fw-semibold text-dark">{{ $items->firstItem() ?? 0 }}</span> - <span class="fw-semibold text-dark">{{ $items->lastItem() ?? 0 }}</span> {{ __('dari') }} <span class="fw-semibold text-dark">{{ $items->total() }}</span> {{ __('item bahan') }}
            </div>
            <div>
                {{ $items->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Import Stok Bahan Wash -->
<div class="modal fade" id="importWashStockModal" tabindex="-1" aria-labelledby="importWashStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold" id="importWashStockModalLabel">
                    <i class="fa-solid fa-file-import text-primary me-2"></i>{{ __('Import Data Stok Bahan Cuci') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('wash.stock.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 mb-3">
                        <div class="fw-bold mb-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>{{ __('Panduan Import Stok Bahan Wash') }}</span>
                        </div>
                        <ul class="mb-2 ps-3 small">
                            <li>{{ __('Mendukung file Excel (.xlsx, .xls) dan CSV (.csv).') }}</li>
                            <li>{{ __('Sistem otomatis mengenali kolom berdasarkan baris header: Nama Item, Kategori, Satuan, Stok Awal, Stok Minimum, Harga Beli Terakhir, Status.') }}</li>
                            <li>{{ __('Jika nama item sudah ada di sistem, kuantitas stok dan data item akan diperbarui otomatis.') }}</li>
                            <li>{{ __('Jika item baru memiliki stok awal > 0, sistem otomatis mencatat riwayat mutasi awal.') }}</li>
                        </ul>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a href="{{ route('wash.stock.template') }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-download me-1"></i> {{ __('Unduh Template Resmi (.xlsx)') }}
                            </a>
                            <a href="{{ route('wash.stock.export') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa-solid fa-file-export me-1"></i> {{ __('Ekspor Data Saat Ini') }}
                            </a>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="wash_stock_file" class="form-label fw-bold">{{ __('Pilih File Excel / CSV') }} <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="wash_stock_file" name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">{{ __('Maksimal ukuran file: 10MB (.xlsx, .xls, .csv)') }}</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> {{ __('Mulai Import') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
