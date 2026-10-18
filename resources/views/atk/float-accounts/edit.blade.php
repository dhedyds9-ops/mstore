@extends('layouts.app')

@section('title', __('Ubah Akun Float ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Ubah Data Akun Float') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Perbarui informasi akun, nama provider, tipe akun, atau status keaktifan.') }}</p>
                </div>
                <a href="{{ route('atk.float-accounts.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                        <span>{{ __('Perbarui Data: ') }} {{ $account->name }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('atk.float-accounts.update', $account) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Kode Unik Akun') }} <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control font-monospace @error('code') is-invalid @enderror" value="{{ old('code', $account->code) }}" required autofocus>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Nama Akun / Provider') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $account->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Tipe Akun') }} <span class="text-danger">*</span></label>
                                <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                                    <option value="">{{ __('Pilih Tipe Akun') }}</option>
                                    <option value="bank" {{ old('account_type', $account->account_type) === 'bank' ? 'selected' : '' }}>{{ __('Rekening Bank (Transfer)') }}</option>
                                    <option value="e-wallet" {{ old('account_type', $account->account_type) === 'e-wallet' ? 'selected' : '' }}>{{ __('E-Wallet (DANA/OVO/GoPay)') }}</option>
                                    <option value="ppob_deposit" {{ old('account_type', $account->account_type) === 'ppob_deposit' ? 'selected' : '' }}>{{ __('Deposit Server PPOB') }}</option>
                                </select>
                                @error('account_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Status Akun') }} <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $account->status) === 'active' ? 'selected' : '' }}>{{ __('Aktif (Tampil di POS)') }}</option>
                                    <option value="inactive" {{ old('status', $account->status) === 'inactive' ? 'selected' : '' }}>{{ __('Nonaktif (Disembunyikan)') }}</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Catatan / Keterangan') }}</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $account->description) }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 mt-3 border-top">
                            <a href="{{ route('atk.float-accounts.index') }}" class="btn btn-outline-secondary px-3">
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
