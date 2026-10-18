@extends('layouts.app')

@section('title', __('Ubah Produk ATK: ') . $product->name)

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Ubah Produk & Stok ATK') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Perbarui informasi harga jual, modal HPP, satuan, dan jumlah stok.') }}</p>
                </div>
                <a href="{{ route('atk.products.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali ke Katalog') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                        <span>{{ __('Edit Data: ') }} {{ $product->name }}</span>
                    </h6>
                    <span class="badge bg-light text-muted font-monospace border px-2 py-1 small">
                        {{ $product->code }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('atk.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Kode / Barcode Barang') }}</label>
                                <input type="text" name="code" class="form-control font-monospace @error('code') is-invalid @enderror" value="{{ old('code', $product->code) }}" autofocus>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Nama Produk / Barang') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
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
                                        <option value="{{ $opt }}" {{ old('category', $product->category) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Satuan Unit') }}</label>
                                <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', $product->unit) }}">
                                @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">{{ __('Harga Modal (HPP)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price) }}" min="0">
                                </div>
                                @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">{{ __('Harga Jual Kasir') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">Rp</span>
                                    <input type="number" name="price" class="form-control fw-bold text-primary" value="{{ old('price', $product->price) }}" min="0" required>
                                </div>
                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">{{ __('Stok Fisik Saat Ini') }}</label>
                                <input type="number" name="stock" class="form-control fw-bold" value="{{ old('stock', $product->stock) }}" min="0">
                                @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Deskripsi / Spesifikasi Produk') }}</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description', $product->description) }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Foto Produk (Opsional)') }}</label>
                                @if($product->image)
                                    <div class="d-flex align-items-center gap-3 mb-2 p-2 border rounded-3 bg-light">
                                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" width="60" height="60" class="rounded-3 object-fit-cover border">
                                        <small class="text-muted">{{ __('Foto saat ini telah terpasang. Unggah file baru untuk menggantinya.') }}</small>
                                    </div>
                                @endif
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">{{ __('Maksimal 2MB (JPG, PNG, WEBP).') }}</small>
                                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 mt-3 border-top">
                            <a href="{{ route('atk.products.index') }}" class="btn btn-outline-secondary px-3">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa-solid fa-check me-1"></i>{{ __('Simpan Perubahan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
