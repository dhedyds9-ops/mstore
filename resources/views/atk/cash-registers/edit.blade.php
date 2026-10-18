@extends('layouts.app')

@section('title', __('Ubah Shift Kasir ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Ubah Sesi Shift Kasir') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Perubahan saldo awal akan otomatis mengupdate mutasi saldo Kas Utama.') }}</p>
                </div>
                <a href="{{ route('atk.cash-registers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                        <span>{{ __('Perbarui Data Shift') }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('atk.cash-registers.update', $register) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">{{ __('Nama Shift') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $register->name) }}" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">{{ __('Saldo Awal Kas Laci') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="opening_balance" class="form-control form-control-lg fw-bold" value="{{ old('opening_balance', $register->opening_balance) }}" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('atk.cash-registers.index') }}" class="btn btn-outline-secondary px-3">
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