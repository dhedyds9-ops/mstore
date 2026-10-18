@extends('layouts.app')

@section('title', __('Tambah Transaksi Dana Talangan ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Catat Dana Talangan') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Pencatatan suntikan modal pinjaman owner atau pengembalian dana ke owner.') }}</p>
                </div>
                <a href="{{ route('atk.owner-funds.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar text-warning"></i>
                        <span>{{ __('Formulir Mutasi Dana Talangan') }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('atk.owner-funds.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Tanggal Transaksi') }} <span class="text-danger">*</span></label>
                                <input type="date" name="transaction_date" class="form-control @error('transaction_date') is-invalid @enderror" value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required>
                                @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Tipe Mutasi') }} <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">{{ __('Pilih Tipe') }}</option>
                                    <option value="loan" {{ old('type') === 'loan' ? 'selected' : '' }}>{{ __('Pinjaman Masuk (Suntik Modal)') }}</option>
                                    <option value="repayment" {{ old('type') === 'repayment' ? 'selected' : '' }}>{{ __('Pengembalian Dana ke Owner') }}</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Jumlah Nominal') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">Rp</span>
                                    <input type="number" name="amount" class="form-control form-control-lg fw-bold" placeholder="0" value="{{ old('amount') }}" min="1" required autofocus>
                                </div>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Keterangan / Catatan') }}</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Contoh: Talangan modal tunai laci untuk transaksi transfer bank">{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 mt-3 border-top">
                            <a href="{{ route('atk.owner-funds.index') }}" class="btn btn-outline-secondary px-3">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa-solid fa-check me-1"></i>{{ __('Simpan Transaksi') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
