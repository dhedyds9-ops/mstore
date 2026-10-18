@extends('layouts.app')

@section('title', __('Tambah Akun Float ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Tambah Akun Float Baru') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Daftarkan rekening bank, akun e-wallet, atau saldo deposit PPOB baru.') }}</p>
                </div>
                <a href="{{ route('atk.float-accounts.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-credit-card text-primary"></i>
                        <span>{{ __('Formulir Data Akun Float') }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('atk.float-accounts.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Kode Unik Akun') }} <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control font-monospace @error('code') is-invalid @enderror" placeholder="Contoh: BCA-01, DANA-01" value="{{ old('code') }}" required autofocus>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Nama Akun / Provider') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: BCA Toko, DANA Bisnis" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Tipe Akun') }} <span class="text-danger">*</span></label>
                                <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                                    <option value="">{{ __('Pilih Tipe Akun') }}</option>
                                    <option value="bank" {{ old('account_type') === 'bank' ? 'selected' : '' }}>{{ __('Rekening Bank (Transfer)') }}</option>
                                    <option value="e-wallet" {{ old('account_type') === 'e-wallet' ? 'selected' : '' }}>{{ __('E-Wallet (DANA/OVO/GoPay)') }}</option>
                                    <option value="ppob_deposit" {{ old('account_type') === 'ppob_deposit' ? 'selected' : '' }}>{{ __('Deposit Server PPOB') }}</option>
                                </select>
                                @error('account_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Saldo Awal') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">Rp</span>
                                    <input type="number" name="current_balance" class="form-control fw-bold" placeholder="0" value="{{ old('current_balance', 0) }}" min="0" required>
                                </div>
                                @error('current_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Status Akun') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>{{ __('Aktif (Tampil di POS)') }}</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('Nonaktif (Disembunyikan)') }}</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Catatan / Keterangan') }}</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Contoh: No Rekening 123456789 a.n Pemilik Toko">{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 mt-3 border-top">
                            <a href="{{ route('atk.float-accounts.index') }}" class="btn btn-outline-secondary px-3">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa-solid fa-check me-1"></i>{{ __('Simpan Akun Float') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
