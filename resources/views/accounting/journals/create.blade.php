@extends('layouts.app')
@section('title', 'Buat Jurnal Umum Manual')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 fw-bold text-gray-800">Buat Jurnal Umum Manual</h1>
            <p class="text-muted small mb-0">Input penyesuaian, penyusutan aset, setoran modal, atau beban operasional berpasangan.</p>
        </div>
        <a href="{{ route('accounting.journals.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Jurnal
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="post" action="{{ route('accounting.journals.store') }}" id="journalForm">
        @csrf
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-file-invoice me-2"></i>Informasi Header Jurnal</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Transaksi <span class="text-danger">*</span></label>
                        <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nomor Jurnal <span class="text-danger">*</span></label>
                        <input type="text" name="journal_no" value="{{ old('journal_no', $defaultNo) }}" class="form-control font-monospace" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Periode Akuntansi</label>
                        <input type="text" class="form-control bg-light" value="{{ $openPeriod ? $openPeriod->name . ' (OPEN)' : 'Belum Ada Periode Terbuka' }}" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Keterangan / Deskripsi Transaksi <span class="text-danger">*</span></label>
                        <input type="text" name="description" value="{{ old('description') }}" class="form-control" placeholder="Contoh: Beban listrik dan air cabang utama bulan berjalan" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-list-ol me-2"></i>Rincian Akun (Debit & Kredit)</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
                    <i class="fas fa-plus me-1"></i> Tambah Baris
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 align-middle" id="entriesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 38%;">Akun (COA) <span class="text-danger">*</span></th>
                                <th style="width: 24%;">Debit (Rp)</th>
                                <th style="width: 24%;">Kredit (Rp)</th>
                                <th style="width: 14%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="entriesBody">
                            <tr class="entry-row">
                                <td>
                                    <select name="entries[0][account_id]" class="form-select select-account" required>
                                        <option value="">Pilih Akun</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->type) }})</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="entries[0][memo]" class="form-control form-control-sm mt-1" placeholder="Catatan memo (opsional)">
                                </td>
                                <td>
                                    <input type="number" name="entries[0][debit]" class="form-control font-monospace input-debit" min="0" step="any" value="0">
                                </td>
                                <td>
                                    <input type="number" name="entries[0][credit]" class="form-control font-monospace input-credit" min="0" step="any" value="0">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="entry-row">
                                <td>
                                    <select name="entries[1][account_id]" class="form-select select-account" required>
                                        <option value="">Pilih Akun</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ strtoupper($acc->type) }})</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="entries[1][memo]" class="form-control form-control-sm mt-1" placeholder="Catatan memo (opsional)">
                                </td>
                                <td>
                                    <input type="number" name="entries[1][debit]" class="form-control font-monospace input-debit" min="0" step="any" value="0">
                                </td>
                                <td>
                                    <input type="number" name="entries[1][credit]" class="form-control font-monospace input-credit" min="0" step="any" value="0">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td class="text-end">Total:</td>
                                <td class="text-end font-monospace" id="displayTotalDebit">Rp 0</td>
                                <td class="text-end font-monospace" id="displayTotalCredit">Rp 0</td>
                                <td class="text-center">
                                    <span id="balanceBadge" class="badge bg-danger">Selisih: Rp 0</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted" id="balanceHint">
                    <i class="fas fa-info-circle me-1"></i> Total Debit harus sama dengan Total Kredit (Balance) sebelum dapat disimpan.
                </span>
                <button type="submit" class="btn btn-primary btn-lg px-4" id="submitBtn" disabled>
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
