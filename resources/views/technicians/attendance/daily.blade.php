@extends('layouts.app')

@section('title', __('Kehadiran Karyawan Harian'))

@section('content')
@php
    $isAdminOrHrd = Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']);
@endphp

<div class="container-fluid px-0 px-md-2 py-2">
    <!-- Header Halaman -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar-title rounded-3 bg-info bg-opacity-10 text-info p-2 d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="fa-solid fa-calendar-day fs-5"></i>
            </span>
            <div>
                <h4 class="fw-bold mb-0 text-body">{{ __('Kehadiran Karyawan Harian') }}</h4>
                <span class="text-muted small">{{ __('Monitoring status kehadiran seluruh staf per tanggal, termasuk karyawan libur (OFF) dan belum absen.') }}</span>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-list-check me-1"></i>{{ __('Kembali ke Rekap') }}
            </a>
            <a href="{{ route('attendance.excel', array_merge(request()->query(), ['scope' => 'daily'])) }}" target="_blank" class="btn btn-outline-success btn-sm">
                <i class="fa-solid fa-file-excel me-1"></i>{{ __('Ekspor Excel') }}
            </a>
        </div>
    </div>

    <!-- Filter Card Toolbar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('attendance.daily') }}" method="GET" class="w-100" id="dailyAttendanceFilterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-sm-6 col-md-2">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Bulan') }}</label>
                        <input type="month" name="month" value="{{ request('month') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-sm-6 col-md-2">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Dari Tanggal') }}</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-sm-6 col-md-2">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Sampai Tanggal') }}</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-sm-6 col-md-2">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Divisi / Unit') }}</label>
                        <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">{{ __('Semua Divisi') }}</option>
                            <option value="technician" @selected(request('category') === 'technician')>{{ __('Teknisi Lapangan') }}</option>
                            <option value="wash" @selected(request('category') === 'wash')>{{ __('Wash (Cuci & Kasir)') }}</option>
                            <option value="atk" @selected(request('category') === 'atk')>{{ __('ATK (Kasir & Toko)') }}</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-6 col-md-2">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Status') }}</label>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">{{ __('Semua Status') }}</option>
                            @foreach(['present' => 'Hadir', 'late' => 'Terlambat', 'leave' => 'Cuti', 'permit' => 'Izin', 'sick' => 'Sakit', 'alpha' => 'Alpha', 'off' => 'Off', 'belum_absen' => 'Belum Absen'] as $val => $label)
                                <option value="{{ $val }}" {{ ($status ?? '') === $val ? 'selected' : '' }}>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Cari Nama / Email') }}</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Cari nama...') }}" class="form-control">
                            <button type="submit" class="btn btn-primary" title="{{ __('Terapkan Filter') }}">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                            @if(request()->hasAny(['search', 'status', 'category', 'month', 'start_date', 'end_date']))
                                <a href="{{ route('attendance.daily') }}" class="btn btn-outline-secondary" title="{{ __('Reset Filter') }}">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Periode Aktif -->
    <div class="alert bg-primary bg-opacity-10 border border-primary border-opacity-25 text-body py-2 px-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2 rounded-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-calendar-check text-primary fs-5"></i>
            <span class="small">
                {{ __('Menampilkan rekap harian periode:') }} 
                <strong>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> — <strong>{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
            </span>
        </div>
        <span class="badge bg-primary bg-opacity-25 text-primary x-small px-2 py-1">
            {{ count($dates) }} {{ __('Hari Kalender') }}
        </span>
    </div>

    <!-- Accordion Daftar Kehadiran Per Tanggal -->
    <div class="accordion" id="accordionAttendance">
        @foreach($dates as $index => $currentDate)
            @php
                $attendances = $attendancesByDate->get($currentDate, collect());
                
                $filteredUsers = $users->filter(function($user) use ($attendances, $currentDate, $status, $isOffByUserAndDate) {
                    $isOff = $isOffByUserAndDate[$user->id][$currentDate] ?? false;
                    $attendance = $attendances->get($user->id);
                    
                    if ($status === '') return true;
                    if ($status === 'belum_absen') return ! $attendance && ! $isOff;
                    if ($status === 'off') return $isOff;
                    return $attendance && $attendance->status === $status;
                });
            @endphp

            <div class="accordion-item mb-2 border-0 shadow-sm rounded-3 overflow-hidden">
                <h2 class="accordion-header" id="heading-{{ $index }}">
                    <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }} fw-bold py-3 text-body" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse-{{ $index }}">
                        <i class="fa-regular fa-calendar-days text-primary me-2"></i>
                        <span>{{ \Carbon\Carbon::parse($currentDate)->translatedFormat('l, d F Y') }}</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 ms-2 small">
                            {{ $filteredUsers->count() }} {{ __('Karyawan') }}
                        </span>
                    </button>
                </h2>
                
                <div id="collapse-{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading-{{ $index }}" data-bs-parent="#accordionAttendance">
                    <div class="accordion-body p-0">
                        @if($filteredUsers->isEmpty())
                            <div class="p-4 text-center text-muted">
                                <i class="fa-regular fa-folder-open d-block fs-3 mb-2 text-secondary"></i>
                                {{ __('Tidak ada data kehadiran yang cocok pada tanggal ini.') }}
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="min-width: 780px;">
                                    <thead class="table-light text-uppercase x-small fw-bold text-muted border-bottom">
                                        <tr>
                                            <th style="width: 45px;" class="ps-3 py-3">#</th>
                                            <th class="py-3">{{ __('Karyawan') }}</th>
                                            <th class="py-3">{{ __('Role / Posisi') }}</th>
                                            <th class="py-3">{{ __('Jam Masuk') }}</th>
                                            <th class="py-3">{{ __('Jam Pulang') }}</th>
                                            <th class="py-3">{{ __('Status') }}</th>
                                            <th class="py-3">{{ __('Keterangan') }}</th>
                                            @if($isAdminOrHrd)
                                                <th class="text-end pe-3 py-3" style="width: 100px;">{{ __('Aksi') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @php $i = 1; @endphp
                                        @foreach($filteredUsers as $user)
                                            @php
                                                $attendance = $attendances->get($user->id);
                                                $isOff = $isOffByUserAndDate[$user->id][$currentDate] ?? false;
                                            @endphp
                                            <tr>
                                                <td class="ps-3 text-muted small">{{ $i++ }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-title rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                                            {{ strtoupper(substr($user->employee?->full_name ?? $user->name, 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <span class="fw-semibold text-body d-block">{{ $user->employee?->full_name ?? $user->name }}</span>
                                                            <span class="x-small text-muted">{{ $user->username ?? $user->email }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 x-small px-2 py-1">
                                                        {{ $user->employee?->position ?? $user->role->name ?? '-' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($isOff)
                                                        <span class="text-muted small">--:--</span>
                                                    @elseif($attendance && $attendance->clock_in)
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>{{ $attendance->clock_in->format('H:i') }}
                                                        </span>
                                                        @if($attendance->lat_clock_in && $attendance->lng_clock_in)
                                                            <a href="https://maps.google.com/?q={{ $attendance->lat_clock_in }},{{ $attendance->lng_clock_in }}" target="_blank" class="text-decoration-none x-small d-block mt-1 text-primary">
                                                                <i class="fa-solid fa-location-dot me-1"></i>{{ __('GPS') }}
                                                            </a>
                                                        @endif
                                                    @else
                                                        <span class="text-muted small">--:--</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($isOff)
                                                        <span class="text-muted small">--:--</span>
                                                    @elseif($attendance && $attendance->clock_out)
                                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                                            <i class="fa-solid fa-arrow-right-from-bracket me-1"></i>{{ $attendance->clock_out->format('H:i') }}
                                                        </span>
                                                        @if($attendance->lat_clock_out && $attendance->lng_clock_out)
                                                            <a href="https://maps.google.com/?q={{ $attendance->lat_clock_out }},{{ $attendance->lng_clock_out }}" target="_blank" class="text-decoration-none x-small d-block mt-1 text-info">
                                                                <i class="fa-solid fa-location-dot me-1"></i>{{ __('GPS') }}
                                                            </a>
                                                        @endif
                                                    @else
                                                        <span class="text-muted small">--:--</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($isOff)
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                                            {{ __('OFF (Libur)') }}
                                                        </span>
                                                    @elseif($attendance)
                                                        @php
                                                            $statusClass = match($attendance->status) {
                                                                'present' => 'bg-success text-success',
                                                                'late'    => 'bg-warning text-warning',
                                                                'leave', 'permit', 'sick' => 'bg-info text-info',
                                                                'alpha'   => 'bg-danger text-danger',
                                                                default   => 'bg-secondary text-secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge {{ $statusClass }} bg-opacity-10 border border-current px-2 py-1">
                                                            {{ __(ucfirst($attendance->status)) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                                            <i class="fa-solid fa-circle-xmark me-1"></i>{{ __('Belum Absen') }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="pe-3 small text-muted">
                                                    <span class="d-inline-block text-truncate" style="max-width: 180px;" title="{{ $attendance->notes ?? '' }}">
                                                        {{ $attendance->notes ?? '-' }}
                                                    </span>
                                                </td>
                                                @if($isAdminOrHrd)
                                                    <td class="text-end pe-3">
                                                        <div class="d-inline-flex gap-1">
                                                            @if($attendance)
                                                                <button type="button" class="btn btn-outline-primary btn-sm p-1 px-2" title="{{ __('Edit Presensi') }}" data-bs-toggle="modal" data-bs-target="#editModalDaily-{{ $user->id }}-{{ $currentDate }}">
                                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                                </button>
                                                            @elseif(!$isOff)
                                                                <button type="button" class="btn btn-outline-success btn-sm p-1 px-2" title="{{ __('Input Presensi') }}" data-bs-toggle="modal" data-bs-target="#createModalDaily-{{ $user->id }}-{{ $currentDate }}">
                                                                    <i class="fa-solid fa-plus"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Edit & Buat Absensi Harian -->
@foreach($dates as $index => $currentDate)
    @php
        $attendances = $attendancesByDate->get($currentDate, collect());
        $filteredUsersForModal = $users->filter(function($user) use ($attendances, $currentDate, $status, $isOffByUserAndDate) {
            $isOff = $isOffByUserAndDate[$user->id][$currentDate] ?? false;
            $attendance = $attendances->get($user->id);
            if ($status === '') return true;
            if ($status === 'belum_absen') return ! $attendance && ! $isOff;
            if ($status === 'off') return $isOff;
            return $attendance && $attendance->status === $status;
        });
    @endphp
    @foreach($filteredUsersForModal as $user)
        @php
            $attendance = $attendances->get($user->id);
            $isOff = $isOffByUserAndDate[$user->id][$currentDate] ?? false;
        @endphp
        @if($attendance)
            <!-- Edit Modal -->
            <div class="modal fade" id="editModalDaily-{{ $user->id }}-{{ $currentDate }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom">
                            <h5 class="modal-title fw-bold text-body">
                                <i class="fa-solid fa-pen-to-square text-primary me-2"></i>{{ __('Edit Absensi') }} — {{ $user->name }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('attendance.updateManual', $attendance->id) }}" method="POST" class="daily-attendance-form">
                            @csrf
                            @method('PUT')
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">{{ __('Tanggal') }}</label>
                                    <input type="date" name="date" class="form-control form-control-sm" required value="{{ $currentDate }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">{{ __('Status Kehadiran') }}</label>
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="present" {{ $attendance->status === 'present' ? 'selected' : '' }}>{{ __('Hadir') }}</option>
                                        <option value="leave" {{ $attendance->status === 'leave' ? 'selected' : '' }}>{{ __('Cuti') }}</option>
                                        <option value="permit" {{ $attendance->status === 'permit' ? 'selected' : '' }}>{{ __('Izin') }}</option>
                                        <option value="sick" {{ $attendance->status === 'sick' ? 'selected' : '' }}>{{ __('Sakit') }}</option>
                                        <option value="late" {{ $attendance->status === 'late' ? 'selected' : '' }}>{{ __('Terlambat') }}</option>
                                        <option value="alpha" {{ $attendance->status === 'alpha' ? 'selected' : '' }}>{{ __('Alpha') }}</option>
                                    </select>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Jam Masuk') }}</label>
                                        <input type="time" name="clock_in" class="form-control form-control-sm" value="{{ $attendance->clock_in?->format('H:i') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Jam Pulang') }}</label>
                                        <input type="time" name="clock_out" class="form-control form-control-sm" value="{{ $attendance->clock_out?->format('H:i') }}">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-muted">{{ __('Catatan') }}</label>
                                    <textarea name="notes" class="form-control form-control-sm" rows="2">{{ $attendance->notes }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer bg-light border-top py-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3">
                                    <i class="fa-solid fa-floppy-disk me-1"></i>{{ __('Simpan Perubahan') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @elseif(!$isOff)
            <!-- Create Modal -->
            <div class="modal fade" id="createModalDaily-{{ $user->id }}-{{ $currentDate }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-bottom">
                            <h5 class="modal-title fw-bold text-body">
                                <i class="fa-solid fa-plus-circle text-success me-2"></i>{{ __('Input Absensi Baru') }} — {{ $user->name }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('attendance.storeManual') }}" method="POST" class="daily-attendance-form">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">{{ __('Tanggal') }}</label>
                                    <input type="date" name="date" class="form-control form-control-sm" required value="{{ $currentDate }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">{{ __('Status Kehadiran') }}</label>
                                    <select name="status" class="form-select form-select-sm" required>
                                        <option value="present">{{ __('Hadir') }}</option>
                                        <option value="leave">{{ __('Cuti') }}</option>
                                        <option value="permit">{{ __('Izin') }}</option>
                                        <option value="sick">{{ __('Sakit') }}</option>
                                        <option value="late">{{ __('Terlambat') }}</option>
                                        <option value="alpha">{{ __('Alpha') }}</option>
                                    </select>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Jam Masuk') }}</label>
                                        <input type="time" name="clock_in_create" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Jam Pulang') }}</label>
                                        <input type="time" name="clock_out_create" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-muted">{{ __('Catatan') }}</label>
                                    <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Keterangan tambahan..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer bg-light border-top py-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3">
                                    <i class="fa-solid fa-floppy-disk me-1"></i>{{ __('Simpan Data') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endforeach
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tombol submit loading state
    document.querySelectorAll('.daily-attendance-form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...`;
            }
        });
    });
});
</script>
@endpush
