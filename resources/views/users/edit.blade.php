@extends('layouts.app')

@section('title', __('Ubah Pengguna - ') . $user->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-user-pen text-primary me-2"></i>{{ __('Ubah Data Pengguna') }}
            </h4>
            <p class="text-body-secondary small mb-0">{{ __('Kelola profil, hak akses sistem, penempatan unit bisnis, dan konfigurasi payroll :name', ['name' => $user->name]) }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Daftar') }}
            </a>
            @if($user->idCardCode)
                <a href="{{ route('users.id-card', $user) }}" class="btn btn-outline-primary btn-sm shadow-sm" target="_blank">
                    <i class="fa-solid fa-id-badge me-1"></i> {{ __('Cetak ID Card') }}
                </a>
            @endif
        </div>
    </div>

    <!-- Form Section -->
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Section 1: Informasi Profil -->
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark">
                            <i class="fa-solid fa-address-card text-primary me-2"></i>{{ __('Informasi Profil Pengguna') }}
                        </h6>
                    </div>
                    <div class="card-body p-3 p-md-4 border-top">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label small fw-semibold text-body-secondary">{{ __('Nama Lengkap') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control form-control-sm @error('name') is-invalid @enderror" required placeholder="Contoh: Ahmad Fauzi">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="job_title" class="form-label small fw-semibold text-body-secondary">{{ __('Jabatan Pekerjaan') }}</label>
                                <input type="text" name="job_title" id="job_title" value="{{ old('job_title', $user->job_title) }}" class="form-control form-control-sm @error('job_title') is-invalid @enderror" maxlength="255" placeholder="{{ __('Contoh: Kasir Utama, Staff Operasional') }}">
                                <div class="form-text small">{{ __('Jabatan struktural atau fungsional operasional.') }}</div>
                                @error('job_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label small fw-semibold text-body-secondary">{{ __('Alamat Email') }}</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control form-control-sm @error('email') is-invalid @enderror" placeholder="user@perusahaan.com">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label small fw-semibold text-body-secondary">{{ __('Nomor HP / WhatsApp') }}</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="form-control form-control-sm @error('phone') is-invalid @enderror" placeholder="08xxxxxxxxxx">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Akses & Keamanan Sistem -->
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark">
                            <i class="fa-solid fa-shield-halved text-primary me-2"></i>{{ __('Akses & Keamanan Akun') }}
                        </h6>
                    </div>
                    <div class="card-body p-3 p-md-4 border-top">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="username" class="form-label small fw-semibold text-body-secondary">{{ __('Username Login') }} <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" class="form-control form-control-sm @error('username') is-invalid @enderror" required placeholder="username_login">
                                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="role_id" class="form-label small fw-semibold text-body-secondary">{{ __('Peran Sistem (Role)') }} <span class="text-danger">*</span></label>
                                <select name="role_id" id="role_id" class="form-select form-select-sm @error('role_id') is-invalid @enderror" required>
                                    <option value="">{{ __('-- Pilih Peran --') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->label }}</option>
                                    @endforeach
                                </select>
                                @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 mt-4">
                                <div class="p-3 bg-light rounded-3 border d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark small" for="is_active">
                                            {{ __('Status Akun Aktif (Dapat Login ke Sistem)') }}
                                        </label>
                                    </div>
                                    <div class="form-check mb-0 text-danger">
                                        <input type="checkbox" name="reset_default_password" id="reset_default_password" value="1" class="form-check-input">
                                        <label class="form-check-label fw-semibold small" for="reset_default_password">
                                            <i class="fa-solid fa-key me-1"></i> {{ __('Reset password ke default (12345678)') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Penempatan Kerja & Scope Bisnis -->
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark">
                            <i class="fa-solid fa-sitemap text-primary me-2"></i>{{ __('Penempatan Kerja & Hak Akses Bisnis') }}
                        </h6>
                    </div>
                    <div class="card-body p-3 p-md-4 border-top">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="company_id" class="form-label small fw-semibold text-body-secondary">{{ __('Perusahaan Induk') }} <span class="text-danger">*</span></label>
                                <select name="company_id" id="company_id" class="form-select form-select-sm @error('company_id') is-invalid @enderror" required>
                                    <option value="">{{ __('-- Pilih Perusahaan --') }}</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                                @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="company_branch_id" class="form-label small fw-semibold text-body-secondary">{{ __('Cabang / Outlet Penempatan') }}</label>
                                <select name="company_branch_id" id="company_branch_id" class="form-select form-select-sm @error('company_branch_id') is-invalid @enderror">
                                    <option value="">{{ __('Pilih Perusahaan terlebih dahulu') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('company_branch_id', $user->company_branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('company_branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label small fw-semibold text-body-secondary d-block mb-2">{{ __('Hak Akses Unit Bisnis (Business Units)') }}</label>
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="row g-2">
                                        @foreach($buOptions as $buCode => $buLabel)
                                            <div class="col-6 col-sm-4 col-md-3">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" name="bu[]" id="bu_{{ $buCode }}" value="{{ $buCode }}"
                                                        {{ in_array($buCode, $selectedBu) ? 'checked' : '' }}>
                                                    <label class="form-check-label small fw-medium text-dark" for="bu_{{ $buCode }}">{{ $buLabel }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('bu') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-md-6 mt-3">
                                <label for="department" class="form-label small fw-semibold text-body-secondary">{{ __('Departemen') }}</label>
                                <select name="department" id="department" class="form-select form-select-sm @error('department') is-invalid @enderror">
                                    <option value="">{{ __('— Pilih (opsional) —') }}</option>
                                    @foreach($departmentOptions as $deptCode => $deptLabel)
                                        <option value="{{ $deptCode }}" {{ $selectedDepartment === $deptCode ? 'selected' : '' }}>{{ $deptLabel }}</option>
                                    @endforeach
                                </select>
                                @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6 mt-3">
                                <label for="bu_level" class="form-label small fw-semibold text-body-secondary">{{ __('Level Operasional BU') }}</label>
                                <select name="bu_level" id="bu_level" class="form-select form-select-sm @error('bu_level') is-invalid @enderror">
                                    <option value="">{{ __('— Pilih (opsional) —') }}</option>
                                    @foreach($buLevelOptions as $lv)
                                        <option value="{{ $lv }}" {{ $selectedBuLevel === $lv ? 'selected' : '' }}>{{ ucwords(str_replace(['-', '_'], ' ', $lv)) }}</option>
                                    @endforeach
                                </select>
                                @error('bu_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Data Payroll & Rekening Bank -->
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark">
                            <i class="fa-solid fa-wallet text-primary me-2"></i>{{ __('Data Payroll & Rekening Bank') }}
                        </h6>
                    </div>
                    <div class="card-body p-3 p-md-4 border-top">
                        <div class="row g-3">
                            @if(\App\Support\ModuleGate::isEnabled('hr'))
                            <div class="col-12 mb-2">
                                <label for="attendance_card_code" class="form-label small fw-semibold text-body-secondary">{{ __('Kode Kartu Absensi (ID Card)') }}</label>
                                <input type="text" name="attendance_card_code" id="attendance_card_code" value="{{ old('attendance_card_code', $user->attendance_card_code) }}" class="form-control form-control-sm font-monospace @error('attendance_card_code') is-invalid @enderror" placeholder="Contoh: EMP001">
                                <div class="form-text small">{{ __('Digunakan sebagai rujukan barcode / QR Code mesin absensi kios.') }}</div>
                                @error('attendance_card_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @endif

                            <div class="col-12 col-md-6">
                                <label for="monthly_salary" class="form-label small fw-semibold text-body-secondary">{{ __('Gaji Pokok Bulanan (Rp)') }}</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="monthly_salary" id="monthly_salary" value="{{ old('monthly_salary', $user->monthly_salary) }}" class="form-control form-control-sm font-monospace @error('monthly_salary') is-invalid @enderror" min="0" placeholder="0">
                                </div>
                                @error('monthly_salary') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="daily_salary" class="form-label small fw-semibold text-body-secondary">{{ __('Gaji Harian (Rp)') }}</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="daily_salary" id="daily_salary" value="{{ old('daily_salary', $user->daily_salary) }}" class="form-control form-control-sm font-monospace @error('daily_salary') is-invalid @enderror" min="0" placeholder="0">
                                </div>
                                <div class="form-text small">{{ __('Otomatis terhitung jika gaji bulanan diisi (estimasi 26 hari kerja).') }}</div>
                                @error('daily_salary') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 col-md-4 mt-3">
                                <label for="bank_name" class="form-label small fw-semibold text-body-secondary">{{ __('Nama Bank') }}</label>
                                <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $user->bank_name) }}" class="form-control form-control-sm @error('bank_name') is-invalid @enderror" placeholder="BCA, Mandiri, BRI, dll.">
                                @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4 mt-3">
                                <label for="bank_account_number" class="form-label small fw-semibold text-body-secondary">{{ __('Nomor Rekening') }}</label>
                                <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}" class="form-control form-control-sm font-monospace @error('bank_account_number') is-invalid @enderror" placeholder="1234567890">
                                @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4 mt-3">
                                <label for="bank_account_name" class="form-label small fw-semibold text-body-secondary">{{ __('Atas Nama Rekening') }}</label>
                                <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name', $user->bank_account_name) }}" class="form-control form-control-sm @error('bank_account_name') is-invalid @enderror" placeholder="Nama Pemilik Rekening">
                                @error('bank_account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Simpan Perubahan') }}
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
        const monthlySalaryInput = document.getElementById('monthly_salary');
        const dailySalaryInput = document.getElementById('daily_salary');

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
                if (usernameInput.value === slugify(nameInput.defaultValue)) {
                    syncUsername();
                }
            });
        }

        if (companySelect && branchSelect) {
            const branchesByCompany = @json(
                $companies->mapWithKeys(fn ($c) => [
                    $c->id => $c->branches()->where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ])
            );
            const selectedOldBranch = @json(old('company_branch_id', $user->company_branch_id));

            const refreshBranches = () => {
                const companyId = parseInt(companySelect.value, 10) || null;
                branchSelect.innerHTML = '';
                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = companyId
                    ? '{{ __('Pilih Cabang (opsional)') }}'
                    : '{{ __('Pilih Perusahaan terlebih dahulu') }}';
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

        if (monthlySalaryInput && dailySalaryInput) {
            monthlySalaryInput.addEventListener('input', function() {
                const monthly = parseFloat(this.value) || 0;
                const workingDays = 26;
                const daily = Math.round(monthly / workingDays);
                dailySalaryInput.value = daily;
            });
        }
    });
</script>
@endpush
