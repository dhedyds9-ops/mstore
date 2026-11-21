@extends('layouts.app')

@section('title', __('Pengaturan Jasa & Layanan (Wash)'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-spray-can-sparkles me-1"></i>{{ __('Jasa & Layanan') }}
                </span>
                <span class="text-muted small">{{ __('Konfigurasi Sistem GTWash') }}</span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Pengaturan Layanan Wash') }}</h1>
            <p class="text-muted small mb-0">{{ __('Kelola identitas usaha, aturan bonus loyalty cuci gratis, template nota kasir, dan integrasi bot.') }}</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('wash.pos') }}" class="btn btn-sm btn-outline-primary shadow-2xs">
                <i class="fa-solid fa-cash-register me-1"></i>{{ __('Buka Kasir POS') }}
            </a>
            <a href="{{ route('wash.loyalty.index') }}" class="btn btn-sm btn-outline-secondary shadow-2xs">
                <i class="fa-solid fa-gift me-1"></i>{{ __('Data Loyalty') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Main Form with Modern Tabs --}}
    <form action="{{ route('settings.wash.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Nav Tabs Pills --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-2 p-md-3">
                <ul class="nav nav-pills gap-1 gap-md-2" id="washSettingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-3 px-3 py-2 fw-semibold" id="tab-store-btn" data-bs-toggle="pill" data-bs-target="#tab-store" type="button" role="tab">
                            <i class="fa-solid fa-store me-1.5"></i><span>{{ __('Identitas Toko') }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-3 px-3 py-2 fw-semibold" id="tab-loyalty-btn" data-bs-toggle="pill" data-bs-target="#tab-loyalty" type="button" role="tab">
                            <i class="fa-solid fa-gift me-1.5 text-warning"></i><span>{{ __('Bonus Loyalty (Cuci Gratis)') }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-3 px-3 py-2 fw-semibold" id="tab-receipt-btn" data-bs-toggle="pill" data-bs-target="#tab-receipt" type="button" role="tab">
                            <i class="fa-solid fa-receipt me-1.5 text-info"></i><span>{{ __('Struk Kasir & QRIS') }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-3 px-3 py-2 fw-semibold" id="tab-extra-btn" data-bs-toggle="pill" data-bs-target="#tab-extra" type="button" role="tab">
                            <i class="fa-solid fa-calendar-days me-1.5 text-danger"></i><span>{{ __('Hari Raya & Notifikasi') }}</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Tab Content Sections --}}
        <div class="tab-content" id="washSettingTabsContent">

            {{-- 1. IDENTITAS TOKO --}}
            <div class="tab-pane fade show active" id="tab-store" role="tabpanel" aria-labelledby="tab-store-btn">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="mb-0 fw-bold text-gray-800 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-store text-primary"></i>
                            <span>{{ __('Profil & Kontak Workshop Cuci') }}</span>
                        </h6>
                        <small class="text-muted">{{ __('Data ini akan ditampilkan pada kop nota cetak, pesan WhatsApp, dan identitas resmi cabang.') }}</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="wash_store_name" class="form-label fw-semibold text-secondary small">{{ __('Nama Toko / Usaha Wash') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="wash_store_name" name="wash_store_name" value="{{ \App\Models\Setting::getValue('wash_store_name', \App\Models\Setting::getValue('store_name', config('app.name', 'MStore'))) }}" required>
                                <div class="form-text small">{{ __('Nama outlet yang tertera di struk, struk digital, dan pesan WhatsApp.') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label for="wash_store_phone" class="form-label fw-semibold text-secondary small">{{ __('Nomor Telepon / WhatsApp Outlet') }}</label>
                                <input type="text" class="form-control" id="wash_store_phone" name="wash_store_phone" value="{{ \App\Models\Setting::getValue('wash_store_phone', \App\Models\Setting::getValue('store_phone', '081234567890')) }}">
                                <div class="form-text small">{{ __('Nomor kontak layanan pelanggan / kasir cuci.') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label for="wash_store_logo_file" class="form-label fw-semibold text-secondary small">{{ __('Logo Toko Wash') }}</label>
                                <input type="file" class="form-control" id="wash_store_logo_file" name="wash_store_logo_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <div class="form-text small">{{ __('Format: JPG, PNG, atau WEBP. Maks 2MB. Tampil di kop struk thermal.') }}</div>

                                @if(\App\Models\Setting::getValue('wash_store_logo'))
                                    <div class="d-flex align-items-center gap-3 p-2 mt-2 bg-light rounded-3 border">
                                        <img src="{{ str_starts_with(\App\Models\Setting::getValue('wash_store_logo'), 'http') ? \App\Models\Setting::getValue('wash_store_logo') : asset(\App\Models\Setting::getValue('wash_store_logo')) }}" alt="Logo Toko Wash" class="img-thumbnail" style="max-height: 48px;">
                                        <div class="form-check mb-0">
                                            <input type="hidden" name="clear_wash_store_logo" value="0">
                                            <input class="form-check-input" type="checkbox" value="1" id="clear_wash_store_logo" name="clear_wash_store_logo">
                                            <label class="form-check-label text-danger small fw-semibold" for="clear_wash_store_logo">
                                                <i class="fa-solid fa-trash-can me-1"></i>{{ __('Hapus Logo') }}
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="wash_store_address" class="form-label fw-semibold text-secondary small">{{ __('Alamat Lengkap Outlet') }}</label>
                                <textarea class="form-control" id="wash_store_address" name="wash_store_address" rows="3">{{ \App\Models\Setting::getValue('wash_store_address', \App\Models\Setting::getValue('store_address', 'Jl. Contoh No. 1')) }}</textarea>
                                <div class="form-text small">{{ __('Alamat fisik lokasi cucian untuk dicetak di struk pelanggan.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. BONUS LOYALTY (CUCI GRATIS) --}}
            <div class="tab-pane fade" id="tab-loyalty" role="tabpanel" aria-labelledby="tab-loyalty-btn">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="mb-0 fw-bold text-gray-800 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-gift text-warning"></i>
                            <span>{{ __('Program Loyalty: Cuci Bayar & Bonus Gratis') }}</span>
                        </h6>
                        <small class="text-muted">{{ __('Atur jumlah stamp cuci berbayar untuk mendapatkan cuci gratis dan siapa yang berhak menerimanya.') }}</small>
                    </div>
                    <div class="card-body p-4">
                        {{-- Info Box Panduan --}}
                        <div class="alert alert-primary bg-primary-subtle border-0 rounded-4 p-3 mb-4 d-flex align-items-start gap-3">
                            <i class="fa-solid fa-circle-info fs-4 text-primary mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-primary mb-1">{{ __('Cara Kerja Kupon Stamp Otomatis:') }}</h6>
                                <p class="small text-muted mb-0">
                                    Setiap kali pelanggan menyelesaikan transaksi cuci berbayar, sistem otomatis mencatat 1 stamp. Ketika stamp mencapai kelipatan target (contoh: 10 cuci bayar), sistem otomatis menerbitkan voucher reward cuci ke-11 gratis (Rp 0) dan mengirim notifikasi WhatsApp langsung ke HP pelanggan.
                                </p>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="wash_loyalty_target" class="form-label fw-semibold text-secondary small">
                                    {{ __('Target Total Kunjungan untuk Cuci Gratis') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-bullseye"></i></span>
                                    <input type="number" min="2" max="100" class="form-control" id="wash_loyalty_target" name="wash_loyalty_target" value="{{ \App\Models\Setting::getValue('wash_loyalty_target', '11') }}" required>
                                    <span class="input-group-text bg-light text-muted">{{ __('Kunjungan') }}</span>
                                </div>
                                <div class="form-text small mt-1">
                                    <strong>{{ __('Contoh:') }}</strong> Jika diisi <code>11</code>, maka pelanggan <strong>bayar 10 kali</strong> dan <strong>kunjungan ke-11 GRATIS</strong>.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="wash_loyalty_member_only" class="form-label fw-semibold text-secondary small">
                                    {{ __('Siapa yang Berhak Mendapat Bonus Loyalty?') }} <span class="text-danger">*</span>
                                </label>
                                @php $isMemberOnly = \App\Models\Setting::getValue('wash_loyalty_member_only', '0'); @endphp
                                <select class="form-select" id="wash_loyalty_member_only" name="wash_loyalty_member_only">
                                    <option value="0" {{ $isMemberOnly == '0' ? 'selected' : '' }}>
                                        👥 {{ __('Semua Pelanggan (Berdasarkan Plat Nomor)') }}
                                    </option>
                                    <option value="1" {{ $isMemberOnly == '1' ? 'selected' : '' }}>
                                        👑 {{ __('Khusus Anggota Member Terdaftar Saja') }}
                                    </option>
                                </select>
                                <div class="form-text small mt-1">
                                    Pilih <strong>Khusus Anggota Member</strong> agar program cuci gratis menjadi benefit eksklusif yang memotivasi pelanggan untuk mendaftar sebagai Member resmi.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. STRUK KASIR & QRIS --}}
            <div class="tab-pane fade" id="tab-receipt" role="tabpanel" aria-labelledby="tab-receipt-btn">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="mb-0 fw-bold text-gray-800 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-receipt text-info"></i>
                            <span>{{ __('Pengaturan Struk Kasir (Nota Thermal) & QRIS') }}</span>
                        </h6>
                        <small class="text-muted">{{ __('Sesuaikan teks header, pesan footer struk, dan kode string pembayaran QRIS kasir.') }}</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="wash_receipt_title" class="form-label fw-semibold text-secondary small">{{ __('Judul Header Nota') }}</label>
                                <input type="text" class="form-control" id="wash_receipt_title" name="wash_receipt_title" value="{{ \App\Models\Setting::getValue('wash_receipt_title', 'NOTA PEMBAYARAN') }}">
                                <div class="form-text small">{{ __('Teks judul di bagian atas struk pembayaran.') }}</div>
                            </div>

                            <div class="col-md-6">
                                <label for="wash_receipt_powered_by" class="form-label fw-semibold text-secondary small">{{ __('Teks Powered / Hak Cipta') }}</label>
                                <input type="text" class="form-control" id="wash_receipt_powered_by" name="wash_receipt_powered_by" value="{{ \App\Models\Setting::getValue('wash_receipt_powered_by', 'POWERED BY MSTORE') }}">
                                <div class="form-text small">{{ __('Teks promosi / penyedia sistem di bagian paling bawah struk.') }}</div>
                            </div>

                            <div class="col-md-6">
                                <label for="wash_receipt_footer_title" class="form-label fw-semibold text-secondary small">{{ __('Judul Footer Nota') }}</label>
                                <input type="text" class="form-control" id="wash_receipt_footer_title" name="wash_receipt_footer_title" value="{{ \App\Models\Setting::getValue('wash_receipt_footer_title', '*** TERIMA KASIH ***') }}">
                                <div class="form-text small">{{ __('Salam penutup sebelum ucapan terima kasih.') }}</div>
                            </div>

                            <div class="col-md-6">
                                <label for="wash_receipt_footer_message" class="form-label fw-semibold text-secondary small">{{ __('Pesan Footer Nota') }}</label>
                                <input type="text" class="form-control" id="wash_receipt_footer_message" name="wash_receipt_footer_message" value="{{ \App\Models\Setting::getValue('wash_receipt_footer_message', 'Kepuasan Anda Kebanggaan Kami.') }}">
                                <div class="form-text small">{{ __('Slogan atau pesan apresiasi kepada pelanggan.') }}</div>
                            </div>

                            <div class="col-12">
                                <label for="wash_receipt_footer_note" class="form-label fw-semibold text-secondary small">{{ __('Catatan Pengingat Barang') }}</label>
                                <input type="text" class="form-control" id="wash_receipt_footer_note" name="wash_receipt_footer_note" value="{{ \App\Models\Setting::getValue('wash_receipt_footer_note', 'Periksa kembali barang bawaan Anda sebelum meninggalkan lokasi.') }}">
                                <div class="form-text small">{{ __('Peringatan keselamatan atau barang bawaan pelanggan di workshop.') }}</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold text-gray-800 d-flex align-items-center gap-2 mb-3">
                            <i class="fa-solid fa-qrcode text-primary"></i>
                            <span>{{ __('Pembayaran QRIS Kasir') }}</span>
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="pos_qris_text" class="form-label fw-semibold text-secondary small">{{ __('Data String / Kode Payload QRIS') }}</label>
                                <textarea class="form-control font-monospace" id="pos_qris_text" name="pos_qris_text" rows="3" placeholder="00020101021126570014ID.LINKAJA.WWW01189360091100224098675204581253033605802ID5911MSTORE WASH6007BANDUNG...">{{ \App\Models\Setting::getValue('pos_qris_text', '') }}</textarea>
                                <div class="form-text small">{{ __('Isi string QRIS statis (EMVCo) untuk memunculkan barcode QRIS dinamis di layar kasir POS cuci.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. HARI RAYA & NOTIFIKASI --}}
            <div class="tab-pane fade" id="tab-extra" role="tabpanel" aria-labelledby="tab-extra-btn">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h6 class="mb-0 fw-bold text-gray-800 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-calendar-days text-danger"></i>
                            <span>{{ __('Penyesuaian Tarif Hari Raya') }}</span>
                        </h6>
                        <small class="text-muted">{{ __('Jadwalkan tanggal aktif tarif musiman hari raya (Lebaran, Natal, Tahun Baru) secara otomatis.') }}</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="wash_holiday_pricing_start_date" class="form-label fw-semibold text-secondary small">{{ __('Tanggal Mulai Tarif Hari Raya') }}</label>
                                <input type="date" class="form-control" id="wash_holiday_pricing_start_date" name="wash_holiday_pricing_start_date" value="{{ \App\Models\Setting::getValue('wash_holiday_pricing_start_date', '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="wash_holiday_pricing_end_date" class="form-label fw-semibold text-secondary small">{{ __('Tanggal Selesai Tarif Hari Raya') }}</label>
                                <input type="date" class="form-control" id="wash_holiday_pricing_end_date" name="wash_holiday_pricing_end_date" value="{{ \App\Models\Setting::getValue('wash_holiday_pricing_end_date', '') }}">
                            </div>
                            <div class="col-12">
                                <div class="form-text small">
                                    {{ __('Tarif hari raya pada master layanan cuci akan aktif otomatis saat tanggal transaksi kasir berada di antara tanggal mulai dan selesai.') }}
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="wash_receipt_holiday_greeting" class="form-label fw-semibold text-secondary small">{{ __('Ucapan Hari Raya pada Struk Kasir') }}</label>
                                <input type="text" class="form-control" id="wash_receipt_holiday_greeting" name="wash_receipt_holiday_greeting" value="{{ \App\Models\Setting::getValue('wash_receipt_holiday_greeting', 'Selamat Hari Raya Idul Fitri, Mohon Maaf Lahir & Batin.') }}">
                                <div class="form-text small">{{ __('Ucapan ini hanya akan dicetak di struk nota saat masa tarif hari raya aktif.') }}</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-gray-800 d-flex align-items-center gap-2 mb-0">
                                <i class="fa-brands fa-telegram text-info"></i>
                                <span>{{ __('Notifikasi Transaksi ke Bot Telegram') }}</span>
                            </h6>
                            <a href="{{ route('telegram.index') }}" class="btn btn-sm btn-outline-info">
                                <i class="fa-brands fa-telegram me-1"></i>{{ __('Pengaturan Token Telegram') }}
                            </a>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch mb-0">
                                        <input type="hidden" name="telegram_wash_notification_enabled" value="0">
                                        <input class="form-check-input" type="checkbox" value="1" id="telegram_wash_notification_enabled" name="telegram_wash_notification_enabled" {{ \App\Models\Setting::getValue('telegram_wash_notification_enabled', '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark small" for="telegram_wash_notification_enabled">
                                            {{ __('Kirim Notifikasi Setiap Transaksi Selesai') }}
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ __('Bot akan otomatis mengirim ringkasan struk dan omzet transaksi ke grup Telegram Anda.') }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="telegram_wash_group_id" class="form-label fw-semibold text-secondary small">{{ __('ID Grup / Chat ID Telegram') }}</label>
                                <input type="text" class="form-control" id="telegram_wash_group_id" name="telegram_wash_group_id" value="{{ \App\Models\Setting::getValue('telegram_wash_group_id', '') }}" placeholder="-1001234567890">
                                <div class="form-text small">{{ __('Masukkan ID grup Telegram yang sudah mengundang Bot Telegram MStore.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Persistent Action Button --}}
        <div class="card border-0 shadow-sm rounded-4 bg-white py-3 px-4 mb-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <div class="text-muted small">
                    <i class="fa-solid fa-circle-check text-success me-1"></i>
                    <span>{{ __('Perubahan pengaturan akan langsung diterapkan secara instan ke sistem kasir POS dan struk.') }}</span>
                </div>
                <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>{{ __('Simpan Seluruh Pengaturan') }}</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Remember Active Tab via Hash in URL
    document.addEventListener('DOMContentLoaded', function () {
        const tabBtns = document.querySelectorAll('#washSettingTabs button[data-bs-toggle="pill"]');
        tabBtns.forEach(btn => {
            btn.addEventListener('shown.bs.tab', function (e) {
                const target = e.target.getAttribute('data-bs-target');
                if (target) {
                    history.replaceState(null, null, ' ' + target);
                }
            });
        });

        const activeHash = window.location.hash;
        if (activeHash) {
            const triggerEl = document.querySelector('#washSettingTabs button[data-bs-target="' + activeHash + '"]');
            if (triggerEl && typeof bootstrap !== 'undefined') {
                bootstrap.Tab.getOrCreateInstance(triggerEl).show();
            }
        }
    });
</script>
@endpush
