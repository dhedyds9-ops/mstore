@extends('layouts.app')

@section('title', __('Catat Barang Masuk (Restok) ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 fw-bold text-gray-800 mb-1">{{ __('Catat Barang Masuk (Restok)') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Tambahkan stok barang dari supplier atau pembelian grosir ke inventaris toko ATK.') }}</p>
                </div>
                <a href="{{ route('atk.stock-movements.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Kembali ke Riwayat') }}
                </a>
            </div>

            {{-- Form Card --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-boxes-packing text-success fs-5"></i>
                        <span>{{ __('Formulir Penerimaan Barang Masuk') }}</span>
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('atk.stock-movements.store') }}" method="POST" id="stockInForm">
                        @csrf

                        <div class="row g-3">
                            {{-- Pilih Produk --}}
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Pilih Produk ATK') }} <span class="text-danger">*</span></label>
                                <select name="atk_product_id" id="productSelect" class="form-select @error('atk_product_id') is-invalid @enderror" required autofocus>
                                    <option value="" data-stock="0" data-unit="Pcs" data-cost="0">{{ __('-- Pilih Produk yang Akan Ditambah Stoknya --') }}</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" 
                                            data-stock="{{ $p->stock }}" 
                                            data-unit="{{ $p->unit ?: 'Pcs' }}" 
                                            data-cost="{{ $p->cost_price ?: 0 }}"
                                            {{ (old('atk_product_id', $selectedProductId) == $p->id) ? 'selected' : '' }}>
                                            {{ $p->name }} ({{ $p->code }}) — Stok Saat Ini: {{ $p->stock }} {{ $p->unit ?: 'Pcs' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('atk_product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Live Projection Card --}}
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between flex-wrap gap-2" id="stockPreviewBox">
                                    <div>
                                        <small class="text-muted d-block">{{ __('Stok Fisik Saat Ini') }}</small>
                                        <span class="fs-6 fw-bold text-dark" id="txtCurrentStock">0 Pcs</span>
                                    </div>
                                    <div class="text-center">
                                        <small class="text-muted d-block">{{ __('Jumlah Masuk') }}</small>
                                        <span class="fs-6 fw-bold text-success" id="txtAddQty">+0 Pcs</span>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">{{ __('Estimasi Stok Akhir') }}</small>
                                        <span class="fs-5 fw-bold text-primary" id="txtFinalStock">0 Pcs</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Qty Masuk --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Jumlah Barang Masuk (+Qty)') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="quantity" id="inputQty" class="form-control fw-bold text-success fs-5 @error('quantity') is-invalid @enderror" placeholder="0" value="{{ old('quantity', 1) }}" min="1" required>
                                    <span class="input-group-text bg-light fw-semibold" id="unitLabel">Pcs</span>
                                </div>
                                <small class="text-muted">{{ __('Stok produk akan otomatis bertambah sebesar angka ini.') }}</small>
                                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Harga Beli Satuan (HPP Baru) --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Harga Beli Satuan / Modal (HPP)') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" name="unit_price" id="inputUnitPrice" class="form-control fw-bold @error('unit_price') is-invalid @enderror" placeholder="0" value="{{ old('unit_price') }}" min="0">
                                </div>
                                <small class="text-muted">{{ __('Harga beli baru dari supplier. Otomatis memperbarui HPP produk jika diisi.') }}</small>
                                @error('unit_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Total Nilai Pembelian --}}
                            <div class="col-12">
                                <div class="alert alert-secondary py-2 px-3 mb-0 d-flex align-items-center justify-content-between rounded-3 border">
                                    <span class="small fw-semibold text-muted">{{ __('Total Nilai Pembelian / Belanja:') }}</span>
                                    <span class="fs-6 fw-bold text-dark" id="txtTotalCost">Rp 0</span>
                                </div>
                            </div>

                            {{-- Supplier / Pemasok --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Supplier / Vendor') }}</label>
                                <select name="supplier_id" id="supplierSelect" class="form-select @error('supplier_id') is-invalid @enderror">
                                    <option value="">{{ __('-- Pilih Supplier yang Ada --') }}</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-2" id="newSupplierWrap">
                                    <input type="text" name="supplier_name" class="form-control form-control-sm" placeholder="{{ __('Atau ketik nama supplier / toko baru...') }}" value="{{ old('supplier_name') }}">
                                </div>
                                @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Nomor Faktur / Surat Jalan --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Nomor Faktur / Surat Jalan / Nota') }}</label>
                                <input type="text" name="invoice_number" class="form-control font-monospace @error('invoice_number') is-invalid @enderror" placeholder="Contoh: INV-2026/09/012" value="{{ old('invoice_number') }}">
                                <small class="text-muted">{{ __('Nomor nota dari supplier untuk kemudahan audit faktur.') }}</small>
                                @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Tanggal Masuk --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Tanggal Penerimaan Barang') }} <span class="text-danger">*</span></label>
                                <input type="date" name="movement_date" class="form-control @error('movement_date') is-invalid @enderror" value="{{ old('movement_date', date('Y-m-d')) }}" required>
                                @error('movement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Catat Pengeluaran Kas Toko --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">{{ __('Integrasi Kas Toko (Kas Utama)') }}</label>
                                <div class="form-check form-switch mt-1 p-2 border rounded-3 bg-light">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="payFromCashSwitch" name="pay_from_cash" value="1" {{ old('pay_from_cash') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="payFromCashSwitch">
                                        {{ __('Bayar Tunai dari Kas Toko') }}
                                    </label>
                                </div>
                                <small class="text-muted">{{ __('Jika dicentang, pengeluaran kas toko akan otomatis dicatat tanpa perlu input ulang di menu Pengeluaran.') }}</small>
                            </div>

                            {{-- Catatan / Keterangan --}}
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">{{ __('Catatan / Keterangan Tambahan') }}</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Catatan kondisi barang, garansi, atau keterangan pembelian...">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4 mt-3 border-top">
                            <a href="{{ route('atk.stock-movements.index') }}" class="btn btn-outline-secondary px-3">
                                {{ __('Batal') }}
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa-solid fa-check me-1"></i>{{ __('Simpan Barang Masuk') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('productSelect');
    const inputQty = document.getElementById('inputQty');
    const inputUnitPrice = document.getElementById('inputUnitPrice');
    const unitLabel = document.getElementById('unitLabel');
    const txtCurrentStock = document.getElementById('txtCurrentStock');
    const txtAddQty = document.getElementById('txtAddQty');
    const txtFinalStock = document.getElementById('txtFinalStock');
    const txtTotalCost = document.getElementById('txtTotalCost');

    function updatePreview() {
        const opt = productSelect.options[productSelect.selectedIndex];
        const stock = parseInt(opt?.dataset?.stock || 0);
        const unit = opt?.dataset?.unit || 'Pcs';
        const cost = parseFloat(opt?.dataset?.cost || 0);

        unitLabel.textContent = unit;

        if (inputUnitPrice && !inputUnitPrice.value && cost > 0) {
            inputUnitPrice.value = cost;
        }

        const qty = parseInt(inputQty.value || 0);
        const unitPrice = parseFloat(inputUnitPrice.value || cost);
        const total = qty * unitPrice;

        txtCurrentStock.textContent = `${stock.toLocaleString('id-ID')} ${unit}`;
        txtAddQty.textContent = `+${qty.toLocaleString('id-ID')} ${unit}`;
        txtFinalStock.textContent = `${(stock + qty).toLocaleString('id-ID')} ${unit}`;
        txtTotalCost.textContent = `Rp ${Math.round(total).toLocaleString('id-ID')}`;
    }

    productSelect.addEventListener('change', function() {
        const opt = productSelect.options[productSelect.selectedIndex];
        if (opt && opt.dataset.cost) {
            inputUnitPrice.value = opt.dataset.cost;
        }
        updatePreview();
    });

    inputQty.addEventListener('input', updatePreview);
    inputUnitPrice.addEventListener('input', updatePreview);

    updatePreview();
});
</script>
@endpush
@endsection
