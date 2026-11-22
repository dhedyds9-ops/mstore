@extends('layouts.app')

@section('title', __('Kasir Jasa & Layanan'))

@section('content')
<div class="wash-pos-page">
    <div class="wash-pos-shell">
        <header class="wash-pos-header">
            <span id="current-time" class="wash-current-time"></span>
        </header>

        <div class="row g-4">
            <div class="col-12 col-lg-8 order-2 order-lg-1">
                <div class="wash-card">
                    <div class="wash-card-header">
                        <h2 class="wash-card-title"><i class="fas fa-th-large"></i> Pilih Layanan</h2>
                        <div class="wash-filter-group">
                            <button class="filter-btn active" data-filter="all" type="button">Semua</button>
                            <button class="filter-btn" data-filter="mobil" type="button">Mobil</button>
                            <button class="filter-btn" data-filter="motor" type="button">Motor</button>
                            <button class="filter-btn" data-filter="addon" type="button">Add On</button>
                            <button class="filter-btn" data-filter="caffe" type="button">Caffe</button>
                        </div>
                        <div class="mt-2">
                            <input type="text" id="serviceSearchInput" class="form-control form-control-sm wash-input" placeholder="Cari layanan cepat...">
                        </div>
                    </div>
                    <div class="wash-card-body">
                        <div class="row g-3" id="services-container">
                            @php
                                $lastFilterType = null;
                                $sectionLabels = [
                                    'mobil' => 'Layanan Mobil',
                                    'motor' => 'Layanan Motor',
                                    'addon' => 'Add On & Skincare',
                                    'caffe' => 'Caffe',
                                    'umum' => 'Layanan Umum',
                                ];
                            @endphp
                            @foreach($services as $service)
                            @php
                                $rawType = strtolower((string) ($service->vehicle_type ?? ''));
                                $categoryRaw = strtolower((string) ($service->service_category ?? 'main'));
                                if ($rawType === 'car') {
                                    $normalizedType = 'mobil';
                                } elseif ($rawType === 'coffee') {
                                    $normalizedType = 'caffe';
                                } else {
                                    $normalizedType = $rawType;
                                }
                                $addonTypeClass = in_array($normalizedType, ['mobil', 'motor', 'caffe'], true) ? $normalizedType : 'umum';
                                $filterType = in_array($categoryRaw, ['addon', 'skincare'], true) ? 'addon' : $normalizedType;
                                $serviceTypeClass = in_array($filterType, ['mobil', 'motor', 'caffe', 'addon']) ? $filterType : 'umum';
                                $fallbackIcon = $normalizedType === 'mobil'
                                    ? 'fa-car-side'
                                    : ($normalizedType === 'motor'
                                        ? 'fa-motorcycle'
                                        : ($normalizedType === 'caffe' ? 'fa-mug-hot' : ($filterType === 'addon' ? 'fa-plus-circle' : 'fa-soap')));
                                $adjustment = is_null($service->holiday_price) ? null : (float) $service->holiday_price;
                                $isHolidayActive = (bool) ($holidaySchedule['active'] ?? false);
                                $effectivePrice = $isHolidayActive && !is_null($adjustment)
                                    ? max(0, ((float) $service->price) + $adjustment)
                                    : (float) $service->price;
                                $rulePayload = $service->priceRules->map(function ($rule) use ($isHolidayActive, $adjustment, $categoryRaw) {
                                    $rulePrice = (float) $rule->price;
                                    if ($isHolidayActive && !is_null($adjustment)) {
                                        $rulePrice = max(0, $rulePrice + (float) $adjustment);
                                    }
                                    $ruleLabel = (string) $rule->label;
                                    if (! in_array($categoryRaw, ['addon', 'skincare'], true)) {
                                        $ruleLabel = preg_replace('/^(Kecil|Sedang|Besar|Extra Besar)\s*-\s*/i', '', $ruleLabel);
                                    }
                                    $ruleLabel = trim((string) $ruleLabel);
                                    if ($ruleLabel === '') {
                                        $ruleLabel = (string) $rule->label;
                                    }

                                    return [
                                        'id' => $rule->id,
                                        'label' => $ruleLabel,
                                        'price' => $rulePrice,
                                    ];
                                })->values();
                            @endphp
                            @if($lastFilterType !== $filterType)
                                <div class="col-12 service-section-heading" data-type="{{ $filterType }}">
                                    <h6 class="mb-1 mt-2">{{ $sectionLabels[$filterType] ?? 'Layanan' }}</h6>
                                </div>
                                @php $lastFilterType = $filterType; @endphp
                            @endif
                            <div class="col-6 col-md-4 service-item" data-type="{{ $filterType }}">
                                <div class="service-card service-card-{{ $serviceTypeClass }}" data-fasttap
                                     data-id="{{ $service->id }}"
                                     data-name="{{ $service->name }}"
                                     data-price="{{ $effectivePrice }}"
                                     data-description="{{ $service->description }}"
                                     data-vehicletype="{{ $normalizedType }}"
                                     data-rules='@json($rulePayload)'>
                                    <div class="service-image-wrap">
                                        @if($service->image)
                                            <img src="{{ asset('storage/' . $service->image) }}" class="img-fluid service-image" alt="{{ $service->name }}" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <i class="fas {{ $fallbackIcon }} service-fallback-icon" style="display: none;"></i>
                                        @else
                                            <i class="fas {{ $fallbackIcon }} service-fallback-icon"></i>
                                        @endif
                                    </div>
                                    <h5 class="service-title">{{ $service->name }}</h5>
                                    @if($rulePayload->count() > 0)
                                        <div class="service-rule-picker mb-2">
                                            <select class="form-select form-select-sm service-rule-select">
                                                @foreach($rulePayload as $rule)
                                                    <option value="{{ $rule['id'] }}" data-label="{{ $rule['label'] }}" data-price="{{ $rule['price'] }}">
                                                        {{ $rule['label'] }} - Rp {{ number_format($rule['price'], 0, ',', '.') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="service-rule-mobile-options mt-2">
                                                @foreach($rulePayload as $idx => $rule)
                                                    <label class="service-rule-mobile-option">
                                                        <input type="radio" class="service-rule-mobile-input" name="service_rule_mobile_{{ $service->id }}" value="{{ $rule['id'] }}" data-label="{{ $rule['label'] }}" data-price="{{ $rule['price'] }}" {{ $idx === 0 ? 'checked' : '' }}>
                                                        <span class="service-rule-mobile-chip">{{ $rule['label'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(!empty($service->description))
                                        @php
                                            $descriptionItems = array_values(array_filter(
                                                preg_split('/\s*[,;\n]+\s*/', trim((string) $service->description)),
                                                function ($item) {
                                                    $item = trim((string) $item);
                                                    if ($item === '') {
                                                        return false;
                                                    }
                                                    if (preg_match('/^dan\s+sejenis/i', $item)) {
                                                        return false;
                                                    }
                                                    if (preg_match('/^(cocok|perawatan|pembersihan|khusus)\b/i', $item)) {
                                                        return false;
                                                    }
                                                    return str_word_count($item) <= 5;
                                                }
                                            ));
                                        @endphp
                                        @if(!empty($descriptionItems))
                                            <div class="service-description-chips">
                                                @foreach($descriptionItems as $item)
                                                    <span class="service-description-chip">{{ $item }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="service-description-chip service-description-chip-empty">-</span>
                                        @endif
                                    @else
                                        <span class="service-description-chip service-description-chip-empty">-</span>
                                    @endif
                                    <div class="service-meta">
                                        <span class="service-price" data-default-price="{{ $effectivePrice }}">Rp {{ number_format($effectivePrice, 0, ',', '.') }}</span>
                                        @if(!is_null($adjustment))
                                        <span class="service-adjustment bg-warning text-dark">
                                            Adj {{ $adjustment >= 0 ? '+' : '-' }}Rp {{ number_format(abs($adjustment), 0, ',', '.') }}
                                        </span>
                                        @endif
                                        @if($filterType === 'addon')
                                        <span class="service-type service-type-{{ $addonTypeClass }}">
                                            Tipe {{ ucfirst($normalizedType) }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4 order-1 order-lg-2">
                <div class="wash-card wash-checkout-card">
                    <div class="wash-card-header">
                        <h2 class="wash-card-title"><i class="fas fa-shopping-cart"></i> Rincian Transaksi</h2>
                    </div>
                    <div class="wash-card-body">
                        @if(($holidaySchedule['active'] ?? false) && !empty($holidaySchedule['start_date']) && !empty($holidaySchedule['end_date']))
                            <div class="alert alert-warning py-2 px-3 mb-3">
                                Harga hari raya aktif otomatis ({{ \Carbon\Carbon::parse($holidaySchedule['start_date'])->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($holidaySchedule['end_date'])->translatedFormat('d M Y') }})
                            </div>
                        @endif
                        <form id="checkoutForm" class="checkout-form">
                            <!-- Kendaraan: Merek & Plat Nomor -->
                            <div class="row g-2 mb-3">
                                <div class="col-5">
                                    <label for="vehicle_brand" class="wash-field-label">Merek</label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control wash-input" id="vehicle_brand_search" placeholder="Cari merek..." autocomplete="off">
                                        <select class="form-select wash-input position-absolute top-100 start-0 end-0 z-1 d-none" id="vehicle_brand" name="vehicle_brand">
                                            <option value="">Pilih</option>
                                            <optgroup label="Motor">
                                                @foreach($brands['Motor'] as $brand)
                                                    <option value="{{ $brand }}">{{ $brand }}</option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="Mobil">
                                                @foreach($brands['Mobil'] as $brand)
                                                    <option value="{{ $brand }}">{{ $brand }}</option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                        <div class="list-group position-absolute top-100 start-0 end-0 z-2 d-none shadow-sm" id="vehicle_brand_dropdown"></div>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <label for="vehicle_plate" class="wash-field-label d-flex justify-content-between align-items-center">
                                        <span>Plat Nomor</span>
                                        <span class="text-muted fw-normal" style="font-size: 10px; text-transform: none;">Riwayat Otomatis</span>
                                    </label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control wash-input text-uppercase fw-bold" id="vehicle_plate" name="vehicle_plate" placeholder="B 1234 ABC" autocomplete="off">
                                        <div class="list-group position-absolute top-100 start-0 end-0 z-3 d-none shadow-sm" id="vehicle_plate_dropdown" style="max-height: 200px; overflow-y: auto;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Pelanggan & Member -->
                            <div class="customer-section-box mb-3 p-2 rounded-3 border bg-light-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="fas fa-id-card text-primary"></i>
                                        <span class="wash-field-label mb-0 text-dark fw-bold">Pelanggan</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" id="btnOpenAddMemberModal" data-bs-toggle="modal" data-bs-target="#posAddMemberModal" style="font-size: 11px; height: 24px; border-radius: 6px;">
                                        <i class="fas fa-user-plus me-1"></i>+ Member Baru
                                    </button>
                                </div>

                                <!-- Tab Switch: Pelanggan Umum vs Anggota Member -->
                                <div class="btn-group w-100 mb-2" role="group">
                                    <input type="radio" class="btn-check" name="customer_mode_toggle" id="modeCustomerUmum" value="umum" checked autocomplete="off">
                                    <label class="btn btn-sm btn-outline-secondary py-1 fw-semibold" for="modeCustomerUmum" style="font-size: 11.5px;">
                                        <i class="fas fa-user me-1"></i> Umum
                                    </label>

                                    <input type="radio" class="btn-check" name="customer_mode_toggle" id="modeCustomerMember" value="member" autocomplete="off">
                                    <label class="btn btn-sm btn-outline-warning text-dark py-1 fw-semibold" for="modeCustomerMember" id="modeCustomerMemberLabel" style="font-size: 11.5px;">
                                        <i class="fas fa-crown text-warning me-1"></i> Member
                                    </label>
                                </div>

                                <input type="hidden" id="wash_member_id" name="wash_member_id" value="">

                                <!-- Mode 1: Pelanggan Umum -->
                                <div id="panelCustomerUmum">
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-white text-muted" style="height: 38px;">
                                            <i class="fab fa-whatsapp text-success"></i>
                                        </span>
                                        <input type="text" class="form-control wash-input border-start-0" id="customer_phone" name="customer_phone" placeholder="No. WhatsApp / HP">
                                        <button class="btn wash-secondary-btn" type="button" id="btnCheckCustomer" title="Cek riwayat cuci & bonus" style="height: 38px;">Cek</button>
                                    </div>
                                    <input type="text" class="form-control wash-input" id="customer_name" name="customer_name" placeholder="Nama Pelanggan (opsional)">
                                </div>

                                <!-- Mode 2: Anggota Member -->
                                <div id="panelCustomerMember" style="display: none;">
                                    <!-- Search Input Member (aktif saat belum ada member terpilih) -->
                                    <div class="position-relative mb-2" id="memberSearchBoxWrap">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 text-muted" style="height: 38px;">
                                                <i class="fas fa-crown text-warning"></i>
                                            </span>
                                            <input type="text" class="form-control wash-input border-start-0" id="member_search" placeholder="Cari nama, No. HP, No. Member..." autocomplete="off">
                                            <button class="btn wash-secondary-btn" type="button" id="btnClearMember" title="Reset Member" style="display:none; height: 38px;">
                                                <i class="fas fa-times text-danger"></i>
                                            </button>
                                        </div>
                                        <div class="list-group position-absolute top-100 start-0 end-0 z-3 shadow-sm d-none" id="member_search_dropdown" style="max-height: 220px; overflow-y: auto;"></div>
                                    </div>

                                    <!-- Member Info Card (aktif saat member terpilih) -->
                                    <div id="memberInfoCard" class="card border border-warning bg-warning bg-opacity-10" style="display:none;">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                                        <i class="fas fa-crown text-warning"></i> <span id="memberInfoName">-</span>
                                                    </div>
                                                    <div class="small text-muted" id="memberInfoNumber">-</div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 fw-semibold" id="btnDetachMember" title="Lepas status member" style="font-size: 11px;">
                                                    <i class="fas fa-times me-1"></i>Lepas
                                                </button>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 small mt-2">
                                                <span class="badge bg-primary" id="memberInfoLevel">Bronze Member</span>
                                                <span class="badge bg-success" id="memberInfoDiscount">Diskon 0%</span>
                                                <span class="badge bg-dark" id="memberInfoVisits">0 Kunjungan</span>
                                            </div>
                                            <div id="memberVehiclesWrap" class="mt-2 pt-2 border-top border-warning-subtle" style="display:none;">
                                                <small class="text-muted d-block mb-1 fw-semibold" style="font-size:10.5px;">Kendaraan Terdaftar (Klik untuk isi):</small>
                                                <div class="d-flex flex-wrap gap-1" id="memberVehiclesChips"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Box Notifikasi Progres Loyalty / Cuci Gratis -->
                                <div id="customerInfo" class="mt-2" style="display:none;"></div>
                            </div>

                            <div class="form-check mb-3 wash-inline-check">
                                <input class="form-check-input" type="checkbox" id="send_whatsapp">
                                <label class="form-check-label" for="send_whatsapp">Kirim nota via WhatsApp</label>
                            </div>

                            <div class="mb-3" id="voucherSection" style="display:none;">
                                <div class="wash-voucher-box">
                                    <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
                                        <div>
                                            <div>
                                                <i class="fas fa-ticket-alt me-1"></i> Bonus cuci tersedia
                                                (<span id="voucherCount">0</span>)
                                            </div>
                                            <small id="voucherInfoText" class="text-success opacity-75" style="font-weight:500;">
                                                Pilih voucher di bawah untuk GRATIS 1x cuci.
                                            </small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <select id="voucher_code" class="form-select form-select-sm wash-input" style="min-width: 180px;">
                                                <option value="">Pilih kode voucher</option>
                                            </select>
                                            <label class="form-check-label wash-use-voucher-label" for="use_voucher">
                                                <input class="form-check-input me-1" type="checkbox" id="use_voucher" name="use_voucher" style="width:1.1em;height:1.1em;vertical-align:-2px;">
                                                <span class="fw-bold ms-1">PAKAI VOUCHER</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="small text-muted mt-1">
                                    ⚠️ Syarat: <b>1 transaksi hanya 1 layanan (qty 1)</b>, voucher sesuai plat & tipe kendaraan, tidak bisa digabung diskon member.
                                </div>
                            </div>

                            <div id="cartItems" class="mb-3 custom-scrollbar">
                                <p class="text-center text-muted py-4 mb-0" id="emptyCartMsg">Keranjang masih kosong</p>
                            </div>

                            <div class="wash-summary-row wash-summary-divider">
                                <span>Subtotal</span>
                                <span id="subtotalAmount">Rp 0</span>
                            </div>
                            <div class="wash-summary-row" id="memberDiscountRow" style="display:none;">
                                <span>Diskon Member</span>
                                <span id="memberDiscountAmount">Rp 0</span>
                            </div>
                            <div class="wash-summary-row" id="voucherDiscountRow" style="display:none;">
                                <span>Diskon Voucher</span>
                                <span id="voucherDiscountAmount">Rp 0</span>
                            </div>
                            <div class="wash-summary-row bg-success bg-opacity-10 text-success fw-bold" id="instantBonusRow" style="display:none;">
                                <span>🎁 Bonus Loyalty (GRATIS)</span>
                                <span id="instantBonusAmount">Rp 0</span>
                            </div>
                            <div id="bonusChoiceRow" class="card card-body bg-warning bg-opacity-10 mt-2 p-2 d-none">
                                <div class="fw-bold text-warning mb-2 small">⚡ Anda dapat BONUS CUCI GRATIS!</div>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="bonus_apply_mode" id="bonusApplyNow" value="now">
                                    <label class="form-check-label small fw-bold text-success" for="bonusApplyNow">
                                        ✅ PAKAI BONUS INI — Gratis Rp0
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="bonus_apply_mode" id="bonusSaveForLater" value="save">
                                    <label class="form-check-label small fw-bold text-primary" for="bonusSaveForLater">
                                        💾 SIMPAN UNTUK NANTI — Bayar biasa dulu
                                    </label>
                                </div>
                                <input type="hidden" name="skip_auto_redeem_voucher" id="skip_auto_redeem_voucher" value="0">
                            </div>
                            <div class="wash-summary-row wash-summary-total">
                                <span>Total Akhir</span>
                                <span id="totalAmount">Rp 0</span>
                            </div>

                            <div class="row g-2 mt-3">
                                <div class="col-6">
                                    <select class="form-select wash-input" id="payment_method">
                                        <option value="cash">💵 Tunai</option>
                                        <option value="qris">📱 QRIS</option>
                                        <option value="transfer">🏦 Transfer</option>
                                        <option value="edc">💳 EDC</option>
                                        <option value="kasbon">📜 Kasbon</option>
                                    </select>
                                </div>
                                <div class="col-6" id="cashSection">
                                    <input type="number" class="form-control wash-input" id="cash_amount" name="cash_amount" placeholder="Bayar Tunai..." oninput="calculateChange()">
                                </div>
                            </div>

                            <div id="changeDisplay" class="wash-change-box mt-2">
                                <span>Kembalian</span>
                                <strong id="changeAmount">Rp 0</strong>
                            </div>

                            <div id="kasbonSection" class="mt-3" style="display: none;">
                                <div class="card card-body bg-light">
                                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-tie me-1"></i> Detail Kasbon</h6>
                                    <div class="mb-3">
                                        <label class="wash-field-label">Tipe Pihak Kasbon</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="kasbon_type" id="kasbon_employee" value="employee" checked>
                                            <label class="form-check-label" for="kasbon_employee">Karyawan (Daftar Akun)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="kasbon_type" id="kasbon_outsider" value="outsider">
                                            <label class="form-check-label" for="kasbon_outsider">Orang Luar / Nama Custom</label>
                                        </div>
                                    </div>
                                    <div id="kasbon_employee_section">
                                        <label for="kasbon_user_id" class="wash-field-label">Pilih Karyawan</label>
                                        <select class="form-select wash-input" id="kasbon_user_id" name="kasbon_user_id">
                                            <option value="">-- Pilih Karyawan --</option>
                                            @foreach($allUsers as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="kasbon_outsider_section" style="display: none;">
                                        <label for="kasbon_name" class="wash-field-label">Nama Pihak Kasbon</label>
                                        <input type="text" class="form-control wash-input" id="kasbon_name" name="kasbon_name" placeholder="Masukkan nama...">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn wash-primary-btn w-100 mt-3" id="btnCheckout" disabled>Proses Pembayaran</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="loyaltyBonusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Info Loyalty</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Informasi loyalty.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnLoyaltyBonusOk">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Daftar Member Baru di POS -->
<div class="modal fade" id="posAddMemberModal" tabindex="-1" aria-labelledby="posAddMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="posAddMemberForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="posAddMemberModalLabel">
                        <i class="fas fa-crown text-warning me-1"></i> Pendaftaran Member Baru di Kasir
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="posAddMemberAlert" class="alert alert-danger d-none py-2 px-3 small"></div>
                    <div class="mb-3">
                        <label for="pos_member_name" class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pos_member_name" required placeholder="Contoh: Budi Santoso">
                    </div>
                    <div class="mb-3">
                        <label for="pos_member_phone" class="form-label fw-bold">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pos_member_phone" required placeholder="Contoh: 081234567890">
                        <small class="text-muted" style="font-size:11px;">Nomor aktif untuk pengiriman struk dan poin loyalitas.</small>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="pos_member_plate" class="form-label fw-bold">Plat Kendaraan</label>
                            <input type="text" class="form-control text-uppercase" id="pos_member_plate" placeholder="B 1234 ABC">
                        </div>
                        <div class="col-6">
                            <label for="pos_member_brand" class="form-label fw-bold">Merek / Model</label>
                            <input type="text" class="form-control" id="pos_member_brand" placeholder="Toyota / Honda">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="pos_member_type" class="form-label fw-bold">Tipe Kendaraan</label>
                        <select class="form-select" id="pos_member_type">
                            <option value="mobil">Mobil</option>
                            <option value="motor">Motor</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold" id="btnSubmitPosMember">
                        <i class="fas fa-check-circle me-1"></i> Simpan & Pilih Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const applyNowRadio = document.getElementById('bonusApplyNow');
    const saveLaterRadio = document.getElementById('bonusSaveForLater');
    if (applyNowRadio) applyNowRadio.addEventListener('change', updateCartUI);
    if (saveLaterRadio) saveLaterRadio.addEventListener('change', updateCartUI);

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Focus search with /
        if (e.key === '/' && !e.ctrlKey && !e.altKey) {
            e.preventDefault();
            const searchInput = document.getElementById('serviceSearchInput');
            if (searchInput) {
                searchInput.focus();
            }
        }
        // Submit checkout with Enter (when not in input)
        if (e.key === 'Enter' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
            const btnCheckout = document.getElementById('btnCheckout');
            if (btnCheckout && !btnCheckout.disabled) {
                e.preventDefault();
                const checkoutForm = document.getElementById('checkoutForm');
                if (checkoutForm) {
                    const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
                    checkoutForm.dispatchEvent(submitEvent);
                }
            }
        }
        // Clear cart with Ctrl+Shift+K
        if (e.ctrlKey && e.shiftKey && e.key === 'K') {
            e.preventDefault();
            if (confirm('Yakin ingin mengosongkan keranjang dan mereset form?')) {
                resetCart();
            }
        }
    });

    const RULE_INTERACTION_LOCK_MS = 500;
    const CARD_ADD_LOCK_MS = 320;

    const markCardRuleInteraction = function (card) {
        if (!card) {
            return;
        }
        card.dataset.ruleInteractedAt = String(Date.now());
    };

    const shouldBlockCardAdd = function (card) {
        const now = Date.now();
        const lastRuleInteraction = parseInt(card?.dataset?.ruleInteractedAt || '0', 10) || 0;
        const lastAddAt = parseInt(card?.dataset?.lastAddAt || '0', 10) || 0;
        if (lastRuleInteraction > 0 && (now - lastRuleInteraction) < RULE_INTERACTION_LOCK_MS) {
            return true;
        }
        if (lastAddAt > 0 && (now - lastAddAt) < CARD_ADD_LOCK_MS) {
            return true;
        }
        return false;
    };

    const updateCardDisplayedPrice = function (card, value) {
        const priceEl = card.querySelector('.service-price');
        if (priceEl) {
            priceEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
        }
    };

    const syncCardRuleSelection = function (card, sourceEl) {
        const selectedValue = String(sourceEl?.value || '');
        const selectedLabel = sourceEl?.dataset?.label || '';
        const selectedPrice = parseFloat(sourceEl?.dataset?.price || 0);
        const selectEl = card.querySelector('.service-rule-select');
        if (selectEl) {
            selectEl.value = selectedValue;
        }
        card.querySelectorAll('.service-rule-mobile-input').forEach(function (radioEl) {
            radioEl.checked = String(radioEl.value) === selectedValue;
        });
        updateCardDisplayedPrice(card, selectedPrice);
        return {
            id: parseInt(selectedValue || 0),
            label: selectedLabel,
            price: selectedPrice,
        };
    };

    const getCardSelectedRule = function (card, rules) {
        const selectedRadio = card.querySelector('.service-rule-mobile-input:checked');
        if (selectedRadio) {
            return syncCardRuleSelection(card, selectedRadio);
        }
        const selectEl = card.querySelector('.service-rule-select');
        if (selectEl) {
            const selected = selectEl.options[selectEl.selectedIndex];
            return syncCardRuleSelection(card, selected);
        }
        return rules[0] || null;
    };

    document.querySelectorAll('.service-rule-select').forEach(function (selectEl) {
        ['click', 'mousedown', 'touchstart'].forEach(function (evt) {
            selectEl.addEventListener(evt, function (event) {
                event.stopPropagation();
                markCardRuleInteraction(this.closest('.service-card'));
            });
        });
        selectEl.addEventListener('change', function (event) {
            event.stopPropagation();
            const card = this.closest('.service-card');
            if (!card) {
                return;
            }
            markCardRuleInteraction(card);
            const option = this.options[this.selectedIndex];
            syncCardRuleSelection(card, option);
        });
    });

    document.querySelectorAll('.service-rule-mobile-input').forEach(function (radioEl) {
        ['click', 'mousedown', 'touchstart'].forEach(function (evt) {
            radioEl.addEventListener(evt, function (event) {
                event.stopPropagation();
                markCardRuleInteraction(this.closest('.service-card'));
            });
        });
        radioEl.addEventListener('change', function (event) {
            event.stopPropagation();
            const card = this.closest('.service-card');
            if (!card) {
                return;
            }
            markCardRuleInteraction(card);
            syncCardRuleSelection(card, this);
        });
    });

    document.querySelectorAll('.service-rule-mobile-option, .service-rule-mobile-chip').forEach(function (el) {
        ['click', 'mousedown', 'touchstart', 'pointerdown'].forEach(function (evt) {
            el.addEventListener(evt, function (event) {
                event.stopPropagation();
                markCardRuleInteraction(this.closest('.service-card'));
            });
        });
    });

    document.querySelectorAll('.service-card').forEach(function (el) {
        el.addEventListener('click', function () {
            if (shouldBlockCardAdd(this)) {
                return;
            }
            const id = parseInt(this.dataset.id);
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const type = this.dataset.vehicletype;
            const rules = JSON.parse(this.dataset.rules || '[]');
            if (Array.isArray(rules) && rules.length > 0) {
                const chosenRule = getCardSelectedRule(this, rules);
                if (!chosenRule) {
                    return;
                }
                this.dataset.lastAddAt = String(Date.now());
                addToCart(id, name, parseFloat(chosenRule.price || 0), type, chosenRule);
                return;
            }
            this.dataset.lastAddAt = String(Date.now());
            addToCart(id, name, price, type, null);
        });
    });
    const timeEl = document.getElementById('current-time');
    if (timeEl) {
        const updateTime = () => {
            timeEl.textContent = new Date().toLocaleString('id-ID', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit'
            });
        };
        updateTime();
        setInterval(updateTime, 60000);
    }
});
</script>
@endpush

<script>
    window.__washLoyalty = {
        instantBonusEligible: false,
        instantBonusTarget: null,
        instantBonusNote: null,
        loyaltyMode: 'voucher',
        applyBonusNow: true
    };
    window.__washCommission = {
        requireEmployee: false
    };
    function isCommissionableType(type) {
        return false;
    }

    function applyCustomerCheckData(data) {
        const info = document.getElementById('customerInfo');
        const voucherSection = document.getElementById('voucherSection');
        const nameInput = document.getElementById('customer_name');
        const voucherCount = document.getElementById('voucherCount');
        const voucherSelect = document.getElementById('voucher_code');
        const useVoucherEl = document.getElementById('use_voucher');
        const voucherInfoText = document.getElementById('voucherInfoText');
        const basisMap = {
            plate: 'plat kendaraan'
        };
        const basis = basisMap[data.loyalty_basis] || 'data pelanggan';
        window.__washLoyalty.instantBonusEligible = !!(data.instant_bonus_eligible);
        window.__washLoyalty.instantBonusTarget = data.target || null;
        window.__washLoyalty.instantBonusNote = data.instant_bonus_note || null;
        window.__washLoyalty.loyaltyMode = data.loyalty_mode || 'voucher';
        setMemberInfo(data);

        // SELALU isi dropdown voucher & tampilkan section jika ADA voucher (meskipun data.found = false)
        const codes = Array.isArray(data.voucher_codes) ? data.voucher_codes : [];
        const hasVouchers = ((data.voucher_count || 0) > 0) && codes.length > 0;
        if (voucherSelect) {
            const prevValue = voucherSelect.value;
            voucherSelect.innerHTML = '<option value="">Pilih kode voucher</option>';
            codes.forEach((code) => {
                const opt = document.createElement('option');
                opt.value = code;
                opt.textContent = code;
                if (code === prevValue) opt.selected = true;
                voucherSelect.appendChild(opt);
            });
        }
        if (hasVouchers) {
            voucherSection.style.display = 'block';
            voucherCount.textContent = data.voucher_count;
            if (voucherInfoText) {
                const vb = (data.voucher_count || 0) > 1 ? 'voucher' : 'voucher';
                voucherInfoText.textContent = `${data.voucher_count} ${vb} tersedia. Pilih di bawah, lalu klik PAKAI VOUCHER.`;
            }
        } else {
            voucherSection.style.display = 'none';
            if (useVoucherEl) useVoucherEl.checked = false;
            if (voucherInfoText) voucherInfoText.textContent = 'Pilih voucher di bawah untuk GRATIS 1x cuci.';
        }

        if (data.found) {
            if (data.name && !nameInput.value) {
                nameInput.value = data.name;
            }
            if (data.member_found && (!currentMember || !currentMember.id)) {
                setCustomerMode('member');
            }
            if (window.__washLoyalty.instantBonusEligible) {
                info.style.display = 'block';
                info.innerHTML = `
                    <div class="alert alert-success py-1 px-2 mb-0 d-flex align-items-center gap-2 border-success" style="font-size:11.5px; border-radius: 8px;">
                        <i class="fas fa-gift text-success fs-5"></i>
                        <div><strong class="text-success">🎉 BONUS CUCI GRATIS TERSEDIA!</strong><br><span class="text-muted" style="font-size:11px;">Transaksi ke-${data.target} ini memenuhi syarat gratis.</span></div>
                    </div>
                `;
            } else {
                info.style.display = 'block';
                info.innerHTML = `
                    <div class="alert alert-primary py-1 px-2 mb-0 d-flex justify-content-between align-items-center border-primary-subtle" style="font-size:11.5px; border-radius: 8px;">
                        <div><i class="fas fa-car-side text-primary me-1"></i> Progres Cuci: <b>${data.progress} / ${data.target}</b></div>
                        <span class="badge bg-primary text-white" style="font-size:10px;">Sisa ${data.remaining}x gratis</span>
                    </div>
                `;
            }
        } else {
            if (data.member_found && (!currentMember || !currentMember.id)) {
                setCustomerMode('member');
            } else if (!document.getElementById('wash_member_id')?.value) {
                clearMember();
            }
            const plateVal = (document.getElementById('vehicle_plate')?.value || '').trim();
            if (plateVal) {
                info.style.display = 'block';
                info.innerHTML = `
                    <div class="alert alert-light py-1 px-2 mb-0 text-muted border" style="font-size:11px; border-radius: 8px;">
                        <i class="fas fa-info-circle text-info me-1"></i> Plat baru. Kumpulkan <b>${data.target || 11}x cuci</b> untuk dapat <b>1x GRATIS</b>.
                    </div>
                `;
            } else {
                info.style.display = 'none';
                info.innerHTML = '';
            }
        }
        updateCartUI();
    }

    function checkCustomerByFields(options = {}) {
        const opts = Object.assign({ silent: false }, options || {});
        const memberId = document.getElementById('wash_member_id')?.value || '';
        const phone = document.getElementById('customer_phone').value;
        const vehiclePlate = document.getElementById('vehicle_plate').value;
        const customerName = document.getElementById('customer_name').value;
        if (!vehiclePlate && !memberId && !phone) {
            if (!opts.silent) alert('Isi plat kendaraan, nomor HP, atau pilih member untuk cek riwayat/bonus');
            return Promise.resolve(null);
        }
        const params = new URLSearchParams({
            member_id: memberId,
            phone: phone || '',
            vehicle_plate: vehiclePlate || '',
            customer_name: customerName || ''
        });
        return fetch(`{{ route('wash.customer.check') }}?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                applyCustomerCheckData(data);
                return data;
            })
            .catch(error => {
                console.error('Error checkCustomer:', error);
                if (!opts.silent) alert('Gagal cek riwayat pelanggan');
                return null;
            });
    }

    document.getElementById('btnCheckCustomer').addEventListener('click', function() {
        checkCustomerByFields({ silent: false });
    });

    // Auto-check plat setelah user selesai input (blur) atau tekan Enter
    ['vehicle_plate', 'customer_phone'].forEach(function (fid) {
        const el = document.getElementById(fid);
        if (!el) return;
        el.addEventListener('blur', function () {
            const plate = (document.getElementById('vehicle_plate').value || '').trim();
            if (plate !== '') checkCustomerByFields({ silent: true });
        });
        el.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const plate = (document.getElementById('vehicle_plate').value || '').trim();
                if (plate !== '') checkCustomerByFields({ silent: true });
            }
        });
    });

    // Saat user pilih voucher dari dropdown → auto centang "PAKAI VOUCHER"
    const voucherCodeSelect = document.getElementById('voucher_code');
    const useVoucherCheckbox = document.getElementById('use_voucher');
    if (voucherCodeSelect) {
        voucherCodeSelect.addEventListener('change', function () {
            if (this.value && useVoucherCheckbox) useVoucherCheckbox.checked = true;
            updateCartUI();
        });
    }

    // Riwayat Plat Kendaraan Terintegrasi ke Input Plat Nomor
    const vehiclePlateInput = document.getElementById('vehicle_plate');
    const vehiclePlateDropdown = document.getElementById('vehicle_plate_dropdown');
    const allKnownPlates = @json($knownVehiclePlates ?? []);

    function renderVehiclePlateDropdown(filter = '') {
        if (!vehiclePlateDropdown) return;
        const filterClean = (filter || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
        const filteredPlates = allKnownPlates.filter(p => {
            const plateStr = (p.plate || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
            const brandStr = (p.brand || '').toUpperCase();
            return !filterClean || plateStr.includes(filterClean) || brandStr.includes(filterClean);
        });

        if (filteredPlates.length === 0) {
            vehiclePlateDropdown.innerHTML = '<div class="list-group-item text-muted small py-2 px-3">Riwayat plat tidak ditemukan</div>';
            vehiclePlateDropdown.classList.remove('d-none');
            return;
        }

        let html = '';
        filteredPlates.slice(0, 8).forEach(item => {
            html += `
                <button type="button" class="list-group-item list-group-item-action py-1 px-3 d-flex justify-content-between align-items-center vehicle-plate-opt" data-plate="${escapeHtml(item.plate)}" data-brand="${escapeHtml(item.brand || '')}">
                    <span class="fw-bold text-dark font-monospace">${escapeHtml(item.plate)}</span>
                    <small class="text-muted">${escapeHtml(item.brand || '')}</small>
                </button>
            `;
        });

        vehiclePlateDropdown.innerHTML = html;
        vehiclePlateDropdown.classList.remove('d-none');

        vehiclePlateDropdown.querySelectorAll('.vehicle-plate-opt').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const plate = this.dataset.plate;
                const brand = this.dataset.brand;
                if (vehiclePlateInput) vehiclePlateInput.value = plate;
                if (brand) {
                    const brandSelect = document.getElementById('vehicle_brand');
                    const brandSearchInputEl = document.getElementById('vehicle_brand_search');
                    if (brandSelect) brandSelect.value = brand;
                    if (brandSearchInputEl) brandSearchInputEl.value = brand;
                }
                vehiclePlateDropdown.classList.add('d-none');
                checkCustomerByFields({ silent: true });
            });
        });
    }

    if (vehiclePlateInput) {
        vehiclePlateInput.addEventListener('focus', function() {
            if (allKnownPlates && allKnownPlates.length > 0) {
                renderVehiclePlateDropdown(this.value);
            }
        });
        vehiclePlateInput.addEventListener('input', function() {
            if (allKnownPlates && allKnownPlates.length > 0) {
                renderVehiclePlateDropdown(this.value);
            }
        });
    }

    // Close vehicle plate dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#vehicle_plate') && !e.target.closest('#vehicle_plate_dropdown')) {
            if (vehiclePlateDropdown) vehiclePlateDropdown.classList.add('d-none');
        }
    });

    // Searchable brand dropdown
    const brandSearchInput = document.getElementById('vehicle_brand_search');
    const brandSelect = document.getElementById('vehicle_brand');
    const brandDropdown = document.getElementById('vehicle_brand_dropdown');
    const allBrands = [];
    let currentBrandTypeFilter = 'all'; // Track current type filter
    // Collect all brands from select
    Array.from(brandSelect.querySelectorAll('option')).forEach(option => {
        if (option.value) {
            allBrands.push({
                value: option.value,
                label: option.textContent,
                group: option.parentElement.label
            });
        }
    });

    function renderBrandDropdown(filter = '') {
        const filterLower = filter.toLowerCase();
        let filteredBrands = allBrands.filter(brand => 
            brand.label.toLowerCase().includes(filterLower)
        );
        
        // Apply type filter if not 'all'
        if (currentBrandTypeFilter !== 'all') {
            filteredBrands = filteredBrands.filter(brand => 
                brand.group.toLowerCase() === currentBrandTypeFilter
            );
        }
        
        if (filteredBrands.length === 0) {
            brandDropdown.innerHTML = '<div class="list-group-item text-muted">Tidak ditemukan</div>';
            brandDropdown.classList.remove('d-none');
            return;
        }

        // Group brands
        const groupedBrands = {
            'Motor': [],
            'Mobil': []
        };
        filteredBrands.forEach(brand => {
            groupedBrands[brand.group].push(brand);
        });

        let html = '';
        for (const [group, brands] of Object.entries(groupedBrands)) {
            // Only show groups that have brands
            if (brands.length > 0) {
                html += `<div class="list-group-item list-group-item-secondary fw-bold">${group}</div>`;
                brands.forEach(brand => {
                    html += `<button type="button" class="list-group-item list-group-item-action brand-option" data-value="${brand.value}">${brand.label}</button>`;
                });
            }
        }

        brandDropdown.innerHTML = html;
        brandDropdown.classList.remove('d-none');

        // Add click listeners to brand options
        brandDropdown.querySelectorAll('.brand-option').forEach(button => {
            button.addEventListener('click', function() {
                const value = this.dataset.value;
                brandSelect.value = value;
                brandSearchInput.value = this.textContent;
                brandDropdown.classList.add('d-none');
            });
        });
    }

    brandSearchInput.addEventListener('focus', function() {
        renderBrandDropdown(this.value);
    });

    brandSearchInput.addEventListener('input', function() {
        renderBrandDropdown(this.value);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#vehicle_brand_search') && !e.target.closest('#vehicle_brand_dropdown')) {
            brandDropdown.classList.add('d-none');
        }
    });

    document.getElementById('use_voucher').addEventListener('change', function() {
        updateCartUI();
    });

    const paymentMethodSelect = document.getElementById('payment_method');
    const cashSection = document.getElementById('cashSection');
    const kasbonSection = document.getElementById('kasbonSection');
    const kasbonTypeRadios = document.querySelectorAll('input[name="kasbon_type"]');
    const kasbonEmployeeSection = document.getElementById('kasbon_employee_section');
    const kasbonOutsiderSection = document.getElementById('kasbon_outsider_section');

    paymentMethodSelect.addEventListener('change', function() {
        const method = this.value;
        if (method === 'cash') {
            cashSection.style.display = 'block';
            kasbonSection.style.display = 'none';
        } else if (method === 'kasbon') {
            cashSection.style.display = 'none';
            kasbonSection.style.display = 'block';
        } else {
            cashSection.style.display = 'none';
            kasbonSection.style.display = 'none';
        }
        calculateChange();
    });

    kasbonTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'employee') {
                kasbonEmployeeSection.style.display = 'block';
                kasbonOutsiderSection.style.display = 'none';
            } else {
                kasbonEmployeeSection.style.display = 'none';
                kasbonOutsiderSection.style.display = 'block';
            }
        });
    });

    let cart = [];
    let currentMember = null;
    const employees = @json($employees ?? []);

    function isMobileDevice() {
        const ua = navigator.userAgent || navigator.vendor || '';
        return /Android|iPhone|iPad|iPod|IEMobile|Opera Mini/i.test(ua) || window.innerWidth <= 768;
    }

    const sendWhatsappCheckbox = document.getElementById('send_whatsapp');
    if (sendWhatsappCheckbox && isMobileDevice()) {
        sendWhatsappCheckbox.checked = true;
    }

    function withAutoPrint(url) {
        try {
            const absoluteUrl = new URL(url, window.location.origin);
            absoluteUrl.searchParams.set('autoprint', '1');
            absoluteUrl.searchParams.set('source', 'pos-wash-mobile');
            return absoluteUrl.toString();
        } catch (error) {
            const separator = url.includes('?') ? '&' : '?';
            return `${url}${separator}autoprint=1&source=pos-wash-mobile`;
        }
    }

    function formatRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
    }

    const allMembers = @json($members ?? []);
    const memberSearchInput = document.getElementById('member_search');
    const memberSearchDropdown = document.getElementById('member_search_dropdown');
    const btnClearMember = document.getElementById('btnClearMember');
    const btnDetachMember = document.getElementById('btnDetachMember');
    const washMemberIdInput = document.getElementById('wash_member_id');
    const memberVehiclesWrap = document.getElementById('memberVehiclesWrap');
    const memberVehiclesChips = document.getElementById('memberVehiclesChips');
    const modeCustomerUmum = document.getElementById('modeCustomerUmum');
    const modeCustomerMember = document.getElementById('modeCustomerMember');
    const panelCustomerUmum = document.getElementById('panelCustomerUmum');
    const panelCustomerMember = document.getElementById('panelCustomerMember');
    const memberSearchBoxWrap = document.getElementById('memberSearchBoxWrap');

    function setCustomerMode(mode) {
        if (mode === 'member') {
            if (modeCustomerMember) modeCustomerMember.checked = true;
            if (panelCustomerUmum) panelCustomerUmum.style.display = 'none';
            if (panelCustomerMember) panelCustomerMember.style.display = 'block';
            if (currentMember && currentMember.id) {
                if (memberSearchBoxWrap) memberSearchBoxWrap.style.display = 'none';
                const card = document.getElementById('memberInfoCard');
                if (card) card.style.display = 'block';
            } else {
                if (memberSearchBoxWrap) memberSearchBoxWrap.style.display = 'block';
                const card = document.getElementById('memberInfoCard');
                if (card) card.style.display = 'none';
                if (memberSearchInput) setTimeout(() => memberSearchInput.focus(), 50);
            }
        } else {
            if (modeCustomerUmum) modeCustomerUmum.checked = true;
            if (panelCustomerUmum) panelCustomerUmum.style.display = 'block';
            if (panelCustomerMember) panelCustomerMember.style.display = 'none';
        }
    }

    if (modeCustomerUmum) {
        modeCustomerUmum.addEventListener('change', function() {
            if (this.checked) {
                if (currentMember && currentMember.id) {
                    clearMember();
                }
                setCustomerMode('umum');
            }
        });
    }

    if (modeCustomerMember) {
        modeCustomerMember.addEventListener('change', function() {
            if (this.checked) {
                setCustomerMode('member');
            }
        });
    }

    function resetMemberInfo() {
        if (!washMemberIdInput || !washMemberIdInput.value) {
            currentMember = null;
            const card = document.getElementById('memberInfoCard');
            if (card) card.style.display = 'none';
            if (memberSearchBoxWrap) memberSearchBoxWrap.style.display = 'block';
            if (memberVehiclesWrap) memberVehiclesWrap.style.display = 'none';
        }
    }

    function setMemberInfo(data) {
        if (!data || !data.member_found) {
            if (!document.getElementById('wash_member_id')?.value) {
                clearMember();
            }
            return;
        }

        currentMember = {
            id: data.member_id || (currentMember ? currentMember.id : null),
            number: data.member_number || '',
            name: data.member_name || data.name || '',
            levelCode: data.member_level_code || 'bronze',
            levelName: data.member_level_name || 'Bronze Member',
            discountPercent: parseFloat(data.member_discount_percent || 0) || 0,
            totalVisits: parseInt(data.member_total_visits || 0, 10) || 0,
            totalTransactions: parseInt(data.member_total_transactions || 0, 10) || 0,
            totalSpending: parseFloat(data.member_total_spending || 0) || 0,
            vehicles: Array.isArray(data.member_vehicles) ? data.member_vehicles : (currentMember?.vehicles || []),
        };

        if (washMemberIdInput && currentMember.id) {
            washMemberIdInput.value = currentMember.id;
        }

        const nameInput = document.getElementById('customer_name');
        const phoneInput = document.getElementById('customer_phone');
        if (nameInput && currentMember.name) nameInput.value = currentMember.name;
        if (phoneInput && data.member_phone) phoneInput.value = data.member_phone;

        setCustomerMode('member');

        const card = document.getElementById('memberInfoCard');
        const nameEl = document.getElementById('memberInfoName');
        const numberEl = document.getElementById('memberInfoNumber');
        const levelEl = document.getElementById('memberInfoLevel');
        const discountEl = document.getElementById('memberInfoDiscount');
        const visitsEl = document.getElementById('memberInfoVisits');

        if (nameEl) nameEl.textContent = currentMember.name || '-';
        if (numberEl) numberEl.textContent = currentMember.number ? (currentMember.number + (data.member_phone ? ' • ' + data.member_phone : '')) : '-';
        if (levelEl) levelEl.textContent = currentMember.levelName;
        if (discountEl) discountEl.textContent = 'Diskon ' + currentMember.discountPercent + '%';
        if (visitsEl) visitsEl.textContent = currentMember.totalVisits + ' Kunjungan';
        
        if (memberSearchBoxWrap) memberSearchBoxWrap.style.display = 'none';
        if (card) card.style.display = 'block';

        // Handle vehicles
        if (memberVehiclesWrap && memberVehiclesChips && currentMember.vehicles.length > 0) {
            memberVehiclesWrap.style.display = 'block';
            memberVehiclesChips.innerHTML = '';
            currentMember.vehicles.forEach((veh, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-warning text-dark py-0 px-2 fw-semibold' + (idx === 0 ? ' active' : '');
                btn.style.fontSize = '11px';
                btn.innerHTML = `<i class="fas fa-car me-1 text-primary"></i> ${escapeHtml(veh.plate)} ${veh.brand ? '<span class="text-muted">(' + escapeHtml(veh.brand) + ')</span>' : ''}`;
                btn.addEventListener('click', function() {
                    memberVehiclesChips.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    setVehicleFromMember(veh);
                });
                memberVehiclesChips.appendChild(btn);
            });
        }
    }

    function selectMember(member) {
        if (!member) return;
        setMemberInfo({
            member_found: true,
            member_id: member.id,
            member_number: member.member_number,
            member_name: member.name,
            member_phone: member.whatsapp,
            member_level_code: member.level_code,
            member_level_name: member.level_name,
            member_discount_percent: member.discount_percent,
            member_total_visits: member.total_visits,
            member_total_transactions: member.total_transactions,
            member_vehicles: member.vehicles || []
        });

        const nameInput = document.getElementById('customer_name');
        const phoneInput = document.getElementById('customer_phone');
        if (nameInput) nameInput.value = member.name || '';
        if (phoneInput && member.whatsapp) phoneInput.value = member.whatsapp;

        if (memberSearchDropdown) memberSearchDropdown.classList.add('d-none');

        // If member has vehicles and vehicle plate is empty, populate from primary vehicle
        if (Array.isArray(member.vehicles) && member.vehicles.length > 0) {
            const plateInput = document.getElementById('vehicle_plate');
            if (plateInput && !plateInput.value.trim()) {
                setVehicleFromMember(member.vehicles[0]);
            }
        }

        updateCartUI();

        // Check customer loyalty and vouchers
        const plate = (document.getElementById('vehicle_plate')?.value || '').trim();
        if (plate) {
            checkCustomerByFields({ silent: true });
        }
    }

    function setVehicleFromMember(veh) {
        if (!veh) return;
        const plateInput = document.getElementById('vehicle_plate');
        const brandSelect = document.getElementById('vehicle_brand');
        const brandSearch = document.getElementById('vehicle_brand_search');
        if (plateInput && veh.plate) {
            plateInput.value = veh.plate;
        }
        if (veh.brand) {
            if (brandSelect) brandSelect.value = veh.brand;
            if (brandSearch) brandSearch.value = veh.brand;
        }
        checkCustomerByFields({ silent: true });
    }

    function clearMember() {
        currentMember = null;
        if (washMemberIdInput) washMemberIdInput.value = '';
        if (memberSearchInput) memberSearchInput.value = '';
        if (btnClearMember) btnClearMember.style.display = 'none';
        const card = document.getElementById('memberInfoCard');
        if (card) card.style.display = 'none';
        if (memberSearchBoxWrap) memberSearchBoxWrap.style.display = 'block';
        if (memberVehiclesWrap) memberVehiclesWrap.style.display = 'none';
        updateCartUI();
    }

    if (btnClearMember) btnClearMember.addEventListener('click', clearMember);
    if (btnDetachMember) btnDetachMember.addEventListener('click', clearMember);

    function renderMemberDropdown(query = '') {
        if (!memberSearchDropdown) return;
        const q = (query || '').toLowerCase().trim();
        let matches = allMembers;
        if (q) {
            matches = allMembers.filter(m => {
                const nameMatch = (m.name || '').toLowerCase().includes(q);
                const numMatch = (m.member_number || '').toLowerCase().includes(q);
                const phoneMatch = (m.whatsapp || '').includes(q);
                const plateMatch = Array.isArray(m.vehicles) && m.vehicles.some(v => (v.plate || '').toLowerCase().replace(/[^a-z0-9]/g, '').includes(q.replace(/[^a-z0-9]/g, '')));
                return nameMatch || numMatch || phoneMatch || plateMatch;
            });
        }

        if (matches.length === 0) {
            memberSearchDropdown.innerHTML = '<div class="list-group-item text-muted small py-2">Member tidak ditemukan</div>';
            memberSearchDropdown.classList.remove('d-none');
            return;
        }

        let html = '';
        matches.slice(0, 10).forEach(m => {
            const platesStr = Array.isArray(m.vehicles) && m.vehicles.length > 0 
                ? m.vehicles.map(v => v.plate).join(', ') 
                : '-';
            html += `
                <button type="button" class="list-group-item list-group-item-action py-2 px-3 member-search-item" data-id="${m.id}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="d-block text-dark">${escapeHtml(m.name)}</strong>
                            <small class="text-muted">${escapeHtml(m.member_number)} &bull; ${escapeHtml(m.whatsapp || '-')}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary">${escapeHtml(m.level_name)}</span>
                            <small class="d-block text-muted" style="font-size:11px;">Plat: ${escapeHtml(platesStr)}</small>
                        </div>
                    </div>
                </button>
            `;
        });

        memberSearchDropdown.innerHTML = html;
        memberSearchDropdown.classList.remove('d-none');

        memberSearchDropdown.querySelectorAll('.member-search-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const mId = parseInt(this.dataset.id, 10);
                const member = allMembers.find(m => m.id === mId);
                selectMember(member);
            });
        });
    }

    if (memberSearchInput) {
        memberSearchInput.addEventListener('focus', function() {
            renderMemberDropdown(this.value);
        });
        memberSearchInput.addEventListener('input', function() {
            renderMemberDropdown(this.value);
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#member_search') && !e.target.closest('#member_search_dropdown')) {
            if (memberSearchDropdown) memberSearchDropdown.classList.add('d-none');
        }
    });

    // POS Register Member Modal Handler
    const posAddMemberModalEl = document.getElementById('posAddMemberModal');
    const posAddMemberForm = document.getElementById('posAddMemberForm');
    const posAddMemberAlert = document.getElementById('posAddMemberAlert');
    const btnSubmitPosMember = document.getElementById('btnSubmitPosMember');

    if (posAddMemberModalEl) {
        posAddMemberModalEl.addEventListener('show.bs.modal', function () {
            // Auto pre-fill from current inputs in POS if available
            const curName = (document.getElementById('customer_name')?.value || '').trim();
            const curPhone = (document.getElementById('customer_phone')?.value || '').trim();
            const curPlate = (document.getElementById('vehicle_plate')?.value || '').trim();
            const curBrand = (document.getElementById('vehicle_brand')?.value || '').trim();

            if (curName) document.getElementById('pos_member_name').value = curName;
            if (curPhone) document.getElementById('pos_member_phone').value = curPhone;
            if (curPlate) document.getElementById('pos_member_plate').value = curPlate;
            if (curBrand) document.getElementById('pos_member_brand').value = curBrand;

            if (posAddMemberAlert) {
                posAddMemberAlert.classList.add('d-none');
                posAddMemberAlert.textContent = '';
            }
        });
    }

    if (posAddMemberForm) {
        posAddMemberForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const name = document.getElementById('pos_member_name').value.trim();
            const whatsapp = document.getElementById('pos_member_phone').value.trim();
            const vehicle_plate = document.getElementById('pos_member_plate').value.trim();
            const vehicle_brand = document.getElementById('pos_member_brand').value.trim();
            const vehicle_type = document.getElementById('pos_member_type').value;

            if (!name || !whatsapp) {
                if (posAddMemberAlert) {
                    posAddMemberAlert.textContent = 'Nama dan Nomor WhatsApp wajib diisi.';
                    posAddMemberAlert.classList.remove('d-none');
                }
                return;
            }

            const origHtml = btnSubmitPosMember.innerHTML;
            btnSubmitPosMember.disabled = true;
            btnSubmitPosMember.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

            fetch('{{ route("wash.members.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: name,
                    whatsapp: whatsapp,
                    vehicle_plate: vehicle_plate,
                    vehicle_brand: vehicle_brand,
                    vehicle_type: vehicle_type
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
            .then(result => {
                if (!result.ok || !result.body.success) {
                    throw new Error(result.body.message || 'Gagal mendaftarkan member.');
                }
                const newMember = result.body.member;
                allMembers.push(newMember);
                selectMember(newMember);

                const modal = bootstrap.Modal.getInstance(posAddMemberModalEl) || new bootstrap.Modal(posAddMemberModalEl);
                modal.hide();
                posAddMemberForm.reset();
                alert(`✅ Member baru berhasil didaftarkan!\nNomor: ${newMember.member_number}\nNama: ${newMember.name}`);
            })
            .catch(err => {
                console.error('Error register member:', err);
                if (posAddMemberAlert) {
                    posAddMemberAlert.textContent = err.message;
                    posAddMemberAlert.classList.remove('d-none');
                }
            })
            .finally(() => {
                btnSubmitPosMember.disabled = false;
                btnSubmitPosMember.innerHTML = origHtml;
            });
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    async function buildWashReceiptBlob(transactionId, payload) {
        if (typeof html2canvas === 'undefined') {
            return null;
        }

        const capture = document.createElement('div');
        capture.style.position = 'fixed';
        capture.style.left = '-10000px';
        capture.style.top = '0';
        capture.style.width = '360px';
        capture.style.background = '#ffffff';
        capture.style.color = '#111827';
        capture.style.padding = '14px';
        capture.style.fontFamily = 'Arial, sans-serif';
        capture.style.fontSize = '12px';
        capture.style.lineHeight = '1.4';
        capture.style.border = '1px solid #e5e7eb';
        const itemsHtml = payload.items.map((item) => `
            <tr>
                <td style="padding:4px 0;border-bottom:1px dashed #d1d5db;">${escapeHtml(item.name)}</td>
                <td style="padding:4px 0;text-align:center;border-bottom:1px dashed #d1d5db;">${item.quantity}</td>
                <td style="padding:4px 0;text-align:right;border-bottom:1px dashed #d1d5db;">${formatRupiah(item.price)}</td>
                <td style="padding:4px 0;text-align:right;border-bottom:1px dashed #d1d5db;">${formatRupiah(item.subtotal)}</td>
            </tr>
        `).join('');

        capture.innerHTML = `
            <div style="text-align:center;font-weight:700;font-size:14px;">${escapeHtml(@json(config('app.name')))}</div>
            <div style="text-align:center;color:#4b5563;">Detail Transaksi Wash</div>
            <div style="margin-top:8px;">No. Transaksi: #${escapeHtml(transactionId)}</div>
            <div>Tanggal: ${escapeHtml(new Date().toLocaleString('id-ID'))}</div>
            <div>Pelanggan: ${escapeHtml(payload.customerName || '-')} ${currentMember ? '<span style="font-weight:bold;color:#d97706;">(' + escapeHtml(currentMember.levelName) + ')</span>' : ''}</div>
            <div>Kendaraan: ${escapeHtml(payload.vehicleBrand || '-')} - ${escapeHtml(payload.vehiclePlate || '-')}</div>
            <div style="margin-top:8px;">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="text-align:left;padding:4px 0;border-bottom:1px solid #111827;">Item</th>
                            <th style="text-align:center;padding:4px 0;border-bottom:1px solid #111827;">Qty</th>
                            <th style="text-align:right;padding:4px 0;border-bottom:1px solid #111827;">Harga</th>
                            <th style="text-align:right;padding:4px 0;border-bottom:1px solid #111827;">Total</th>
                        </tr>
                    </thead>
                    <tbody>${itemsHtml}</tbody>
                </table>
            </div>
            <div style="margin-top:8px;display:flex;justify-content:space-between;">
                <span>Metode Bayar</span>
                <strong>${escapeHtml((payload.paymentMethod || '').toUpperCase())}</strong>
            </div>
            <div style="margin-top:4px;display:flex;justify-content:space-between;font-size:14px;">
                <span>Total</span>
                <strong>${formatRupiah(payload.total)}</strong>
            </div>
        `;

        document.body.appendChild(capture);
        try {
            const canvas = await html2canvas(capture, { scale: 2, backgroundColor: '#ffffff' });
            return await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
        } finally {
            capture.remove();
        }
    }

    async function sendWashWhatsappReceipt(transactionId, phone, payload) {
        const formData = new FormData();
        formData.append('phone', phone);
        try {
            const blob = await buildWashReceiptBlob(transactionId, payload);
            if (blob) {
                formData.append('receipt_image', blob, `struk-wash-${transactionId}.png`);
            }
        } catch (error) {
            console.error(error);
        }

        return fetch(`{{ url('wash/transactions') }}/${transactionId}/whatsapp-receipt`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
    }

    function addToCart(id, name, price, type, selectedRule = null) {
        id = parseInt(id);
        const ruleId = selectedRule ? parseInt(selectedRule.id) : null;
        const cartKey = `${id}::${ruleId || 'base'}`;
        const displayName = selectedRule ? `${name} (${selectedRule.label})` : name;
        const existingItem = cart.find(item => item.key === cartKey);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({
                key: cartKey,
                id,
                rule_id: ruleId,
                name: displayName,
                base_name: name,
                rule_label: selectedRule ? selectedRule.label : null,
                price,
                type,
                quantity: 1,
            });
        }
        updateCartUI();
        filterBrands(cart.length > 0 ? 'all' : type);
    }

    function filterBrands(type) {
        const brandSelect = document.getElementById('vehicle_brand');
        const brandSearchInput = document.getElementById('vehicle_brand_search');
        const brandDropdown = document.getElementById('vehicle_brand_dropdown');
        let typeLower = type.toLowerCase();
        
        // Map database types to optgroup labels
        if (typeLower === 'car') {
            typeLower = 'mobil';
        } else if (typeLower === 'motor') {
            typeLower = 'motor';
        }

        if (!['mobil', 'motor'].includes(typeLower)) {
            typeLower = 'all';
        }

        currentBrandTypeFilter = typeLower; // Update current filter

        // Update allBrands filter and re-render
        if (typeLower !== 'all') {
            // Filter brands by type when rendering dropdown
            const filteredByType = allBrands.filter(brand => 
                brand.group.toLowerCase() === typeLower
            );
            if (filteredByType.length === 0) {
                brandSearchInput.value = '';
                brandSelect.value = '';
                brandDropdown.classList.add('d-none');
            }
        }

        const optgroups = brandSelect.querySelectorAll('optgroup');
        optgroups.forEach(function (optgroup) {
            if (typeLower === 'all' || optgroup.label.toLowerCase() === typeLower) {
                optgroup.hidden = false;
            } else {
                optgroup.hidden = true;
            }
        });
        // Select the first valid option or reset
        brandSelect.value = "";
        brandSearchInput.value = "";
        brandDropdown.classList.add('d-none');
    }

    function removeFromCart(id) {
        if (confirm('Yakin ingin menghapus item ini dari keranjang?')) {
            id = parseInt(id);
            const index = cart.findIndex(item => item.id === id);
            if (index > -1) {
                cart.splice(index, 1);
            }
            updateCartUI();
            if (cart.length === 0) {
                resetServiceSelection();
            }
        }
    }

    function removeFromCartByKey(key) {
        if (confirm('Yakin ingin menghapus item ini dari keranjang?')) {
            const index = cart.findIndex(item => item.key === key);
            if (index > -1) {
                cart.splice(index, 1);
            }
            updateCartUI();
            if (cart.length === 0) {
                resetServiceSelection();
            }
        }
    }

    function resetServiceSelection() {
        // Only reset filters and service visibility, NOT customer data
        
        // Reset filters by clicking All button to trigger all UI logic
        const allBtn = document.querySelector('.filter-btn[data-filter="all"]');
        if (allBtn) {
            allBtn.click();
        }
        const serviceSearchInput = document.getElementById('serviceSearchInput');
        if (serviceSearchInput) {
            serviceSearchInput.value = '';
        }
        
        // Reset brand options visibility
        const brandSelect = document.getElementById('vehicle_brand');
        const optgroups = brandSelect.querySelectorAll('optgroup');
        optgroups.forEach(function (optgroup) {
            optgroup.hidden = false;
        });
    }

    function resetCart() {
        cart = [];
        document.getElementById('vehicle_brand').value = "";
        document.getElementById('vehicle_brand_search').value = "";
        document.getElementById('vehicle_brand_dropdown').classList.add('d-none');
        document.getElementById('vehicle_plate').value = "";
        const vehiclePlateDropdownEl = document.getElementById('vehicle_plate_dropdown');
        if (vehiclePlateDropdownEl) vehiclePlateDropdownEl.classList.add('d-none');
        document.getElementById('customer_name').value = "";
        document.getElementById('customer_phone').value = "";
        const sendWhatsapp = document.getElementById('send_whatsapp');
        if (sendWhatsapp) {
            sendWhatsapp.checked = false;
        }
        document.getElementById('cash_amount').value = "";
        document.getElementById('changeAmount').textContent = "Rp 0";
        const customerInfoEl = document.getElementById('customerInfo');
        if (customerInfoEl) {
            customerInfoEl.innerHTML = "";
            customerInfoEl.style.display = 'none';
        }
        document.getElementById('voucherSection').style.display = 'none';
        document.getElementById('use_voucher').checked = false;
        const voucherCodeEl = document.getElementById('voucher_code');
        if (voucherCodeEl) voucherCodeEl.innerHTML = '<option value="">Pilih voucher</option>';
        window.__washLoyalty = { instantBonusEligible: false, instantBonusTarget: null, instantBonusNote: null, loyaltyMode: 'voucher', applyBonusNow: true };
        const bonusChoiceRow = document.getElementById('bonusChoiceRow');
        const applyNowRadio = document.getElementById('bonusApplyNow');
        const saveLaterRadio = document.getElementById('bonusSaveForLater');
        const skipAutoRedeemInput = document.getElementById('skip_auto_redeem_voucher');
        if (bonusChoiceRow) bonusChoiceRow.classList.add('d-none');
        if (applyNowRadio) applyNowRadio.checked = false;
        if (saveLaterRadio) saveLaterRadio.checked = false;
        if (skipAutoRedeemInput) skipAutoRedeemInput.value = '0';
        clearMember();
        setCustomerMode('umum');
        
        resetServiceSelection();

        updateCartUI();
    }

    function updateCartUI() {
        const cartContainer = document.getElementById('cartItems');
        const emptyMsg = document.getElementById('emptyCartMsg');
        const totalEl = document.getElementById('totalAmount');
        const subtotalEl = document.getElementById('subtotalAmount');
        const btnCheckout = document.getElementById('btnCheckout');
        const voucherEl = document.getElementById('use_voucher');
        const memberDiscountRow = document.getElementById('memberDiscountRow');
        const memberDiscountAmountEl = document.getElementById('memberDiscountAmount');
        const voucherDiscountRow = document.getElementById('voucherDiscountRow');
        const voucherDiscountAmountEl = document.getElementById('voucherDiscountAmount');
        const instantBonusRow = document.getElementById('instantBonusRow');
        const instantBonusAmountEl = document.getElementById('instantBonusAmount');
        const bonusChoiceRow = document.getElementById('bonusChoiceRow');
        const applyNowRadio = document.getElementById('bonusApplyNow');
        const saveLaterRadio = document.getElementById('bonusSaveForLater');
        const skipAutoRedeemInput = document.getElementById('skip_auto_redeem_voucher');
        const missingAlert = document.getElementById('employeeMissingAlert');
        const missingAlertTitle = document.getElementById('employeeMissingAlertTitle');
        const missingAlertBody = document.getElementById('employeeMissingAlertBody');
        const useVoucher = voucherEl ? !!voucherEl.checked : false;
        
        cartContainer.innerHTML = '';
        let total = 0;
        let discount = 0;
        let memberDiscount = 0;
        let voucherDiscount = 0;
        let instantBonusDiscount = 0;
        let missingEmployeeCount = 0;
        const commissionCfg = window.__washCommission || {};
        const loyalty = window.__washLoyalty || {};
        const canApplyInstantBonus = !!(
            loyalty.instantBonusEligible && !useVoucher &&
            cart.length === 1 && (cart[0]?.quantity || 0) === 1
        );

        if (canApplyInstantBonus) {
            if (bonusChoiceRow) bonusChoiceRow.classList.remove('d-none');
            const isVoucherMode = (loyalty.loyaltyMode || 'voucher') !== 'instant_discount';
            if (saveLaterRadio) {
                const wrapper = saveLaterRadio.closest('.form-check') || saveLaterRadio.parentElement;
                if (wrapper) {
                    if (isVoucherMode) {
                        wrapper.style.display = '';
                    } else {
                        wrapper.style.display = 'none';
                        if (saveLaterRadio.checked && applyNowRadio) {
                            saveLaterRadio.checked = false;
                            applyNowRadio.checked = true;
                        }
                    }
                }
            }
            if (!applyNowRadio.checked && !saveLaterRadio.checked) {
                if (applyNowRadio) applyNowRadio.checked = true;
            }
            if (skipAutoRedeemInput) skipAutoRedeemInput.value = saveLaterRadio?.checked ? '1' : '0';
        } else {
            if (bonusChoiceRow) bonusChoiceRow.classList.add('d-none');
            if (skipAutoRedeemInput) skipAutoRedeemInput.value = '0';
        }
        const userAppliedBonus = canApplyInstantBonus && applyNowRadio?.checked;

        if (cart.length === 0) {
            if (emptyMsg) emptyMsg.style.display = 'block';
            if (btnCheckout) btnCheckout.disabled = true;
        } else {
            if (emptyMsg) emptyMsg.style.display = 'none';
            if (btnCheckout) btnCheckout.disabled = false;

            cart.forEach((item, itemIdx) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;

                const isCommissionable = isCommissionableType(item.type);
                const hasEmployee = !!(item.employee_id);
                if (isCommissionable && !hasEmployee) {
                    missingEmployeeCount++;
                }
                
                const div = document.createElement('div');
                div.className = 'cart-item';
                const selectId = 'emp_sel_' + String(item.key || item.id).replace(/[^a-zA-Z0-9_]/g, '_');
                const empSelectClass = 'form-select form-select-sm wash-input';
                const empOptions = ['<option value=\"\">- Pegawai -</option>']
                    .concat(employees.map(e => `<option value=\"${e.id}\" ${item.employee_id==e.id?'selected':''}>${e.name}</option>`))
                    .join('');
                div.innerHTML = `
                    <div class="cart-item-left">
                        <div class="cart-item-name">${item.name}</div>
                        <small class="cart-item-meta">${item.quantity} x Rp ${item.price.toLocaleString('id-ID')}</small>
                        <div class="mt-2">
                            <select id="${selectId}" class="${empSelectClass} emp-select">
                                ${empOptions}
                            </select>
                        </div>
                    </div>
                    <div class="cart-item-right">
                        <span class="cart-item-total">Rp ${itemTotal.toLocaleString('id-ID')}</span>
                        <button class="btn btn-sm text-danger p-0" type="button" onclick="removeFromCartByKey('${item.key}')"><i class="fas fa-trash"></i></button>
                    </div>
                `;
                setTimeout(() => {
                    const sel = document.getElementById(selectId);
                    if (sel) {
                        sel.addEventListener('change', function() {
                            item.employee_id = this.value ? parseInt(this.value) : null;
                        });
                    }
                }, 0);
                cartContainer.appendChild(div);
            });

            const syncEmployeeMissingAlert = function() {};
            window.__syncMissingEmpAlert = syncEmployeeMissingAlert;

            syncEmployeeMissingAlert();

            if (currentMember && !useVoucher && !canApplyInstantBonus) {
                const memberPct = parseFloat(currentMember.discountPercent || 0) || 0;
                if (memberPct > 0) {
                    memberDiscount = Math.round((total * memberPct) * 100 / 100) / 100;
                    discount += memberDiscount;
                }
            }

            if (useVoucher) {
                const voucherSelect = document.getElementById('voucher_code');
                const voucherCode = voucherSelect ? String(voucherSelect.value || '') : '';
                if (voucherCode === '') {
                    alert('Pilih kode voucher terlebih dahulu.');
                    if (voucherEl) voucherEl.checked = false;
                } else if (cart.length !== 1 || (cart[0]?.quantity || 0) !== 1) {
                    alert('Voucher hanya berlaku untuk 1 transaksi dengan 1 layanan (qty 1).');
                    if (voucherEl) voucherEl.checked = false;
                } else {
                    voucherDiscount = total;
                    discount = total;
                    memberDiscount = 0;
                    instantBonusDiscount = 0;
                }
            } else if (userAppliedBonus) {
                instantBonusDiscount = total;
                discount = total;
                memberDiscount = 0;
            }
        }

        const finalTotal = Math.max(0, total - discount);
        if (subtotalEl) subtotalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        if (memberDiscountRow) memberDiscountRow.style.display = memberDiscount > 0 ? '' : 'none';
        if (memberDiscountAmountEl) memberDiscountAmountEl.textContent = '- Rp ' + memberDiscount.toLocaleString('id-ID');
        if (voucherDiscountRow) voucherDiscountRow.style.display = voucherDiscount > 0 ? '' : 'none';
        if (voucherDiscountAmountEl) voucherDiscountAmountEl.textContent = '- Rp ' + voucherDiscount.toLocaleString('id-ID');
        if (instantBonusRow) {
            instantBonusRow.style.display = instantBonusDiscount > 0 ? '' : 'none';
        }
        if (instantBonusAmountEl) instantBonusAmountEl.textContent = '- Rp ' + instantBonusDiscount.toLocaleString('id-ID');
        
        if (discount > 0) {
             totalEl.innerHTML = `<small class="text-decoration-line-through text-muted">Rp ${total.toLocaleString('id-ID')}</small> <span class="text-success fw-bold">Rp ${finalTotal.toLocaleString('id-ID')}</span>`;
        } else {
             totalEl.textContent = 'Rp ' + finalTotal.toLocaleString('id-ID');
        }
        
        if (totalEl) totalEl.dataset.amount = finalTotal; // Store numeric value
        calculateChange();
    }

    function calculateChange() {
        const totalEl = document.getElementById('totalAmount');
        const total = parseInt(totalEl?.dataset?.amount || 0) || 0;
        const method = document.getElementById('payment_method')?.value || 'cash';
        const cashInput = document.getElementById('cash_amount')?.value || 0;
        const cash = parseInt(cashInput) || 0;
        const changeEl = document.getElementById('changeAmount');
        const cashSection = document.getElementById('cashSection');
        const changeBox = document.getElementById('changeDisplay');

        if (cashSection) cashSection.style.display = (method === 'cash') ? '' : 'none';

        if (method === 'cash' && cash >= total) {
            if (changeEl) changeEl.textContent = 'Rp ' + (cash - total).toLocaleString('id-ID');
            if (changeBox) changeBox.style.display = '';
        } else {
            if (changeEl) changeEl.textContent = 'Rp 0';
            if (changeBox) changeBox.style.display = method === 'cash' ? '' : 'none';
        }
    }

    const serviceSearchInput = document.getElementById('serviceSearchInput');
    const applyServiceVisibility = function () {
        const activeBtn = document.querySelector('.filter-btn.active');
        const type = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';
        const keyword = (serviceSearchInput?.value || '').trim().toLowerCase();

        document.querySelectorAll('.service-item').forEach(item => {
            const typeMatch = type === 'all' || item.dataset.type === type;
            const card = item.querySelector('.service-card');
            const rawName = (card?.dataset?.name || '').toLowerCase();
            const desc = (card?.dataset?.description || '').toLowerCase();
            const textMatch = keyword === '' || rawName.includes(keyword) || desc.includes(keyword);
            item.style.display = (typeMatch && textMatch) ? 'block' : 'none';
        });

        document.querySelectorAll('.service-section-heading').forEach(section => {
            const sectionType = section.dataset.type;
            const hasVisibleItems = Array.from(document.querySelectorAll('.service-item[data-type="' + sectionType + '"]'))
                .some(item => item.style.display !== 'none');
            section.style.display = hasVisibleItems ? 'block' : 'none';
        });
    };

    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            const type = this.getAttribute('data-filter');
            if (cart.length === 0) {
                filterBrands(type);
            }
            applyServiceVisibility();
        });
    });

    if (serviceSearchInput) {
        serviceSearchInput.addEventListener('input', applyServiceVisibility);
    }

    // Toggle cash section on payment method change
    const paymentMethodEl = document.getElementById('payment_method');
    if (paymentMethodEl) {
        paymentMethodEl.addEventListener('change', calculateChange);
        // Initialize visibility
        calculateChange();
    }
    function showLoyaltyBonusPopup(message) {
        return new Promise((resolve) => {
            const modalEl = document.getElementById('loyaltyBonusModal');
            const okBtn = document.getElementById('btnLoyaltyBonusOk');
            const bodyEl = modalEl ? modalEl.querySelector('.modal-body') : null;
            if (bodyEl && message) {
                bodyEl.textContent = message;
            }
            if (!modalEl || !okBtn || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                alert(message || 'Voucher reward berhasil diproses.');
                resolve();
                return;
            }
            const bonusModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const handleResolve = () => {
                okBtn.removeEventListener('click', handleOk);
                modalEl.removeEventListener('hidden.bs.modal', handleResolve);
                resolve();
            };
            const handleOk = () => {
                bonusModal.hide();
            };
            okBtn.addEventListener('click', handleOk);
            modalEl.addEventListener('hidden.bs.modal', handleResolve, { once: true });
            bonusModal.show();
        });
    }
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (cart.length === 0) return;

        const method = document.getElementById('payment_method')?.value || 'cash';
        const cashInput = document.getElementById('cash_amount')?.value || 0;
        const cash = parseInt(cashInput) || 0;
        const sendWhatsapp = !!document.getElementById('send_whatsapp')?.checked;
        const phone = document.getElementById('customer_phone').value;

        if (sendWhatsapp && !phone) {
            alert('Isi nomor WhatsApp pelanggan terlebih dahulu.');
            return;
        }

        /* if (cash < total) {
             alert('Insufficient cash!');
             return;
        } */ // Allow credit/later payment if needed, or enforce. Let's enforce for POS.
        
        const data = {
            items: cart,
            payment_method: method,
            cash_amount: method === 'cash' ? cash : null,
            wash_member_id: currentMember ? currentMember.id : (document.getElementById('wash_member_id')?.value || null),
            customer_name: document.getElementById('customer_name').value,
            customer_phone: document.getElementById('customer_phone').value,
            use_voucher: document.getElementById('use_voucher').checked,
            voucher_code: document.getElementById('voucher_code')?.value || null,
            vehicle_plate: document.getElementById('vehicle_plate').value,
            vehicle_brand: document.getElementById('vehicle_brand').value,
            bonus_apply_mode: document.querySelector('input[name="bonus_apply_mode"]:checked')?.value || 'now',
            skip_auto_redeem_voucher: document.getElementById('skip_auto_redeem_voucher')?.value === '1' ? '1' : '0',
        };

        if (method === 'kasbon') {
            const kasbonType = document.querySelector('input[name="kasbon_type"]:checked')?.value;
            data.kasbon_type = kasbonType;
            if (kasbonType === 'employee') {
                data.kasbon_user_id = document.getElementById('kasbon_user_id')?.value;
            } else if (kasbonType === 'outsider') {
                data.kasbon_name = document.getElementById('kasbon_name')?.value;
            }
        }
        
        const btn = document.getElementById('btnCheckout');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';

        fetch('{{ route("wash.transactions.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            let responseText = '';
            try {
                responseText = await response.text();
                if (!response.ok) {
                    let errorMsg = 'Respons jaringan tidak valid';
                    try {
                        const errorData = JSON.parse(responseText);
                        errorMsg = errorData.message || errorMsg;
                    } catch (e) {
                        // If not JSON, use first 200 chars of response text
                        errorMsg = responseText.substring(0, 200);
                    }
                    throw new Error(errorMsg);
                }
                return JSON.parse(responseText);
            } catch (e) {
                if (e instanceof SyntaxError) {
                    // JSON parse error
                    throw new Error('Respons server tidak valid: ' + (responseText.substring(0, 200) || 'Tidak ada data'));
                }
                throw e;
            }
        })
        .then(async data => {
            if (data.success) {
                const url = data.receipt_url ? data.receipt_url : ('{{ url("wash/transactions") }}/' + data.transaction_id + '/receipt');
                const openReceipt = () => {
                    if (isMobileDevice()) {
                        window.location.href = withAutoPrint(url);
                    } else {
                        window.open(url, '_blank', 'width=400,height=600');
                    }
                };
                if (data.reward_voucher_created_code) {
                    await showLoyaltyBonusPopup('🎁 Voucher reward berhasil dibuat: ' + data.reward_voucher_created_code + '\n\nVoucher bisa langsung dipakai di transaksi BERIKUTNYA (1 layanan kendaraan qty 1).');
                    // Setelah voucher dibuat, background refresh riwayat & list voucher agar siap dipakai transaksi berikutnya
                    if ((document.getElementById('vehicle_plate').value || '').trim() !== '') {
                        setTimeout(() => { checkCustomerByFields({ silent: true }); }, 400);
                    }
                } else if (data.discount_type === 'reward_voucher') {
                    await showLoyaltyBonusPopup('✅ Voucher reward berhasil digunakan. Total transaksi menjadi Rp0.');
                    // Voucher sudah dipakai → refresh agar status hilang dari list
                    if ((document.getElementById('vehicle_plate').value || '').trim() !== '') {
                        setTimeout(() => { checkCustomerByFields({ silent: true }); }, 400);
                    }
                } else if (data.instant_bonus_applied) {
                    const target = data.instant_bonus_target || window.__washLoyalty?.instantBonusTarget || '';
                    await showLoyaltyBonusPopup('🎉 BONUS CUCI ke-' + target + ' DITERAPKAN! Total transaksi menjadi GRATIS (Rp0).');
                } else if (data.voucher_saved_for_later && data.saved_voucher_code) {
                    await showLoyaltyBonusPopup('💾 VOUCHER BONUS DISIMPAN!\n\nKode: ' + data.saved_voucher_code + '\nVoucher bisa dipakai di KUNJUNGAN BERIKUTNYA.\n\nTransaksi ini dibayar normal.');
                    if ((document.getElementById('vehicle_plate').value || '').trim() !== '') {
                        setTimeout(() => { checkCustomerByFields({ silent: true }); }, 400);
                    }
                }
                openReceipt();
                const resolvedPhone = data.customer_phone || phone;
                if (sendWhatsapp && resolvedPhone && !data.wa_sent) {
                    await sendWashWhatsappReceipt(data.transaction_id, resolvedPhone, {
                        items: cart.map((item) => ({
                            name: item.name,
                            quantity: item.quantity,
                            price: item.price,
                            subtotal: item.price * item.quantity
                        })),
                        total: parseInt(document.getElementById('totalAmount')?.dataset?.amount || 0) || 0,
                        customerName: document.getElementById('customer_name')?.value || '',
                        vehicleBrand: document.getElementById('vehicle_brand')?.value || '',
                        vehiclePlate: document.getElementById('vehicle_plate')?.value || '',
                        paymentMethod: method
                    }).catch(() => {});
                }
                resetCart();
            } else {
                alert('Kesalahan: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
</script>

<style>
    .wash-pos-page {
        color: #1e293b;
    }

    .wash-pos-shell {
        max-width: 1440px;
        margin: 0 auto;
        padding: 0 0.5rem 1rem 0.5rem;
    }

    .wash-pos-header {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        gap: 1rem;
        margin-bottom: 0.6rem;
    }

    .wash-pos-title {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 700;
        color: #0f172a;
    }

    .wash-pos-subtitle {
        margin: 0.25rem 0 0;
        font-size: 0.9rem;
        color: #64748b;
    }

    .wash-current-time {
        display: inline-flex;
        align-items: center;
        min-height: 42px;
        min-width: 170px;
        justify-content: center;
        padding: 0.45rem 0.9rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        background: #fff;
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    }

    .wash-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .wash-checkout-card {
        position: sticky;
        top: 1.2rem;
    }

    .wash-card-header {
        padding: 1rem 1.1rem;
        background: linear-gradient(180deg, rgb(58 126 232 / 22%) 0%, rgb(231 236 255 / 30%) 100%);
        border-bottom-color: rgb(22 22 23 / 98%);
        display: flex;
        gap: 0.8rem;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .wash-card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .wash-card-title i {
        color: #3b82f6;
    }

    .wash-card-body {
        padding: 1rem;
    }

    .wash-filter-group {
        background: #e2e8f0;
        border-radius: 0.8rem;
        padding: 0.2rem;
        display: inline-flex;
        gap: 0.25rem;
    }

    #serviceSearchInput {
        min-width: 220px;
    }

    .wash-filter-group .filter-btn {
        border: 0;
        background: transparent;
        color: #475569;
        border-radius: 0.6rem;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        transition: all 0.2s ease;
    }

    .wash-filter-group .filter-btn.active {
        background: #fff;
        color: #2563eb;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.12);
    }

    .wash-filter-group .filter-btn:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }

    .service-card {
        height: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 0.85rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
        background: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .service-card:hover {
        border-color: #60a5fa;
        box-shadow: 0 10px 18px rgba(37, 99, 235, 0.14);
    }

    .service-card:active {
        transform: none;
    }

    .service-card-mobil {
        border-color: #bfdbfe;
    }

    .service-card-motor {
        border-color: #fed7aa;
    }

    .service-card-caffe {
        border-color: #f5d0a7;
    }

    .service-card-addon {
        border-color: #bbf7d0;
    }

    .service-section-heading h6 {
        font-size: 0.82rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
        border-bottom: 1px dashed #cbd5e1;
        padding-bottom: 0.32rem;
    }

    .service-image-wrap {
        aspect-ratio: 1 / 1;
        border-radius: 0.8rem;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-bottom: 0.75rem;
    }

    .service-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .25s ease;
    }

    .service-fallback-icon {
        width: 52px;
        height: 52px;
        border-radius: 999px;
        background: #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #64748b;
        transition: transform .25s ease;
    }

    .service-title {
        font-size: 0.94rem;
        font-weight: 700;
        line-height: 1.35;
        color: #1e293b;
        margin: 0;
    }

    .service-description {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0.32rem 0 0;
        min-height: 2.2em;
    }

    .service-description-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.32rem;
        margin-top: 0.35rem;
        min-height: 1.8rem;
    }

    .service-description-chip {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0.24rem 0.52rem;
        border-radius: 0.65rem;
        background: #eef2ff;
        border: 1px solid #c7d2fe;
        color: #3730a3;
        font-size: 0.68rem;
        line-height: 1.25;
        font-weight: 600;
    }

    .service-description-chip-empty {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #64748b;
        font-weight: 500;
    }

    .service-rule-picker .form-select {
        border-radius: 0.6rem;
        font-size: 0.74rem;
        font-weight: 500;
        padding-top: 0.32rem;
        padding-bottom: 0.32rem;
        padding-right: 1.5rem;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    .service-rule-mobile-options {
        display: none;
        gap: 0.28rem;
        flex-wrap: wrap;
    }

    .service-rule-mobile-option {
        display: inline-flex;
        align-items: center;
        margin: 0;
    }

    .service-rule-mobile-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .service-rule-mobile-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        font-size: 0.64rem;
        font-weight: 700;
        padding: 0.14rem 0.45rem;
        line-height: 1.2;
    }

    .service-rule-mobile-input:checked + .service-rule-mobile-chip {
        background: #11bb4dff;
        border-color: #13b433ff;
        color: #ffffffff;
    }

    .service-meta {
        margin-top: auto;
        padding-top: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .service-price {
        font-size: 0.95rem;
        font-weight: 800;
        color: #2563eb;
        line-height: 1.2;
    }

    .service-type {
        font-size: 0.68rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background: #eff6ff;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
    }

    .service-type-mobil {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .service-type-motor {
        background: #ffedd5;
        color: #c2410c;
    }

    .service-type-caffe {
        background: #fef3c7;
        color: #92400e;
    }

    .service-type-umum {
        background: #e2e8f0;
        color: #334155;
    }

    .service-adjustment {
        border: 1px solid rgba(180, 83, 9, 0.2);
    }

    .wash-field-label {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 0.68rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.07em;
    }

    .wash-input {
        min-height: 42px;
        border-color: #e2e8f0;
        background: #f8fafc;
        border-radius: 0.7rem;
    }

    .wash-input::placeholder {
        color: #94a3b8;
    }

    .wash-input:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.18);
        background: #fff;
    }

    .wash-secondary-btn {
        border-radius: 0.7rem;
        border: 1px solid #cbd5e1;
        background: #e2e8f0;
        color: #334155;
        min-width: 66px;
        font-weight: 600;
    }

    .wash-secondary-btn:hover {
        background: #cbd5e1;
    }

    .wash-secondary-btn:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(148, 163, 184, 0.35);
    }

    .wash-inline-check .form-check-input {
        margin-top: 0.2rem;
        border-color: #94a3b8;
    }

    .wash-inline-check .form-check-label {
        color: #475569;
        font-size: 0.84rem;
    }

    .wash-voucher-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem;
        padding: 0.85rem 1rem;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 0.85rem;
        color: #166534;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .wash-use-voucher-label {
        cursor: pointer;
        user-select: none;
        padding: 0.35rem 0.65rem;
        border-radius: 0.5rem;
        background: #16a34a;
        color: #fff !important;
        font-weight: 700;
        font-size: 0.82rem;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }
    .wash-use-voucher-label:hover {
        background: #15803d;
    }
    .wash-use-voucher-label input:checked + span::after {
        content: " ✔";
        color: #fff;
    }

    #cartItems {
        border: 1px solid #e2e8f0;
        border-radius: 0.8rem;
        background: #fff;
        padding: 0.7rem;
        max-height: 230px;
        overflow-y: auto;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .cart-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.65rem;
        padding-bottom: 0.65rem;
        margin-bottom: 0.65rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .cart-item-warning {
        background: linear-gradient(90deg, rgba(251, 191, 36, 0.08) 0%, rgba(251, 191, 36, 0.02) 100%);
        border: 1px solid rgba(251, 191, 36, 0.35) !important;
        border-radius: 0.65rem;
        padding: 0.55rem 0.6rem 0.65rem 0.6rem;
        margin: 0 0 0.6rem 0;
        border-left: 4px solid #f59e0b !important;
    }

    .cart-item:last-child {
        border-bottom: 0;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .cart-item-left {
        flex: 1 1 auto;
        min-width: 0;
    }

    .cart-item-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.9rem;
    }

    .cart-item-meta {
        display: block;
        margin-top: 0.15rem;
        color: #64748b;
        font-size: 0.75rem;
    }

    .cart-item-right {
        flex: 0 0 auto;
        text-align: right;
    }

    .cart-item-total {
        display: inline-block;
        font-size: 0.82rem;
        font-weight: 700;
        color: #1e293b;
        margin-right: 0.4rem;
    }

    .wash-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #475569;
        font-size: 0.9rem;
    }

    .wash-summary-divider {
        padding-top: 0.65rem;
        border-top: 1px solid #e2e8f0;
    }

    .wash-summary-total {
        margin-top: 0.2rem;
        color: #0f172a;
        font-weight: 700;
        font-size: 1rem;
    }

    .wash-summary-total #totalAmount {
        color: #2563eb;
        font-size: 1.2rem;
        font-weight: 800;
    }

    .wash-change-box {
        border-radius: 0.75rem;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        padding: 0.55rem 0.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #1e3a8a;
        font-size: 0.84rem;
    }

    .wash-primary-btn {
        min-height: 44px;
        border-radius: 0.8rem;
        background: linear-gradient(135deg, #16a34a, #22c55e);
        border: 0;
        color: #fff;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .wash-primary-btn:hover {
        color: #fff;
        filter: brightness(1.03);
    }

    .wash-primary-btn:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 0.24rem rgba(59, 130, 246, 0.3);
    }

    @media (max-width: 991.98px) {
        .wash-checkout-card {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .wash-pos-shell {
            padding: 0.25rem 0.35rem 0.8rem;
        }

        .wash-pos-header {
            justify-content: flex-end;
            align-items: flex-end;
            margin-bottom: 1rem;
        }

        .wash-current-time {
            min-width: 0;
            width: fit-content;
        }

        .wash-card-header {
            padding: 0.72rem;
            gap: 0.55rem;
        }

        .wash-card-body {
            padding: 0.72rem;
        }

        .wash-filter-group {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
        }

        .wash-filter-group .filter-btn {
            width: 100%;
            padding-left: 0.2rem;
            padding-right: 0.2rem;
            min-height: 34px;
        }

        #serviceSearchInput {
            width: 100%;
            min-width: 0;
            font-size: 0.82rem;
            min-height: 34px;
        }

        .service-meta {
            gap: 0.35rem;
        }

        .service-price {
            font-size: 0.82rem;
        }

        .service-type {
            font-size: 0.62rem;
            letter-spacing: 0.02em;
            padding: 0.18rem 0.45rem;
            white-space: nowrap;
        }

        .cart-item {
            flex-direction: column;
            gap: 0.45rem;
        }

        .cart-item-right {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .wash-summary-row {
            font-size: 0.84rem;
        }

        .wash-summary-total {
            font-size: 1rem;
        }
    }

    @media (max-width: 767.98px) {
        .wash-filter-group {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.2rem;
        }

        .wash-filter-group .filter-btn {
            width: 100%;
            padding: 0.3rem 0.15rem;
            min-height: 34px;
            font-size: 0.7rem;
        }
    }

    @media (max-width: 575.98px) {
        .wash-card {
            border-radius: 0.85rem;
        }

        .service-item {
            width: 50%;
        }

        .service-card {
            border-radius: 0.85rem;
            padding: 0.62rem;
        }

        .service-image-wrap {
            aspect-ratio: 1 / 1;
            margin-bottom: 0.5rem;
        }

        .service-title {
            font-size: 0.8rem;
            line-height: 1.2;
            margin-bottom: 0.3rem;
        }

        .service-rule-picker .form-select {
            font-size: 0.68rem;
            padding-top: 0.2rem;
            padding-bottom: 0.2rem;
            min-height: 30px;
        }

        .service-rule-picker .form-select {
            display: none;
        }

        .service-rule-mobile-options {
            display: flex;
        }

        .service-description-chips {
            min-height: 0;
            margin-top: 0.2rem;
            gap: 0.22rem;
        }

        .service-description-chip {
            font-size: 0.58rem;
            min-height: 20px;
            padding: 0.16rem 0.34rem;
        }

        .service-price {
            font-size: 0.76rem;
        }

        .service-type {
            font-size: 0.56rem;
            padding: 0.14rem 0.34rem;
        }

        .wash-inline-check {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        #cartItems {
            max-height: 220px;
        }
        
        .wash-filter-group {
            grid-template-columns: repeat(5, 1fr);
        }
        
        .wash-filter-group .filter-btn {
            font-size: 0.65rem;
            padding: 0.25rem 0.1rem;
        }
    }

    [data-bs-theme="dark"] .wash-pos-page {
        color: #e2e8f0;
        background: linear-gradient(180deg, #0f172a 0%, #0b1228 100%);
        border-color: rgba(96, 165, 250, 0.28);
        border-radius: 1rem;
    }

    [data-bs-theme="dark"] .wash-pos-title {
        color: #f8fafc;
    }

    [data-bs-theme="dark"] .wash-pos-subtitle {
        color: #94a3b8;
    }

    [data-bs-theme="dark"] .wash-current-time,
    [data-bs-theme="dark"] .wash-card,
    [data-bs-theme="dark"] .service-card,
    [data-bs-theme="dark"] #cartItems {
        background: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }

    [data-bs-theme="dark"] .wash-current-time {
        box-shadow: none;
        color: #cbd5e1;
        border-color: #334155;
    }

    [data-bs-theme="dark"] .wash-card {
        box-shadow: 0 14px 30px rgba(2, 6, 23, 0.45);
    }

    [data-bs-theme="dark"] .wash-card-header {
        background: linear-gradient(180deg, rgba(59, 130, 246, 0.22) 0%, rgba(15, 23, 42, 0.3) 100%);
        border-bottom-color: rgba(96, 165, 250, 0.28);
    }

    [data-bs-theme="dark"] .wash-card-title {
        color: #e2e8f0;
    }

    [data-bs-theme="dark"] .wash-card-title i {
        color: #93c5fd;
    }

    [data-bs-theme="dark"] .service-image-wrap {
        background: #1e293b;
    }

    [data-bs-theme="dark"] .service-card-mobil {
        border-color: rgba(96, 165, 250, 0.45);
    }

    [data-bs-theme="dark"] .service-card-motor {
        border-color: rgba(251, 146, 60, 0.45);
    }

    [data-bs-theme="dark"] .service-card-caffe {
        border-color: rgba(217, 119, 6, 0.45);
    }

    [data-bs-theme="dark"] .service-card-addon {
        border-color: rgba(74, 222, 128, 0.42);
    }

    [data-bs-theme="dark"] .service-rule-mobile-chip {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }

    [data-bs-theme="dark"] .service-rule-mobile-input:checked + .service-rule-mobile-chip {
        background: rgba(14, 182, 45, 0.86);
        border-color: rgba(34, 197, 94, 0.55);
        color: #daeae0ff;
    }

    [data-bs-theme="dark"] .service-section-heading h6 {
        color: #94a3b8;
        border-bottom-color: #334155;
    }

    [data-bs-theme="dark"] .service-fallback-icon {
        background: #334155;
        color: #bfdbfe;
    }

    [data-bs-theme="dark"] .service-title,
    [data-bs-theme="dark"] .cart-item-name,
    [data-bs-theme="dark"] .cart-item-total {
        color: #f8fafc;
    }

    [data-bs-theme="dark"] .service-description,
    [data-bs-theme="dark"] .wash-field-label,
    [data-bs-theme="dark"] .wash-inline-check .form-check-label,
    [data-bs-theme="dark"] #customerInfo {
        color: #94a3b8;
    }

    [data-bs-theme="dark"] .service-description-chip {
        background: rgba(59, 130, 246, 0.2);
        border-color: rgba(96, 165, 250, 0.42);
        color: #bfdbfe;
    }

    [data-bs-theme="dark"] .service-description-chip-empty {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }

    [data-bs-theme="dark"] .service-price {
        color: #60a5fa;
    }

    [data-bs-theme="dark"] .wash-input {
        background: #0b1220;
        border-color: #334155;
        color: #e2e8f0;
    }

    [data-bs-theme="dark"] .wash-input::placeholder {
        color: #64748b;
    }

    [data-bs-theme="dark"] .wash-input option,
    [data-bs-theme="dark"] .wash-input optgroup {
        background: #0f172a;
        color: #e2e8f0;
    }

    [data-bs-theme="dark"] .wash-secondary-btn {
        background: #1e293b;
        border-color: #334155;
        color: #e2e8f0;
    }

    [data-bs-theme="dark"] .wash-secondary-btn:hover {
        background: #334155;
        border-color: #475569;
    }

    [data-bs-theme="dark"] .wash-filter-group {
        background: #1e293b;
    }

    [data-bs-theme="dark"] .wash-inline-check .form-check-input {
        background-color: #0b1220;
        border-color: #475569;
    }

    [data-bs-theme="dark"] .wash-inline-check .form-check-input:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    [data-bs-theme="dark"] .wash-inline-check .form-check-input:focus {
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.24);
    }

    [data-bs-theme="dark"] .wash-filter-group .filter-btn {
        color: #cbd5e1;
    }

    [data-bs-theme="dark"] .wash-filter-group .filter-btn.active {
        background: #0b1220;
        color: #93c5fd;
        box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.32);
    }

    [data-bs-theme="dark"] .wash-voucher-box {
        background: rgba(22, 101, 52, 0.2);
        border-color: rgba(74, 222, 128, 0.35);
        color: #86efac;
    }

    [data-bs-theme="dark"] #cartItems .text-muted,
    [data-bs-theme="dark"] #emptyCartMsg {
        color: #94a3b8 !important;
    }

    [data-bs-theme="dark"] .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
    }

    [data-bs-theme="dark"] .wash-summary-row,
    [data-bs-theme="dark"] .cart-item-meta {
        color: #94a3b8;
    }

    [data-bs-theme="dark"] .cart-item {
        border-bottom-color: #334155;
    }

    [data-bs-theme="dark"] .wash-summary-divider {
        border-top-color: #334155;
    }

    [data-bs-theme="dark"] .wash-summary-total {
        color: #f8fafc;
    }

    [data-bs-theme="dark"] .wash-summary-total #totalAmount {
        color: #60a5fa;
    }

    [data-bs-theme="dark"] .service-type-mobil {
        background: rgba(30, 64, 175, 0.3);
        color: #93c5fd;
    }

    [data-bs-theme="dark"] .service-type-motor {
        background: rgba(154, 52, 18, 0.35);
        color: #fdba74;
    }

    [data-bs-theme="dark"] .service-type-caffe {
        background: rgba(146, 64, 14, 0.35);
        color: #fcd34d;
    }

    [data-bs-theme="dark"] .service-type-umum {
        background: #334155;
        color: #cbd5e1;
    }

    [data-bs-theme="dark"] .wash-change-box {
        background: rgba(30, 64, 175, 0.22);
        border-color: rgba(96, 165, 250, 0.35);
        color: #bfdbfe;
    }

    [data-bs-theme="dark"] .wash-primary-btn {
        background: linear-gradient(135deg, #15803d, #16a34a);
    }

    #vehicle_brand_dropdown,
    #vehicle_plate_dropdown {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.7rem;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
    }

    #vehicle_brand_dropdown .list-group-item,
    #vehicle_plate_dropdown .list-group-item {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    [data-bs-theme="dark"] #vehicle_brand_dropdown,
    [data-bs-theme="dark"] #vehicle_plate_dropdown {
        background: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }

    [data-bs-theme="dark"] #vehicle_brand_dropdown .list-group-item-secondary {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }

    [data-bs-theme="dark"] #vehicle_brand_dropdown .list-group-item-action:hover,
    [data-bs-theme="dark"] #vehicle_plate_dropdown .list-group-item-action:hover {
        background: #334155;
    }
</style>
@endsection