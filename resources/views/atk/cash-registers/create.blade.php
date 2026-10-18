@extends('layouts.app')

@section('title', __('Buka Shift Kasir ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Buka Shift Kasir Baru') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Buka sesi baru untuk memulai transaksi kasir hari ini.') }}</p>
                </div>
                <a href="{{ route('atk.cash-registers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-door-open text-primary"></i>
                        <span>{{ __('Informasi Sesi Shift') }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('atk.cash-registers.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">{{ __('Nama Shift') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Shift Pagi, Shift Siang, Shift Malam" value="{{ old('name', 'Shift ' . (\Carbon\Carbon::now()->hour < 14 ? 'Pagi' : 'Siang')) }}" required autofocus>
                            <small class="text-muted">{{ __('Beri nama penanda untuk membedakan sesi pergantian kasir.') }}</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">{{ __('Saldo Awal Kas Laci') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="opening_balance" class="form-control form-control-lg fw-bold" placeholder="0" value="{{ old('opening_balance', 0) }}" required>
                            </div>
                            <small class="text-muted">{{ __('Uang modal receh / uang kembalian yang ada di laci saat shift dibuka.') }}</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('atk.cash-registers.index') }}" class="btn btn-outline-secondary px-3">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa-solid fa-check me-1"></i>{{ __('Konfirmasi Buka Shift') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
