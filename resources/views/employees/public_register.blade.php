<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Formulir Pendaftaran Karyawan') }} - {{ $storeName }}</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
            color: #334155;
        }
        .register-container {
            max-width: 800px;
        }
        .form-section-title {
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0d6efd;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .card {
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.35rem;
            color: #1e293b;
        }
        .form-control, .form-select {
            border-radius: 0.5rem;
            padding: 0.6rem 0.85rem;
            font-size: 0.9rem;
            border: 1px solid #cbd5e1;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }
        .btn-submit {
            border-radius: 0.6rem;
            font-weight: 600;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: none;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #0b5ed7, #0a58ca);
        }
        .upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 0.75rem;
            padding: 1rem;
            text-align: center;
            background-color: #f8fafc;
            transition: all 0.2s;
        }
        .upload-box:hover {
            border-color: #0d6efd;
            background-color: #f0f7ff;
        }
    </style>
</head>
<body class="py-4 py-md-5">

<div class="container register-container">
    {{-- Header Logo & Title --}}
    <div class="text-center mb-4">
        <a href="/" class="d-inline-block mb-3">
            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="img-fluid" style="max-height: 48px;">
        </a>
        <h1 class="h3 fw-bold text-gray-900 mb-1">{{ __('Formulir Pendaftaran Karyawan') }}</h1>
        <p class="text-muted small mb-0">{{ __('Bergabung bersama tim ') }} <strong>{{ $storeName }}</strong>. {{ __('Lengkapi data diri Anda dengan teliti di bawah ini.') }}</p>
    </div>

    {{-- Error Banner --}}
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                <span class="fw-bold">{{ __('Mohon periksa kembali formulir Anda:') }}</span>
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="card border-0 mb-4">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('employees.public.register.store') }}" method="POST" enctype="multipart/form-data" id="employeeRegisterForm">
                @csrf

                {{-- Bagian 1: Data Identitas & Biodata Diri --}}
                <div class="form-section-title">
                    <i class="fa-solid fa-id-card"></i>
                    <span>{{ __('1. Data Pribadi & Identitas') }}</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-8">
                        <label for="full_name" class="form-label">{{ __('Nama Lengkap (Sesuai KTP)') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="Contoh: Budi Santoso" required autofocus>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="nik" class="form-label">{{ __('Nomor KTP / NIK') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik') }}" placeholder="16 digit angka NIK" maxlength="32" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="gender" class="form-label">{{ __('Jenis Kelamin') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                            <option value="">{{ __('-- Pilih --') }}</option>
                            <option value="Laki-laki" {{ old('gender') === 'Laki-laki' ? 'selected' : '' }}>{{ __('Laki-laki') }}</option>
                            <option value="Perempuan" {{ old('gender') === 'Perempuan' ? 'selected' : '' }}>{{ __('Perempuan') }}</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="date_of_birth" class="form-label">{{ __('Tanggal Lahir') }} <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="phone" class="form-label">{{ __('Nomor WhatsApp / HP') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-success"><i class="fa-brands fa-whatsapp"></i></span>
                            <input type="tel" class="form-control border-start-0 @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="email" class="form-label">{{ __('Alamat Email') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com">
                        </div>
                        <small class="text-muted">{{ __('Digunakan untuk pengiriman surat pemberitahuan atau slip gaji.') }}</small>
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label">{{ __('Alamat Lengkap Domisili Saat Ini') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten" required>{{ old('address') }}</textarea>
                    </div>
                </div>

                {{-- Bagian 2: Posisi / Lamaran Kerja --}}
                <div class="form-section-title">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>{{ __('2. Posisi & Penempatan Kerja') }}</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="position" class="form-label">{{ __('Posisi yang Dilamar / Diminati') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('position') is-invalid @enderror" id="position" name="position" required onchange="handlePositionChange(this)">
                            <option value="">{{ __('-- Pilih Posisi Pekerjaan --') }}</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}" {{ old('position') === $pos ? 'selected' : '' }}>{{ $pos }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="department" class="form-label">{{ __('Departemen') }}</label>
                        <select class="form-select @error('department') is-invalid @enderror" id="department" name="department">
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ old('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="join_date" class="form-label">{{ __('Tanggal Siap Mulai Bekerja') }}</label>
                        <input type="date" class="form-control @error('join_date') is-invalid @enderror" id="join_date" name="join_date" value="{{ old('join_date', date('Y-m-d')) }}">
                        <small class="text-muted">{{ __('Kapan Anda dapat mulai bergabung.') }}</small>
                    </div>
                </div>

                {{-- Bagian 3: Rekening Bank Penggajian (Opsional) --}}
                <div class="form-section-title">
                    <i class="fa-solid fa-building-columns"></i>
                    <span>{{ __('3. Rekening Bank Penggajian (Opsional)') }}</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label for="bank_name" class="form-label">{{ __('Nama Bank / E-Wallet') }}</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name') }}" placeholder="BCA / BRI / Mandiri / BNI / DANA">
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="bank_account_number" class="form-label">{{ __('Nomor Rekening') }}</label>
                        <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="1234567890">
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="bank_account_name" class="form-label">{{ __('Nama Pemilik Rekening') }}</label>
                        <input type="text" class="form-control" id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="Sesuai buku tabungan">
                    </div>
                </div>

                {{-- Bagian 4: Unggah Dokumen --}}
                <div class="form-section-title">
                    <i class="fa-solid fa-file-arrow-up"></i>
                    <span>{{ __('4. Unggah Foto KTP & Berkas CV') }}</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="id_card_photo" class="form-label">{{ __('Foto KTP / Pas Foto') }}</label>
                        <div class="upload-box">
                            <i class="fa-solid fa-image text-primary fs-3 mb-2 d-block"></i>
                            <input type="file" class="form-control form-control-sm" id="id_card_photo" name="id_card_photo" accept="image/jpeg,image/png,image/webp">
                            <span class="text-muted d-block mt-2" style="font-size: 0.75rem;">{{ __('Format: JPG, PNG, WEBP (Maksimal 5MB)') }}</span>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="document" class="form-label">{{ __('Berkas CV / Surat Lamaran / Ijazah') }}</label>
                        <div class="upload-box">
                            <i class="fa-solid fa-file-pdf text-danger fs-3 mb-2 d-block"></i>
                            <input type="file" class="form-control form-control-sm" id="document" name="document" accept=".pdf,.doc,.docx,image/*">
                            <span class="text-muted d-block mt-2" style="font-size: 0.75rem;">{{ __('Format: PDF, Word, atau Gambar (Maksimal 10MB)') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Pernyataan Persetujuan --}}
                <div class="form-check bg-light p-3 rounded-3 mb-4 border">
                    <input class="form-check-input ms-0 me-2" type="checkbox" id="agreementCheck" required>
                    <label class="form-check-label small text-muted" for="agreementCheck">
                        {{ __('Saya menyatakan bahwa semua data dan dokumen yang saya isikan pada formulir pendaftaran ini adalah benar dan dapat dipertanggungjawabkan.') }}
                    </label>
                </div>

                {{-- Submit Button --}}
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-submit text-white shadow-sm" id="btnSubmitForm">
                        <i class="fa-solid fa-paper-plane me-2"></i>{{ __('Kirim Pendaftaran Sekarang') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Footer Copyright --}}
    <div class="text-center text-muted small">
        <p class="mb-0">&copy; {{ date('Y') }} {{ $storeName }}. {{ __('Sistem Informasi Rekrutmen & Manajemen Karyawan.') }}</p>
    </div>
</div>

<script>
    const posMap = @json($positionDeptMap);

    function handlePositionChange(select) {
        const val = select.value;
        const deptSelect = document.getElementById('department');
        if (posMap && posMap[val] && deptSelect) {
            deptSelect.value = posMap[val];
        }
    }

    document.getElementById('employeeRegisterForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitForm');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sedang Menyimpan Data...';
    });
</script>
</body>
</html>
