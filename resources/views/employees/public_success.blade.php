<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ __('Pendaftaran Berhasil') }} - {{ $storeName }}</title>

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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            max-width: 580px;
            width: 100%;
            border-radius: 1.25rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }
        .success-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #dcfce7;
            color: #16a34a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>
<body class="p-3">

<div class="card success-card border-0 bg-white p-4 p-md-5 text-center my-4">
    <div class="mb-3">
        <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="img-fluid" style="max-height: 44px;">
    </div>

    <div class="success-icon-wrapper shadow-xs">
        <i class="fa-solid fa-circle-check"></i>
    </div>

    <h2 class="h4 fw-bold text-gray-900 mb-2">{{ __('Pendaftaran Karyawan Berhasil!') }}</h2>
    <p class="text-muted small mb-4">
        {{ __('Terima kasih telah mendaftar. Data profil dan berkas lamaran Anda telah berhasil tercatat dalam sistem HRD ') }} <strong>{{ $storeName }}</strong>.
    </p>

    @if(session('registered_name'))
        <div class="card bg-light border text-start p-3 rounded-3 mb-4">
            <h6 class="fw-bold text-dark small text-uppercase mb-2 pb-1 border-bottom">
                <i class="fa-solid fa-file-invoice me-1 text-primary"></i>{{ __('Ringkasan Pendaftaran') }}
            </h6>
            <div class="row g-2 small">
                <div class="col-5 text-muted">{{ __('Nama Calon Karyawan:') }}</div>
                <div class="col-7 fw-semibold text-dark">{{ session('registered_name') }}</div>

                <div class="col-5 text-muted">{{ __('Posisi Pekerjaan:') }}</div>
                <div class="col-7 fw-semibold text-primary">{{ session('registered_position') }}</div>

                <div class="col-5 text-muted">{{ __('Nomor WhatsApp:') }}</div>
                <div class="col-7 fw-semibold text-dark">{{ session('registered_phone') }}</div>

                @if(session('registered_date'))
                    <div class="col-5 text-muted">{{ __('Waktu Pendaftaran:') }}</div>
                    <div class="col-7 text-dark">{{ session('registered_date') }}</div>
                @endif
            </div>
        </div>
    @endif

    <div class="alert alert-info border-0 rounded-3 text-start small mb-4">
        <div class="fw-bold mb-1 d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-info"></i>
            <span>{{ __('Tahapan Selanjutnya:') }}</span>
        </div>
        <ol class="mb-0 ps-3">
            <li>{{ __('Tim HRD kami akan meninjau kelengkapan berkas Anda.') }}</li>
            <li>{{ __('Jika memenuhi kualifikasi, Anda akan segera dihubungi melalui pesan WhatsApp atau Telepon untuk jadwal wawancara kerja.') }}</li>
        </ol>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
        <a href="{{ route('employees.public.register.create') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-rotate-left me-1"></i>{{ __('Isi Formulir Baru') }}
        </a>
        <a href="/" class="btn btn-primary px-4">
            <i class="fa-solid fa-house me-1"></i>{{ __('Kembali ke Beranda') }}
        </a>
    </div>
</div>

</body>
</html>
