@extends('layouts.app')

@section('title', __('Edit Investor - ') . $investor->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i>{{ __('Edit Data Investor') }}
            </h4>
            <p class="text-body-secondary small mb-0">{{ __('Perbarui informasi profil penanam modal :name', ['name' => $investor->name]) }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('investors.show', $investor) }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Detail') }}
            </a>
            <a href="{{ route('investors.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-list me-1"></i> {{ __('Daftar Investor') }}
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="fa-solid fa-file-pen text-primary me-2"></i>{{ __('Formulir Pembaruan Data') }}
                    </h6>
                </div>

                <div class="card-body p-3 p-md-4 border-top">
                    <form method="POST" action="{{ route('investors.update', $investor) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="coordinator_id" class="form-label small fw-semibold text-body-secondary">{{ __('Pengurus / Koordinator') }} <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm @error('coordinator_id') is-invalid @enderror" id="coordinator_id" name="coordinator_id" required>
                                <option value="">{{ __('-- Pilih Koordinator --') }}</option>
                                @foreach($coordinators as $coordinator)
                                    <option value="{{ $coordinator->id }}" {{ old('coordinator_id', $investor->coordinator_id) == $coordinator->id ? 'selected' : '' }}>
                                        {{ $coordinator->name }} ({{ $coordinator->region->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('coordinator_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-body-secondary">{{ __('Nama Investor') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $investor->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label small fw-semibold text-body-secondary">{{ __('Nomor Telepon / WhatsApp') }}</label>
                            <input type="text" class="form-control form-control-sm @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $investor->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label small fw-semibold text-body-secondary">{{ __('Keterangan / Catatan Tambahan') }}</label>
                            <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $investor->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-4">
                            <a href="{{ route('investors.show', $investor) }}" class="btn btn-outline-secondary btn-sm shadow-sm px-3">{{ __('Batal') }}</a>
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4">
                                <i class="fa-solid fa-floppy-disk me-1"></i>{{ __('Perbarui Data Investor') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
