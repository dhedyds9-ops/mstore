@extends('layouts.app')

@section('title', __('Beban & Pengeluaran Toko ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-arrow-trend-down me-1"></i>{{ __('Biaya & Operasional') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-calendar-days me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Beban & Pengeluaran Kasir ATK') }}</h1>
            <p class="text-muted small mb-0">{{ __('Pencatatan biaya operasional toko ATK seperti pembelian perlengkapan, konsumsi kasir, dan tagihan listrik.') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('atk.dashboard') }}" class="btn btn-sm btn-outline-secondary shadow-2xs">
                <i class="fa-solid fa-gauge me-1"></i>{{ __('Dasbor') }}
            </a>
            <a href="{{ route('atk.expenses.create') }}" class="btn btn-sm btn-primary px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i>{{ __('Catat Pengeluaran Baru') }}
            </a>
        </div>
    </div>

    {{-- Ringkasan Metrik Beban --}}
    @php
        $totalExpenseAmount = \App\Models\Transaction::where('type', 'expense')
            ->where('reference_number', 'like', 'ATK-EXP-%')
            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
        $totalExpenseCount = \App\Models\Transaction::where('type', 'expense')
            ->where('reference_number', 'like', 'ATK-EXP-%')
            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-danger-subtle text-danger fs-4">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Beban Bulan Ini') }} ({{ now()->translatedFormat('F Y') }})</div>
                        <div class="fs-5 fw-bold text-danger">Rp {{ number_format($totalExpenseAmount, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-secondary-subtle text-secondary fs-4">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Catatan Beban') }}</div>
                        <div class="fs-5 fw-bold text-gray-800">{{ $totalExpenseCount }} {{ __('Transaksi') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-4">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Sumber Pembayaran') }}</div>
                        <div class="fs-6 fw-bold text-gray-900">{{ __('Dipotong dari Kas Utama Laci') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Pengeluaran --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-danger"></i>
                <span>{{ __('Daftar Seluruh Pengeluaran ATK') }}</span>
            </h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">
                {{ $expenses->total() }} {{ __('Data') }}
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="min-width: 130px;" class="py-3">{{ __('Tanggal Transaksi') }}</th>
                        <th style="min-width: 250px;" class="py-3">{{ __('Keterangan / Keperluan') }}</th>
                        <th style="min-width: 140px;" class="py-3 text-end">{{ __('Nominal Beban') }}</th>
                        <th style="min-width: 150px;" class="py-3">{{ __('Nomor Referensi') }}</th>
                        <th style="min-width: 100px;" class="py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($expenses as $e)
                    <tr>
                        <td>
                            <div class="fw-semibold text-gray-800">{{ $e->transaction_date->translatedFormat('d M Y') }}</div>
                            <small class="text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ $e->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-gray-900">{{ $e->description }}</div>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-danger fs-6">Rp {{ number_format($e->amount, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark font-monospace border px-2 py-1">
                                {{ $e->reference_number }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('atk.expenses.edit', $e->id) }}" class="btn btn-outline-secondary" title="{{ __('Ubah') }}">
                                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                                </a>
                                <form action="{{ route('atk.expenses.destroy', $e->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pengeluaran ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary text-danger" title="{{ __('Hapus') }}">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-file-invoice-dollar fs-1 text-secondary-subtle mb-3 d-block"></i>
                                <h6 class="fw-bold mb-1">{{ __('Belum Ada Catatan Pengeluaran') }}</h6>
                                <p class="small mb-3">{{ __('Catat pengeluaran operasional toko ATK jika ada beban kas yang dikeluarkan.') }}</p>
                                <a href="{{ route('atk.expenses.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-plus me-1"></i>{{ __('Catat Pengeluaran Baru') }}
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
                {{ __('Menampilkan') }} <span class="fw-semibold text-dark">{{ $expenses->firstItem() ?? 0 }}</span> - <span class="fw-semibold text-dark">{{ $expenses->lastItem() ?? 0 }}</span> {{ __('dari') }} <span class="fw-semibold text-dark">{{ $expenses->total() }}</span> {{ __('catatan beban') }}
            </div>
            <div>
                {{ $expenses->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
