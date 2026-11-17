{{-- ============================================ 
     SHIFT LEGEND BAR 
     ============================================ --}}
@php
    $teknisiShift = $shiftConfig['teknisi'] ?? null;
    $washShift = $shiftConfig['wash'] ?? null;
@endphp

<div class="card shadow-sm border-0 rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="small fw-bold text-dark d-flex align-items-center">
                    <i class="fa-solid fa-tags text-primary me-2"></i>{{ __('Keterangan Shift:') }}
                </span>

                <div class="d-flex align-items-center gap-1 bg-light rounded-3 px-2 py-1 border">
                    <span class="badge bg-success-subtle text-success border border-success-subtle">S1 / Piket</span>
                    <span class="small text-muted font-monospace">{{ $teknisiShift['shift_1_start'] ?? '08:00' }} - {{ $teknisiShift['shift_1_end'] ?? '17:00' }}</span>
                </div>

                <div class="d-flex align-items-center gap-1 bg-light rounded-3 px-2 py-1 border">
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">S2 / Backup</span>
                    <span class="small text-muted font-monospace">{{ $teknisiShift['shift_2_start'] ?? '13:00' }} - {{ $teknisiShift['shift_2_end'] ?? '22:00' }}</span>
                </div>

                <div class="d-flex align-items-center gap-1 bg-light rounded-3 px-2 py-1 border">
                    <span class="badge bg-info-subtle text-info border border-info-subtle">LS / Longshift</span>
                    <span class="small text-muted font-monospace">{{ $teknisiShift['longshift_start'] ?? '08:00' }} - {{ $teknisiShift['longshift_end'] ?? '20:00' }}</span>
                </div>

                <div class="d-flex align-items-center gap-1 bg-light rounded-3 px-2 py-1 border">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">OFF</span>
                    <span class="small text-muted">{{ __('Libur') }}</span>
                </div>
            </div>

            <div class="text-body-secondary small">
                <i class="fa-solid fa-lightbulb text-warning me-1"></i>
                <span class="d-none d-md-inline">{{ __('Klik sel pada tabel untuk mengubah penugasan shift.') }}</span>
            </div>
        </div>
    </div>
</div>
