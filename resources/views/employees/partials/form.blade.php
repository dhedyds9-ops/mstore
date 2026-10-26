<div class="row g-4">
    <!-- SECTION 1: DATA PERSONAL & KONTAK -->
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="m-0 fw-bold text-dark">
                    <i class="fa-solid fa-user text-primary me-2"></i>{{ __('Data Personal & Kontak Karyawan') }}
                </h6>
            </div>
            <div class="card-body p-3 p-md-4 border-top">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="full_name" class="form-label small fw-semibold text-body-secondary">{{ __('Nama Lengkap') }} <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="full_name" class="form-control form-control-sm @error('full_name') is-invalid @enderror" value="{{ old('full_name', $employee->full_name ?? '') }}" placeholder="Contoh: Budi Santoso" required>
                        @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="nik" class="form-label small fw-semibold text-body-secondary">{{ __('Nomor Induk Kependudukan (NIK)') }} <span class="text-danger">*</span></label>
                        <input type="text" name="nik" id="nik" class="form-control form-control-sm font-monospace @error('nik') is-invalid @enderror" value="{{ old('nik', $employee->nik ?? '') }}" placeholder="16 digit NIK KTP" required>
                        @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label small fw-semibold text-body-secondary">{{ __('Alamat Email') }} <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control form-control-sm @error('email') is-invalid @enderror" value="{{ old('email', $employee->email ?? '') }}" placeholder="karyawan@perusahaan.com" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="phone" class="form-label small fw-semibold text-body-secondary">{{ __('Nomor HP / WhatsApp') }} <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control form-control-sm @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone ?? '') }}" placeholder="08xxxxxxxxxx" required>
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="date_of_birth" class="form-label small fw-semibold text-body-secondary">{{ __('Tanggal Lahir') }} <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" id="date_of_birth" class="form-control form-control-sm @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', isset($employee?->date_of_birth) ? $employee?->date_of_birth->format('Y-m-d') : '') }}" required>
                        @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="gender" class="form-label small fw-semibold text-body-secondary">{{ __('Jenis Kelamin') }} <span class="text-danger">*</span></label>
                        <select name="gender" id="gender" class="form-select form-select-sm @error('gender') is-invalid @enderror" required>
                            <option value="Laki-laki" {{ old('gender', $employee->gender ?? '') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender', $employee->gender ?? '') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label small fw-semibold text-body-secondary">{{ __('Alamat Lengkap Domisili') }} <span class="text-danger">*</span></label>
                        <textarea name="address" id="address" rows="3" class="form-control form-control-sm @error('address') is-invalid @enderror" placeholder="Alamat lengkap domisili saat ini" required>{{ old('address', $employee->address ?? '') }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: INFORMASI PEKERJAAN & PENEMPATAN -->
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="m-0 fw-bold text-dark">
                    <i class="fa-solid fa-briefcase text-primary me-2"></i>{{ __('Informasi Pekerjaan, Jabatan & Cuti') }}
                </h6>
            </div>
            <div class="card-body p-3 p-md-4 border-top">
                @php
                    $positionOptions = $positions ?? \App\Services\EmployeeSyncService::allPositions();
                    $deptOptions = $departments ?? \App\Services\EmployeeSyncService::allDepartments();
                @endphp
                <div class="row g-3">
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="position" class="form-label small fw-semibold text-body-secondary">{{ __('Jabatan Pekerjaan') }} <span class="text-danger">*</span></label>
                        <select name="position" id="position" class="form-select form-select-sm @error('position') is-invalid @enderror" required>
                            @php
                                $pos = old('position', $employee->position ?? '');
                                $groupedPos = \App\Services\EmployeeSyncService::groupedPositions();
                            @endphp
                            <option value="">{{ __('-- Pilih Jabatan --') }}</option>
                            @foreach($groupedPos as $grpLabel => $grpList)
                                <optgroup label="{{ $grpLabel }}">
                                    @foreach($grpList as $p)
                                        <option value="{{ $p }}" {{ $pos === $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                            @if($pos && !in_array($pos, $positionOptions))
                                <option value="{{ $pos }}" selected>{{ $pos }}</option>
                            @endif
                        </select>
                        @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="department" class="form-label small fw-semibold text-body-secondary">{{ __('Departemen / Divisi') }} <span class="text-danger">*</span></label>
                        <select name="department" id="department" class="form-select form-select-sm @error('department') is-invalid @enderror" required>
                            @php $dep = old('department', $employee->department ?? ''); @endphp
                            <option value="">{{ __('-- Pilih Departemen --') }}</option>
                            @foreach($deptOptions as $d)
                                <option value="{{ $d }}" {{ $dep === $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                            @if($dep && !in_array($dep, $deptOptions))
                                <option value="{{ $dep }}" selected>{{ $dep }}</option>
                            @endif
                        </select>
                        @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="join_date" class="form-label small fw-semibold text-body-secondary">{{ __('Tanggal Masuk Kerja') }} <span class="text-danger">*</span></label>
                        <input type="date" name="join_date" id="join_date" class="form-control form-control-sm @error('join_date') is-invalid @enderror" value="{{ old('join_date', isset($employee?->join_date) ? $employee?->join_date->format('Y-m-d') : '') }}" required>
                        @error('join_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="employment_status" class="form-label small fw-semibold text-body-secondary">{{ __('Status Karyawan') }} <span class="text-danger">*</span></label>
                        <select name="employment_status" id="employment_status" class="form-select form-select-sm @error('employment_status') is-invalid @enderror" required>
                            @foreach(['Tetap','Training'] as $s)
                                <option value="{{ $s }}" {{ old('employment_status', $employee->employment_status ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('employment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="annual_leave_quota" class="form-label small fw-semibold text-body-secondary">{{ __('Jatah Cuti Tahunan (Hari)') }}</label>
                        <input type="number" name="annual_leave_quota" id="annual_leave_quota" value="{{ old('annual_leave_quota', $employee->annual_leave_quota ?? 12) }}" class="form-control form-control-sm font-monospace @error('annual_leave_quota') is-invalid @enderror" min="0">
                        <div class="form-text small">{{ __('Total jatah hak cuti tahunan karyawan.') }}</div>
                        @error('annual_leave_quota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="sick_leave_quota" class="form-label small fw-semibold text-body-secondary">{{ __('Jatah Izin Sakit (Hari)') }}</label>
                        <input type="number" name="sick_leave_quota" id="sick_leave_quota" value="{{ old('sick_leave_quota', $employee->sick_leave_quota ?? 14) }}" class="form-control form-control-sm font-monospace @error('sick_leave_quota') is-invalid @enderror" min="0">
                        <div class="form-text small">{{ __('Batas toleransi izin sakit dalam 1 tahun buku.') }}</div>
                        @error('sick_leave_quota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="wash_employee_id" class="form-label small fw-semibold text-body-secondary">{{ __('Integrasi Operator Cuci (Wash Employee)') }}</label>
                        <select name="wash_employee_id" id="wash_employee_id" class="form-select form-select-sm @error('wash_employee_id') is-invalid @enderror">
                            <option value="">{{ __('-- Tidak Terkait Unit Cuci Kendaraan --') }}</option>
                            @foreach(($washEmployees ?? []) as $we)
                                <option value="{{ $we->id }}" {{ (string) old('wash_employee_id', $employee->wash_employee_id ?? '') === (string) $we->id ? 'selected' : '' }}>
                                    {{ $we->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">{{ __('Pilih jika karyawan ini juga bertugas sebagai staf cuci di unit usaha car/motor wash.') }}</div>
                        @error('wash_employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: DATA PAYROLL & REKENING BANK -->
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="m-0 fw-bold text-dark">
                    <i class="fa-solid fa-wallet text-primary me-2"></i>{{ __('Data Payroll & Rekening Bank') }}
                </h6>
            </div>
            <div class="card-body p-3 p-md-4 border-top">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="monthly_salary" class="form-label small fw-semibold text-body-secondary">{{ __('Gaji Pokok Bulanan (Rp)') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="monthly_salary" id="monthly_salary" value="{{ old('monthly_salary', $employee->monthly_salary ?? 0) }}" class="form-control form-control-sm font-monospace @error('monthly_salary') is-invalid @enderror" min="0" placeholder="0">
                        </div>
                        <div class="form-text small">{{ __('Gaji pokok satu bulan.') }}</div>
                        @error('monthly_salary') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="daily_salary" class="form-label small fw-semibold text-body-secondary">{{ __('Gaji Harian (Rp)') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="daily_salary" id="daily_salary" value="{{ old('daily_salary', $employee->daily_salary ?? 0) }}" class="form-control form-control-sm font-monospace @error('daily_salary') is-invalid @enderror" min="0" placeholder="0">
                        </div>
                        <div class="form-text small">{{ __('Otomatis terhitung dari gaji bulanan atau nilai manual.') }}</div>
                        @error('daily_salary') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="attendance_card_code" class="form-label small fw-semibold text-body-secondary">{{ __('Kode ID Card Absensi') }}</label>
                        <input type="text" name="attendance_card_code" id="attendance_card_code" value="{{ old('attendance_card_code', $employee->attendance_card_code ?? '') }}" class="form-control form-control-sm font-monospace @error('attendance_card_code') is-invalid @enderror" placeholder="Contoh: EMP001">
                        <div class="form-text small">{{ __('Rujukan barcode / QR Code mesin kios absensi.') }}</div>
                        @error('attendance_card_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4 mt-3">
                        <label for="bank_name" class="form-label small fw-semibold text-body-secondary">{{ __('Nama Bank') }}</label>
                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $employee->bank_name ?? '') }}" class="form-control form-control-sm @error('bank_name') is-invalid @enderror" placeholder="BCA, Mandiri, BRI, dll.">
                        @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4 mt-3">
                        <label for="bank_account_number" class="form-label small fw-semibold text-body-secondary">{{ __('Nomor Rekening') }}</label>
                        <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $employee->bank_account_number ?? '') }}" class="form-control form-control-sm font-monospace @error('bank_account_number') is-invalid @enderror" placeholder="1234567890">
                        @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4 mt-3">
                        <label for="bank_account_name" class="form-label small fw-semibold text-body-secondary">{{ __('Atas Nama Rekening') }}</label>
                        <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name', $employee->bank_account_name ?? '') }}" class="form-control form-control-sm @error('bank_account_name') is-invalid @enderror" placeholder="Nama Pemilik Rekening">
                        @error('bank_account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 4: AKSES AKUN LOGIN SISTEM (OPSIONAL) -->
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark">
                    <i class="fa-solid fa-shield-halved text-primary me-2"></i>{{ __('Akses Akun Login Sistem') }}
                </h6>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 small">
                    Opsional
                </span>
            </div>
            <div class="card-body p-3 p-md-4 border-top">
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" name="create_user_account" id="create_user_account" value="1" {{ old('create_user_account', $employee?->user_id ? '1' : '') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark small" for="create_user_account">
                            {{ $employee?->user_id ? __('Perbarui / Aktifkan Akun Login Sistem') : __('Aktifkan Akun Login untuk Karyawan Ini') }}
                        </label>
                    </div>
                </div>

                <div id="user_account_fields" style="display: {{ old('create_user_account', $employee?->user_id ? '1' : '') ? 'block' : 'none' }};">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="username" class="form-label small fw-semibold text-body-secondary">{{ __('Username Login') }} <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="username" class="form-control form-control-sm @error('username') is-invalid @enderror" value="{{ old('username', $employee->user?->username ?? '') }}" placeholder="username_karyawan">
                            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="password" class="form-label small fw-semibold text-body-secondary">{{ __('Password') }} {{ $employee?->user_id ? __('(Kosongkan jika tidak diubah)') : '*' }}</label>
                            <input type="password" name="password" id="password" class="form-control form-control-sm @error('password') is-invalid @enderror" placeholder="{{ $employee?->user_id ? '••••••••' : 'Minimal 8 karakter' }}">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="role_id" class="form-label small fw-semibold text-body-secondary">{{ __('Peran Sistem (Role)') }} <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-select form-select-sm @error('role_id') is-invalid @enderror">
                                <option value="">{{ __('-- Pilih Peran --') }}</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-label="{{ $role->label }}" data-name="{{ $role->name }}" {{ (string) old('role_id', $employee->user?->role_id ?? '') === (string) $role->id ? 'selected' : '' }}>
                                        {{ $role->label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mt-3 pt-3 border-top">
                            <label for="user_id" class="form-label small fw-semibold text-body-secondary mb-1">
                                <i class="fa-solid fa-link me-1"></i> {{ __('Atau Hubungkan dengan User Sistem yang Sudah Ada') }}
                            </label>
                            <select name="user_id" id="user_id" class="form-select form-select-sm">
                                <option value="">{{ __('-- Cari Akun User Terdaftar --') }}</option>
                                @foreach(($users ?? []) as $u)
                                    <option value="{{ $u->id }}" data-role-label="{{ $u->role?->label ?? $u->role?->name }}" data-role-name="{{ $u->role?->name }}" {{ (string) old('user_id', $employee?->user_id ?? '') === (string) $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->username }}) &bull; {{ $u->role?->label ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">{{ __('Gunakan opsi ini jika akun user login telah dibuat sebelumnya melalui modul Manajemen Pengguna.') }}</div>
                        </div>
                    </div>
                </div>

                <div id="no_account_msg" class="text-center py-4 text-body-secondary small" style="display: {{ old('create_user_account', $employee?->user_id ? '1' : '') ? 'none' : 'block' }};">
                    <i class="fa-solid fa-user-lock fa-2x mb-2 d-block opacity-25"></i>
                    {{ __('Karyawan ini tidak memiliki akun login ke sistem.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 5: BERKAS & PAS FOTO ID CARD -->
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="m-0 fw-bold text-dark">
                    <i class="fa-solid fa-file-invoice text-primary me-2"></i>{{ __('Berkas, Pas Foto & Dokumen Kontrak') }}
                </h6>
            </div>
            <div class="card-body p-3 p-md-4 border-top">
                <div class="row g-4">
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold text-body-secondary d-block mb-2">{{ __('Pas Foto ID Card (Rasio 1:1)') }}</label>
                        <div class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-light">
                            <div id="photoPreviewContainer" class="{{ !empty($employee?->id_card_photo_path) ? '' : 'd-none' }}">
                                <img id="employeePhotoPreview" src="{{ !empty($employee?->id_card_photo_path) ? asset('storage/'.$employee?->id_card_photo_path) : '' }}" class="rounded-3 shadow-sm border" style="width: 72px; height: 72px; object-fit: cover;">
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" name="id_card_photo" id="employee_id_card_photo" class="form-control form-control-sm" accept="image/*">
                                <input type="hidden" name="id_card_photo_base64" id="employee_id_card_photo_base64">
                                <div class="small text-body-secondary mt-1">{{ __('Format: JPG, PNG. Rekomendasi: Persegi (1:1).') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="id_card_expires_at" class="form-label small fw-semibold text-body-secondary">{{ __('Masa Berlaku ID Card') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-calendar-day"></i></span>
                            <input type="date" name="id_card_expires_at" id="id_card_expires_at" class="form-control form-control-sm" value="{{ old('id_card_expires_at', isset($employee?->id_card_expires_at) ? $employee?->id_card_expires_at->format('Y-m-d') : '') }}">
                        </div>
                        <div class="form-text small">{{ __('Kosongkan jika kartu berlaku selamanya.') }}</div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="document" class="form-label small fw-semibold text-body-secondary">{{ __('Dokumen Kontrak / SK Kerja') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-file-pdf"></i></span>
                            <input type="file" name="document" id="document" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
                        </div>
                        <div class="form-text small">{{ __('Format: PDF, DOC, DOCX. Maksimal 5MB.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="col-12">
        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm px-4">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4">
                <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Simpan Data Karyawan') }}
            </button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<div class="modal fade" id="employeePhotoCropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 py-3">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-crop me-2 text-primary"></i>{{ __('Atur & Potong Foto ID Card') }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div style="max-height: 65vh;" class="overflow-hidden rounded-3">
                    <img id="employeeCropImage" src="" style="max-width: 100%; display: block;">
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                <button type="button" class="btn btn-primary btn-sm shadow-sm px-4" id="employeeCropApplyBtn">
                    <i class="fa-solid fa-check me-1"></i> {{ __('Gunakan Foto Ini') }}
                </button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

@php
    $rolePosMap = collect(\App\Services\EmployeeSyncService::positionRoleDepartmentMap())
        ->mapWithKeys(fn ($item) => [strtolower($item['role']) => ['position' => $item['position'], 'department' => $item['department']]])
        ->all();
    $posDeptMap = $positionDeptMap ?? collect(\App\Services\EmployeeSyncService::positionRoleDepartmentMap())
        ->mapWithKeys(fn ($item) => [$item['position'] => $item['department']])
        ->unique()
        ->all();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    const createUserCheck = document.getElementById('create_user_account');
    const userFields = document.getElementById('user_account_fields');
    const noAccountMsg = document.getElementById('no_account_msg');
    const fullNameInput = document.getElementById('full_name');
    const usernameInput = document.getElementById('username');
    const roleSelect = document.getElementById('role_id');
    const userSelect = document.getElementById('user_id');
    const posSelect = document.getElementById('position');
    const depSelect = document.getElementById('department');
    const monthlySalaryInput = document.getElementById('monthly_salary');
    const dailySalaryInput = document.getElementById('daily_salary');
    const workingDays = {{ \App\Models\Setting::getValue('attendance_working_days', 26) }};

    const ROLE_TO_POS_DEPT = @json($rolePosMap);
    const POS_TO_DEPT = @json($posDeptMap);

    function setSelectValue(selectEl, value) {
        if (!selectEl || !value) return;
        for (let i = 0; i < selectEl.options.length; i++) {
            if (selectEl.options[i].value === value) {
                selectEl.selectedIndex = i;
                return true;
            }
        }
        return false;
    }

    function setDepartmentByPosition(position) {
        if (!depSelect || !position) return;
        const dept = POS_TO_DEPT[position] || 'Operasional';
        if (!setSelectValue(depSelect, dept)) {
            const opt = new Option(dept, dept, true, true);
            depSelect.add(opt);
        }
    }

    if (createUserCheck) {
        createUserCheck.addEventListener('change', function() {
            if (userFields) userFields.style.display = this.checked ? 'block' : 'none';
            if (noAccountMsg) noAccountMsg.style.display = this.checked ? 'none' : 'block';
            
            if (this.checked && usernameInput && !usernameInput.value && fullNameInput && fullNameInput.value) {
                usernameInput.value = fullNameInput.value.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 20);
            }
        });
    }

    if (fullNameInput) {
        fullNameInput.addEventListener('blur', function() {
            if (createUserCheck && createUserCheck.checked && usernameInput && !usernameInput.value) {
                usernameInput.value = this.value.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 20);
            }
        });
    }

    if (posSelect) {
        posSelect.addEventListener('change', function() {
            setDepartmentByPosition(this.value);
        });
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                const label = opt.getAttribute('data-label');
                const role = (opt.getAttribute('data-name') || '').toLowerCase();
                autoFillJobDept(label, role);
            }
        });
    }

    if (userSelect) {
        userSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                const label = opt.getAttribute('data-role-label');
                const role = (opt.getAttribute('data-role-name') || '').toLowerCase();
                autoFillJobDept(label, role);
            }
        });
    }

    function autoFillJobDept(label, role) {
        let targetPosition = label;
        let targetDept = 'Operasional';

        if (role && ROLE_TO_POS_DEPT[role]) {
            targetPosition = ROLE_TO_POS_DEPT[role].position;
            targetDept = ROLE_TO_POS_DEPT[role].department;
        }

        if (targetPosition && posSelect) {
            if (!setSelectValue(posSelect, targetPosition)) {
                posSelect.add(new Option(targetPosition, targetPosition, true, true));
            }
        }

        setDepartmentByPosition(targetPosition);
    }

    const idPhotoInput = document.getElementById('employee_id_card_photo');
    const cropModalEl = document.getElementById('employeePhotoCropModal');
    const cropImageEl = document.getElementById('employeeCropImage');
    const cropApplyBtn = document.getElementById('employeeCropApplyBtn');
    const base64Hidden = document.getElementById('employee_id_card_photo_base64');
    const previewEl = document.getElementById('employeePhotoPreview');
    let cropper = null;
    let modalInstance = null;
    let backdropEl = null;
    let objectUrl = null;

    if (cropModalEl && window.bootstrap && bootstrap.Modal) {
        modalInstance = new bootstrap.Modal(cropModalEl);
    }

    function showCropModal() {
        if (!cropModalEl) return;
        if (modalInstance) {
            modalInstance.show();
            return;
        }

        cropModalEl.classList.add('show');
        cropModalEl.style.display = 'block';
        cropModalEl.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');

        backdropEl = document.createElement('div');
        backdropEl.className = 'modal-backdrop fade show';
        document.body.appendChild(backdropEl);

        setTimeout(() => {
            initCropper();
        }, 10);
    }

    function hideCropModal({ resetFileInput = false } = {}) {
        if (!cropModalEl) return;
        if (modalInstance) {
            modalInstance.hide();
        } else {
            cropModalEl.classList.remove('show');
            cropModalEl.style.display = 'none';
            cropModalEl.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            if (backdropEl) {
                backdropEl.remove();
                backdropEl = null;
            }
        }

        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        if (cropImageEl) cropImageEl.src = '';
        if (resetFileInput && idPhotoInput) {
            idPhotoInput.value = '';
        }
        if (resetFileInput && base64Hidden) {
            base64Hidden.value = '';
        }
    }

    function initCropper() {
        if (!cropImageEl || !cropImageEl.src) return;
        if (typeof Cropper === 'undefined') {
            return;
        }
        if (cropper) {
            cropper.destroy();
        }
        cropper = new Cropper(cropImageEl, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 1,
            background: false,
            movable: true,
            zoomable: true,
            rotatable: true,
            scalable: false,
        });
    }

    if (idPhotoInput) {
        idPhotoInput.addEventListener('change', function () {
            const file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) return;

            if (base64Hidden) {
                base64Hidden.value = '';
            }

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }
            objectUrl = URL.createObjectURL(file);
            if (cropImageEl) cropImageEl.src = objectUrl;
            showCropModal();
        });
    }

    if (cropModalEl) {
        cropModalEl.addEventListener('shown.bs.modal', function () {
            initCropper();
        });

        cropModalEl.addEventListener('hidden.bs.modal', function () {
            hideCropModal();
        });

        cropModalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach((btn) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                hideCropModal({ resetFileInput: true });
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && cropModalEl.classList.contains('show')) {
                hideCropModal({ resetFileInput: true });
            }
        });
    }

    if (cropApplyBtn) {
        cropApplyBtn.addEventListener('click', function () {
            if (!idPhotoInput) return;
            if (!cropper) {
                if (previewEl && idPhotoInput.files && idPhotoInput.files[0]) {
                    previewEl.src = URL.createObjectURL(idPhotoInput.files[0]);
                    const container = document.getElementById('photoPreviewContainer');
                    if (container) container.classList.remove('d-none');
                }
                hideCropModal();
                return;
            }
            const canvas = cropper.getCroppedCanvas({ width: 600, height: 600 });
            if (base64Hidden) {
                base64Hidden.value = canvas.toDataURL('image/jpeg', 0.95);
            }
            canvas.toBlob(function (blob) {
                if (!blob) return;

                try {
                    const original = idPhotoInput.files && idPhotoInput.files[0] ? idPhotoInput.files[0] : null;
                    const fileName = original ? original.name : 'id-card.jpg';
                    const file = new File([blob], fileName, { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    idPhotoInput.files = dt.files;

                    if (previewEl) {
                        previewEl.src = URL.createObjectURL(file);
                        const container = document.getElementById('photoPreviewContainer');
                        if (container) container.classList.remove('d-none');
                    }
                } catch (e) {
                    // Fallback to base64Hidden
                }

                hideCropModal();
            }, 'image/jpeg', 0.95);
        });
    }

    if (monthlySalaryInput && dailySalaryInput) {
        monthlySalaryInput.addEventListener('input', function() {
            const monthly = parseFloat(this.value) || 0;
            const daily = Math.round(monthly / workingDays);
            dailySalaryInput.value = daily;
        });
    }
});
</script>
