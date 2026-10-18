<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Masuk - {{ config('app.name', 'MStore') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ app()->environment('production') ? secure_asset('favicon.svg') : asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ app()->environment('production') ? secure_asset('favicon.ico') : asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Custom Auth CSS -->
    <link rel="stylesheet" href="{{ app()->environment('production') ? secure_asset('css/auth-custom.css') : asset('css/auth-custom.css') }}">
    
    <script>
        (function() {
            const storedTheme = localStorage.getItem('theme');
            if (storedTheme) {
                document.documentElement.setAttribute('data-bs-theme', storedTheme);
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
        })();
    </script>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-header-actions d-flex justify-content-between align-items-center mb-3">
            <a href="{{ url('/') }}" class="back-home-btn" title="Kembali ke Beranda">
                <i class="fa-solid fa-arrow-left me-2"></i> <span>Kembali ke Beranda</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="theme-toggle-btn" id="themeToggleLogin" title="Ganti Mode Gelap/Terang" aria-label="Toggle tema">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </div>
        <div class="auth-card">
            <div class="auth-left">
                <div class="auth-left-content">
                    <div class="auth-left-title">
                        <img src="{{ app()->environment('production') ? secure_asset('img/logo.png') : asset('img/logo.png') }}" alt="{{ config('app.name', 'MStore') }}">
                    </div>
                    <h2 class="auth-brand-name">{{ config('app.name', 'MStore') }}</h2>
                    <div class="auth-left-sub">
                        Sistem Informasi & Operasional Terpadu
                    </div>
                    
                    <div class="auth-feature-list mt-4">
                        <div class="auth-feature-item">
                            <i class="fa-solid fa-store"></i>
                            <span>Kasir Toko & Retail Modern</span>
                        </div>
                        <div class="auth-feature-item">
                            <i class="fa-solid fa-hand-holding-heart"></i>
                            <span>Jasa, Layanan & Perawatan</span>
                        </div>
                        <div class="auth-feature-item">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>Booking & Reservasi Usaha</span>
                        </div>
                        <div class="auth-feature-item">
                            <i class="fa-solid fa-calculator"></i>
                            <span>Keuangan & Akuntansi Standar</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="auth-right">
                <div class="auth-right-header mb-4">
                    <h1 class="auth-header-title">Selamat Datang</h1>
                    <p class="auth-header-sub mb-0">Silakan masukkan akun Anda untuk melanjutkan ke sistem.</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show mb-3 w-100" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show mb-3 w-100" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login', [], false) }}" class="w-100 auth-form">
                    @csrf
                    <input type="hidden" name="mode" value="admin">

                    <div class="mb-3">
                        <label for="login" class="auth-form-label">Username atau Email</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text auth-input-addon border-end-0">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input id="login" class="form-control auth-input border-start-0 @error('login') is-invalid @enderror" type="text" name="login" value="{{ old('login') }}" required autofocus placeholder="Masukkan username atau email" autocomplete="username" />
                        </div>
                        @error('login')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="auth-form-label">Password</label>
                        <div class="input-group auth-input-group auth-input-group-toggle">
                            <span class="input-group-text auth-input-addon border-end-0">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input id="password" class="form-control auth-input border-start-0 border-end-0 @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password" />
                            <button type="button" class="input-group-text auth-input-addon auth-eye-btn border-start-0" id="togglePasswordBtn" title="Lihat/Sembunyikan Password" tabindex="-1">
                                <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 auth-links">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">
                                Ingat Saya
                            </label>
                        </div>
                        @if (Route::has('password.forgot'))
                            <a href="{{ route('password.forgot') }}" class="auth-forgot-link">Lupa Password?</a>
                        @endif
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-auth-primary" id="btnSubmitLogin">
                            <span>Masuk ke Sistem</span>
                            <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </div>

                    <div class="p-3 bg-light rounded text-center mb-3 border">
                        <div class="small text-muted mb-1">Ingin menggunakan sistem kasir ini untuk usaha Anda?</div>
                        <a href="{{ route('tenant.register.create') }}" class="btn btn-outline-primary btn-sm w-100 fw-bold">
                            <i class="fa-solid fa-gift me-1"></i> Daftar Toko Baru & Coba Gratis 14 Hari
                        </a>
                    </div>

                    <div class="auth-help-box text-center mt-3">
                        <span class="text-muted small">Mengalami kendala saat login? Hubungi <strong>Administrator</strong>.</span>
                    </div>
                </form>

                <div class="auth-footer text-center mt-4">
                    &copy; {{ date('Y') }} {{ config('app.name', 'MStore') }}. Seluruh hak cipta dilindungi.
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        const savedTheme = document.documentElement.getAttribute('data-bs-theme') || localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        const themeToggleLogin = document.getElementById('themeToggleLogin');
        
        function updateThemeIcon(theme) {
            if (themeToggleLogin) {
                const icon = themeToggleLogin.querySelector('i');
                if (theme === 'dark') {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            }
        }
        
        updateThemeIcon(savedTheme);

        if (themeToggleLogin) {
            themeToggleLogin.addEventListener('click', function () {
                const current = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', next);
                localStorage.setItem('theme', next);
                updateThemeIcon(next);
            });
        }

        const loginPasswordField = document.getElementById('password');
        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const togglePasswordIcon = document.getElementById('togglePasswordIcon');

        if (loginPasswordField && togglePasswordBtn && togglePasswordIcon) {
            togglePasswordBtn.addEventListener('click', function () {
                const isPassword = loginPasswordField.type === 'password';
                loginPasswordField.type = isPassword ? 'text' : 'password';
                if (isPassword) {
                    togglePasswordIcon.classList.remove('fa-eye');
                    togglePasswordIcon.classList.add('fa-eye-slash');
                } else {
                    togglePasswordIcon.classList.remove('fa-eye-slash');
                    togglePasswordIcon.classList.add('fa-eye');
                }
            });
        }
        
        // Prevent 419 Page Expired on back button
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
