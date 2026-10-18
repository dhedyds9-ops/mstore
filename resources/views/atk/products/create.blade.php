@extends('layouts.app')

@section('title', __('Tambah Produk ATK Baru'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Tambah Produk Baru') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Daftarkan barang atau layanan baru ke dalam katalog toko ATK.') }}</p>
                </div>
                <a href="{{ route('atk.products.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali ke Katalog') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-box-open text-primary"></i>
                        <span>{{ __('Informasi Master Produk') }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('atk.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Kode / Barcode Barang') }}</label>
                                <input type="text" name="code" class="form-control font-monospace @error('code') is-invalid @enderror" placeholder="Kosongkan untuk auto-generate" value="{{ old('code') }}" autofocus>
                                <small class="text-muted">{{ __('Bisa diisi nomor barcode fisik barang jika ada.') }}</small>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Nama Produk / Barang') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Kertas A4 Sinar Dunia 70gr" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Kategori Produk') }}</label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror">
                                    <option value="">{{ __('Pilih Kategori (opsional)') }}</option>
                                    @php
                                        $opts = ['ATK', 'JASA POTOCOPY', 'JASA TRANSFER BANK'];
                                    @endphp
                                    @foreach($opts as $opt)
                                        <option value="{{ $opt }}" {{ old('category') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Satuan Unit') }}</label>
                                <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" placeholder="Contoh: Pcs, Rim, Pack, Box" value="{{ old('unit', 'Pcs') }}">
                                @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">{{ __('Harga Modal (HPP)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="cost_price" class="form-control" placeholder="0" value="{{ old('cost_price') }}" min="0">
                                </div>
                                @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">{{ __('Harga Jual Kasir') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">Rp</span>
                                    <input type="number" name="price" class="form-control fw-bold text-primary" placeholder="0" value="{{ old('price') }}" min="0" required>
                                </div>
                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">{{ __('Stok Awal Fisik') }}</label>
                                <input type="number" name="stock" class="form-control fw-bold" placeholder="0" value="{{ old('stock', 0) }}" min="0">
                                @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Deskripsi / Spesifikasi Produk') }}</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Catatan ukuran, warna, atau spesifikasi barang">{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Foto Produk (Opsional)') }}</label>
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">{{ __('Maksimal 2MB (JPG, PNG, WEBP). Foto akan muncul di kasir POS.') }}</small>
                                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 mt-3 border-top">
                            <a href="{{ route('atk.products.index') }}" class="btn btn-outline-secondary px-3">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa-solid fa-check me-1"></i>{{ __('Simpan Produk') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
