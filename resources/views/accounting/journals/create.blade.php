@extends('layouts.app')
@section('title', 'Buat Jurnal Umum Manual')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-file-circle-plus text-primary me-2"></i>Buat Jurnal Umum Manual</h4>
            <p class="text-secondary small mb-0">Input penyesuaian, penyusutan aset, setoran modal, atau beban operasional berpasangan.</p>
        </div>
        <a href="{{ route('accounting.journals.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Jurnal
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3 d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <form method="post" action="{{ route('accounting.journals.store') }}" id="journalForm">
        @csrf
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-2"></i>Informasi Header Jurnal</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Tanggal Transaksi <span class="text-danger">*</span></label>
                        <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Nomor Jurnal <span class="text-danger">*</span></label>
                        <input type="text" name="journal_no" value="{{ old('journal_no', $defaultNo) }}" class="form-control form-control-sm font-monospace" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Periode Akuntansi</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $openPeriod ? $openPeriod->name . ' (TERBUKA)' : 'Belum Ada Periode Terbuka' }}" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small text-secondary">Keterangan / Deskripsi Transaksi <span class="text-danger">*</span></label>
                        <input type="text" name="description" value="{{ old('description') }}" class="form-control form-control-sm" placeholder="Contoh: Beban listrik dan air cabang utama bulan berjalan" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list-ol text-primary me-2"></i>Rincian Akun (Debit & Kredit)</h6>
                <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" id="addRowBtn">
                    <i class="fas fa-plus me-1"></i> Tambah Baris
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="entriesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 small text-uppercase text-body-secondary fw-semibold" style="width: 38%;">Akun (COA) <span class="text-danger">*</span></th>
                                <th class="small text-uppercase text-body-secondary fw-semibold" style="width: 24%;">Debit (Rp)</th>
                                <th class="small text-uppercase text-body-secondary fw-semibold" style="width: 24%;">Kredit (Rp)</th>
                                <th class="pe-4 text-center small text-uppercase text-body-secondary fw-semibold" style="width: 14%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="entriesBody">
                            <tr class="entry-row">
                                <td class="ps-4">
                                    <select name="entries[0][account_id]" class="form-select form-select-sm select-account" required>
                                        <option value="">Pilih Akun</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->type) }})</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="entries[0][memo]" class="form-control form-control-sm mt-1" placeholder="Catatan memo (opsional)">
                                </td>
                                <td>
                                    <input type="number" name="entries[0][debit]" class="form-control form-control-sm font-monospace input-debit" min="0" step="any" value="0">
                                </td>
                                <td>
                                    <input type="number" name="entries[0][credit]" class="form-control form-control-sm font-monospace input-credit" min="0" step="any" value="0">
                                </td>
                                <td class="text-center pe-4">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="entry-row">
                                <td class="ps-4">
                                    <select name="entries[1][account_id]" class="form-select form-select-sm select-account" required>
                                        <option value="">Pilih Akun</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->type) }})</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="entries[1][memo]" class="form-control form-control-sm mt-1" placeholder="Catatan memo (opsional)">
                                </td>
                                <td>
                                    <input type="number" name="entries[1][debit]" class="form-control form-control-sm font-monospace input-debit" min="0" step="any" value="0">
                                </td>
                                <td>
                                    <input type="number" name="entries[1][credit]" class="form-control form-control-sm font-monospace input-credit" min="0" step="any" value="0">
                                </td>
                                <td class="text-center pe-4">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light fw-bold border-top-2">
                            <tr>
                                <td class="ps-4 text-end text-uppercase small">Total:</td>
                                <td class="font-monospace text-primary" id="displayTotalDebit">Rp 0</td>
                                <td class="font-monospace text-primary" id="displayTotalCredit">Rp 0</td>
                                <td class="text-center pe-4">
                                    <span id="balanceBadge" class="badge bg-danger rounded-pill px-3 py-2">Selisih: Rp 0</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <span class="small text-secondary" id="balanceHint">
                    <i class="fas fa-info-circle text-primary me-1"></i> Total Debit harus sama dengan Total Kredit (Balance) sebelum dapat disimpan.
                </span>
                <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm" id="submitBtn" disabled>
                    <i class="fas fa-check me-1"></i> Posting Jurnal
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 2;
    const body = document.getElementById('entriesBody');
    const addBtn = document.getElementById('addRowBtn');
    const displayDebit = document.getElementById('displayTotalDebit');
    const displayCredit = document.getElementById('displayTotalCredit');
    const balanceBadge = document.getElementById('balanceBadge');
    const submitBtn = document.getElementById('submitBtn');

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function calculateBalance() {
        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll('.input-debit').forEach(el => {
            totalDebit += parseFloat(el.value) || 0;
        });

        document.querySelectorAll('.input-credit').forEach(el => {
            totalCredit += parseFloat(el.value) || 0;
        });

        displayDebit.textContent = formatRupiah(totalDebit);
        displayCredit.textContent = formatRupiah(totalCredit);

        const diff = Math.abs(totalDebit - totalCredit);
        const isBalanced = diff < 0.01 && totalDebit > 0;

        if (isBalanced) {
            balanceBadge.className = 'badge bg-success';
            balanceBadge.textContent = 'SEIMBANG (OK)';
            submitBtn.disabled = false;
        } else {
            balanceBadge.className = 'badge bg-danger';
            balanceBadge.textContent = 'Selisih: ' + formatRupiah(diff);
            submitBtn.disabled = true;
        }
    }

    addBtn.addEventListener('click', function () {
        const firstRow = document.querySelector('.entry-row');
        const clone = firstRow.cloneNode(true);

        clone.querySelectorAll('input').forEach(input => {
            if (input.classList.contains('input-debit') || input.classList.contains('input-credit')) {
                input.value = '0';
            } else {
                input.value = '';
            }
        });

        clone.querySelector('select').selectedIndex = 0;

        clone.querySelector('.select-account').name = `entries[${rowIndex}][account_id]`;
        clone.querySelector('input[placeholder*="memo"]').name = `entries[${rowIndex}][memo]`;
        clone.querySelector('.input-debit').name = `entries[${rowIndex}][debit]`;
        clone.querySelector('.input-credit').name = `entries[${rowIndex}][credit]`;

        const removeBtn = clone.querySelector('.remove-row');
        removeBtn.disabled = false;

        body.appendChild(clone);
        rowIndex++;
        calculateBalance();
    });

    body.addEventListener('click', function (e) {
        if (e.target.closest('.remove-row')) {
            const rows = document.querySelectorAll('.entry-row');
            if (rows.length > 2) {
                e.target.closest('.entry-row').remove();
                calculateBalance();
            }
        }
    });

    body.addEventListener('input', function (e) {
        if (e.target.classList.contains('input-debit') || e.target.classList.contains('input-credit')) {
            calculateBalance();
        }
    });

    calculateBalance();
});
</script>
@endsection
