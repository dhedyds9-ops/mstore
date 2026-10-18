@extends('layouts.app')

@section('title', __('Laporan Bagi Hasil Investor'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header Halaman -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>{{ __('Laporan Bagi Hasil Investor') }}</h4>
            <p class="text-secondary small mb-0">{{ __('Perhitungan pembagian dividen keuntungan dan alokasi dana kas investor.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.investor_report.pdf', ['month' => $selectedMonth, 'coordinator_id' => $coordinatorId]) }}" class="btn btn-outline-danger btn-sm shadow-sm" target="_blank">
                <i class="fa-solid fa-file-pdf me-1"></i> {{ __('Ekspor PDF') }}
            </a>
            <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Kas') }}
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('finance.investor_report') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-secondary fw-semibold mb-1">Pilih Pengurus / Koordinator</label>
                    <select name="coordinator_id" class="form-select form-select-sm">
                        <option value="">{{ __('Semua Pengurus (Konsolidasi)') }}</option>
                        @foreach($coordinators as $coordinator)
                            <option value="{{ $coordinator->id }}" {{ $coordinatorId == $coordinator->id ? 'selected' : '' }}>
                                {{ $coordinator->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-secondary fw-semibold mb-1">Periode Bulan</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">
                        <i class="fa-solid fa-filter me-1"></i> {{ __('Terapkan') }}
                    </button>
                </div>
                <div class="col-12 col-md-3 text-md-end">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2">
                        <i class="fa-regular fa-calendar me-1"></i> {{ \Carbon\Carbon::parse($selectedMonth.'-01')->translatedFormat('F Y') }}
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">Pendapatan Kotor</div>
                    <div class="fs-4 fw-bold text-success font-monospace">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</div>
                    <small class="text-secondary">Member & Voucher Hotspot</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-4 border-danger rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">Potongan & Beban</div>
                    <div class="fs-4 fw-bold text-danger font-monospace">- Rp {{ number_format($commission + $ispShare + $toolFund + $operationalExpenses, 0, ',', '.') }}</div>
                    <small class="text-secondary">Komisi, ISP, Alat, & Operasional</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">Kas Wajib Investor (5%)</div>
                    <div class="fs-4 fw-bold text-warning-emphasis font-monospace">Rp {{ number_format($investorCashFund, 0, ',', '.') }}</div>
                    <small class="text-secondary">Cadangan kas kasbon / darurat</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">Laba Bersih Investor</div>
                    <div class="fs-4 fw-bold text-primary font-monospace">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                    <small class="text-primary fw-medium">Siap dibagikan ke {{ $investorCount }} investor</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 1: Perhitungan Rekonsiliasi Bagi Hasil -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-calculator text-primary me-2"></i>{{ __('Tahapan Perhitungan Pembagian Hasil (Cascade Calculation)') }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 small text-uppercase text-body-secondary fw-semibold">{{ __('Komponen / Keterangan') }}</th>
                        <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">{{ __('Nilai Nominal (Rp)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-success bg-opacity-10">
                        <td class="ps-4 fw-bold text-success">
                            <i class="fas fa-coins me-2"></i>{{ __('1. Total Pendapatan Kotor') }}
                            <small class="text-secondary fw-normal d-block">(Akumulasi Pembayaran Member Langganan + Penjualan Voucher)</small>
                        </td>
                        <td class="text-end pe-4 font-monospace fw-bold text-success fs-6">Rp {{ number_format($grossRevenue, 0, ',', '.') }}</td>
                    </tr>
                    
                    <!-- Potongan Sistem -->
                    <tr class="table-light">
                        <td colspan="2" class="ps-4 small fw-bold text-secondary text-uppercase">
                            <i class="fas fa-percent me-1 text-danger"></i>2. Alokasi Sistem & Potongan Tetap
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 me-2">15%</span>
                            {{ __('Komisi Pengurus Lapangan') }}
                        </td>
                        <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($commission, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 me-2">25%</span>
                            {{ __('Pembagian / Biaya Bandwidth ISP') }}
                        </td>
                        <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($ispShare, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 me-2">20%</span>
                            {{ __('Alokasi Dana Alat / Manajemen Perusahaan') }}
                        </td>
                        <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($toolFund, 0, ',', '.') }}</td>
                    </tr>
                    
                    <!-- Pengeluaran Operasional -->
                    <tr class="table-light">
                        <td colspan="2" class="ps-4 small fw-bold text-secondary text-uppercase">
                            <i class="fas fa-screwdriver-wrench me-1 text-warning"></i>3. Beban Operasional Lapangan & Material
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">
                            {{ __('Total Pengeluaran Operasional') }}
                            <small class="text-secondary d-block">(Server, Pengambilan Barang / Material Kantor, Biaya Lapangan)</small>
                        </td>
                        <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($operationalExpenses, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Kas Wajib Investor -->
                    <tr class="table-light">
                        <td colspan="2" class="ps-4 small fw-bold text-secondary text-uppercase">
                            <i class="fas fa-vault me-1 text-info"></i>4. Cadangan Dana Kas Investor
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-5 text-dark">
                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis rounded-pill px-2 py-1 me-2">5%</span>
                            {{ __('Kas Wajib Investor (Cadangan kas saldo berjalan)') }}
                        </td>
                        <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($investorCashFund, 0, ',', '.') }}</td>
                    </tr>

                    <!-- Hasil Akhir Laba Bersih -->
                    <tr class="bg-primary bg-opacity-10 border-top-2 fw-bold">
                        <td class="ps-4 py-3 text-primary fs-6">
                            <i class="fa-solid fa-hand-holding-dollar me-2"></i>{{ __('TOTAL LABA BERSIH INVESTOR (NET PROFIT)') }}
                        </td>
                        <td class="text-end pe-4 py-3 font-monospace fs-5 text-primary">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
                    </tr>
                    
                    <tr class="table-light border-top">
                        <td class="ps-4 py-3 fw-semibold text-dark">
                            <i class="fas fa-users me-2 text-secondary"></i>{{ __('Jumlah Investor Terdaftar') }}
                        </td>
                        <td class="text-end pe-4 font-monospace fw-bold text-dark fs-6">{{ $investorCount }} Orang</td>
                    </tr>
                    <tr class="bg-success bg-opacity-10 fw-bold border-top-2">
                        <td class="ps-4 py-3 text-success fs-6">
                            <i class="fas fa-gift me-2"></i>{{ __('DIVIDEN LABA BERSIH PER INVESTOR') }}
                        </td>
                        <td class="text-end pe-4 py-3 font-monospace fs-5 text-success">Rp {{ number_format($profitPerInvestor, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Card 2: Rincian Pembagian Per Investor -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-users text-primary me-2"></i>{{ __('Rincian Pembagian Dividen Per Investor') }}</h6>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">Total: {{ $investors->count() }} Investor</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 small text-uppercase text-body-secondary fw-semibold" style="width: 60px;">#</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold">{{ __('Nama Investor') }}</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold">{{ __('Unit / Koordinator') }}</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold text-center" style="width: 140px;">{{ __('Peran') }}</th>
                        <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold" style="width: 220px;">{{ __('Dividen Laba (Rp)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investors as $index => $investor)
                        <tr>
                            <td class="ps-4 text-secondary">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($investor->name, 0, 1)) }}
                                    </div>
                                    <div class="fw-semibold text-dark">{{ $investor->name }}</div>
                                </div>
                            </td>
                            <td>
                                @if($investor->coordinator)
                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                        <i class="fas fa-user-tie me-1 text-primary"></i>{{ $investor->coordinator->name }}
                                    </span>
                                @else
                                    <span class="text-secondary small fst-italic">Global / Kantor Pusat</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-semibold">
                                    <i class="fas fa-check-circle me-1 small"></i>{{ __('Investor Aktif') }}
                                </span>
                            </td>
                            <td class="text-end pe-4 font-monospace fw-bold text-success fs-6">
                                Rp {{ number_format($profitPerInvestor, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-user-slash fa-2x mb-3 text-secondary opacity-50 d-block"></i>
                                {{ __('Tidak ada data investor untuk filter pengurus ini.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($investors->count() > 0)
                <tfoot class="table-light fw-bold border-top-2">
                    <tr>
                        <td colspan="4" class="ps-4 text-end text-uppercase small">{{ __('Total Dana Dividen Dibagikan:') }}</td>
                        <td class="text-end pe-4 font-monospace text-primary fs-6">
                            Rp {{ number_format($profitPerInvestor * $investors->count(), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Catatan Penjelasan Perhitungan -->
    <div class="alert alert-light border-0 shadow-sm rounded-4 p-3 d-flex align-items-start gap-3">
        <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary mt-1">
            <i class="fas fa-circle-info fa-lg"></i>
        </div>
        <div class="small text-secondary">
            <strong class="text-dark d-block mb-1">Metodologi Perhitungan Laba Bersih Investor (Cascade):</strong>
            Pendapatan Kotor ➔ Dikurangi Komisi Pengurus (15%) ➔ Sisa dikurangi Pembagian ISP (25%) ➔ Sisa dikurangi Alokasi Dana Alat (20%) ➔ Dikurangi Realisasi Biaya Operasional & Pengambilan Barang Lapangan ➔ Dikurangi 5% Kas Wajib Investor ➔ Hasil bersih (Net Profit) dibagi rata secara proporsional kepada seluruh investor aktif.
        </div>
    </div>
</div>
@endsection
