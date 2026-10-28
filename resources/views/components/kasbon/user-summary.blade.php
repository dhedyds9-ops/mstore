@props([
    'user',
    'totalKasbonBiasa' => 0,
    'totalPinjamanAktif' => 0,
    'totalCicilan' => 0,
    'sisaPinjaman' => 0,
    'totalOutstanding' => null,
])

@php
    $outstanding = $totalOutstanding !== null ? (float) $totalOutstanding : ((float) $totalKasbonBiasa + (float) $sisaPinjaman);
@endphp

<div class="card h-100 shadow-sm border rounded-3 bg-body transition-all">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center">
                @php
                    $empPhoto = $user->employee?->id_card_photo_path ? asset('storage/' . $user->employee->id_card_photo_path) : ($user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar)) : null);
                @endphp
                @if($empPhoto)
                    <img src="{{ $empPhoto }}" alt="{{ $user->name }}" class="rounded-circle border me-2.5 flex-shrink-0" style="width: 38px; height: 38px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2.5 d-flex align-items-center justify-content-center border border-primary-subtle flex-shrink-0" style="width: 38px; height: 38px; font-size: 0.9rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h6 class="mb-0 fw-bold text-body-emphasis text-truncate" style="max-width: 130px;" title="{{ $user->name }}">{{ $user->name }}</h6>
                    <small class="text-body-secondary text-truncate d-block" style="max-width: 130px;">
                        {{ $user->employee?->position ?: ($user->job_title ?: ($user->role?->label ?: 'Staf')) }}
                    </small>
                </div>
            </div>
            @if($outstanding > 0)
                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle small px-2 py-1">
                    Ada Hutang
                </span>
            @else
                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle small px-2 py-1">
                    Lunas
                </span>
            @endif
        </div>
        
        <div class="vstack gap-2">
            <div class="p-2 rounded-2 bg-body-tertiary border d-flex justify-content-between align-items-center">
                <small class="text-body-secondary fw-semibold">{{ __('Kasbon Biasa') }}</small>
                <span class="fw-bold {{ $totalKasbonBiasa > 0 ? 'text-warning' : 'text-body-emphasis' }}">
                    Rp {{ number_format($totalKasbonBiasa, 0, ',', '.') }}
                </span>
            </div>
            
            <div class="p-2 rounded-2 bg-body-tertiary border d-flex justify-content-between align-items-center">
                <small class="text-body-secondary fw-semibold">{{ __('Pinjaman Aktif') }}</small>
                <span class="fw-bold {{ $totalPinjamanAktif > 0 ? 'text-primary' : 'text-body-emphasis' }}">
                    Rp {{ number_format($totalPinjamanAktif, 0, ',', '.') }}
                </span>
            </div>
            
            <div class="p-2 rounded-2 bg-body-tertiary border d-flex justify-content-between align-items-center">
                <small class="text-body-secondary fw-semibold">{{ __('Sudah Dicicil') }}</small>
                <span class="fw-bold {{ $totalCicilan > 0 ? 'text-success' : 'text-body-emphasis' }}">
                    Rp {{ number_format($totalCicilan, 0, ',', '.') }}
                </span>
            </div>
            
            <div class="p-2 rounded-2 bg-danger-subtle bg-opacity-25 border border-danger-subtle d-flex justify-content-between align-items-center">
                <small class="text-danger fw-bold">{{ __('Sisa Kewajiban') }}</small>
                <span class="fw-bold {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                    Rp {{ number_format($outstanding, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>
