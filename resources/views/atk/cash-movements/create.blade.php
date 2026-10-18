@extends('layouts.app')

@section('title', __('Koreksi Saldo Kas Utama ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Koreksi / Penyesuaian Saldo Kas') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Gunakan formulir ini jika terjadi selisih antara saldo sistem dengan uang fisik laci kasir.') }}</p>
                </div>
                <a href="{{ route('atk.cash-movements.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-sliders text-warning"></i>
                        <span>{{ __('Penyesuaian Saldo Kas Utama') }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('atk.cash-movements.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">{{ __('Saldo Kas Fisik yang Sebenarnya') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="amount" class="form-control form-control-lg fw-bold" placeholder="0" value="{{ old('amount') }}" required autofocus min="0">
                            </div>
                            <small class="text-muted">{{ __('Sistem akan otomatis menghitung selisih (masuk/keluar) untuk menyesuaikan saldo kas.') }}</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">{{ __('Alasan Penyesuaian / Catatan') }}</label>
                            <input type="text" name="description" class="form-control" placeholder="Contoh: Selisih uang kembalian, uang modal awal, dll" value="{{ old('description') }}" maxlength="255">
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('atk.cash-movements.index') }}" class="btn btn-outline-secondary px-3">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="btn btn-warning text-dark px-4 shadow-sm fw-semibold">
                                <i class="fa-solid fa-check me-1"></i>{{ __('Simpan Penyesuaian') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection