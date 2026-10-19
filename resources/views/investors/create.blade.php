@extends('layouts.app')

@section('title', __('Tambah Investor'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-user-plus text-primary me-2"></i>{{ __('Tambah Investor') }}
            </h4>
            <p class="text-body-secondary small mb-0">{{ __('Daftarkan penanam modal baru atau kaitkan investor dengan koordinator pengurus.') }}</p>
        </div>
        <div>
            <a href="{{ route('investors.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Daftar') }}
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="fa-solid fa-file-pen text-primary me-2"></i>{{ __('Formulir Data Investor') }}
                    </h6>
                </div>

                <div class="card-body p-3 p-md-4 border-top">
                    <form method="POST" action="{{ route('investors.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="coordinator_id" class="form-label small fw-semibold text-body-secondary">{{ __('Pengurus / Koordinator') }} <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm @error('coordinator_id') is-invalid @enderror" id="coordinator_id" name="coordinator_id" required>
                                <option value="">{{ __('-- Pilih Koordinator --') }}</option>
                                @foreach($coordinators as $coordinator)
                                    <option value="{{ $coordinator->id }}" {{ old('coordinator_id') == $coordinator->id ? 'selected' : '' }}>
                                        {{ $coordinator->name }} ({{ $coordinator->region->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('coordinator_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-body-secondary">{{ __('Pilihan Pendaftaran') }}</label>
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <label class="investor-mode-option flex-fill">
                                    <input class="form-check-input me-2" type="radio" name="mode" id="mode_new" value="new" {{ old('mode', 'new') === 'new' ? 'checked' : '' }}>
                                    <span class="small fw-semibold">{{ __('Buat Investor Baru') }}</span>
                                </label>
                                <label class="investor-mode-option flex-fill">
                                    <input class="form-check-input me-2" type="radio" name="mode" id="mode_select" value="select" {{ old('mode') === 'select' ? 'checked' : '' }}>
                                    <span class="small fw-semibold">{{ __('Pilih Investor yang Sudah Ada') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3" id="existingInvestorWrapper">
                            <label for="source_investor_id" class="form-label small fw-semibold text-body-secondary">{{ __('Pilih Investor Terdaftar') }} <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm @error('source_investor_id') is-invalid @enderror" id="source_investor_id" name="source_investor_id">
                                <option value="">{{ __('-- Pilih Investor --') }}</option>
                                @foreach($existingInvestors as $investor)
                                    <option value="{{ $investor->id }}" {{ old('source_investor_id') == $investor->id ? 'selected' : '' }}>
                                        {{ $investor->name }} @if($investor->phone) ({{ $investor->phone }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('source_investor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">
                                {{ __('Gunakan opsi ini jika investor telah terdaftar sebelumnya pada koordinator lain.') }}
                            </div>
                        </div>

                        <div id="newInvestorFields">
                            <div class="mb-3">
                                <label for="name" class="form-label small fw-semibold text-body-secondary">{{ __('Nama Investor') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Bpk. H. Hendra">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label small fw-semibold text-body-secondary">{{ __('Nomor Telepon / WhatsApp') }}</label>
                                <input type="text" class="form-control form-control-sm @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 08123456789">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label small fw-semibold text-body-secondary">{{ __('Keterangan / Catatan Tambahan') }}</label>
                                <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Informasi tambahan mengenai investor, kesepakatan bagi hasil, dll.">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-4">
                            <a href="{{ route('investors.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm px-3">{{ __('Batal') }}</a>
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4">
                                <i class="fa-solid fa-floppy-disk me-1"></i>{{ __('Simpan Data Investor') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .investor-mode-option {
        display: flex;
        align-items: center;
        padding: 0.65rem 0.9rem;
        border: 1px solid var(--bs-border-color);
        border-radius: 0.6rem;
        background: var(--bs-body-bg);
        cursor: pointer;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    .investor-mode-option:hover {
        background-color: var(--bs-tertiary-bg);
    }
    .investor-mode-option .form-check-input {
        margin-top: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        function updateMode() {
            var mode = document.querySelector('input[name="mode"]:checked')?.value || 'new';
            var existingWrapper = document.getElementById('existingInvestorWrapper');
            var newFields = document.getElementById('newInvestorFields');

            if (mode === 'select') {
                if (existingWrapper) existingWrapper.style.display = '';
                if (newFields) newFields.style.display = 'none';
            } else {
                if (existingWrapper) existingWrapper.style.display = 'none';
                if (newFields) newFields.style.display = '';
            }
        }

        var modeNew = document.getElementById('mode_new');
        var modeSelect = document.getElementById('mode_select');
        if (modeNew) modeNew.addEventListener('change', updateMode);
        if (modeSelect) modeSelect.addEventListener('change', updateMode);

        updateMode();
    })();
</script>
@endpush
