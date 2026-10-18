@extends('layouts.app')

@section('title', __('Laporan Operasional Pengurus'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-file-invoice text-primary me-2"></i>Laporan Operasional & Setoran Pengurus</h4>
            <p class="text-secondary small mb-0">Rincian pendapatan kotor, beban operasional lapangan, dan kewajiban setoran kas pusat.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Kas
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm shadow-sm">
                <i class="fas fa-print me-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Kartu Summary KPI Utama -->
    <div class="row g-3 mb-4">
        <!-- 1. Pendapatan Kotor -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">{{ __('Pendapatan Kotor') }}</div>
                    <div class="fs-4 fw-bold text-success font-monospace">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</div>
                    <small class="text-secondary">{{ __('Total uang masuk member') }}</small>
                </div>
            </div>
        </div>

        <!-- 2. Total Beban Pengurus -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-4 border-danger rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">{{ __('Total Beban Pengurus') }}</div>
                    <div class="fs-4 fw-bold text-danger font-monospace">- Rp {{ number_format($totalExpenses + $commission, 0, ',', '.') }}</div>
                    <small class="text-secondary">{{ __('Operasional + Komisi 15%') }}</small>
                </div>
            </div>
        </div>

        <!-- 3. Dana Wajib Setor (Net) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">{{ __('Sisa Wajib Setor') }}</div>
                    <div class="fs-4 fw-bold text-primary font-monospace">Rp {{ number_format($netBalance, 0, ',', '.') }}</div>
                    <small class="text-secondary">{{ __('Pendapatan - Beban') }}</small>
                </div>
            </div>
        </div>

        <!-- 4. Dana Pengambilan Barang (Stok) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">{{ __('Pengambilan Barang') }}</div>
                    <div class="fs-4 fw-bold text-warning-emphasis font-monospace">- Rp {{ number_format($inventoryUsageValue, 0, ',', '.') }}</div>
                    <small class="text-secondary">{{ __('Nilai barang keluar inventaris') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL REKONSILIASI -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list-check text-primary me-2"></i>Rincian Rekonsiliasi Alur Dana Pengurus</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 small text-uppercase text-body-secondary fw-semibold" style="width: 65%;">Uraian Transaksi</th>
                        <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- A. PENDAPATAN -->
                    <tr class="bg-success bg-opacity-10">
                        <td class="ps-4 fw-bold text-success">A. PENDAPATAN MEMBER (LANGGANAN)</td>
                        <td class="text-end pe-4 font-monospace fw-bold text-success fs-6">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</td>
                    </tr>

                    <!-- B. PENGELUARAN OPERASIONAL -->
                    <tr class="table-light">
                        <td colspan="2" class="ps-4 small fw-bold text-secondary text-uppercase">B. BIAYA OPERASIONAL REALISASI</td>
                    </tr>
                    @foreach($operationalExpenses as $expense)
                    <tr>
                        <td class="ps-5 text-dark">
                            <i class="fa-solid fa-circle text-danger me-2" style="font-size: 6px;"></i>
                            {{ $expense->category }} - {{ $expense->description }}
                        </td>
                        <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach

                    <!-- C. POTONGAN KOMISI -->
                    <tr>
                        <td class="ps-4 fw-semibold text-primary">
                            <i class="fa-solid fa-scissors me-2"></i>C. POTONGAN KOMISI PENGURUS (15%)
                        </td>
                        <td class="text-end pe-4 font-monospace text-danger fw-semibold">- Rp {{ number_format($commission, 0, ',', '.') }}</td>
                    </tr>

                    <!-- D. SISA HASIL USAHA -->
                    <tr class="table-light fw-bold border-top-2">
                        <td class="ps-4">D. SISA HASIL USAHA (NET INCOME OPERASIONAL)</td>
                        <td class="text-end pe-4 font-monospace text-primary">Rp {{ number_format($netIncome, 0, ',', '.') }}</td>
                    </tr>

                    <!-- E. PENGAMBILAN BARANG -->
                    <tr class="table-light">
                        <td colspan="2" class="ps-4 small fw-bold text-secondary text-uppercase">
                            <i class="fa-solid fa-box-open me-2 text-warning"></i>E. PENGAMBILAN BARANG / MATERIAL INVENTARIS
                        </td>
                    </tr>
                    @foreach($inventoryItems as $item)
                    <tr>
                        <td class="ps-5 text-dark">
                            <i class="fa-solid fa-box text-warning me-2" style="font-size: 8px;"></i>
                            {{ $item->item_name }} ({{ $item->quantity }} unit)
                        </td>
                        <td class="text-end pe-4 font-monospace text-warning-emphasis">- Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach

                    <!-- F. TOTAL WAJIB SETOR -->
                    <tr class="bg-primary bg-opacity-10 fw-bold border-top-2">
                        <td class="ps-4 py-3 text-primary fs-6">
                            <i class="fas fa-hand-holding-dollar me-2"></i>TOTAL WAJIB SETOR KE KAS PUSAT
                        </td>
                        <td class="text-end pe-4 py-3 font-monospace text-primary fs-5">
                            Rp {{ number_format($netBalance, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
