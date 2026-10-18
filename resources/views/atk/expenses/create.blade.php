@extends('layouts.app')

@section('title', isset($expense) ? __('Ubah Pengeluaran ATK') : __('Catat Pengeluaran ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">
                        {{ isset($expense) ? __('Ubah Catatan Pengeluaran') : __('Catat Beban & Pengeluaran Toko') }}
                    </h1>
                    <p class="text-muted small mb-0">{{ __('Pengeluaran akan langsung memotong saldo Kas Utama laci kasir.') }}</p>
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
                        <span>{{ isset($expense) ? __('Perbarui Detail Beban') : __('Formulir Pengeluaran Kas') }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ isset($expense) ? route('atk.expenses.update', $expense->id) : route('atk.expenses.store') }}">
                        @csrf
                        @if(isset($expense))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">{{ __('Tanggal Pengeluaran') }} <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', isset($expense) ? optional($expense->transaction_date)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">{{ __('Nominal Pengeluaran') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="amount" class="form-control form-control-lg fw-bold text-danger" placeholder="0" value="{{ old('amount', $expense->amount ?? '') }}" required min="1" autofocus>
                            </div>
                            <small class="text-muted">{{ __('Masukkan jumlah uang yang dikeluarkan dari kas laci.') }}</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">{{ __('Deskripsi / Keperluan Beban') }} <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" placeholder="Contoh: Beli kertas struk thermal, refill tinta, konsumsi kasir" value="{{ old('description', $expense->description ?? '') }}" required maxlength="255">
                            <small class="text-muted">{{ __('Tuliskan rincian barang/keperluan belanja dengan jelas.') }}</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('atk.expenses.index') }}" class="btn btn-outline-secondary px-3">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa-solid fa-check me-1"></i>{{ isset($expense) ? __('Simpan Perubahan') : __('Simpan Pengeluaran') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
