@extends('layouts.app')

@section('title', __('Laporan Pendapatan Material'))

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-boxes-packing text-primary me-2"></i>Laporan Pendapatan Material</h4>
            <p class="text-secondary small mb-0">Laporan pergerakan penjualan barang/material & perhitungan komisi pengurus.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Kas') }}
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm shadow-sm">
                <i class="fa-solid fa-print me-1"></i> {{ __('Cetak') }}
            </button>
        </div>
    </div>

    <!-- Bagian Filter -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('finance.material_report') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small text-secondary fw-semibold mb-1">Tanggal Awal</label>
                    <input type="date" class="form-control form-control-sm" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small text-secondary fw-semibold mb-1">Tanggal Akhir</label>
                    <input type="date" class="form-control form-control-sm" name="end_date" value="{{ request('end_date') }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small text-secondary fw-semibold mb-1">Pengurus / Koordinator</label>
                    <select class="form-select form-select-sm" name="coordinator_id">
                        <option value="">{{ __('Semua Pengurus (Konsolidasi)') }}</option>
                        @foreach($coordinators as $coord)
                            <option value="{{ $coord->id }}" {{ request('coordinator_id') == $coord->id ? 'selected' : '' }}>
                                {{ $coord->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">
                        <i class="fa-solid fa-filter me-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ringkasan KPI -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-info rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">Total Item Terjual</div>
                    <div class="fs-4 fw-bold text-dark font-monospace">{{ number_format($totalQuantity, 0, ',', '.') }} Unit</div>
                    <small class="text-secondary"><i class="fas fa-check-circle text-info me-1"></i>Transaksi Sukses</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-success rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">Pendapatan Kotor (Gross)</div>
                    <div class="fs-4 fw-bold text-success font-monospace">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
                    <small class="text-secondary">Sebelum komisi pengurus</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-primary rounded-4 h-100">
                <div class="card-body p-3">
                    <div class="small fw-semibold text-secondary text-uppercase mb-1">Pendapatan Bersih (Net)</div>
                    <div class="fs-4 fw-bold text-primary font-monospace">Rp {{ number_format($netTotal, 0, ',', '.') }}</div>
                    <small class="text-danger">- Komisi Pengurus: Rp {{ number_format($commissionAmount, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list text-primary me-2"></i>{{ __('Rincian Transaksi Penjualan Material') }}</h6>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">Periode: {{ request('start_date') ?? 'Awal' }} s/d {{ request('end_date') ?? 'Akhir' }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 small text-uppercase text-body-secondary fw-semibold" style="width: 120px;">{{ __('Tanggal') }}</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold">{{ __('Item / Barang') }}</th>
                        <th class="small text-uppercase text-body-secondary fw-semibold" style="width: 160px;">{{ __('Pengurus') }}</th>
                        <th class="text-center small text-uppercase text-body-secondary fw-semibold" style="width: 100px;">{{ __('Qty') }}</th>
                        <th class="text-end small text-uppercase text-body-secondary fw-semibold" style="width: 160px;">{{ __('Total Harga') }}</th>
                        <th class="text-end pe-4 small text-uppercase text-body-secondary fw-semibold" style="width: 160px;">{{ __('Net Setelah Komisi') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    @php
                        $price = $t->item->price ?? 0;
                        $gross = $t->quantity * $price;
                        $commission = $gross * ($commissionRate / 100);
                        $net = $gross - $commission;
                    @endphp
                    <tr>
                        <td class="ps-4 text-secondary small">{{ $t->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $t->item->name ?? '-' }}</div>
                            <small class="text-secondary">{{ $t->item->brand ?? '' }} {{ $t->item->model ?? '' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1">
                                {{ $t->coordinator->name ?? '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1">
                                {{ number_format($t->quantity, 0, ',', '.') }} {{ $t->item->unit ?? '' }}
                            </span>
                        </td>
                        <td class="text-end font-monospace fw-semibold text-success">Rp {{ number_format($gross, 0, ',', '.') }}</td>
                        <td class="text-end pe-4 font-monospace fw-bold text-primary">
                            Rp {{ number_format($net, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-box-open fa-2x mb-3 text-secondary opacity-50 d-block"></i>
                            {{ __('Tidak ada transaksi material pada periode ini.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($transactions) > 0)
                <tfoot class="table-light fw-bold border-top-2">
                    <tr>
                        <td colspan="3" class="ps-4 text-end text-uppercase small">{{ __('TOTAL KESELURUHAN:') }}</td>
                        <td class="text-center font-monospace">{{ number_format($totalQuantity, 0, ',', '.') }}</td>
                        <td class="text-end font-monospace text-success">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                        <td class="text-end pe-4 font-monospace text-primary">Rp {{ number_format($netTotal, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="card-footer bg-light py-3 small text-muted border-0">
            <i class="fa-solid fa-circle-info me-1 text-primary"></i>
            <strong>{{ __('Catatan:') }}</strong> {{ __('Pendapatan Bersih (Net) adalah total harga penjualan dikurangi komisi pengurus sebesar') }} <strong>{{ $commissionRate }}%</strong>.
        </div>
    </div>
</div>
@endsection
