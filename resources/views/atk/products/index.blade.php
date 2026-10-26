@extends('layouts.app')

@section('title', __('Manajemen Produk ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman & Aksi Cepat --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1 fw-bold text-gray-800 d-flex align-items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-primary"></i>
                <span>{{ __('Katalog Produk & Stok ATK') }}</span>
            </h1>
            <p class="text-muted small mb-0">{{ __('Kelola master produk, harga jual, HPP modal, stok fisik, dan cetak barcode.') }}</p>
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
                        <a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fa-solid fa-file-import text-primary me-2"></i>{{ __('Impor Produk (.xlsx / .csv)') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('atk.products.template') }}">
                            <i class="fa-solid fa-download text-success me-2"></i>{{ __('Unduh Template Excel') }}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('atk.products.export') }}">
                            <i class="fa-solid fa-file-export text-secondary me-2"></i>{{ __('Ekspor Semua Data (.xlsx)') }}
                        </a>
                    </li>
                </ul>
            </div>

            <a href="{{ route('atk.products.barcodes') }}" class="btn btn-sm btn-outline-dark" title="{{ __('Cetak Label Barcode') }}">
                <i class="fa-solid fa-barcode me-1"></i>
                <span class="d-none d-sm-inline">{{ __('Cetak Barcode') }}</span>
            </a>

            <a href="{{ route('atk.products.create') }}" class="btn btn-sm btn-primary shadow-sm" title="{{ __('Tambah Produk Baru') }}">
                <i class="fa-solid fa-plus me-1"></i>
                <span>{{ __('Tambah Produk') }}</span>
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
                        <h6 class="fw-bold mb-1 text-gray-800">{{ __('Petunjuk Manajemen Produk ATK') }}</h6>
                        <ul class="mb-0 small text-muted ps-3">
                            <li><strong>{{ __('Tambah & Edit Produk:') }}</strong> Gunakan tombol "Tambah Produk" untuk input manual, atau klik tombol pensil di baris produk untuk mengubah harga/stok.</li>
                            <li><strong>{{ __('Impor Excel/CSV:') }}</strong> Unduh template resmi melalui menu "Impor / Ekspor", isi data produk Anda, lalu unggah kembali untuk impor massal cepat.</li>
                            <li><strong>{{ __('Scan Barcode Kasir:') }}</strong> Klik ikon barcode pada kolom pencarian untuk mendeteksi barang lewat kamera handphone/laptop secara langsung.</li>
                            <li><strong>{{ __('Filter Cepat & Stok:') }}</strong> Klik kartu statistik (Total Produk, Stok Aman, Stok Menipis, Stok Habis) untuk filter instan.</li>
                            <li><strong>{{ __('Hapus Massal:') }}</strong> Centang kotak pilihan produk yang ingin dihapus, bilah aksi akan muncul secara otomatis.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mini Metric Stat Cards (Interaktif Filter) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('atk.products.index', array_merge(request()->except(['stock_status', 'page']))) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 {{ !request('stock_status') ? 'border-start border-4 border-primary' : '' }}">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-5">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ __('Total Produk') }}</div>
                            <div class="fs-5 fw-bold text-gray-800">{{ number_format($stats['total_items'] ?? $products->total(), 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('atk.products.index', array_merge(request()->except(['page']), ['stock_status' => 'in'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 {{ request('stock_status') === 'in' ? 'border-start border-4 border-success' : '' }}">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 bg-success-subtle text-success fs-5">
                            <i class="fa-solid fa-cubes"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ __('Stok Aman (> 5)') }}</div>
                            <div class="fs-5 fw-bold text-success">{{ number_format($stats['total_stock'] ?? 0, 0, ',', '.') }} <span class="small fw-normal text-muted">unit</span></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('atk.products.index', array_merge(request()->except(['page']), ['stock_status' => 'low'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 {{ request('stock_status') === 'low' ? 'border-start border-4 border-warning' : '' }}">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-3 p-3 bg-warning-subtle text-warning-emphasis fs-5">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium">{{ __('Stok Menipis (<= 5)') }}</div>
                            <div class="fs-5 fw-bold text-warning-emphasis">{{ number_format($stats['low_stock'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('atk.products.index', array_merge(request()->except(['page']), ['stock_status' => 'out'])) }}" class="text-decoration-none">
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

    {{-- Card Tabel Produk & Filter --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        {{-- Filter Bar --}}
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <form method="GET" action="{{ route('atk.products.index') }}" id="filterForm">
                <div class="row g-2 align-items-center">
                    {{-- Pencarian --}}
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" name="search" id="productSearch" class="form-control bg-light border-start-0 border-end-0" placeholder="{{ __('Cari nama, kode, barcode...') }}" value="{{ request('search') }}">
                            @if(request('search'))
                                <a href="{{ route('atk.products.index', array_merge(request()->except(['search', 'page']))) }}" class="btn btn-light border-top border-bottom border-0 text-muted" title="{{ __('Hapus Pencarian') }}">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            @endif
                            <button type="button" class="btn btn-light border border-start-0 text-primary" id="openAtkBarcodeScan" title="{{ __('Scan Barcode via Kamera') }}">
                                <i class="fa-solid fa-barcode"></i>
                            </button>
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
                                <option value="in" {{ request('stock_status') === 'in' ? 'selected' : '' }}>{{ __('Stok Aman (> 5)') }}</option>
                                <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>{{ __('Stok Menipis (<= 5)') }}</option>
                                <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>{{ __('Stok Habis (0)') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Urutkan / Sort --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small"><i class="fa-solid fa-arrow-down-short-wide"></i></span>
                            <select name="sort" class="form-select bg-light" onchange="this.form.submit()">
                                <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>{{ __('Terbaru') }}</option>
                                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>{{ __('Nama (A - Z)') }}</option>
                                <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>{{ __('Nama (Z - A)') }}</option>
                                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('Harga Terendah') }}</option>
                                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('Harga Tertinggi') }}</option>
                                <option value="stock_asc" {{ request('sort') === 'stock_asc' ? 'selected' : '' }}>{{ __('Stok Tersedikit') }}</option>
                                <option value="stock_desc" {{ request('sort') === 'stock_desc' ? 'selected' : '' }}>{{ __('Stok Terbanyak') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Per Page --}}
                    <div class="col-6 col-md-2 col-lg-1">
                        <select name="per_page" id="per_page" class="form-select bg-light" onchange="this.form.submit()" title="{{ __('Jumlah per halaman') }}">
                            @php $pp = request('per_page', '10'); @endphp
                            <option value="10" {{ $pp == '10' ? 'selected' : '' }}>10 / hal</option>
                            <option value="25" {{ $pp == '25' ? 'selected' : '' }}>25 / hal</option>
                            <option value="50" {{ $pp == '50' ? 'selected' : '' }}>50 / hal</option>
                            <option value="100" {{ $pp == '100' ? 'selected' : '' }}>100 / hal</option>
                            <option value="all" {{ $pp == 'all' ? 'selected' : '' }}>Semua</option>
                        </select>
                    </div>

                    {{-- Tombol Filter & Reset --}}
                    <div class="col-12 col-md-3 col-lg-2 d-flex gap-2 justify-content-md-end">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill flex-md-grow-0 px-3" title="{{ __('Terapkan Filter') }}">
                            <i class="fa-solid fa-filter me-1"></i>{{ __('Filter') }}
                        </button>
                        @if(request('search') || request('category') || request('stock_status') || (request('sort') && request('sort') !== 'latest') || (request('per_page') && request('per_page') != '10'))
                            <a href="{{ route('atk.products.index') }}" class="btn btn-outline-secondary btn-sm px-2" title="{{ __('Reset Semua Filter') }}">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Filter Aktif Indicators --}}
                @if(request('search') || request('category') || request('stock_status') || (request('sort') && request('sort') !== 'latest'))
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-2 border-top">
                        <span class="small text-muted me-1"><i class="fa-solid fa-tags me-1"></i>{{ __('Filter Aktif:') }}</span>
                        @if(request('search'))
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                                {{ __('Cari:') }} "{{ request('search') }}"
                                <a href="{{ route('atk.products.index', array_merge(request()->except(['search', 'page']))) }}" class="text-danger ms-1 text-decoration-none fw-bold">&times;</a>
                            </span>
                        @endif
                        @if(request('category'))
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                                {{ __('Kategori:') }} {{ request('category') }}
                                <a href="{{ route('atk.products.index', array_merge(request()->except(['category', 'page']))) }}" class="text-danger ms-1 text-decoration-none fw-bold">&times;</a>
                            </span>
                        @endif
                        @if(request('stock_status'))
                            @php
                                $statusLabels = ['in' => 'Stok Aman (> 5)', 'low' => 'Stok Menipis (<= 5)', 'out' => 'Stok Habis (0)'];
                            @endphp
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                                {{ __('Status Stok:') }} {{ $statusLabels[request('stock_status')] ?? request('stock_status') }}
                                <a href="{{ route('atk.products.index', array_merge(request()->except(['stock_status', 'page']))) }}" class="text-danger ms-1 text-decoration-none fw-bold">&times;</a>
                            </span>
                        @endif
                        @if(request('sort') && request('sort') !== 'latest')
                            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                                {{ __('Urutan:') }} {{ request('sort') }}
                                <a href="{{ route('atk.products.index', array_merge(request()->except(['sort', 'page']))) }}" class="text-danger ms-1 text-decoration-none fw-bold">&times;</a>
                            </span>
                        @endif
                        <a href="{{ route('atk.products.index') }}" class="btn btn-link btn-sm p-0 text-decoration-none text-danger small ms-auto">
                            <i class="fa-solid fa-xmark me-1"></i>{{ __('Reset Semua Filter') }}
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Contextual Bulk Action Bar --}}
        <div id="bulkActionBar" class="alert alert-primary d-none mx-3 my-2 py-2 px-3 align-items-center justify-content-between rounded-3 border-primary-subtle shadow-xs">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check text-primary fs-5"></i>
                <span class="fw-semibold text-dark"><span id="bulkSelectedCount">0</span> {{ __('produk dipilih') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" id="btnCancelBulk" class="btn btn-sm btn-outline-secondary">{{ __('Batal Pilih') }}</button>
                <form id="bulkDeleteForm" action="{{ route('atk.products.bulk_destroy') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" id="btnBulkDelete" class="btn btn-sm btn-danger shadow-xs" onclick="return confirm('Hapus semua produk yang dipilih?')">
                        <i class="fa-solid fa-trash-can me-1"></i>
                        <span id="bulkDeleteCount" class="badge bg-white text-danger rounded-pill me-1 d-none">0</span>
                        <span>{{ __('Hapus Terpilih') }}</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="width: 36px;" class="text-center py-3">
                            <input type="checkbox" id="checkAll" class="form-check-input">
                        </th>
                        <th style="width: 60px;" class="text-center py-3">{{ __('Foto') }}</th>
                        <th style="min-width: 130px;" class="py-3">{{ __('Kode / Barcode') }}</th>
                        <th style="min-width: 200px;" class="py-3">{{ __('Nama Produk & Deskripsi') }}</th>
                        <th style="min-width: 120px;" class="py-3">{{ __('Kategori') }}</th>
                        <th style="min-width: 150px;" class="py-3 text-end">{{ __('Harga Jual / HPP') }}</th>
                        <th style="min-width: 110px;" class="py-3 text-center">{{ __('Stok Fisik') }}</th>
                        <th style="min-width: 100px;" class="py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($products as $product)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input row-check" value="{{ $product->id }}">
                        </td>
                        <td class="text-center">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" width="44" height="44" class="rounded-3 object-fit-cover border shadow-2xs">
                            @else
                                <div class="rounded-3 bg-light border d-inline-flex align-items-center justify-content-center text-muted" style="width: 44px; height: 44px;">
                                    <i class="fa-solid fa-box text-secondary-subtle fs-5"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold font-monospace text-dark">{{ $product->code }}</div>
                            @if($product->barcode && $product->barcode !== $product->code)
                                <div class="small text-muted font-monospace"><i class="fa-solid fa-barcode me-1"></i>{{ $product->barcode }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $product->name }}</div>
                            @if($product->description)
                                <div class="text-muted small text-truncate" style="max-width: 280px;" title="{{ $product->description }}">
                                    {{ $product->description }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @php
                                $catClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                if(str_contains(strtoupper($product->category ?? ''), 'FOTOCOPY')) {
                                    $catClass = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                                } elseif(str_contains(strtoupper($product->category ?? ''), 'TRANSFER')) {
                                    $catClass = 'bg-success-subtle text-success border border-success-subtle';
                                }
                            @endphp
                            <span class="badge rounded-pill {{ $catClass }} fw-medium px-2 py-1">
                                {{ $product->category ?: 'ATK' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="fw-bold text-dark fs-6">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                            @if($product->cost_price > 0)
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    HPP: Rp {{ number_format($product->cost_price, 0, ',', '.') }}
                                    @php
                                        $margin = $product->price - $product->cost_price;
                                    @endphp
                                    @if($margin > 0)
                                        <span class="text-success ms-1">(+Rp {{ number_format($margin, 0, ',', '.') }})</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($product->stock > 10)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-7">
                                    <i class="fa-solid fa-circle-check me-1"></i>{{ $product->stock }} {{ $product->unit ?: 'Pcs' }}
                                </span>
                            @elseif($product->stock > 0)
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fs-7">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $product->stock }} {{ $product->unit ?: 'Pcs' }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-7">
                                    <i class="fa-solid fa-circle-xmark me-1"></i>{{ __('Habis (0)') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('atk.products.edit', $product) }}" class="btn btn-outline-secondary" title="{{ __('Ubah Produk') }}">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>
                                <form action="{{ route('atk.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk {{ addslashes($product->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary text-danger" title="{{ __('Hapus Produk') }}">
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
                                <h6 class="fw-bold mb-1">{{ __('Tidak ada produk ditemukan') }}</h6>
                                <p class="small mb-3">{{ __('Coba ubah kata kunci pencarian atau sesuaikan filter.') }}</p>
                                @if(request('search') || request('category') || request('stock_status'))
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('atk.products.index') }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa-solid fa-rotate-left me-1"></i>{{ __('Reset Filter') }}
                                        </a>
                                        <a href="{{ route('atk.products.create') }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-plus me-1"></i>{{ __('Tambah Produk Baru') }}
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('atk.products.create') }}" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-plus me-1"></i>{{ __('Tambah Produk Baru') }}
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
                {{ __('Menampilkan') }} <span class="fw-semibold text-dark">{{ $products->firstItem() ?? 0 }}</span> - <span class="fw-semibold text-dark">{{ $products->lastItem() ?? 0 }}</span> {{ __('dari') }} <span class="fw-semibold text-dark">{{ $products->total() }}</span> {{ __('produk') }}
            </div>
            <div>
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold" id="importModalLabel">
                    <i class="fa-solid fa-file-import text-primary me-2"></i>{{ __('Import Data & Stok Produk ATK') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('atk.products.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 mb-3">
                        <div class="fw-bold mb-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>{{ __('Panduan Import Stok & Produk ATK') }}</span>
                        </div>
                        <ul class="mb-2 ps-3 small">
                            <li>{{ __('Mendukung file Excel (.xlsx, .xls) dan CSV (.csv).') }}</li>
                            <li>{{ __('Sistem otomatis mendeteksi kolom berdasarkan baris judul (Header Kolom Dinamis).') }}</li>
                            <li>{{ __('Kolom: Kode Barang, Nama Produk, Kategori, Harga Jual, Harga Beli/HPP, Stok Awal, Satuan, Deskripsi.') }}</li>
                            <li>{{ __('Wajib diisi: Nama Produk. Jika Kode Barang dikosongkan, sistem akan otomatis membuat kode unik unik untuk Anda.') }}</li>
                            <li>{{ __('Format harga/stok otomatis dibersihkan (bisa menggunakan Rp atau tanda titik/koma ribuan).') }}</li>
                        </ul>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a href="{{ route('atk.products.template') }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-download me-1"></i> {{ __('Unduh Template Resmi (.xlsx)') }}
                            </a>
                            <a href="{{ route('atk.products.export') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa-solid fa-file-export me-1"></i> {{ __('Export Data Saat Ini') }}
                            </a>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="file" class="form-label fw-bold">{{ __('Pilih File Excel / CSV') }} <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
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

<!-- Barcode Scan Modal -->
<div class="modal fade" id="atkBarcodeScanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-barcode text-primary me-2"></i>{{ __('Scan Barcode Produk') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div id="atk-barcode-reader" class="atk-scan-reader mx-auto rounded-3 overflow-hidden border" style="width:100%; max-width: 320px;"></div>
                <div id="atkScanStatus" class="small text-muted mt-3">
                    <i class="fa-solid fa-camera me-1"></i> {{ __('Arahkan kamera ke barcode pada produk ATK.') }}
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-outline-danger" id="stopAtkBarcodeScan">{{ __('Hentikan Scan') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<style>
    .atk-scan-reader video {
        width: 100% !important;
        height: auto !important;
        object-fit: cover;
        border-radius: 0.5rem;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Existing selection logic
    const checkAll = document.getElementById('checkAll');
    const rowChecks = Array.from(document.querySelectorAll('.row-check'));
    const bulkForm = document.getElementById('bulkDeleteForm');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const btnSelectAll = document.getElementById('btnSelectAll');

    const bulkDeleteCount = document.getElementById('bulkDeleteCount');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const bulkSelectedCount = document.getElementById('bulkSelectedCount');
    const btnCancelBulk = document.getElementById('btnCancelBulk');

    function updateBulkState() {
        const selected = rowChecks.filter(cb => cb.checked).map(cb => cb.value);
        if (bulkForm) {
            bulkForm.querySelectorAll('input[name="ids[]"]').forEach(e => e.remove());
            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                bulkForm.appendChild(input);
            });
        }
        if (btnBulkDelete) {
            btnBulkDelete.disabled = selected.length === 0;
        }
        if (bulkDeleteCount) {
            bulkDeleteCount.textContent = selected.length;
            if (selected.length > 0) {
                bulkDeleteCount.classList.remove('d-none');
            } else {
                bulkDeleteCount.classList.add('d-none');
            }
        }
        if (bulkActionBar && bulkSelectedCount) {
            bulkSelectedCount.textContent = selected.length;
            if (selected.length > 0) {
                bulkActionBar.classList.remove('d-none');
                bulkActionBar.classList.add('d-flex');
            } else {
                bulkActionBar.classList.add('d-none');
                bulkActionBar.classList.remove('d-flex');
            }
        }
        if (checkAll) {
            checkAll.checked = selected.length > 0 && selected.length === rowChecks.length;
            checkAll.indeterminate = selected.length > 0 && selected.length < rowChecks.length;
        }
    }
    if (btnCancelBulk) {
        btnCancelBulk.addEventListener('click', function() {
            rowChecks.forEach(cb => cb.checked = false);
            updateBulkState();
        });
    }
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            rowChecks.forEach(cb => cb.checked = checkAll.checked);
            updateBulkState();
        });
    }
    rowChecks.forEach(cb => cb.addEventListener('change', updateBulkState));
    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function() {
            const allChecked = rowChecks.every(cb => cb.checked);
            rowChecks.forEach(cb => cb.checked = !allChecked);
            updateBulkState();
        });
    }

    // Barcode Scanner Logic
    let atkBarcodeScanner = null;
    let isAtkBarcodeScannerRunning = false;

    function setAtkScanStatus(message, type = 'muted') {
        const statusEl = document.getElementById('atkScanStatus');
        if (!statusEl) return;
        statusEl.classList.remove('text-muted', 'text-danger', 'text-success');
        if (type === 'error') {
            statusEl.classList.add('text-danger');
        } else if (type === 'success') {
            statusEl.classList.add('text-success');
        } else {
            statusEl.classList.add('text-muted');
        }
        statusEl.textContent = message;
    }

    async function stopAtkBarcodeScanner() {
        if (!atkBarcodeScanner || !isAtkBarcodeScannerRunning) return;
        try {
            await atkBarcodeScanner.stop();
            await atkBarcodeScanner.clear();
        } catch (error) {
            console.warn('Stop ATK barcode scanner error:', error);
        } finally {
            isAtkBarcodeScannerRunning = false;
        }
    }

    async function startAtkBarcodeScanner() {
        if (typeof Html5Qrcode === 'undefined') {
            setAtkScanStatus('Library scanner belum tersedia.', 'error');
            return;
        }
        if (isAtkBarcodeScannerRunning) {
            setAtkScanStatus('Scanner sudah aktif.');
            return;
        }
        atkBarcodeScanner = atkBarcodeScanner || new Html5Qrcode('atk-barcode-reader');
        
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            disableFlip: true
        };

        const onDecoded = async (decodedText) => {
            const productSearchInput = document.getElementById('productSearch');
            if (productSearchInput) {
                productSearchInput.value = String(decodedText || '').trim();
                productSearchInput.form.submit(); // Auto submit on scan
            }
            setAtkScanStatus('Barcode berhasil dibaca.', 'success');
            await stopAtkBarcodeScanner();
            const modalEl = document.getElementById('atkBarcodeScanModal');
            if (modalEl && window.bootstrap) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalInstance.hide();
            }
        };

        const onDecodeError = () => {};

        try {
            await atkBarcodeScanner.start({ facingMode: "environment" }, config, onDecoded, onDecodeError);
            isAtkBarcodeScannerRunning = true;
            setAtkScanStatus('Arahkan kamera ke barcode.');
        } catch (err) {
            console.error(err);
            setAtkScanStatus('Gagal mengakses kamera.', 'error');
        }
    }

    const openScanBtn = document.getElementById('openAtkBarcodeScan');
    const stopScanBtn = document.getElementById('stopAtkBarcodeScan');
    const scanModalEl = document.getElementById('atkBarcodeScanModal');

    if (openScanBtn) {
        openScanBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(scanModalEl);
            modal.show();
        });
    }

    if (scanModalEl) {
        scanModalEl.addEventListener('shown.bs.modal', function() {
            startAtkBarcodeScanner();
        });
        scanModalEl.addEventListener('hidden.bs.modal', function() {
            stopAtkBarcodeScanner();
        });
    }

    if (stopScanBtn) {
        stopScanBtn.addEventListener('click', function() {
            stopAtkBarcodeScanner();
            const modalInstance = bootstrap.Modal.getInstance(scanModalEl);
            if (modalInstance) modalInstance.hide();
        });
    }
});
</script>
@endpush
@endsection
