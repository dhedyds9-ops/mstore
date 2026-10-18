<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Daftar Toko & Coba Gratis 14 Hari') }} - {{ config('app.name', 'MStore') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .register-container {
            width: 100%;
            max-width: 900px;
        }

        .auth-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .auth-header-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            padding: 2.5rem 2rem;
        }

        .type-option-card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .type-option-card:hover {
            border-color: var(--primary);
            background-color: #f0f7ff;
        }

        .type-option-card.active {
            border-color: var(--primary);
            background-color: #eff6ff;
            box-shadow: 0 0 0 2px var(--primary);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .btn-primary-custom {
            background-color: var(--primary);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            color: white;
            transition: all 0.2s;
        }

        .btn-primary-custom:hover {
            background-color: var(--primary-hover);
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="text-center mb-4">
        <a href="{{ url('/') }}" class="text-decoration-none">
            <span class="fs-3 fw-bold text-primary"><i class="fa-solid fa-cube me-2"></i>{{ config('app.name', 'MStore') }}</span>
        </a>
    </div>

    <div class="auth-card">
        <div class="auth-header-gradient text-center">
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-2">
                <i class="fa-solid fa-gift me-1"></i> Gratis Coba 14 Hari Tanpa Kartu Kredit
            </span>
            <h3 class="fw-bold mb-1">Mulai Digitalkan Toko & Usaha Anda</h3>
            <p class="mb-0 opacity-75 small">Kasir POS cepat, manajemen stok barcode, komisi staf, dan pembukuan otomatis dalam satu aplikasi.</p>
        </div>

        <div class="p-4 p-md-5">
            <form action="{{ route('tenant.register.store') }}" method="POST">
                @csrf

                <!-- Pilihan Jenis Usaha -->
                <label class="form-label fw-bold small text-muted text-uppercase mb-2">1. Pilih Jenis Usaha Anda</label>
                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-lg-3">
                        <label class="type-option-card d-block h-100 {{ old('business_type', $selectedPlan) == 'starter' ? 'active' : '' }}" for="type_starter">
                            <input type="radio" name="business_type" id="type_starter" value="starter" class="visually-hidden" {{ old('business_type', $selectedPlan) == 'starter' ? 'checked' : '' }}>
                            <div class="text-primary mb-2"><i class="fa-solid fa-store fs-4"></i></div>
                            <div class="fw-bold text-dark small">Toko & Retail</div>
                            <div class="text-muted" style="font-size: 0.78rem;">Sembako, ATK, Distro, Swalayan, Warung</div>
                        </label>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="type-option-card d-block h-100 {{ old('business_type', $selectedPlan) == 'service' ? 'active' : '' }}" for="type_service">
                            <input type="radio" name="business_type" id="type_service" value="service" class="visually-hidden" {{ old('business_type', $selectedPlan) == 'service' ? 'checked' : '' }}>
                            <div class="text-info mb-2"><i class="fa-solid fa-hand-sparkles fs-4"></i></div>
                            <div class="fw-bold text-dark small">Jasa & Servis</div>
                            <div class="text-muted" style="font-size: 0.78rem;">Car wash, Salon, Bengkel, Barbershop</div>
                        </label>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="type-option-card d-block h-100 {{ old('business_type', $selectedPlan) == 'pro' ? 'active' : '' }}" for="type_pro">
                            <input type="radio" name="business_type" id="type_pro" value="pro" class="visually-hidden" {{ old('business_type', $selectedPlan) == 'pro' ? 'checked' : '' }}>
                            <div class="text-success mb-2"><i class="fa-solid fa-crown fs-4"></i></div>
                            <div class="fw-bold text-dark small">Multi-Bisnis (Pro)</div>
                            <div class="text-muted" style="font-size: 0.78rem;">Ritel + Jasa + Booking & Reservasi</div>
                        </label>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <label class="type-option-card d-block h-100 {{ old('business_type', $selectedPlan) == 'enterprise' ? 'active' : '' }}" for="type_enterprise">
                            <input type="radio" name="business_type" id="type_enterprise" value="enterprise" class="visually-hidden" {{ old('business_type', $selectedPlan) == 'enterprise' ? 'checked' : '' }}>
                            <div class="text-dark mb-2"><i class="fa-solid fa-building-columns fs-4"></i></div>
                            <div class="fw-bold text-dark small">Enterprise</div>
                            <div class="text-muted" style="font-size: 0.78rem;">Multi Cabang & Outlet Skala Besar</div>
                        </label>
                    </div>
                </div>

                <!-- Informasi Toko & Pemilik -->
                <label class="form-label fw-bold small text-muted text-uppercase mb-2">2. Informasi Toko & Pemilik</label>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="store_name" class="form-label small fw-semibold">Nama Toko / Usaha <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-shop text-muted"></i></span>
                            <input type="text" name="store_name" id="store_name" class="form-control @error('store_name') is-invalid @enderror" placeholder="Contoh: Toko Berkah Mandiri" value="{{ old('store_name') }}" required>
                            @error('store_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="owner_name" class="form-label small fw-semibold">Nama Pemilik Usaha <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" name="owner_name" id="owner_name" class="form-control @error('owner_name') is-invalid @enderror" placeholder="Nama lengkap Anda" value="{{ old('owner_name') }}" required>
                            @error('owner_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@tokoanda.com" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label small fw-semibold">No. WhatsApp / Telepon <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-brands fa-whatsapp text-muted"></i></span>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Contoh: 08123456789" value="{{ old('phone') }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Keamanan Password -->
                <label class="form-label fw-bold small text-muted text-uppercase mb-2">3. Buat Password Akun</label>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label small fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-shield-halved text-muted"></i></span>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password" required>
                        </div>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary-custom fs-5">
                        <i class="fa-solid fa-rocket me-2"></i> Daftar & Mulai Coba Gratis 14 Hari
                    </button>
                </div>

                <div class="text-center small text-muted">
                    Dengan mendaftar, Anda menyetujui Ketentuan Layanan & Kebijakan Privasi kami.
                    <div class="mt-3">
                        Sudah punya akun toko? <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Masuk ke Kasir / Dasbor</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('.type-option-card');
    cards.forEach(card => {
        card.addEventListener('click', function() {
            cards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>

</body>
</html>
