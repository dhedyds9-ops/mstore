@extends('layouts.app')

@section('title', __('Catat Mutasi Saldo Float ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Catat Mutasi Saldo Float') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Input penambahan saldo deposit, penarikan, transfer antar akun, atau penyesuaian.') }}</p>
                </div>
                <a href="{{ route('atk.float-accounts.show', $account) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali ke Akun') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">{{ __('Akun Target:') }}</span>
                        <h6 class="fw-bold mb-0 text-gray-900">{{ $account->name }} ({{ $account->code }})</h6>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small">{{ __('Saldo Saat Ini:') }}</span>
                        <div class="fw-bold text-success fs-6">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('atk.float-accounts.store-transaction', $account) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Tipe Mutasi') }} <span class="text-danger">*</span></label>
                                <select name="transaction_type" class="form-select @error('transaction_type') is-invalid @enderror" required autofocus>
                                    <option value="">{{ __('Pilih Tipe Mutasi') }}</option>
                                    <option value="deposit" {{ old('transaction_type') === 'deposit' ? 'selected' : '' }}>{{ __('Deposit / Tambah Modal Saldo (+)') }}</option>
                                    <option value="withdrawal" {{ old('transaction_type') === 'withdrawal' ? 'selected' : '' }}>{{ __('Tarik Saldo ke Rekening / Kas (-)') }}</option>
                                    <option value="transfer" {{ old('transaction_type') === 'transfer' ? 'selected' : '' }}>{{ __('Transfer Keluar (-)') }}</option>
                                    <option value="topup" {{ old('transaction_type') === 'topup' ? 'selected' : '' }}>{{ __('Topup Pelanggan (-)') }}</option>
                                    <option value="ppob" {{ old('transaction_type') === 'ppob' ? 'selected' : '' }}>{{ __('Pembayaran PPOB (-)') }}</option>
                                    <option value="adjustment" {{ old('transaction_type') === 'adjustment' ? 'selected' : '' }}>{{ __('Penyesuaian Saldo (Koreksi)') }}</option>
                                </select>
                                @error('transaction_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Jumlah Nominal') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">Rp</span>
                                    <input type="number" name="amount" class="form-control form-control-lg fw-bold" placeholder="0" value="{{ old('amount') }}" min="1" required>
                                </div>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Keterangan / Catatan Transaksi') }}</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Contoh: Suntik deposit saldo server PPOB via Kas Utama">{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 mt-3 border-top">
                            <a href="{{ route('atk.float-accounts.show', $account) }}" class="btn btn-outline-secondary px-3">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa-solid fa-check me-1"></i>{{ __('Simpan Mutasi Saldo') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
