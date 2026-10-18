{{-- Modal Impor Barang --}}
<div class="modal fade" id="importItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <form action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-file-import me-2 text-primary"></i>{{ __('Impor Data & Stok Inventaris') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="fw-bold mb-2"><i class="fa-solid fa-circle-info me-1"></i> {{ __('Panduan Impor Inventaris') }}</div>
                        <ul class="mb-2 ps-3 small">
                            <li>{{ __('Mendukung file Excel (.xlsx, .xls) dan CSV (.csv).') }}</li>
                            <li>{{ __('Kolom template: Nama Barang, Kategori, Tipe, Merek, Model, Deskripsi / Spesifikasi, Stok Awal, Satuan, Harga Beli / Nilai Aset.') }}</li>
                            <li>{{ __('Sistem otomatis memetakan nama kolom dan membersihkan format mata uang / angka ribuan.') }}</li>
                        </ul>
                        <a href="{{ route('inventory.import.template') }}" class="btn btn-sm btn-primary mt-1">
                            <i class="fa-solid fa-download me-1"></i> {{ __('Unduh Template Inventaris (.xlsx)') }}
                        </a>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">{{ __('Pilih File Excel / CSV') }} <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                        <small class="text-muted">{{ __('Maksimal ukuran file: 10MB') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-cloud-arrow-up me-1"></i> {{ __('Mulai Impor') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
