@extends('layouts.app')

@section('title', __('Laporan Keuangan Manajemen'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-chart-pie text-primary me-2"></i>Laporan Laba Rugi & Kas Manajemen</h4>
            <p class="text-secondary small mb-0">Rekonsiliasi pendapatan, pengeluaran operasional, dan saldo setoran kas.</p>
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Kas
            </a>
            <a href="{{ route('finance.manager_report.excel', ['month' => request('month'), 'coordinator_id' => request('coordinator_id')]) }}" class="btn btn-outline-success btn-sm shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Ekspor Excel
            </a>
            <a href="{{ route('finance.manager_report.pdf', ['month' => request('month'), 'coordinator_id' => request('coordinator_id')]) }}" class="btn btn-outline-danger btn-sm shadow-sm">
                <i class="fa-solid fa-file-pdf me-1"></i> Ekspor PDF
            </a>
        </div>
    </div>

    <!-- Bagian Filter -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('finance.manager_report') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-secondary fw-semibold mb-1">Pilih Pengurus / Koordinator</label>
                    <select name="coordinator_id" class="form-select form-select-sm">
                        <option value="">{{ __('Semua Pengurus (Konsolidasi)') }}</option>
                        @foreach($coordinators as $coordinator)
                            <option value="{{ $coordinator->id }}" {{ request('coordinator_id') == $coordinator->id ? 'selected' : '' }}>
                                {{ $coordinator->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-secondary fw-semibold mb-1">Periode Laporan</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') ?? date('Y-m') }}" required>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">
                        <i class="fa-solid fa-filter me-1"></i> Terapkan
                    </button>
                </div>
                <div class="col-12 col-md-3 text-md-end">
                    @if(request('month'))
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2">
                        <i class="fa-regular fa-calendar me-1"></i> {{ \Carbon\Carbon::parse(request('month').'-01')->translatedFormat('F Y') }}
                    </span>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Laporan Utama -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark"><i class="fa-solid fa-table-list text-primary me-2"></i>Rekonsiliasi Keuangan & Arus Kas</h6>
        </div>
        <div class="card-body p-4">
            
            <!-- TABEL 1: PENDAPATAN -->
            <h6 class="text-uppercase text-secondary small fw-bold mb-3"><i class="fas fa-arrow-down text-success me-1"></i>A. Pendapatan Usaha</h6>
            <div class="table-responsive mb-4">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 small text-uppercase text-body-secondary fw-semibold" style="width: 70%">Sumber Pendapatan</th>
                            <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4">{{ __('Pendapatan Member (Langganan)') }}</td>
                            <td class="text-end pe-4 font-monospace fw-bold text-dark">{{ number_format($memberIncome, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">{{ __('Pendapatan Voucher Hotspot') }}</td>
                            <td class="text-end pe-4 font-monospace fw-bold text-dark">{{ number_format($voucherIncome, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold border-top-2">
                        <tr class="bg-success bg-opacity-10">
                            <td class="ps-4 text-success">TOTAL PENDAPATAN KOTOR</td>
                            <td class="text-end pe-4 font-monospace text-success fs-6">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- TABEL 2: POTONGAN & PENGELUARAN -->
            <h6 class="text-uppercase text-secondary small fw-bold mb-3"><i class="fas fa-arrow-up text-danger me-1"></i>B. Potongan & Beban Operasional</h6>
            <div class="table-responsive mb-4">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 small text-uppercase text-body-secondary fw-semibold" style="width: 70%">Jenis Potongan & Beban</th>
                            <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Komisi Otomatis -->
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 me-1">SISTEM</span> {{ __('Komisi Pengurus') }}
                                <small class="text-muted">({{ $coordRate ?? 0 }}%)</small>
                            </td>
                            <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($coordCommission, 0, ',', '.') }}</td>
                        </tr>
                        
                        <!-- Rincian Pengeluaran Manual -->
                        <tr>
                            <td class="ps-4">{{ __('Transportasi & Bensin') }}</td>
                            <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($transportExpenses, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">{{ __('Konsumsi Lapangan') }}</td>
                            <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($consumptionExpenses, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">{{ __('Perbaikan & Pemeliharaan (Maintenance)') }}</td>
                            <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($repairExpenses, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">{{ __('Biaya Operasional Lainnya') }}</td>
                            <td class="text-end pe-4 font-monospace text-danger">- Rp {{ number_format($otherOperatingExpenses, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold border-top-2">
                        <tr class="bg-danger bg-opacity-10">
                            <td class="ps-4 text-danger">TOTAL PENGELUARAN & POTONGAN</td>
                            <td class="text-end pe-4 font-monospace text-danger fs-6">
                                - Rp {{ number_format($coordCommission + $operatingExpenses, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- TABEL 3: POSISI SALDO (NET INCOME & SETORAN) -->
            <h6 class="text-uppercase text-secondary small fw-bold mb-3"><i class="fas fa-wallet text-primary me-1"></i>C. Posisi Saldo Akhir & Wajib Setor</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 small text-uppercase text-body-secondary fw-semibold" style="width: 70%">Ringkasan Saldo</th>
                            <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 fw-semibold text-dark">{{ __('Laba Bersih Operasional') }}</td>
                            <td class="text-end pe-4 font-monospace fw-bold text-primary">
                                Rp {{ number_format($totalRevenue - ($coordCommission + $operatingExpenses), 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-4 text-muted">
                                <i class="fa-solid fa-arrow-down me-1 text-danger"></i> 
                                {{ __('Sudah Disetor ke Kas Pusat') }}
                            </td>
                            <td class="text-end pe-4 font-monospace text-muted">- Rp {{ number_format($deposited, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-top-2">
                        <tr class="bg-primary bg-opacity-10 fw-bold">
                            <td class="ps-4 py-3 text-primary fs-6">
                                <i class="fa-solid fa-wallet me-2"></i> {{ __('SISA SALDO / WAJIB SETOR') }}
                            </td>
                            <td class="text-end pe-4 py-3 font-monospace fs-5 text-primary">
                                Rp {{ number_format($netBalance, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
        <div class="card-footer bg-light py-3 small text-muted text-center border-0">
            {{ __('Laporan ini dihasilkan secara otomatis oleh sistem. Potongan komisi dihitung berdasarkan persentase dari pendapatan kotor.') }}
        </div>
    </div>

</div>
@endsection
