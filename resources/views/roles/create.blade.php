@extends('layouts.app')

@section('title', __('Tambah Peran Baru'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-user-shield text-primary me-2"></i>{{ __('Tambah Peran Baru') }}
            </h4>
            <p class="text-body-secondary small mb-0">{{ __('Tentukan nama peran pengguna dan pilih template hak akses modul untuk keamanan sistem.') }}</p>
        </div>
        <div>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Daftar') }}
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <!-- Section 1: Identitas & Template Peran -->
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark">
                            <i class="fa-solid fa-id-badge text-primary me-2"></i>{{ __('Identitas & Template Peran') }}
                        </h6>
                    </div>
                    <div class="card-body p-3 p-md-4 border-top">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="label_select" class="form-label small fw-semibold text-body-secondary">
                                    {{ __('Pilih Template Peran (Rekomendasi)') }}
                                </label>
                                <select id="label_select" class="form-select form-select-sm">
                                    <option value="">{{ __('-- Pilih Template Standar --') }}</option>
                                    @foreach($standardPermissions as $roleName => $perms)
                                        <option value="{{ $roleName }}">{{ $roleName }}</option>
                                    @endforeach
                                    <option value="Custom">{{ __('Kustom / Peran Lainnya...') }}</option>
                                </select>
                                <div class="form-text small">{{ __('Memilih template akan otomatis mencentang paket hak akses standar yang aman.') }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="label" class="form-label small fw-semibold text-body-secondary">
                                    {{ __('Nama Peran (Label)') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="label" name="label" class="form-control form-control-sm @error('label') is-invalid @enderror" placeholder="{{ __('Contoh: Kasir POS Wash, Supervisor Operasional') }}" value="{{ old('label') }}" required>
                                @error('label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text small">{{ __('Kode sistem (slug) otomatis dibuat dari nama peran ini.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Hak Akses Modul (Permissions) -->
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark">
                            <i class="fa-solid fa-key text-primary me-2"></i>{{ __('Hak Akses Modul (Permissions)') }}
                        </h6>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 small">
                            <i class="fa-solid fa-circle-info me-1"></i> Centang izin yang boleh diakses
                        </span>
                    </div>
                    <div class="card-body p-3 p-md-4 border-top">
                        @if(empty($filteredPermissions))
                            <div class="alert alert-info mb-0">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                {{ __('Tidak ada izin akses yang tersedia untuk diberikan.') }}
                            </div>
                        @else
                            <ul class="nav nav-pills mb-3 gap-1" role="tablist">
                                @foreach($filteredPermissions as $tab => $groups)
                                    @php $tid = \Illuminate\Support\Str::slug($tab); @endphp
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link btn-sm {{ $loop->first ? 'active' : '' }}" id="tab-{{ $tid }}" data-bs-toggle="tab" data-bs-target="#pane-{{ $tid }}" type="button" role="tab" aria-controls="pane-{{ $tid }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $tab }}</button>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content border rounded-3 p-3 bg-light bg-opacity-50">
                                @foreach($filteredPermissions as $tab => $groups)
                                    @php $tid = \Illuminate\Support\Str::slug($tab); @endphp
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pane-{{ $tid }}" role="tabpanel" aria-labelledby="tab-{{ $tid }}">
                                        @foreach($groups as $group => $perms)
                                            <div class="card mb-3 border rounded-3 permission-group shadow-none">
                                                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                                    <h6 class="mb-0 fw-semibold text-dark small">
                                                        <i class="fa-regular fa-folder-open text-primary me-1"></i> {{ $group }}
                                                    </h6>
                                                    <div class="form-check m-0">
                                                        <input type="checkbox" class="form-check-input group-checkbox" id="grp_{{ $tid }}_{{ $loop->index }}" onchange="toggleGroup(this)">
                                                        <label class="form-check-label small text-muted" for="grp_{{ $tid }}_{{ $loop->index }}">{{ __('Pilih Semua') }}</label>
                                                    </div>
                                                </div>
                                                <div class="card-body p-3">
                                                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
                                                        @foreach($perms as $permission)
                                                            <div class="col">
                                                                <div class="form-check">
                                                                    <input id="perm_{{ $permission->id }}" name="permissions[]" type="checkbox" value="{{ $permission->id }}" class="form-check-input permission-checkbox" @if(in_array($permission->id, old('permissions', []))) checked @endif>
                                                                    <label for="perm_{{ $permission->id }}" class="form-check-label small text-body">{{ $permission->label }}</label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm px-4">
                        {{ __('Batal') }}
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4" {{ empty($filteredPermissions) ? 'disabled' : '' }}>
                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Simpan Peran Baru') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleGroup(source) {
        const group = source.closest('.permission-group');
        const checkboxes = group.querySelectorAll('.permission-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const standardPermissions = @json($standardPermissions);
        const labelSelect = document.getElementById('label_select');
        const labelInput = document.getElementById('label');

        if (labelSelect && labelInput) {
            labelSelect.addEventListener('change', function() {
                const selectedRole = this.value;
                
                if (selectedRole === 'Custom') {
                    labelInput.value = '';
                    labelInput.focus();
                } else if (selectedRole) {
                    labelInput.value = selectedRole;
                    
                    if (standardPermissions[selectedRole]) {
                        // Uncheck all first
                        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
                        document.querySelectorAll('.group-checkbox').forEach(cb => cb.checked = false);
                        
                        // Check relevant ones
                        const ids = standardPermissions[selectedRole];
                        ids.forEach(id => {
                            const cb = document.getElementById('perm_' + id);
                            if (cb) cb.checked = true;
                        });
                        
                        // Update group checkboxes
                        document.querySelectorAll('.permission-group').forEach(group => {
                            const checkboxes = group.querySelectorAll('.permission-checkbox');
                            const groupCheckbox = group.querySelector('.group-checkbox');
                            if (checkboxes.length > 0 && groupCheckbox) {
                                const allChecked = Array.from(checkboxes).every(c => c.checked);
                                groupCheckbox.checked = allChecked;
                            }
                        });
                    }
                }
            });
        }
    });
</script>
@endsection
