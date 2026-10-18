@extends('layouts.app')

@section('title', __('Riwayat Transaksi Toko & Retail'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-receipt me-1"></i>{{ __('Kasir & Penjualan') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-calendar-days me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Riwayat Transaksi & Struk') }}</h1>
            <p class="text-muted small mb-0">{{ __('Daftar seluruh transaksi penjualan barang, jasa fotocopy, dan layanan digital.') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(Auth::user()->hasPermission('atk.manage') && Route::has('atk.transactions.bulkDestroy'))
            <button type="button" class="btn btn-sm btn-outline-danger d-none shadow-2xs" id="bulkDeleteBtn" onclick="confirmBulkDelete()">
                <i class="fa-solid fa-trash-can me-1"></i>
                <span>{{ __('Hapus Terpilih') }}</span> (<span id="selectedCountDesktop">0</span>)
            </button>
            <form id="bulkDeleteForm" action="{{ route('atk.transactions.bulkDestroy') }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
            @endif

            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle shadow-2xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-export me-1 text-secondary"></i>
                    <span>{{ __('Ekspor Data') }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('atk.transactions.export.pdf', request()->all()) }}">
                            <i class="fa-solid fa-file-pdf text-danger me-2"></i>{{ __('Ekspor Dokumen PDF') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('atk.transactions.export.excel', request()->all()) }}">
                            <i class="fa-solid fa-file-excel text-success me-2"></i>{{ __('Ekspor Spreadsheet Excel') }}
                        </a>
                    </li>
                </ul>
            </div>

            <a href="{{ route('atk.pos') }}" class="btn btn-sm btn-primary px-3 shadow-sm">
                <i class="fa-solid fa-cash-register me-1"></i> {{ __('Buka Kasir (POS)') }}
            </a>
        </div>
    </div>

    {{-- Ringkasan Metrik Cepat --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success-subtle text-success fs-4">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Pendapatan Terfilter') }}</div>
                        <div class="fs-5 fw-bold text-success">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-4">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Transaksi') }}</div>
                        <div class="fs-5 fw-bold text-gray-800">{{ number_format($transactions->total(), 0, ',', '.') }} {{ __('Struk') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info-subtle text-info fs-4">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Rata-rata Nilai / Struk') }}</div>
                        <div class="fs-5 fw-bold text-gray-800">
                            Rp {{ number_format($transactions->total() > 0 ? ($totalRevenue / $transactions->total()) : 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card & Tabel Transaksi --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <form action="{{ route('atk.transactions.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="{{ __('No. Struk, Produk, Kasir...') }}" value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted small">{{ __('Dari') }}</span>
                        <input type="date" name="start_date" class="form-control bg-light" value="{{ request('start_date') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted small">{{ __('Sampai') }}</span>
                        <input type="date" name="end_date" class="form-control bg-light" value="{{ request('end_date') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    @php
                        $opts = isset($categories) ? $categories : ['ATK','JASA POTOCOPY','JASA TRANSFER BANK'];
                    @endphp
                    <select name="category" class="form-select form-select-sm bg-light">
                        <option value="">{{ __('Semua Kategori') }}</option>
                        @foreach($opts as $opt)
                            <option value="{{ $opt }}" {{ request('category')===$opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-sm btn-dark px-3 flex-fill flex-md-grow-0" title="{{ __('Terapkan Filter') }}">
                        <i class="fa-solid fa-filter me-1"></i>{{ __('Filter') }}
                    </button>
                    @if(request('search') || request('start_date') || request('end_date') || request('category'))
                        <a href="{{ route('atk.transactions.index') }}" class="btn btn-sm btn-outline-secondary px-2" title="{{ __('Reset Filter') }}">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        @if(Auth::user()->hasPermission('atk.manage') && Route::has('atk.transactions.bulkDestroy'))
                        <th style="width: 38px;" class="text-center py-3">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                        </th>
                        @endif
                        <th style="min-width: 140px;" class="py-3">{{ __('Waktu & Tanggal') }}</th>
                        <th style="min-width: 150px;" class="py-3">{{ __('No. Transaksi') }}</th>
                        <th style="min-width: 220px;" class="py-3">{{ __('Rincian Produk / Item') }}</th>
                        <th style="min-width: 120px;" class="py-3">{{ __('Kasir') }}</th>
                        <th style="min-width: 130px;" class="py-3 text-end">{{ __('Total Belanja') }}</th>
                        <th style="min-width: 110px;" class="py-3 text-center">{{ __('Metode') }}</th>
                        <th style="min-width: 120px;" class="py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($transactions as $transaction)
                    <tr>
                        @if(Auth::user()->hasPermission('atk.manage') && Route::has('atk.transactions.bulkDestroy'))
                        <td class="text-center">
                            <input class="form-check-input transaction-checkbox" type="checkbox" value="{{ $transaction->id }}">
                        </td>
                        @endif
                        <td>
                            <div class="fw-semibold text-gray-800 small">{{ $transaction->created_at->translatedFormat('d M Y') }}</div>
                            <small class="text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ $transaction->created_at->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            <a href="{{ route('atk.transactions.show', $transaction) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                {{ $transaction->transaction_number }}
                            </a>
                            @if($transaction->status === 'reversed')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill ms-1 small">
                                    {{ __('Dibatalkan') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-medium text-dark text-truncate" style="max-width: 260px;" title="{{ $transaction->product_names['display'] }}">
                                {{ $transaction->product_names['display'] }}
                            </div>
                            <small class="text-muted"><i class="fa-solid fa-layer-group me-1"></i>{{ $transaction->product_names['total'] }} item belanja</small>
                        </td>
                        <td>
                            <span class="small text-gray-700 fw-medium">
                                <i class="fa-regular fa-user me-1 text-muted"></i>{{ $transaction->user->name ?? '-' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="fw-bold text-gray-900 fs-6">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $method = strtoupper($transaction->payment_method ?? 'CASH');
                                $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                if(str_contains($method, 'TRANSFER') || str_contains($method, 'BANK')) {
                                    $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                } elseif(str_contains($method, 'QRIS')) {
                                    $badgeClass = 'bg-purple-subtle text-purple border border-purple-subtle';
                                } elseif(str_contains($method, 'DEBIT') || str_contains($method, 'CARD')) {
                                    $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                                }
                            @endphp
                            <span class="badge rounded-pill {{ $badgeClass }} px-2 py-1 small fw-medium">
                                {{ $method }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('atk.transactions.show', $transaction) }}" class="btn btn-outline-secondary" title="{{ __('Lihat Detail') }}">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                </a>
                                <a href="{{ route('atk.transactions.receipt', $transaction) }}" target="_blank" class="btn btn-outline-secondary" title="{{ __('Cetak Struk') }}">
                                    <i class="fa-solid fa-print text-secondary"></i>
                                </a>
                                @if(Auth::user()->hasPermission('atk.manage'))
                                <form action="{{ route('atk.transactions.destroy', $transaction) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus transaksi {{ $transaction->transaction_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary text-danger" title="{{ __('Batalkan Transaksi') }}">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-receipt fs-1 text-secondary-subtle mb-3 d-block"></i>
                                <h6 class="fw-bold mb-1">{{ __('Tidak Ada Transaksi Ditemukan') }}</h6>
                                <p class="small mb-3">{{ __('Coba sesuaikan rentang tanggal atau kata kunci pencarian Anda.') }}</p>
                                <a href="{{ route('atk.pos') }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-cash-register me-1"></i>{{ __('Mulai Transaksi Baru') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Card Footer & Paginasi --}}
        <div class="card-footer bg-white border-top py-3 px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <div class="text-muted small">
                {{ __('Menampilkan') }} <span class="fw-semibold text-dark">{{ $transactions->firstItem() ?? 0 }}</span> - <span class="fw-semibold text-dark">{{ $transactions->lastItem() ?? 0 }}</span> {{ __('dari') }} <span class="fw-semibold text-dark">{{ $transactions->total() }}</span> {{ __('transaksi') }}
            </div>
            <div>
                {{ $transactions->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->hasPermission('atk.manage'))
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.transaction-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const selectedCountDesktop = document.getElementById('selectedCountDesktop');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        const updateBulkDeleteBtn = () => {
            const selected = document.querySelectorAll('.transaction-checkbox:checked');
            if (selectedCountDesktop) {
                selectedCountDesktop.textContent = selected.length;
            }
            if (bulkDeleteBtn) {
                bulkDeleteBtn.classList.toggle('d-none', selected.length === 0);
            }
            if (selectAll && checkboxes.length > 0) {
                selectAll.checked = selected.length === checkboxes.length;
                selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
            }
        };

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBulkDeleteBtn();
            });
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateBulkDeleteBtn));

        window.confirmBulkDelete = function() {
            const selected = Array.from(document.querySelectorAll('.transaction-checkbox:checked')).map(cb => cb.value);
            if (selected.length === 0) return;

            if (confirm(`Apakah Anda yakin ingin menghapus/membatalkan ${selected.length} transaksi yang dipilih?`)) {
                if (bulkDeleteForm) {
                    bulkDeleteForm.querySelectorAll('input[name="ids[]"]').forEach(e => e.remove());
                    selected.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        bulkDeleteForm.appendChild(input);
                    });
                    bulkDeleteForm.submit();
                }
            }
        };
    });
</script>
@endpush
@endif
@endsection
