@extends('layouts.landing-public')

@section('content')
    @php
        $siteName = $storeName ?? config('app.name', 'MStore');
        $waUrlBase = 'https://wa.me/'.$waNumber;
        $services = collect($serviceCatalog ?? [])->values();
        $featuredServices = $services->take(3);
        $serviceCount = $services->count();
    @endphp

    <section id="beranda" class="hero home-hero">
        <div class="container">
            <div class="row align-items-center g-5 home-hero-grid">
                <div class="col-lg-6 fade-up">
                    <span class="home-kicker">{{ $siteName }} • Aplikasi Kasir & Operasional Bisnis Terpadu</span>
                    <h1 class="hero-title home-hero-title">Aplikasi Kasir POS & Manajemen Usaha Terlengkap untuk Semua UMKM</h1>
                    <p class="hero-desc home-hero-desc">Solusi cerdas terintegrasi untuk Toko Jualan & Retail, Usaha Jasa & Perawatan, serta Booking & Reservasi. Lengkap dengan kasir POS cepat, scan barcode, cetak & kirim struk WhatsApp, shift kasir, hitung komisi pegawai, dan pembukuan akuntansi otomatis.</p>
                    <div class="home-cta-row">
                        <a href="{{ route('tenant.register.create') }}" class="btn btn-primary track-service-action">
                            <i class="fas fa-rocket me-2"></i> Coba Gratis 14 Hari
                        </a>
                        <a href="#services" class="btn btn-outline-primary track-service-action">
                            <i class="fas fa-layer-group me-2"></i> Solusi Bisnis
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-in-alt me-2"></i> Masuk Toko
                        </a>
                    </div>

                    <div class="home-metric-grid">
                        <div class="home-metric-card">
                            <strong>{{ $serviceCount }}</strong>
                            <span>Unit Layanan</span>
                        </div>
                        <div class="home-metric-card">
                            <strong>100%</strong>
                            <span>Pelayanan Prima</span>
                        </div>
                        <div class="home-metric-card">
                            <strong>Cepat</strong>
                            <span>Respon Cepat CS</span>
                        </div>
                    </div>

                    <div class="landing-trust mt-4 home-trust-row">
                        <div class="landing-trust-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Pelayanan Profesional</span>
                        </div>
                        <div class="landing-trust-item">
                            <i class="fas fa-clock"></i>
                            <span>Respon Cepat WhatsApp</span>
                        </div>
                        <div class="landing-trust-item">
                            <i class="fas fa-target"></i>
                            <span>Solusi Sesuai Kebutuhan</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 fade-up">
                    <div class="home-showcase-shell">
                        <div class="home-showcase-main">
                            <div class="home-showcase-badge">Ringkasan Ekosistem</div>
                            <h3>Lebih mudah memilih, lebih cepat diproses</h3>
                            <p>Setiap unit layanan kami susun agar mudah dipahami: informasi ringkas, highlight benefit, dan jalur konsultasi yang jelas.</p>
                            <div class="home-service-stack">
                                @foreach($featuredServices as $service)
                                    <a href="{{ $service['url'] }}" class="home-service-pill track-service-action" data-track-service="{{ $service['slug'] }}" data-track-action="hero_service_pill">
                                        <span class="home-service-pill-icon"><i class="fas {{ $service['icon'] }}"></i></span>
                                        <span>
                                            <strong>{{ $service['name'] }}</strong>
                                            <small>{{ $service['stat'] }}</small>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="home-floating-card home-floating-card-dark">
                            <div class="home-floating-icon"><i class="fas fa-diagram-project"></i></div>
                            <div>
                                <strong>Alur Lebih Rapi</strong>
                                <span>Setiap layanan punya flow yang jelas</span>
                            </div>
                        </div>

                        <div class="home-floating-card home-floating-card-green">
                            <div class="home-floating-icon"><i class="fab fa-whatsapp"></i></div>
                            <div>
                                <strong>Respon Cepat</strong>
                                <span>{{ $waNumber }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4 Pilar Solusi Bisnis UMKM -->
    <section id="services" class="py-5 home-section">
        <div class="container py-2">
            <div class="section-header text-center mb-5 fade-up home-section-header">
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold mb-2">Solusi Lengkap</span>
                <h2 class="display-6 fw-800">Satu Platform untuk Berbagai Jenis Usaha</h2>
                <p class="text-muted mb-0">Dirancang khusus untuk mempermudah operasional kasir, manajemen stok, komisi staf, dan pembukuan keuangan toko Anda.</p>
            </div>

            <div class="row g-4">
                <!-- Solusi 1: Toko & Retail -->
                <div class="col-lg-3 col-md-6 fade-up">
                    <div class="landing-service-card home-service-card h-100 border shadow-sm">
                        <div class="home-service-card-top">
                            <span class="badge bg-primary text-white position-absolute top-0 start-0 m-2">Retail POS</span>
                            <div class="landing-service-icon home-service-card-icon text-primary">
                                <i class="fas fa-store"></i>
                            </div>
                            <div class="landing-service-stat home-service-stat">Kasir & Barcode</div>
                        </div>
                        <h3 class="landing-service-title">Toko & Retail Modern</h3>
                        <p class="landing-service-desc">Sembako, kelontong, distro, ATK, swalayan, minimarket, dan toko grosir.</p>
                        <ul class="landing-service-points">
                            <li><i class="fas fa-check-circle text-primary"></i> Kasir POS cepat & scan barcode</li>
                            <li><i class="fas fa-check-circle text-primary"></i> Cetak struk thermal & struk WhatsApp</li>
                            <li><i class="fas fa-check-circle text-primary"></i> Shift kasir & kontrol mutasi laci uang</li>
                            <li><i class="fas fa-check-circle text-primary"></i> Pantau stok produk & peringatan habis</li>
                        </ul>
                        <div class="home-service-card-actions mt-auto">
                            <a href="{{ route('tenant.register.create', ['plan' => 'starter']) }}" class="btn btn-primary w-100">
                                Coba Kasir Toko <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Solusi 2: Jasa & Servis -->
                <div class="col-lg-3 col-md-6 fade-up">
                    <div class="landing-service-card home-service-card h-100 border shadow-sm">
                        <div class="home-service-card-top">
                            <span class="badge bg-info text-white position-absolute top-0 start-0 m-2">Jasa & Servis</span>
                            <div class="landing-service-icon home-service-card-icon text-info">
                                <i class="fas fa-hand-sparkles"></i>
                            </div>
                            <div class="landing-service-stat home-service-stat">Komisi & Member</div>
                        </div>
                        <h3 class="landing-service-title">Jasa & Layanan</h3>
                        <p class="landing-service-desc">Car wash / cuci kendaraan, salon kecantikan, barbershop, laundry, dan bengkel servis.</p>
                        <ul class="landing-service-points">
                            <li><i class="fas fa-check-circle text-info"></i> Kasir pengerjaan layanan cepat</li>
                            <li><i class="fas fa-check-circle text-info"></i> Hitung otomatis komisi per pegawai</li>
                            <li><i class="fas fa-check-circle text-info"></i> Paket langganan member prabayar</li>
                            <li><i class="fas fa-check-circle text-info"></i> Loyalty points & otomatis reward kunjungan</li>
                        </ul>
                        <div class="home-service-card-actions mt-auto">
                            <a href="{{ route('tenant.register.create', ['plan' => 'service']) }}" class="btn btn-outline-info w-100">
                                Coba Kasir Jasa <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Solusi 3: Booking & Reservasi -->
                <div class="col-lg-3 col-md-6 fade-up">
                    <div class="landing-service-card home-service-card h-100 border shadow-sm">
                        <div class="home-service-card-top">
                            <span class="badge bg-success text-white position-absolute top-0 start-0 m-2">Booking & Sewa</span>
                            <div class="landing-service-icon home-service-card-icon text-success">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="landing-service-stat home-service-stat">Jadwal & DP</div>
                        </div>
                        <h3 class="landing-service-title">Booking & Reservasi</h3>
                        <p class="landing-service-desc">Penyewaan alat/gedung, event organizer, wedding planner, studio foto, dan reservasi jasa.</p>
                        <ul class="landing-service-points">
                            <li><i class="fas fa-check-circle text-success"></i> Kelola paket layanan & varian sewa</li>
                            <li><i class="fas fa-check-circle text-success"></i> Kalender jadwal acara & booking</li>
                            <li><i class="fas fa-check-circle text-success"></i> Pembayaran DP & pelunasan bertahap</li>
                            <li><i class="fas fa-check-circle text-success"></i> Galeri portofolio & approval klien</li>
                        </ul>
                        <div class="home-service-card-actions mt-auto">
                            <a href="{{ route('tenant.register.create', ['plan' => 'pro']) }}" class="btn btn-outline-success w-100">
                                Coba Reservasi <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Solusi 4: Keuangan & Akuntansi -->
                <div class="col-lg-3 col-md-6 fade-up">
                    <div class="landing-service-card home-service-card h-100 border shadow-sm">
                        <div class="home-service-card-top">
                            <span class="badge bg-dark text-white position-absolute top-0 start-0 m-2">PSAK Standar</span>
                            <div class="landing-service-icon home-service-card-icon text-dark">
                                <i class="fas fa-book-journal-whills"></i>
                            </div>
                            <div class="landing-service-stat home-service-stat">Laba Rugi Riil</div>
                        </div>
                        <h3 class="landing-service-title">Keuangan & Akuntansi</h3>
                        <p class="landing-service-desc">Pembukuan standar akuntansi tanpa perlu ahli akuntan manual.</p>
                        <ul class="landing-service-points">
                            <li><i class="fas fa-check-circle text-dark"></i> Bagan Akun (COA) otomatis</li>
                            <li><i class="fas fa-check-circle text-dark"></i> Jurnal umum otomatis dari transaksi kasir</li>
                            <li><i class="fas fa-check-circle text-dark"></i> Neraca saldo, arus kas, & buku besar</li>
                            <li><i class="fas fa-check-circle text-dark"></i> Laba rugi segmented & laporan bagi hasil</li>
                        </ul>
                        <div class="home-service-card-actions mt-auto">
                            <a href="{{ route('tenant.register.create', ['plan' => 'pro']) }}" class="btn btn-outline-dark w-100">
                                Coba Akuntansi <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section (Paket Langganan UMKM) -->
    <section id="pricing" class="py-5 home-section bg-light">
        <div class="container py-3">
            <div class="section-header text-center mb-5 fade-up">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-2">
                    <i class="fas fa-tag me-1"></i> Transparan & Terjangkau
                </span>
                <h2 class="display-6 fw-800">Paket Langganan Sesuai Skala Bisnis Anda</h2>
                <p class="text-muted mb-0">Nikmati fitur lengkap tanpa biaya tersembunyi. Mulai gratis 14 hari sekarang!</p>
            </div>

            <div class="row g-4 align-items-stretch">
                <!-- Paket Starter -->
                <div class="col-lg-3 col-md-6 fade-up">
                    <div class="card h-100 border shadow-sm rounded-4 p-3 bg-white">
                        <div class="card-body d-flex flex-column">
                            <div class="text-center mb-3">
                                <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-1 rounded-pill">Starter</span>
                                <h4 class="fw-bold mt-2 mb-1">Toko & Retail</h4>
                                <p class="text-muted small">Untuk toko sembako, kelontong, distro & warung.</p>
                            </div>
                            <div class="text-center py-2 mb-3 bg-light rounded-3">
                                <span class="fs-3 fw-bold">Rp 99.000</span><span class="text-muted small">/bln</span>
                            </div>
                            <ul class="list-unstyled small mb-4 flex-grow-1">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Kasir POS & Scan Barcode</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Struk Thermal & Struk WA</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Shift Kasir & Laci Uang</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Manajemen Stok Produk</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Maksimal 5 Staf Kasir</li>
                            </ul>
                            <a href="{{ route('tenant.register.create', ['plan' => 'starter']) }}" class="btn btn-outline-primary w-100">
                                Mulai Coba Gratis
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Paket Service -->
                <div class="col-lg-3 col-md-6 fade-up">
                    <div class="card h-100 border shadow-sm rounded-4 p-3 bg-white">
                        <div class="card-body d-flex flex-column">
                            <div class="text-center mb-3">
                                <span class="badge bg-info-subtle text-info fw-bold text-uppercase px-3 py-1 rounded-pill">Service</span>
                                <h4 class="fw-bold mt-2 mb-1">Jasa & Layanan</h4>
                                <p class="text-muted small">Untuk car wash, salon, bengkel, laundry.</p>
                            </div>
                            <div class="text-center py-2 mb-3 bg-light rounded-3">
                                <span class="fs-3 fw-bold">Rp 129.000</span><span class="text-muted small">/bln</span>
                            </div>
                            <ul class="list-unstyled small mb-4 flex-grow-1">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Kasir Antrean Layanan Cepat</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Hitung Otomatis Komisi Pegawai</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Paket Member & Loyalty Reward</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Shift Kerja & Laci Uang</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Maksimal 5 Staf</li>
                            </ul>
                            <a href="{{ route('tenant.register.create', ['plan' => 'service']) }}" class="btn btn-outline-info w-100">
                                Mulai Coba Gratis
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Paket Pro (Paling Diminati) -->
                <div class="col-lg-3 col-md-6 fade-up">
                    <div class="card h-100 border-primary border-2 shadow rounded-4 p-3 bg-white position-relative">
                        <div class="position-absolute top-0 start-50 translate-middle">
                            <span class="badge bg-primary text-white px-3 py-1 rounded-pill shadow-sm">
                                <i class="fas fa-fire me-1"></i> Paling Diminati
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="text-center mb-3 mt-1">
                                <span class="badge bg-success-subtle text-success fw-bold text-uppercase px-3 py-1 rounded-pill">Pro Usaha</span>
                                <h4 class="fw-bold mt-2 mb-1">Multi-Bisnis</h4>
                                <p class="text-muted small">Ritel + Jasa + Reservasi + Akuntansi.</p>
                            </div>
                            <div class="text-center py-2 mb-3 bg-primary bg-opacity-10 rounded-3">
                                <span class="fs-3 fw-bold text-primary">Rp 199.000</span><span class="text-muted small">/bln</span>
                            </div>
                            <ul class="list-unstyled small mb-4 flex-grow-1">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> SEMUA Fitur Toko & Retail</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> SEMUA Fitur Jasa & Komisi</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Modul Booking & Reservasi</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Akuntansi PSAK Lengkap</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Maksimal 15 Staf & Multi-Cabang</li>
                            </ul>
                            <a href="{{ route('tenant.register.create', ['plan' => 'pro']) }}" class="btn btn-primary w-100 fw-bold">
                                Mulai Coba Gratis 14 Hari
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Paket Enterprise -->
                <div class="col-lg-3 col-md-6 fade-up">
                    <div class="card h-100 border shadow-sm rounded-4 p-3 bg-white">
                        <div class="card-body d-flex flex-column">
                            <div class="text-center mb-3">
                                <span class="badge bg-dark text-white fw-bold text-uppercase px-3 py-1 rounded-pill">Enterprise</span>
                                <h4 class="fw-bold mt-2 mb-1">Franchise & Jaringan</h4>
                                <p class="text-muted small">Bisnis berkembang dengan banyak cabang.</p>
                            </div>
                            <div class="text-center py-2 mb-3 bg-light rounded-3">
                                <span class="fs-3 fw-bold">Rp 399.000</span><span class="text-muted small">/bln</span>
                            </div>
                            <ul class="list-unstyled small mb-4 flex-grow-1">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Semua Fitur Pro Tanpa Batas</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited Cabang & Outlet</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited Staf & Pengguna</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Integrasi CCTV Keamanan</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Akses API & Prioritas CS</li>
                            </ul>
                            <a href="{{ route('tenant.register.create', ['plan' => 'enterprise']) }}" class="btn btn-outline-dark w-100">
                                Mulai Coba Gratis
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Proof / Benefit Section -->
    <section class="py-5 home-benefit-band">
        <div class="container py-2">
            <div class="row g-4">
                <div class="col-lg-4 fade-up">
                    <div class="landing-mini-card h-100 home-mini-card">
                        <i class="fas fa-mobile-screen-button text-primary fs-3"></i>
                        <div>
                            <div class="fw-bold">Bisa Diakses Dari Mana Saja</div>
                            <div class="text-muted small">Gunakan dari HP, tablet kasir, atau laptop tanpa perlu instalasi rumit.</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 fade-up">
                    <div class="landing-mini-card h-100 home-mini-card">
                        <i class="fab fa-whatsapp text-success fs-3"></i>
                        <div>
                            <div class="fw-bold">Kirim Struk WhatsApp Instan</div>
                            <div class="text-muted small">Pelanggan menerima nota digital langsung di WhatsApp dengan logo & nama toko Anda.</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 fade-up">
                    <div class="landing-mini-card h-100 home-mini-card">
                        <i class="fas fa-shield-halved text-info fs-3"></i>
                        <div>
                            <div class="fw-bold">Data Aman & Terbackup Otomatis</div>
                            <div class="text-muted small">Didukung enkripsi cloud modern dan audit trail aktivitas staf kasir.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Consult Form Section -->
    <section id="quick-consult" class="py-5 home-consult-section">
        <div class="container py-2">
            <div class="row g-4 align-items-start home-consult-shell">
                <div class="col-lg-5 fade-up">
                    <div class="section-header mb-3 home-consult-copy">
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold mb-2">Bantuan Konsultasi</span>
                        <h2 class="display-6 fw-800 mb-2">Punya Kebutuhan Khusus untuk Usaha Anda?</h2>
                        <p class="text-muted mb-0">Tim kami siap membantu merekomendasikan alur kasir, perangkat printer barcode, atau migrasi data toko Anda secara gratis.</p>
                    </div>

                    <div class="home-consult-points mt-4">
                        <div class="home-consult-point mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Respon Cepat via WhatsApp</span>
                        </div>
                        <div class="home-consult-point mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Bantuan Setup & Panduan Penggunaan</span>
                        </div>
                        <div class="home-consult-point mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Mendukung Printer Thermal & Scanner Barcode</span>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success mt-3">
                            <div class="fw-bold">{{ session('success') }}</div>
                            @if(session('lead_whatsapp_url'))
                                <div class="mt-2">
                                    <a class="btn btn-primary" href="{{ session('lead_whatsapp_url') }}">
                                        <i class="fab fa-whatsapp me-2"></i> Lanjutkan via WhatsApp
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="col-lg-7 fade-up">
                    <div class="lead-card home-lead-card p-4 rounded-4 shadow-sm bg-white border">
                        <form method="POST" action="{{ route('landing.leads.store') }}" class="lead-form">
                            @csrf
                            <input type="hidden" name="landing_page" value="home">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                                    <input name="name" value="{{ old('name') }}" class="form-control home-form-control @error('name') is-invalid @enderror" required placeholder="Nama Anda">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Nomor WhatsApp</label>
                                    <input name="phone" value="{{ old('phone') }}" class="form-control home-form-control @error('phone') is-invalid @enderror" required placeholder="08xxxxxxxxxx">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Jenis Bisnis</label>
                                    <select name="service_interest" class="form-select home-form-control @error('service_interest') is-invalid @enderror">
                                        <option value="retail">Toko Jualan / Retail / Sembako</option>
                                        <option value="service">Jasa / Cuci Mobil / Salon / Bengkel</option>
                                        <option value="booking">Sewa / Event / Reservasi</option>
                                        <option value="enterprise">Multi Cabang / Grosir</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Kota / Wilayah Usaha</label>
                                    <input name="coverage_area" value="{{ old('coverage_area') }}" class="form-control home-form-control" placeholder="Contoh: Jakarta / Surabaya / Medan">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">Pertanyaan / Kebutuhan</label>
                                    <textarea name="message" rows="3" class="form-control home-form-control @error('message') is-invalid @enderror" placeholder="Ceritakan jenis usaha atau kebutuhan kasir Anda...">{{ old('message') }}</textarea>
                                </div>
                                <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                                    <button class="btn btn-primary px-4" type="submit">
                                        <i class="fas fa-paper-plane me-1"></i> Kirim Pesan
                                    </button>
                                    <a class="btn btn-green" href="{{ $waUrlBase }}?text={{ urlencode('Halo, saya ingin konsultasi software kasir UMKM.') }}">
                                        <i class="fab fa-whatsapp me-1"></i> Chat WhatsApp
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Banner -->
    <section class="py-5 bg-primary text-white text-center">
        <div class="container py-4 fade-up">
            <h2 class="display-6 fw-800 mb-3 text-white">Siap Mengembangkan Usaha Anda dengan Sistem Modern?</h2>
            <p class="lead mb-4 opacity-90" style="max-width: 650px; margin: 0 auto;">Daftarkan toko Anda sekarang dalam 1 menit. Dapatkan masa uji coba gratis 14 hari tanpa biaya komitmen.</p>
            <div class="d-flex justify-content-center flex-wrap gap-3">
                <a href="{{ route('tenant.register.create') }}" class="btn btn-light btn-lg text-primary fw-bold px-4 shadow">
                    <i class="fas fa-rocket me-2"></i> Daftar & Coba Gratis 14 Hari
                </a>
                <a href="{{ $waUrlBase }}?text={{ urlencode('Halo, saya ingin bertanya tentang fitur MStore.') }}" class="btn btn-outline-light btn-lg px-4">
                    <i class="fab fa-whatsapp me-2"></i> Hubungi Tim Sales
                </a>
            </div>
        </div>
    </section>
@endsection
