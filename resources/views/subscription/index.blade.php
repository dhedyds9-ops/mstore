@extends('layouts.app')

@section('title', __('Langganan & Paket Usaha'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-crown text-warning me-2"></i>{{ __('Langganan & Paket Usaha') }}
            </h4>
            <p class="text-body-secondary small mb-0">{{ __('Kelola paket aplikasi, masa aktif langganan, dan tingkatkan kuota bisnis UMKM Anda.') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($isExpired)
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fs-6">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Masa Aktif Habis
                </span>
            @elseif($isTrial)
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fs-6">
                    <i class="fa-solid fa-hourglass-half me-1"></i> Masa Trial: {{ $daysRemaining }} Hari Tersisa
                </span>
            @else
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fs-6">
                    <i class="fa-solid fa-circle-check me-1"></i> Berlangganan Aktif
                </span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Current Plan & Quota Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-primary">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase text-body-secondary small fw-bold">{{ __('Paket Saat Ini') }}</span>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid {{ $currentPlan['icon'] }}"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-primary mb-1">{{ $currentPlan['name'] }}</h4>
                    <div class="small text-body-secondary">{{ $company->name ?? 'Toko Anda' }}</div>
                    <div class="pt-2 mt-2 border-top small text-body-secondary d-flex justify-content-between">
                        <span>Masa Aktif:</span>
                        <strong class="text-body">
                            @if($isTrial)
                                Trial s/d {{ $company->trial_ends_at ? $company->trial_ends_at->format('d M Y') : '-' }}
                            @elseif($isActive)
                                s/d {{ $company->subscription_ends_at ? $company->subscription_ends_at->format('d M Y') : 'Selamanya' }}
                            @else
                                <span class="text-danger">Kedaluwarsa</span>
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-info">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase text-body-secondary small fw-bold">{{ __('Kuota Pengguna / Kasir') }}</span>
                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-1 text-body">{{ $totalUsers }} <span class="fs-6 text-body-secondary font-monospace">/ {{ $maxUsers }} Pengguna</span></h4>
                    <div class="progress mt-2" style="height: 6px;">
                        @php $userPercent = min(100, round(($totalUsers / max(1, $maxUsers)) * 100)); @endphp
                        <div class="progress-bar bg-info" style="width: {{ $userPercent }}%"></div>
                    </div>
                    <div class="pt-2 mt-2 border-top small text-body-secondary d-flex justify-content-between">
                        <span>Tersisa:</span>
                        <strong class="text-body">{{ max(0, $maxUsers - $totalUsers) }} Slot Staf</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-uppercase text-body-secondary small fw-bold">{{ __('Kuota Cabang / Outlet') }}</span>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="fa-solid fa-store"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-1 text-body">{{ $totalBranches }} <span class="fs-6 text-body-secondary font-monospace">/ {{ $maxBranches }} Lokasi</span></h4>
                    <div class="progress mt-2" style="height: 6px;">
                        @php $branchPercent = min(100, round(($totalBranches / max(1, $maxBranches)) * 100)); @endphp
                        <div class="progress-bar bg-success" style="width: {{ $branchPercent }}%"></div>
                    </div>
                    <div class="pt-2 mt-2 border-top small text-body-secondary d-flex justify-content-between">
                        <span>Status Outlet:</span>
                        <strong class="text-body">Tersinkronisasi Terpusat</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Matrix Section -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-0 pt-4 pb-2 text-center">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase px-3 py-2 rounded-pill fw-semibold">
                <i class="fa-solid fa-sparkles me-1"></i> Skema Langganan Terjangkau
            </span>
            <h4 class="fw-bold mt-2 mb-1 text-body">Pilih Paket Sesuai Kebutuhan Usaha Anda</h4>
            <p class="text-body-secondary small mb-0">Tingkatkan efisiensi usaha dengan kasir modern, manajemen stok, multi-cabang, dan pembukuan otomatis.</p>
        </div>

        <div class="card-body p-3 p-md-4">
            <div class="row g-4 align-items-stretch">
                @foreach($plans as $key => $plan)
                @php
                    $isCurrent = ($currentPlanKey === $key);
                @endphp
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card h-100 rounded-4 {{ $plan['popular'] ? 'border-primary border-2 shadow' : 'border shadow-sm' }} position-relative overflow-hidden">
                        @if($plan['popular'])
                            <div class="position-absolute top-0 start-50 translate-middle mt-3">
                                <span class="badge bg-primary text-white px-3 py-1 rounded-pill shadow-sm">
                                    <i class="fa-solid fa-fire me-1"></i> Paling Diminati
                                </span>
                            </div>
                        @endif

                        <div class="card-body p-4 d-flex flex-column {{ $plan['popular'] ? 'pt-5' : '' }}">
                            <div class="text-center mb-3">
                                <div class="rounded-circle p-3 d-inline-flex align-items-center justify-content-center bg-{{ $plan['badge'] }} bg-opacity-10 text-{{ $plan['badge'] }} mb-2" style="width: 54px; height: 54px;">
                                    <i class="fa-solid {{ $plan['icon'] }} fs-4"></i>
                                </div>
                                <h5 class="fw-bold mb-1 text-body">{{ $plan['name'] }}</h5>
                                <p class="text-body-secondary small mb-0" style="min-height: 40px;">{{ $plan['tagline'] }}</p>
                            </div>

                            <div class="text-center py-2 mb-3 border-top border-bottom bg-light rounded-3">
                                <div class="fs-4 fw-bold text-body">
                                    Rp {{ number_format($plan['price_monthly'], 0, ',', '.') }}
                                    <span class="fs-6 fw-normal text-body-secondary">/bln</span>
                                </div>
                                <div class="small text-body-secondary">
                                    atau Rp {{ number_format($plan['price_yearly'], 0, ',', '.') }}/thn <span class="badge bg-success-subtle text-success border border-success-subtle">Hemat 2 Bln</span>
                                </div>
                            </div>

                            <div class="mb-4 flex-grow-1">
                                <h6 class="small fw-bold text-uppercase text-body-secondary mb-2">Fitur Unggulan:</h6>
                                <ul class="list-unstyled small mb-0">
                                    @foreach($plan['features'] as $feature)
                                    <li class="d-flex align-items-start gap-2 mb-2">
                                        <i class="fa-solid fa-circle-check text-success mt-1 flex-shrink-0"></i>
                                        <span class="text-body">{{ $feature }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="mt-auto">
                                @if($isCurrent)
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100 disabled rounded-3 shadow-sm" disabled>
                                        <i class="fa-solid fa-check me-1"></i> Paket Aktif Anda
                                    </button>
                                    <button type="button" class="btn btn-sm btn-link w-100 mt-2 text-decoration-none" data-bs-toggle="modal" data-bs-target="#upgradeModal" data-plan="{{ $key }}" data-name="{{ $plan['name'] }}" data-monthly="{{ $plan['price_monthly'] }}" data-yearly="{{ $plan['price_yearly'] }}">
                                        Perpanjang Masa Aktif
                                    </button>
                                @else
                                    <button type="button" class="btn {{ $plan['popular'] ? 'btn-primary' : 'btn-outline-dark' }} btn-sm w-100 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#upgradeModal" data-plan="{{ $key }}" data-name="{{ $plan['name'] }}" data-monthly="{{ $plan['price_monthly'] }}" data-yearly="{{ $plan['price_yearly'] }}">
                                        Pilih Paket Ini <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Upgrade / Perpanjang -->
    <div class="modal fade" id="upgradeModal" tabindex="-1" aria-labelledby="upgradeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                <form action="{{ route('subscription.upgrade') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" id="modalPlanInput">

                    <div class="modal-header bg-light border-0 py-3">
                        <h6 class="modal-title fw-bold text-dark" id="upgradeModalLabel">
                            <i class="fa-solid fa-crown text-warning me-2"></i>Aktifkan / Perpanjang Paket
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="small text-body-secondary">Paket yang dipilih:</div>
                            <h5 class="fw-bold text-primary mb-0" id="modalPlanName">Paket</h5>
                        </div>

                        <label class="form-label fw-bold small text-body-secondary">{{ __('PILIH PERIODE LANGGANAN') }}</label>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="card p-3 border rounded-3 text-center h-100 cursor-pointer period-card active" for="period_1">
                                    <input class="form-check-input visually-hidden" type="radio" name="period" id="period_1" value="1_month" checked>
                                    <div class="fw-bold text-body">1 Bulan</div>
                                    <div class="small text-primary fw-semibold mt-1" id="modalPriceMonthly">Rp -</div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="card p-3 border rounded-3 text-center h-100 cursor-pointer period-card" for="period_12">
                                    <input class="form-check-input visually-hidden" type="radio" name="period" id="period_12" value="12_month">
                                    <div class="fw-bold text-body">1 Tahun (12 Bln)</div>
                                    <div class="small text-success fw-semibold mt-1" id="modalPriceYearly">Rp -</div>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small mt-1">Hemat 2 Bln</span>
                                </label>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 rounded-3 small mb-0">
                            <i class="fa-solid fa-shield-halved me-1"></i> Transaksi aman. Paket otomatis aktif seketika setelah dikonfirmasi.
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4">
                            <i class="fa-solid fa-check me-1"></i> Konfirmasi & Aktifkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const upgradeModal = document.getElementById('upgradeModal');
    if (upgradeModal) {
        upgradeModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const planKey = button.getAttribute('data-plan');
            const planName = button.getAttribute('data-name');
            const monthly = parseInt(button.getAttribute('data-monthly')) || 0;
            const yearly = parseInt(button.getAttribute('data-yearly')) || 0;

            document.getElementById('modalPlanInput').value = planKey;
            document.getElementById('modalPlanName').textContent = planName;
            document.getElementById('modalPriceMonthly').textContent = 'Rp ' + monthly.toLocaleString('id-ID');
            document.getElementById('modalPriceYearly').textContent = 'Rp ' + yearly.toLocaleString('id-ID');
        });
    }

    const periodCards = document.querySelectorAll('.period-card');
    periodCards.forEach(card => {
        card.addEventListener('click', function() {
            periodCards.forEach(c => c.classList.remove('border-primary', 'bg-primary-subtle', 'active'));
            this.classList.add('border-primary', 'bg-primary-subtle', 'active');
        });
    });
});
</script>
@endsection
