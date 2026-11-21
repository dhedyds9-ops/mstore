@extends('layouts.app')

@section('title', 'Edit Layanan Wash')

@section('content')
<div class="container-fluid wash-service-edit-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800" id="pageTitle">Edit Layanan</h1>
        <a href="{{ route('wash.services.index') }}" class="btn btn-secondary" title="Kembali">
            <i class="fa-solid fa-arrow-left"></i>
            <span class="d-none d-md-inline ms-2">Kembali</span>
        </a>
    </div>

    <form action="{{ route('wash.services.update', $service->id) }}" method="POST" enctype="multipart/form-data" id="editServiceForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Kolom Kiri: Info Utama & Harga -->
            <div class="col-12 col-lg-7">
                <!-- Card 1: Informasi Layanan -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fas fa-info-circle text-primary me-2"></i>Informasi Layanan
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold" id="nameLabel">Nama Layanan</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $service->name) }}" required placeholder="Contoh: Cuci Mobil Besar">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="vehicle_type" class="form-label fw-semibold" id="vehicleTypeLabel">Jenis Kendaraan / Produk</label>
                                <select class="form-select @error('vehicle_type') is-invalid @enderror" id="vehicle_type" name="vehicle_type" required>
                                    <option value="car" {{ old('vehicle_type', $service->vehicle_type) == 'car' ? 'selected' : '' }}>Mobil (Car)</option>
                                    <option value="motor" {{ old('vehicle_type', $service->vehicle_type) == 'motor' ? 'selected' : '' }}>Motor (Motorcycle)</option>
                                    <option value="coffee" {{ old('vehicle_type', $service->vehicle_type) == 'coffee' ? 'selected' : '' }}>Caffe (Minuman/Snack)</option>
                                </select>
                                @error('vehicle_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-primary d-none small mt-1" id="coffeeModeHint">
                                    <i class="fas fa-info-circle me-1"></i>Mode Caffe aktif: formulir disederhanakan.
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="service_category" class="form-label fw-semibold">Kategori Layanan</label>
                                <select class="form-select @error('service_category') is-invalid @enderror" id="service_category" name="service_category" required>
                                    @foreach(\App\Models\WashService::CATEGORY_OPTIONS as $value => $label)
                                        <option value="{{ $value }}" {{ old('service_category', $service->service_category ?? 'main') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('service_category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3" id="washAttributeGroup">
                            <div class="col-12 col-md-6">
                                <label for="size_tier" class="form-label fw-semibold">Ukuran Kendaraan</label>
                                <select class="form-select @error('size_tier') is-invalid @enderror" id="size_tier" name="size_tier" required>
                                    @foreach(\App\Models\WashService::SIZE_TIER_OPTIONS as $value => $label)
                                        <option value="{{ $value }}" {{ old('size_tier', $service->size_tier ?? 'none') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('size_tier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="package_type" class="form-label fw-semibold">Jenis Paket</label>
                                <select class="form-select @error('package_type') is-invalid @enderror" id="package_type" name="package_type" required>
                                    @foreach(\App\Models\WashService::PACKAGE_TYPE_OPTIONS as $value => $label)
                                        <option value="{{ $value }}" {{ old('package_type', $service->package_type ?? 'general') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('package_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-2" id="stockItemField">
                            <label for="wash_stock_item_id" class="form-label fw-semibold">Produk Stok Terkait (Opsional)</label>
                            <select class="form-select @error('wash_stock_item_id') is-invalid @enderror" id="wash_stock_item_id" name="wash_stock_item_id">
                                <option value="">Tidak terhubung ke stok bahan</option>
                                @foreach($stockItems as $item)
                                    <option value="{{ $item->id }}" {{ old('wash_stock_item_id', $service->wash_stock_item_id) == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }} (Stok: {{ number_format($item->current_stock, 2, ',', '.') }} {{ $item->unit }})
                                    </option>
                                @endforeach
                            </select>
                            @error('wash_stock_item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small">Pilih jika layanan ini otomatis memotong stok barang saat transaksi.</div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Penetapan Harga (Tarif) -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fas fa-tags text-success me-2"></i>Penetapan Harga & Tarif
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="price" class="form-label fw-bold text-success fs-6" id="sellingPriceLabel">Harga Jual (Tarif Kasir) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success bg-opacity-10 text-success fw-bold">Rp</span>
                                    <input type="number" class="form-control form-control-lg fw-bold text-success @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $service->price) }}" required min="0" placeholder="50000">
                                </div>
                                @error('price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="cost_price" class="form-label fw-semibold" id="costPriceLabel">Harga Modal / HPP</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">Rp</span>
                                    <input type="number" class="form-control @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', $service->cost_price ?? 0) }}" min="0" placeholder="0">
                                </div>
                                @error('cost_price')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted" style="font-size:11px;">Biaya modal bahan/operasional per layanan.</small>
                            </div>
                        </div>

                        <div class="mb-2" id="holidayPriceGroup">
                            <label for="holiday_price" class="form-label fw-semibold">Penyesuaian Hari Raya (+ / -)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">Rp</span>
                                <input type="number" class="form-control @error('holiday_price') is-invalid @enderror" id="holiday_price" name="holiday_price" value="{{ old('holiday_price', $service->holiday_price) }}" placeholder="Contoh: 5000 atau -3000">
                            </div>
                            @error('holiday_price')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small">Penambahan atau pengurangan tarif otomatis saat jadwal Hari Raya aktif.</div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Aturan Variasi Harga POS (Accordion Rapi) -->
                @php
                    $existingRules = old('rule_price')
                        ? null
                        : ($service->priceRules ?? collect());
                    if (is_null($existingRules)) {
                        $ruleRows = max(1, count(old('rule_price', [])));
                    } else {
                        $ruleRows = max(1, $existingRules->count());
                    }
                    $hasRules = is_null($existingRules) ? count(old('rule_price', [])) > 0 : $existingRules->count() > 0;
                @endphp
                <div class="card shadow-sm border-0 mb-4" id="priceRulesSection">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fas fa-sliders-h text-info me-2"></i>Aturan Variasi Harga POS
                            </h5>
                            <span class="badge {{ $hasRules ? 'bg-primary' : 'bg-light text-muted border' }}">
                                {{ $hasRules ? ($existingRules ? $existingRules->count() : count(old('rule_price', []))) . ' Aturan' : 'Opsional' }}
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="applyPriceTemplate">Template Otomatis</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addPriceRuleRow">+ Tambah Baris</button>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <p class="text-muted small mb-3">
                            Gunakan aturan variasi ini jika 1 layanan memiliki opsi tarif bertingkat (berdasarkan ukuran kendaraan atau paket). Jika tidak ada variasi, tabel ini boleh dikosongkan.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0" id="priceRulesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 100px;">Tipe</th>
                                        <th style="min-width: 100px;">Ukuran</th>
                                        <th style="min-width: 140px;">Paket</th>
                                        <th style="min-width: 120px;">Harga (Rp)</th>
                                        <th style="width: 70px;">Urutan</th>
                                        <th style="width: 60px;" class="text-center">Aktif</th>
                                        <th style="width: 45px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($i = 0; $i < $ruleRows; $i++)
                                        @php
                                            $rule = is_null($existingRules) ? null : $existingRules->values()->get($i);
                                            $oldVehicle = old('rule_vehicle_type.'.$i, $rule?->vehicle_type ?? 'all');
                                            $oldSize = old('rule_size_tier.'.$i, $rule?->size_tier ?? 'none');
                                            $oldPackage = old('rule_package_type.'.$i, $rule?->package_type ?? 'general');
                                            $oldPrice = old('rule_price.'.$i, $rule?->price);
                                            $oldSort = old('rule_sort_order.'.$i, $rule?->sort_order ?? 0);
                                            $activeFromOld = collect(old('rule_is_active', []))->map(fn($v) => (string) $v)->all();
                                            $isActive = in_array((string) $i, $activeFromOld, true);
                                            if (! old('rule_is_active') && ! is_null($rule)) {
                                                $isActive = (bool) $rule->is_active;
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <select class="form-select form-select-sm" name="rule_vehicle_type[]">
                                                    <option value="all" {{ $oldVehicle === 'all' || is_null($oldVehicle) ? 'selected' : '' }}>Semua</option>
                                                    <option value="car" {{ $oldVehicle === 'car' ? 'selected' : '' }}>Mobil</option>
                                                    <option value="motor" {{ $oldVehicle === 'motor' ? 'selected' : '' }}>Motor</option>
                                                    <option value="coffee" {{ $oldVehicle === 'coffee' ? 'selected' : '' }}>Caffe</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm" name="rule_size_tier[]">
                                                    @foreach(\App\Models\WashService::SIZE_TIER_OPTIONS as $value => $label)
                                                        <option value="{{ $value }}" {{ $oldSize === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm" name="rule_package_type[]">
                                                    @foreach(\App\Models\WashService::PACKAGE_TYPE_OPTIONS as $value => $label)
                                                        <option value="{{ $value }}" {{ $oldPackage === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" min="0" class="form-control form-control-sm" name="rule_price[]" value="{{ $oldPrice }}" placeholder="0"></td>
                                            <td><input type="number" min="0" class="form-control form-control-sm" name="rule_sort_order[]" value="{{ $oldSort }}"></td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input" name="rule_is_active[]" value="{{ $i }}" {{ $isActive ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" data-remove-rule title="Hapus baris">&times;</button>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Foto, Deskripsi, Status & Aksi -->
            <div class="col-12 col-lg-5">
                <!-- Card 4: Media & Gambar Layanan -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fas fa-image text-warning me-2"></i>Foto & Media Layanan
                        </h5>
                    </div>
                    <div class="card-body p-4 text-center">
                        <label for="image" class="form-label d-block text-start fw-semibold" id="imageLabel">Unggah Foto Layanan</label>
                        @if($service->image)
                            <div class="mb-3 text-center">
                                <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}" class="img-thumbnail rounded shadow-sm" style="max-height: 160px; object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                <div class="alert alert-warning py-1 px-2 small d-none mt-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> File gambar tidak ditemukan di server.</div>
                            </div>
                        @else
                            <div class="bg-light rounded p-4 mb-3 text-center text-muted border border-dashed">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-secondary"></i>
                                <div class="small">Belum ada foto layanan</div>
                            </div>
                        @endif
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback text-start">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-start text-muted small mt-1">Format: JPG, PNG, WEBP. Maks 2MB.</div>
                    </div>
                </div>

                <!-- Card 5: Pengaturan Tampilan & Deskripsi -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fas fa-cog text-secondary me-2"></i>Status & Keterangan
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3 d-flex justify-content-between align-items-center p-3 bg-light rounded border">
                            <div>
                                <label class="form-check-label fw-bold d-block text-dark" for="is_active">Status Layanan</label>
                                <small class="text-muted">Aktifkan agar layanan muncul di Kasir POS.</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" style="width: 2.4em; height: 1.3em;" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                            </div>
                        </div>

                        <div class="mb-3" id="sortOrderGroup">
                            <label for="sort_order" class="form-label fw-semibold">Urutan Tampil di POS</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $service->sort_order ?? 0) }}" min="0" placeholder="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small">Nomor urut lebih kecil akan tampil lebih awal di POS.</div>
                        </div>

                        <div class="mb-2">
                            <label for="description" class="form-label fw-semibold" id="descriptionLabel">Deskripsi / Contoh Kendaraan</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Contoh: Pajero, Fortuner, Alphard...">{{ old('description', $service->description) }}</textarea>
                            <div class="wash-description-editor mt-2" data-description-editor data-target="description" id="descriptionEditorWrap">
                                <div class="wash-description-chips" data-description-chips></div>
                                <div class="input-group input-group-sm mt-2">
                                    <input type="text" class="form-control" data-description-input placeholder="Tambah contoh mobil/tag cepat...">
                                    <button class="btn btn-outline-primary" type="button" data-description-add>+ Tambah</button>
                                </div>
                            </div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Card 6: Aksi Simpan -->
                <div class="card shadow-sm border-0 mb-4 bg-light">
                    <div class="card-body p-3 d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold" id="submitButtonDesktop">
                            <i class="fas fa-save me-2"></i>Update Layanan
                        </button>
                        <a href="{{ route('wash.services.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="position-fixed start-0 end-0 bg-body border-top shadow d-md-none wash-mobile-action-bar">
    <div class="container py-2">
        <div class="d-flex gap-2">
            <a href="{{ route('wash.services.index') }}" class="btn btn-outline-secondary w-50">Batal</a>
            <button type="submit" class="btn btn-primary w-50" form="editServiceForm" id="submitButtonMobile">Update Layanan</button>
        </div>
    </div>
</div>
@push('styles')
<style>
    .wash-service-edit-page .form-control,
    .wash-service-edit-page .form-select {
        min-height: 44px;
    }

    .wash-service-edit-page .wash-description-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .wash-service-edit-page .wash-description-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.58rem;
        border-radius: 999px;
        border: 1px solid #c7d2fe;
        background: #eef2ff;
        color: #3730a3;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .wash-service-edit-page .wash-description-chip button {
        border: 0;
        background: transparent;
        color: inherit;
        font-size: 0.9rem;
        line-height: 1;
        padding: 0;
        cursor: pointer;
    }

    .wash-service-edit-page .wash-description-chip-empty {
        border-color: #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        font-weight: 500;
    }

    [data-bs-theme="dark"] .wash-service-edit-page .wash-description-chip {
        background: rgba(59, 130, 246, 0.2);
        border-color: rgba(96, 165, 250, 0.42);
        color: #bfdbfe;
    }

    [data-bs-theme="dark"] .wash-service-edit-page .wash-description-chip-empty {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }

    @media (max-width: 767.98px) {
        .wash-service-edit-page {
            padding-left: 0.35rem;
            padding-right: 0.35rem;
            padding-bottom: 6rem !important;
        }

        .wash-service-edit-page .h3 {
            font-size: 1.1rem;
            margin-bottom: 0.9rem !important;
        }

        .wash-service-edit-page .card-body {
            padding: 0.9rem;
        }
    }

    .wash-mobile-action-bar {
        bottom: 0;
        z-index: 1080;
    }
</style>
@endpush
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-description-editor]').forEach(function (editor) {
            const targetId = editor.dataset.target;
            const textarea = document.getElementById(targetId);
            if (!textarea) {
                return;
            }

            const chipsWrap = editor.querySelector('[data-description-chips]');
            const input = editor.querySelector('[data-description-input]');
            const addButton = editor.querySelector('[data-description-add]');

            const parseItems = function (value) {
                return value
                    .split(/[,;\n]/)
                    .map(function (item) { return item.trim(); })
                    .filter(function (item) { return item.length > 0; });
            };

            const syncTextarea = function () {
                const items = Array.from(chipsWrap.querySelectorAll('[data-item-value]')).map(function (el) {
                    return el.dataset.itemValue;
                });
                textarea.value = items.join(', ');
            };

            const getCurrentItems = function () {
                return Array.from(chipsWrap.querySelectorAll('[data-item-value]')).map(function (el) {
                    return el.dataset.itemValue;
                });
            };

            const render = function (items) {
                chipsWrap.innerHTML = '';
                if (!items.length) {
                    const empty = document.createElement('span');
                    empty.className = 'wash-description-chip wash-description-chip-empty';
                    empty.textContent = 'Belum ada label';
                    chipsWrap.appendChild(empty);
                    textarea.value = '';
                    return;
                }
                items.forEach(function (item) {
                    const chip = document.createElement('span');
                    chip.className = 'wash-description-chip';
                    chip.dataset.itemValue = item;
                    chip.textContent = item;

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.innerHTML = '&times;';
                    removeButton.addEventListener('click', function () {
                        chip.remove();
                        syncTextarea();
                    });

                    chip.appendChild(removeButton);
                    chipsWrap.appendChild(chip);
                });
                syncTextarea();
            };

            const addItems = function (rawValue) {
                const existingItems = getCurrentItems();
                const current = new Set(existingItems.map(function (item) {
                    return item.toLowerCase();
                }));
                const added = [];
                parseItems(rawValue).forEach(function (item) {
                    if (!current.has(item.toLowerCase())) {
                        current.add(item.toLowerCase());
                        added.push(item);
                    }
                });
                render(existingItems.concat(added));
            };

            const syncFromTextarea = function () {
                const items = [];
                const dedupe = new Set();
                parseItems(textarea.value).forEach(function (item) {
                    const key = item.toLowerCase();
                    if (dedupe.has(key)) {
                        return;
                    }
                    dedupe.add(key);
                    items.push(item);
                });
                render(items);
            };

            textarea.addEventListener('input', function () {
                const selectionStart = textarea.selectionStart;
                const selectionEnd = textarea.selectionEnd;
                syncFromTextarea();
                textarea.setSelectionRange(selectionStart, selectionEnd);
            });

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addButton.click();
                }
            });

            addButton.addEventListener('click', function () {
                if (!input.value.trim()) {
                    return;
                }
                addItems(input.value);
                input.value = '';
                input.focus();
            });

            const initialItems = [];
            const initialSeen = new Set();
            parseItems(textarea.value).forEach(function (item) {
                const key = item.toLowerCase();
                if (initialSeen.has(key)) {
                    return;
                }
                initialSeen.add(key);
                initialItems.push(item);
            });
            render(initialItems);
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.querySelector('#priceRulesTable tbody');
        const addButton = document.getElementById('addPriceRuleRow');
        const templateButton = document.getElementById('applyPriceTemplate');
        const basePriceInput = document.getElementById('price');
        const vehicleTypeInput = document.getElementById('vehicle_type');
        const categoryInput = document.getElementById('service_category');
        const sizeTierInput = document.getElementById('size_tier');
        const packageTypeInput = document.getElementById('package_type');
        const pageTitle = document.getElementById('pageTitle');
        const nameLabel = document.getElementById('nameLabel');
        const vehicleTypeLabel = document.getElementById('vehicleTypeLabel');
        const coffeeModeHint = document.getElementById('coffeeModeHint');
        const washAttributeGroup = document.getElementById('washAttributeGroup');
        const sortOrderGroup = document.getElementById('sortOrderGroup');
        const holidayPriceGroup = document.getElementById('holidayPriceGroup');
        const priceRulesSection = document.getElementById('priceRulesSection');
        const descriptionLabel = document.getElementById('descriptionLabel');
        const descriptionEditorWrap = document.getElementById('descriptionEditorWrap');
        const imageLabel = document.getElementById('imageLabel');
        const costPriceLabel = document.getElementById('costPriceLabel');
        const sellingPriceLabel = document.getElementById('sellingPriceLabel');
        const submitButtonDesktop = document.getElementById('submitButtonDesktop');
        const submitButtonMobile = document.getElementById('submitButtonMobile');
        if (!tableBody || !addButton) {
            return;
        }

        const renderRow = function (index) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select class="form-select form-select-sm" name="rule_vehicle_type[]">
                        <option value="all" selected>Semua</option>
                        <option value="car">Mobil</option>
                        <option value="motor">Motor</option>
                        <option value="coffee">Caffe</option>
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm" name="rule_size_tier[]">
                        <option value="none" selected>-</option>
                        <option value="kecil">Kecil</option>
                        <option value="sedang">Sedang</option>
                        <option value="besar">Besar</option>
                        <option value="extra_besar">Extra Besar</option>
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm" name="rule_package_type[]">
                        <option value="general" selected>General</option>
                        <option value="body_only">Body Only</option>
                        <option value="full_clean">Body + Kolong + Vacuum</option>
                        <option value="express">Cuci Cepat + Semir Ban</option>
                        <option value="engine_cleaner">Cleaner Mesin</option>
                        <option value="leather_cleaner">Cleaner Jok Kulit</option>
                    </select>
                </td>
                <td><input type="number" min="0" class="form-control form-control-sm" name="rule_price[]" placeholder="0"></td>
                <td><input type="number" min="0" class="form-control form-control-sm" name="rule_sort_order[]" value="${index}"></td>
                <td class="text-center"><input type="checkbox" class="form-check-input" name="rule_is_active[]" value="${index}" checked></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-rule>&times;</button></td>
            `;
            tableBody.appendChild(row);
        };

        const syncActiveIndexes = function () {
            Array.from(tableBody.querySelectorAll('tr')).forEach((row, index) => {
                const active = row.querySelector('input[name="rule_is_active[]"]');
                if (active) {
                    active.value = String(index);
                }
            });
        };

        const addRow = function (rowData, index) {
            renderRow(index);
            const row = tableBody.lastElementChild;
            if (!row) {
                return;
            }
            row.querySelector('select[name="rule_vehicle_type[]"]').value = rowData.vehicle_type || 'all';
            row.querySelector('select[name="rule_size_tier[]"]').value = rowData.size_tier || 'none';
            row.querySelector('select[name="rule_package_type[]"]').value = rowData.package_type || 'general';
            row.querySelector('input[name="rule_price[]"]').value = rowData.price || '';
            row.querySelector('input[name="rule_sort_order[]"]').value = rowData.sort_order ?? index;
            row.querySelector('input[name="rule_is_active[]"]').checked = rowData.is_active !== false;
        };

        const buildTemplateRows = function () {
            const base = parseFloat(basePriceInput?.value || 0) || 0;
            const vehicleType = vehicleTypeInput?.value || 'car';
            const category = categoryInput?.value || 'main';

            if (vehicleType === 'coffee') {
                return [
                    { vehicle_type: 'coffee', size_tier: 'none', package_type: 'general', price: Math.max(0, Math.round(base)), sort_order: 1, is_active: true },
                ];
            }

            if (category === 'addon' || category === 'skincare') {
                const sizes = ['kecil', 'sedang', 'besar'];
                return sizes.map((size, idx) => ({
                    vehicle_type: vehicleType,
                    size_tier: size,
                    package_type: category === 'skincare' ? 'leather_cleaner' : 'engine_cleaner',
                    price: Math.max(0, Math.round(base + (idx * 5000))),
                    sort_order: idx + 1,
                    is_active: true,
                }));
            }

            if (vehicleType === 'motor') {
                return [
                    { vehicle_type: 'motor', size_tier: 'kecil', package_type: 'express', price: Math.max(0, Math.round(base * 0.85)), sort_order: 1, is_active: true },
                    { vehicle_type: 'motor', size_tier: 'sedang', package_type: 'express', price: Math.max(0, Math.round(base)), sort_order: 2, is_active: true },
                    { vehicle_type: 'motor', size_tier: 'besar', package_type: 'express', price: Math.max(0, Math.round(base * 1.25)), sort_order: 3, is_active: true },
                ];
            }

            return [
                { vehicle_type: 'car', size_tier: 'kecil', package_type: 'body_only', price: Math.max(0, Math.round(base * 0.8)), sort_order: 1, is_active: true },
                { vehicle_type: 'car', size_tier: 'kecil', package_type: 'full_clean', price: Math.max(0, Math.round(base)), sort_order: 2, is_active: true },
                { vehicle_type: 'car', size_tier: 'sedang', package_type: 'body_only', price: Math.max(0, Math.round(base)), sort_order: 3, is_active: true },
                { vehicle_type: 'car', size_tier: 'sedang', package_type: 'full_clean', price: Math.max(0, Math.round(base * 1.2)), sort_order: 4, is_active: true },
                { vehicle_type: 'car', size_tier: 'besar', package_type: 'body_only', price: Math.max(0, Math.round(base * 1.2)), sort_order: 5, is_active: true },
                { vehicle_type: 'car', size_tier: 'besar', package_type: 'full_clean', price: Math.max(0, Math.round(base * 1.4)), sort_order: 6, is_active: true },
            ];
        };

        const applyCoffeeDefaults = function () {
            if (vehicleTypeInput?.value !== 'coffee') {
                return;
            }
            if (categoryInput) {
                categoryInput.value = 'main';
            }
            if (sizeTierInput) {
                sizeTierInput.value = 'none';
            }
            if (packageTypeInput) {
                packageTypeInput.value = 'general';
            }
        };

        const toggleCoffeeMode = function () {
            const isCoffee = vehicleTypeInput?.value === 'coffee';
            if (isCoffee) {
                applyCoffeeDefaults();
            }

            if (pageTitle) {
                pageTitle.textContent = isCoffee ? 'Edit Produk Caffe' : 'Edit Layanan';
            }
            if (nameLabel) {
                nameLabel.textContent = isCoffee ? 'Nama Produk Caffe' : 'Nama Layanan';
            }
            if (vehicleTypeLabel) {
                vehicleTypeLabel.textContent = isCoffee ? 'Jenis Produk' : 'Jenis Kendaraan';
            }
            if (costPriceLabel) {
                costPriceLabel.textContent = isCoffee ? 'Cost Price (HPP)' : 'Harga Beli';
            }
            if (sellingPriceLabel) {
                sellingPriceLabel.textContent = isCoffee ? 'Selling Price' : 'Harga Jual';
            }
            if (descriptionLabel) {
                descriptionLabel.textContent = isCoffee ? 'Deskripsi Produk' : 'Deskripsi';
            }
            if (imageLabel) {
                imageLabel.textContent = isCoffee ? 'Gambar Produk' : 'Gambar Layanan';
            }
            if (submitButtonDesktop) {
                submitButtonDesktop.textContent = isCoffee ? 'Update Produk' : 'Update Layanan';
            }
            if (submitButtonMobile) {
                submitButtonMobile.textContent = isCoffee ? 'Update Produk' : 'Update Layanan';
            }
            if (coffeeModeHint) {
                coffeeModeHint.classList.toggle('d-none', !isCoffee);
            }
            if (washAttributeGroup) {
                washAttributeGroup.classList.toggle('d-none', isCoffee);
            }
            if (sortOrderGroup) {
                sortOrderGroup.classList.toggle('d-none', isCoffee);
            }
            if (holidayPriceGroup) {
                holidayPriceGroup.classList.toggle('d-none', isCoffee);
            }
            if (priceRulesSection) {
                priceRulesSection.classList.toggle('d-none', isCoffee);
            }
            if (descriptionEditorWrap) {
                descriptionEditorWrap.classList.toggle('d-none', isCoffee);
            }
        };

        vehicleTypeInput?.addEventListener('change', toggleCoffeeMode);
        toggleCoffeeMode();

        addButton.addEventListener('click', function () {
            renderRow(tableBody.querySelectorAll('tr').length);
            syncActiveIndexes();
        });

        if (templateButton) {
            templateButton.addEventListener('click', function () {
                const rows = buildTemplateRows();
                tableBody.innerHTML = '';
                rows.forEach((row, index) => addRow(row, index));
                if (rows.length === 0) {
                    renderRow(0);
                }
                syncActiveIndexes();
            });
        }

        tableBody.addEventListener('click', function (event) {
            const button = event.target.closest('[data-remove-rule]');
            if (!button) {
                return;
            }
            const row = button.closest('tr');
            if (!row) {
                return;
            }
            if (tableBody.querySelectorAll('tr').length <= 1) {
                row.querySelectorAll('input[type="number"]').forEach(input => input.value = '');
                return;
            }
            row.remove();
            syncActiveIndexes();
        });
    });
</script>
@endpush
@endsection
