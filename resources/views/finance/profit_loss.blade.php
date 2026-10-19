@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h4 mb-1 fw-bold text-body"><i class="fa-solid fa-chart-line text-primary me-2"></i>{{ __('Laporan Laba Rugi') }}</h1>
            <p class="text-body-secondary small mb-0">{{ __('Ringkasan performa pendapatan, beban pokok, dan laba bersih.') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-md-end">
            <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali') }}
            </a>
            <a href="{{ route('finance.profit_loss.excel', ['month' => request('month'), 'coordinator_id' => request('coordinator_id')]) }}" class="btn btn-outline-success btn-sm shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> {{ __('Excel') }}
            </a>
            <a href="{{ route('finance.profit_loss.pdf', ['month' => request('month'), 'coordinator_id' => request('coordinator_id')]) }}" class="btn btn-outline-danger btn-sm shadow-sm" target="_blank">
                <i class="fa-solid fa-file-pdf me-1"></i> {{ __('PDF') }}
            </a>
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm shadow-sm">
                <i class="fa-solid fa-print me-1"></i> {{ __('Cetak') }}
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-body-tertiary border-0 py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <h6 class="m-0 fw-bold text-primary">{{ __('Periode Laporan') }}</h6>
            <form action="{{ route('finance.profit_loss') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2 w-100 w-md-auto">
                <select name="coordinator_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Semua Pengurus') }}</option>
                    @foreach($coordinators as $coordinator)
                        <option value="{{ $coordinator->id }}" {{ $selectedCoordinatorId == $coordinator->id ? 'selected' : '' }}>
                            {{ $coordinator->name }}
                        </option>
                    @endforeach
                </select>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}" onchange="this.form.submit()">
            </form>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 border-start border-4 border-success shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="small fw-bold text-success text-uppercase mb-1">{{ __('Total Pendapatan') }}</div>
                            <div class="h5 mb-0 fw-bold finance-kpi-value text-body">{{ number_format($totalRevenue, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 border-start border-4 border-warning shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="small fw-bold text-warning text-uppercase mb-1">{{ __('Total Beban Pokok') }}</div>
                            <div class="h5 mb-0 fw-bold finance-kpi-value text-danger">-{{ number_format($totalCOGS, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 border-start border-4 border-info shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="small fw-bold text-info text-uppercase mb-1">{{ __('Laba Kotor') }}</div>
                            <div class="h5 mb-0 fw-bold finance-kpi-value text-body">{{ number_format($grossProfit, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 border-start border-4 border-primary shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <div class="small fw-bold text-primary text-uppercase mb-1">{{ __('Laba Bersih') }}</div>
                            <div class="h5 mb-0 fw-bold finance-kpi-value text-body">{{ number_format($netProfit, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
    <table class="table table-hover table-bordered align-middle table-responsive-mobile">
        <thead class="table-light">
            <tr>
                <th class="text-uppercase">{{ __('Uraian') }}</th>
                <th class="text-end text-success">{{ __('Jumlah Pemasukan') }}</th>
                <th class="text-end text-danger">{{ __('Jumlah Pengeluaran') }}</th>
            </tr>
        </thead>
        <tbody>

            {{-- ================= PENDAPATAN ================= --}}
            <tr class="table-light fw-bold">
                <td colspan="3" class="text-uppercase text-primary">{{ __('Pendapatan') }}</td>
            </tr>
            <tr>
                <td>{{ __('Pendapatan Member') }}</td>
                <td class="text-end text-muted">{{ number_format($memberIncome, 0, ',', '.') }}</td>
                <td class="text-end text-muted">-</td>
            </tr>
            <tr>
                <td>{{ __('Pendapatan Voucher') }}</td>
                <td class="text-end text-muted">{{ number_format($voucherIncome, 0, ',', '.') }}</td>
                <td class="text-end text-muted">-</td>
            </tr>
            <tr>
                <td>{{ __('Pendapatan Lainnya') }}</td>
                <td class="text-end text-muted">{{ number_format($otherIncome, 0, ',', '.') }}</td>
                <td class="text-end text-muted">-</td>
            </tr>
            <tr class="fw-bold table-success">
                <td>{{ __('Total Pendapatan') }}</td>
                <td class="text-end text-muted">{{ number_format($totalRevenue, 0, ',', '.') }}</td>
                <td class="text-end text-muted">-</td>
            </tr>

            <tr><td colspan="3"></td></tr>

            {{-- ================= BEBAN POKOK ================= --}}
            <tr class="table-light fw-bold">
                <td colspan="3" class="text-uppercase text-warning">{{ __('Beban Pokok Pendapatan') }}</td>
            </tr>
            <tr>
                <td>{{ __('Komisi Pengurus') }} (±{{ number_format($coordRate, 1) }}%)</td>
                <td class="text-end text-muted">-</td>
                <td class="text-end text-muted">{{ number_format($coordCommission, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>{{ __('Pembayaran ISP') }} (±{{ number_format($ispRate, 1) }}%)</td>
                <td class="text-end text-muted">-</td>
                <td class="text-end text-muted">{{ number_format($ispPayment, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>{{ __('Dana Alat') }} (±{{ number_format($toolRate, 1) }}%)</td>
                <td class="text-end text-muted">-</td>
                <td class="text-end text-muted">{{ number_format($toolFund, 0, ',', '.') }}</td>
            </tr>
            <tr class="fw-bold table-warning">
                <td>{{ __('Total Beban Pokok Pendapatan') }}</td>
                <td class="text-end text-muted">-</td>
                <td class="text-end text-muted">{{ number_format($totalCOGS, 0, ',', '.') }}</td>
            </tr>

            <tr><td colspan="3"></td></tr>

            {{-- ================= LABA KOTOR ================= --}}
            <tr class="table-secondary text-white fw-bold">
                <td>{{ __('Laba Kotor') }}</td>
                <td class="text-end text-muted">{{ number_format($grossProfit, 0, ',', '.') }}</td>
                <td class="text-end text-muted">-</td>
            </tr>

            <tr><td colspan="3"></td></tr>

            {{-- ================= BIAYA OPERASIONAL ================= --}}
            <tr class="table-light fw-bold">
                <td colspan="3" class="text-uppercase text-danger">{{ __('Biaya Operasional') }}</td>
            </tr>
            <tr>
                <td>{{ __('Biaya Server') }}</td>
                <td class="text-end text-muted">-</td>
                <td class="text-end text-muted">{{ number_format($serverExpenses, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>{{ __('Transportasi') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($transportExpenses, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>{{ __('Konsumsi') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($consumptionExpenses, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>{{ __('Perbaikan') }}</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($repairExpenses, 0, ',', '.') }}</td>
            </tr>
            @foreach($otherExpensesBreakdown as $category => $amount)
            <tr>
                <td>{{ $category }}</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @if($otherOperatingExpenses == 0 && count($otherExpensesBreakdown) == 0)
            <!-- No other expenses -->
            @endif

            <tr><td colspan="3"></td></tr>

            {{-- ================= LABA BERSIH ================= --}}
            <tr class="table-primary text-white fw-bold">
                <td>{{ __('Laba Bersih') }}</td>
                <td class="text-end">{{ number_format($netProfit, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
            </tr>
            <tr>
                <td>{{ __('Cadangan Kas Investor') }} ({{ $investorCashPercent }}%)</td>
                <td class="text-end">-</td>
                <td class="text-end">{{ number_format($investorCashReserve, 0, ',', '.') }}</td>
            </tr>
            <tr class="fw-bold table-primary">
                <td>{{ __('Bagi Hasil Investor Setelah Kas') }}</td>
                <td class="text-end">{{ number_format($investorShareAfterCash, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
            </tr>

        </tbody>
    </table>
</div>

        </div>
    </div>
</div>
@endsection
