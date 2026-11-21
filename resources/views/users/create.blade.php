@extends('layouts.app')

@section('title', __('Tambah Pengguna Baru'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-user-plus text-primary me-2"></i>{{ __('Tambah Pengguna Baru') }}
            </h4>
            <p class="text-body-secondary small mb-0">
                {{ __('Cukup isi data utama untuk membuat akun login. Pengaturan hak akses unit bisnis akan otomatis disesuaikan.') }}
            </p>
        </div>
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Daftar') }}
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <form action="{{ route('users.store') }}" method="POST" id="createUserForm">
                @csrf

                <!-- KARTU UTAMA: DATA INTI & AKSES LOGIN (RINGKAS & CEPAT) -->
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 fw-bold text-dark">
                            <i class="fa-solid fa-id-badge text-primary me-2"></i>{{ __('Data Akun & Hak Akses Utama') }}
                        </h6>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Wajib Diisi</span>
                    </div>
                    <div class="card-body p-3 p-md-4 border-top">
                        <div class="row g-3">
                            <!-- Nama Lengkap -->
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label small fw-semibold text-body-secondary">
                                    {{ __('Nama Lengkap') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                                       class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                       required placeholder="Contoh: Ahmad Fauzi" autofocus>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Username Login -->
                            <div class="col-12 col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="username" class="form-label small fw-semibold text-body-secondary mb-0">
                                        {{ __('Username Login') }} <span class="text-danger">*</span>
                                    </label>
                                    <span class="badge bg-light text-muted border small" style="font-size: 10px;">
                                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Auto
                                    </span>
                                </div>
                                <input type="text" name="username" id="username" value="{{ old('username') }}" 
                                       class="form-control form-control-sm font-monospace @error('username') is-invalid @enderror" 
                                       required placeholder="ahmad_fauzi">
                                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Peran Sistem (Role) -->
                            <div class="col-12 col-md-6">
                                <label for="role_id" class="form-label small fw-semibold text-body-secondary">
                                    {{ __('Peran Sistem (Role)') }} <span class="text-danger">*</span>
                                </label>
                                <select name="role_id" id="role_id" class="form-select form-select-sm @error('role_id') is-invalid @enderror" required>
                                    <option value="">{{ __('-- Pilih Peran / Posisi --') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text small">{{ __('Hak akses menu, modul, dan unit bisnis otomatis mengikuti peran ini.') }}</div>
                                @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Nomor WhatsApp / HP -->
                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label small fw-semibold text-body-secondary">
                                    {{ __('Nomor WhatsApp / HP') }}
                                </label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" 
                                       class="form-control form-control-sm @error('phone') is-invalid @enderror" 
                                       placeholder="Contoh: 081234567890">
                                <div class="form-text small">{{ __('Digunakan untuk kirim detail akun & kredensial login via WA.') }}</div>
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Email (Opsional) -->
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label small fw-semibold text-body-secondary">
                                    {{ __('Alamat Email') }} <span class="text-muted fw-normal">({{ __('Opsional') }})</span>
                                </label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" 
                                       class="form-control form-control-sm @error('email') is-invalid @enderror" 
                                       placeholder="nama@perusahaan.com">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Jabatan Tambahan (Opsional) -->
                            <div class="col-12 col-md-6">
                                <label for="job_title" class="form-label small fw-semibold text-body-secondary">
                                    {{ __('Keterangan Jabatan') }} <span class="text-muted fw-normal">({{ __('Opsional') }})</span>
                                </label>
                                <input type="text" name="job_title" id="job_title" value="{{ old('job_title') }}" 
                                       class="form-control form-control-sm @error('job_title') is-invalid @enderror" 
                                       placeholder="{{ __('Otomatis mengikuti peran jika dikosongkan') }}">
                                @error('job_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Informasi Password & Status Akun -->
                            <div class="col-12 pt-2">
                                <div class="p-3 bg-light rounded-3 border d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark small" for="is_active">
                                            {{ __('Status Akun Aktif (Dapat Login)') }}
                                        </label>
                                    </div>
                                    <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
                                        <i class="fa-solid fa-key me-1"></i> {{ __('Password awal default: Password123!') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACCORDION: PENGATURAN LANJUTAN (OPSIONAL) -->
                @php
                    $hasAdvancedErrors = $errors->has('company_id') || $errors->has('company_branch_id') || 
                                         $errors->has('bu') || $errors->has('bu_level') || $errors->has('department') || 
                                         $errors->has('bank_name') || $errors->has('bank_account_number');
                @endphp

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <button class="btn btn-link text-decoration-none p-0 w-100 d-flex justify-content-between align-items-center text-dark" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#advancedSettingsCollapse" 
                                aria-expanded="{{ $hasAdvancedErrors ? 'true' : 'false' }}" aria-controls="advancedSettingsCollapse">
                            <span class="fw-bold small d-flex align-items-center">
                                <i class="fa-solid fa-sliders text-secondary me-2"></i>
                                {{ __('Pengaturan Lanjutan (Penempatan Cabang, Scope BU Khusus, & Payroll)') }}
                                <span class="badge bg-secondary-subtle text-secondary border ms-2 small">Opsional</span>
                            </span>
                            <i class="fa-solid fa-chevron-down text-muted transition-icon" id="advancedCollapseIcon"></i>
                        </button>
                    </div>

                    <div class="collapse {{ $hasAdvancedErrors ? 'show' : '' }}" id="advancedSettingsCollapse">
                        <div class="card-body p-3 p-md-4 border-top bg-light bg-opacity-25">
                            <div class="row g-3">
                                <!-- Penempatan Perusahaan & Cabang -->
                                <div class="col-12 col-md-6">
                                    <label for="company_id" class="form-label small fw-semibold text-body-secondary">{{ __('Perusahaan Induk') }}</label>
                                    <select name="company_id" id="company_id" class="form-select form-select-sm @error('company_id') is-invalid @enderror">
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id', $defaultCompanyId ?? '') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="company_branch_id" class="form-label small fw-semibold text-body-secondary">{{ __('Cabang / Outlet Penempatan') }}</label>
                                    <select name="company_branch_id" id="company_branch_id" class="form-select form-select-sm @error('company_branch_id') is-invalid @enderror">
                                        <option value="">{{ __('Semua Cabang / Sesuai Perusahaan') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('company_branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Scope Unit Bisnis Khusus -->
                                <div class="col-12 mt-3">
                                    <label class="form-label small fw-semibold text-body-secondary d-block mb-1">
                                        {{ __('Kustomisasi Scope Unit Bisnis (Opsional)') }}
                                    </label>
                                    <div class="p-3 bg-white rounded-3 border">
                                        <div class="form-text small mb-2 text-muted">
                                            <i class="fa-solid fa-circle-info me-1"></i>{{ __('Biarkan kosong jika ingin otomatis mengikuti bawaan peran sistem.') }}
                                        </div>
                                        <div class="row g-2">
                                            @foreach($buOptions as $buCode => $buLabel)
                                                <div class="col-6 col-sm-3">
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" name="bu[]" id="bu_{{ $buCode }}" value="{{ $buCode }}"
                                                            {{ in_array($buCode, (array) old('bu', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label small fw-medium text-dark" for="bu_{{ $buCode }}">{{ $buLabel }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('bu') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <!-- Departemen & Level BU -->
                                <div class="col-12 col-md-6 mt-3">
                                    <label for="department" class="form-label small fw-semibold text-body-secondary">{{ __('Departemen') }}</label>
                                    <select name="department" id="department" class="form-select form-select-sm @error('department') is-invalid @enderror">
                                        <option value="">{{ __('Otomatis sesuai peran') }}</option>
                                        @foreach($departmentOptions as $deptCode => $deptLabel)
                                            <option value="{{ $deptCode }}" {{ old('department') === $deptCode ? 'selected' : '' }}>{{ $deptLabel }}</option>
                                        @endforeach
                                    </select>
                                    @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6 mt-3">
                                    <label for="bu_level" class="form-label small fw-semibold text-body-secondary">{{ __('Tingkat Operasional (Level)') }}</label>
                                    <select name="bu_level" id="bu_level" class="form-select form-select-sm @error('bu_level') is-invalid @enderror">
                                        <option value="">{{ __('Otomatis sesuai peran') }}</option>
                                        @foreach($buLevelOptions as $lv)
                                            <option value="{{ $lv }}" {{ old('bu_level') === $lv ? 'selected' : '' }}>{{ ucwords(str_replace(['-', '_'], ' ', $lv)) }}</option>
                                        @endforeach
                                    </select>
                                    @error('bu_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Bagian Presensi & Profil Kepegawaian -->
                                <div class="col-12 mt-3">
                                    <hr class="my-2 border-secondary opacity-25">
                                    <h6 class="fw-bold small text-dark mt-2 mb-3">
                                        <i class="fa-solid fa-id-card-clip text-secondary me-1"></i>{{ __('Pendaftaran Karyawan & Presensi (Opsional)') }}
                                    </h6>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch" name="register_as_employee" id="register_as_employee" value="1" {{ old('register_as_employee') ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold text-dark small" for="register_as_employee">
                                                {{ __('Daftarkan Akun ini ke Data Karyawan (Profil HRD)') }}
                                            </label>
                                        </div>
                                        <div class="text-muted small mt-1 ps-4">
                                            {{ __('Centang jika akun ini adalah pegawai perusahaan yang digaji. Jika tidak dicentang (misal akun Pemilik, Direksi, Investor, Auditor, atau Vendor), akun ini HANYA menjadi akun login dan TIDAK akan masuk ke daftar Karyawan.') }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mb-2">
                                    <div class="alert alert-light border rounded-3 p-3 text-muted small d-flex align-items-center gap-2 mb-0">
                                        <i class="fa-solid fa-circle-info text-primary"></i>
                                        <span>{{ __('Pengisian Gaji Pokok & Kompensasi dikelola terpusat di menu Data Karyawan. Akun pengguna ini dapat ditautkan ke data karyawan setelah dibuat.') }}</span>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4 mt-2">
                                    <label for="bank_name" class="form-label small fw-semibold text-body-secondary">{{ __('Nama Bank') }}</label>
                                    <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" class="form-control form-control-sm @error('bank_name') is-invalid @enderror" placeholder="BCA, Mandiri, BRI, dll.">
                                    @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-4 mt-2">
                                    <label for="bank_account_number" class="form-label small fw-semibold text-body-secondary">{{ __('Nomor Rekening') }}</label>
                                    <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number') }}" class="form-control form-control-sm font-monospace @error('bank_account_number') is-invalid @enderror" placeholder="1234567890">
                                    @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-4 mt-2">
                                    <label for="bank_account_name" class="form-label small fw-semibold text-body-secondary">{{ __('Atas Nama Rekening') }}</label>
                                    <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name') }}" class="form-control form-control-sm @error('bank_account_name') is-invalid @enderror" placeholder="Nama Pemilik Rekening">
                                    @error('bank_account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 mb-4">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm px-4">
                        {{ __('Batal') }}
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Simpan Pengguna') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('name');
        const usernameInput = document.getElementById('username');
        const companySelect = document.getElementById('company_id');
        const branchSelect = document.getElementById('company_branch_id');

        // Auto Slug Name -> Username
        if (nameInput && usernameInput) {
            const slugify = (value) => value
                .toLowerCase()
                .normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');

            const syncUsername = () => {
                usernameInput.value = slugify(nameInput.value);
            };

            nameInput.addEventListener('input', function() {
                if (usernameInput.value === '' || usernameInput.dataset.autoSynced === 'true') {
                    usernameInput.dataset.autoSynced = 'true';
                    syncUsername();
                }
            });

            usernameInput.addEventListener('input', function() {
                usernameInput.dataset.autoSynced = 'false';
            });
        }

        // Auto Branch filtering by Company
        if (companySelect && branchSelect) {
            const branchesByCompany = @json(
                $companies->mapWithKeys(fn ($c) => [
                    $c->id => $c->branches()->where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ])
            );
            const selectedOldBranch = @json(old('company_branch_id'));

            const refreshBranches = () => {
                const companyId = parseInt(companySelect.value, 10) || null;
                branchSelect.innerHTML = '';
                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = '{{ __('Semua Cabang / Sesuai Perusahaan') }}';
                branchSelect.appendChild(defaultOpt);

                if (companyId && branchesByCompany[companyId]) {
                    branchesByCompany[companyId].forEach(function (branch) {
                        const opt = document.createElement('option');
                        opt.value = branch.id;
                        opt.textContent = branch.name;
                        if (String(selectedOldBranch) === String(branch.id)) {
                            opt.selected = true;
                        }
                        branchSelect.appendChild(opt);
                    });
                }
            };

            companySelect.addEventListener('change', refreshBranches);
            refreshBranches();
        }

        // Accordion arrow animation
        const collapseEl = document.getElementById('advancedSettingsCollapse');
        const collapseIcon = document.getElementById('advancedCollapseIcon');
        if (collapseEl && collapseIcon) {
            collapseEl.addEventListener('show.bs.collapse', function () {
                collapseIcon.classList.remove('fa-chevron-down');
                collapseIcon.classList.add('fa-chevron-up');
            });
            collapseEl.addEventListener('hide.bs.collapse', function () {
                collapseIcon.classList.remove('fa-chevron-up');
                collapseIcon.classList.add('fa-chevron-down');
            });
        }
    });
</script>
@endpush
