@extends('layouts.app')

@section('title', __('Ubah Pengeluaran ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">
                        {{ __('Ubah Catatan Pengeluaran ATK') }}
                    </h1>
                    <p class="text-muted small mb-0">{{ __('Perubahan nominal akan otomatis menyesuaikan saldo Kas Utama laci.') }}</p>
                </div>
                <a href="{{ route('atk.expenses.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-danger"></i>
                        <span>{{ __('Perbarui Rincian Beban') }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('atk.expenses.update', $expense->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">{{ __('No. Referensi') }}</label>
                            <input type="text" class="form-control bg-light font-monospace" value="{{ $expense->reference_number }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">{{ __('Tanggal Pengeluaran') }} <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', optional($expense->transaction_date)->format('Y-m-d') ?: now()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">{{ __('Nominal Pengeluaran') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="amount" class="form-control form-control-lg fw-bold text-danger" placeholder="0" value="{{ old('amount', (int)$expense->amount) }}" required min="1" autofocus>
                            </div>
                            <small class="text-muted">{{ __('Masukkan nominal beban operasional.') }}</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">{{ __('Deskripsi / Keperluan Beban') }} <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" placeholder="Contoh: Beli kertas struk thermal, refill tinta, konsumsi kasir" value="{{ old('description', $expense->description) }}" required maxlength="255">
                            <small class="text-muted">{{ __('Tuliskan rincian barang/keperluan belanja dengan jelas.') }}</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('atk.expenses.index') }}" class="btn btn-outline-secondary px-3">
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
