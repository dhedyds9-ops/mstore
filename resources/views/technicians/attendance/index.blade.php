@extends('layouts.app')

@section('title', __('Rekap Presensi Karyawan'))

@section('content')
@php
    $authUser = Auth::user();
    $isAdmin = $authUser && $authUser->hasAnyRole(['super-admin', 'manager', 'finance']);
@endphp

<div class="container-fluid px-0 px-md-2 py-2">
    <!-- Header Halaman & Tombol Aksi Cepat -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="avatar-title rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    <i class="fa-solid fa-clipboard-user fs-5"></i>
                </span>
                <div>
                    <h4 class="fw-bold mb-0 text-body">{{ __('Rekap Presensi Karyawan') }}</h4>
                    <span class="text-muted small">{{ __('Pantau riwayat kehadiran, jam kerja, status absensi, dan kalkulasi penggajian.') }}</span>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#guideCollapse" aria-expanded="false" aria-controls="guideCollapse">
                <i class="fa-solid fa-circle-question me-1"></i>{{ __('Panduan') }}
            </button>
            <a href="{{ route('attendance.daily') }}" class="btn btn-outline-info btn-sm">
                <i class="fa-solid fa-calendar-day me-1"></i>{{ __('Absensi Harian') }}
            </a>
            <a href="{{ route('attendance.kiosk') }}" class="btn btn-outline-dark btn-sm">
                <i class="fa-solid fa-barcode me-1"></i>{{ __('Kiosk Barcode') }}
            </a>
            @if($isAdmin)
                <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#manualAttendanceModal">
                    <i class="fa-solid fa-plus me-1"></i>{{ __('Tambah Manual') }}
                </button>
                <button type="button" class="btn btn-outline-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#salaryAdjustmentModal">
                    <i class="fa-solid fa-money-bill-transfer me-1"></i>{{ __('Bonus / Kasbon') }}
                </button>
            @endif
        </div>
    </div>

    <!-- Panduan Penggunaan (Collapsible agar tidak memakan ruang) -->
    <div class="collapse mb-4" id="guideCollapse">
        <div class="card border-0 shadow-sm border-start border-4 border-info">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold text-info mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-info"></i>{{ __('Panduan Rekap Presensi & Payroll') }}
                    </h6>
                    <button type="button" class="btn-close btn-sm" data-bs-toggle="collapse" data-bs-target="#guideCollapse" aria-label="Close"></button>
                </div>
                <div class="row g-3 small">
                    <div class="col-12 col-md-4">
                        <div class="p-2 rounded bg-body-tertiary h-100">
                            <span class="fw-bold text-primary d-block mb-1"><i class="fa-solid fa-calendar-check me-1"></i> {{ __('Filter Periode') }}</span>
                            <span class="text-muted">{{ __('Gunakan filter "Bulan" untuk siklus bulanan, atau tentukan rentang "Dari Tanggal" s/d "Sampai Tanggal" (misal: 26 Des - 25 Jan) untuk cut-off payroll.') }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-2 rounded bg-body-tertiary h-100">
                            <span class="fw-bold text-success d-block mb-1"><i class="fa-solid fa-receipt me-1"></i> {{ __('Slip Gaji Otomatis') }}</span>
                            <span class="text-muted">{{ __('Tombol "Slip Gaji" otomatis mengkalkulasi kompensasi kehadiran harian serta penambahan bonus dan pemotongan kasbon karyawan.') }}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-2 rounded bg-body-tertiary h-100">
                            <span class="fw-bold text-warning d-block mb-1"><i class="fa-solid fa-money-bill-wave me-1"></i> {{ __('Catat Gaji ke Keuangan') }}</span>
                            <span class="text-muted">{{ __('Admin dapat langsung membukukan total beban gaji ke modul Keuangan & Kas secara instan melalui tombol "Catat Gaji".') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik Presensi (KPI Cards) -->
    <div class="row g-2 g-md-3 mb-4">
        @php
            $statCards = [
                'present' => ['label' => __('Hadir'), 'icon' => 'fa-circle-check', 'color' => 'success', 'val' => $stats['present'] ?? 0],
                'late'    => ['label' => __('Terlambat'), 'icon' => 'fa-clock-rotate-left', 'color' => 'warning', 'val' => $stats['late'] ?? 0],
                'leave'   => ['label' => __('Cuti'), 'icon' => 'fa-plane-departure', 'color' => 'info', 'val' => $stats['leave'] ?? 0],
                'permit'  => ['label' => __('Izin'), 'icon' => 'fa-envelope-open-text', 'color' => 'primary', 'val' => $stats['permit'] ?? 0],
                'sick'    => ['label' => __('Sakit'), 'icon' => 'fa-notes-medical', 'color' => 'secondary', 'val' => $stats['sick'] ?? 0],
                'alpha'   => ['label' => __('Alpha'), 'icon' => 'fa-circle-xmark', 'color' => 'danger', 'val' => $stats['alpha'] ?? 0],
            ];
        @endphp
        @foreach($statCards as $stKey => $stData)
            @php
                $isActive = (request('status') === $stKey);
                $queryWithStatus = array_merge(request()->query(), ['status' => $isActive ? '' : $stKey]);
            @endphp
            <div class="col-6 col-sm-4 col-xl-2">
                <a href="{{ route('attendance.index', $queryWithStatus) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-3 h-100 transition-all {{ $isActive ? 'border border-2 border-' . $stData['color'] : '' }}">
                        <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block text-muted x-small fw-semibold text-uppercase">{{ $stData['label'] }}</span>
                                <h3 class="fw-bold mb-0 text-{{ $stData['color'] }}">{{ number_format($stData['val']) }}</h3>
                            </div>
                            <span class="avatar-title rounded-circle bg-{{ $stData['color'] }} bg-opacity-10 text-{{ $stData['color'] }} d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="fa-solid {{ $stData['icon'] }} fs-6"></i>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Filter Toolbar & Action Buttons -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('attendance.index') }}" method="GET" id="attendanceFilterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Karyawan') }}</label>
                        <select name="user_id" class="form-select form-select-sm js-search-select">
                            <option value="">{{ __('Semua Karyawan') }}</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ (string)request('user_id') === (string)$tech->id ? 'selected' : '' }}>
                                    {{ $tech->employee?->full_name ?? $tech->name }} ({{ $tech->employee?->position ?? $tech->role->name ?? __('Staff') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Status') }}</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">{{ __('Semua Status') }}</option>
                            <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>{{ __('Hadir') }}</option>
                            <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>{{ __('Terlambat') }}</option>
                            <option value="leave" {{ request('status') === 'leave' ? 'selected' : '' }}>{{ __('Cuti') }}</option>
                            <option value="permit" {{ request('status') === 'permit' ? 'selected' : '' }}>{{ __('Izin') }}</option>
                            <option value="sick" {{ request('status') === 'sick' ? 'selected' : '' }}>{{ __('Sakit') }}</option>
                            <option value="alpha" {{ request('status') === 'alpha' ? 'selected' : '' }}>{{ __('Alpha') }}</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Bulan') }}</label>
                        <input type="month" name="month" value="{{ request('month') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Dari Tanggal') }}</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label x-small fw-bold text-muted mb-1 text-uppercase">{{ __('Sampai Tanggal') }}</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-12 col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100" id="btnFilterSubmit" title="{{ __('Terapkan Filter') }}">
                            <i class="fa-solid fa-filter me-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary btn-sm" title="{{ __('Reset Filter') }}">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    </div>
                </div>

                <!-- Tag Filter Aktif -->
                @php
                    $activeFilters = [];
                    if(request('user_id')) {
                        $selectedUser = $technicians->where('id', request('user_id'))->first();
                        $activeFilters['user_id'] = 'Karyawan: ' . ($selectedUser->employee?->full_name ?? $selectedUser->name ?? '-');
                    }
                    if(request('status')) {
                        $stMap = [
                            'present' => 'Hadir',
                            'late'    => 'Terlambat',
                            'leave'   => 'Cuti',
                            'permit'  => 'Izin',
                            'sick'    => 'Sakit',
                            'alpha'   => 'Alpha'
                        ];
                        $activeFilters['status'] = 'Status: ' . ($stMap[request('status')] ?? request('status'));
                    }
                    if(request('start_date') && request('end_date')) {
                        $activeFilters['date_range'] = 'Periode: ' . \Carbon\Carbon::parse(request('start_date'))->translatedFormat('d M Y') . ' - ' . \Carbon\Carbon::parse(request('end_date'))->translatedFormat('d M Y');
                    } elseif(request('month')) {
                        $activeFilters['month'] = 'Bulan: ' . \Carbon\Carbon::parse(request('month'))->translatedFormat('F Y');
                    } elseif(request('date')) {
                        $activeFilters['date'] = 'Tanggal: ' . \Carbon\Carbon::parse(request('date'))->translatedFormat('d M Y');
                    }
                @endphp
                @if(count($activeFilters) > 0)
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">
                        <span class="x-small fw-bold text-muted"><i class="fa-solid fa-tags me-1"></i>{{ __('Filter Aktif:') }}</span>
                        @foreach($activeFilters as $fKey => $fVal)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 fw-medium">
                                {{ $fVal }}
                            </span>
                        @endforeach
                        <a href="{{ route('attendance.index') }}" class="badge bg-danger bg-opacity-10 text-danger text-decoration-none px-2 py-1 fw-medium">
                            <i class="fa-solid fa-xmark me-1"></i>{{ __('Hapus Semua') }}
                        </a>
                    </div>
                @endif
            </form>

            <!-- Toolbar Tombol Laporan & Ekspor -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3 border-top">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('attendance.payslip', request()->all()) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                        <i class="fa-solid fa-receipt me-1 text-primary"></i>{{ __('Slip Gaji') }}
                    </a>
                    <a href="{{ route('attendance.excel', array_merge(request()->all(), ['download' => 'details'])) }}" class="btn btn-outline-success btn-sm" target="_blank">
                        <i class="fa-solid fa-file-excel me-1 text-success"></i>{{ __('Download Excel') }}
                    </a>
                </div>
                @if($isAdmin)
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-warning btn-sm text-dark fw-bold shadow-xs" onclick="confirmRecapFinance()">
                            <i class="fa-solid fa-money-bill-wave me-1"></i>{{ __('Catat Gaji ke Kas') }}
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm d-none" id="btnBulkDelete" onclick="submitBulkDelete()">
                            <i class="fa-regular fa-trash-can me-1"></i>{{ __('Hapus') }} (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabel Data Rekap Presensi -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent py-3 border-0">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-body d-flex align-items-center gap-2">
                    <i class="fa-solid fa-list text-primary"></i>{{ __('Data Riwayat Presensi') }}
                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold rounded-pill px-2">
                        {{ $attendances->total() }} {{ __('Data') }}
                    </span>
                </h6>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 850px;">
                <thead class="table-light text-uppercase x-small fw-bold text-muted border-bottom">
                    <tr>
                        @if($isAdmin)
                            <th class="ps-3 py-3" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAllAttendance" onclick="toggleSelectAll()">
                            </th>
                        @endif
                        <th class="py-3">{{ __('Karyawan') }}</th>
                        <th class="py-3">{{ __('Tanggal') }}</th>
                        <th class="py-3">{{ __('Jam Masuk') }}</th>
                        <th class="py-3">{{ __('Jam Pulang') }}</th>
                        <th class="py-3">{{ __('Status') }}</th>
                        <th class="py-3">{{ __('Lokasi & Foto') }}</th>
                        @if($isAdmin)
                            <th class="text-end pe-3 py-3" style="width: 140px;">{{ __('Aksi') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($attendances as $attendance)
                        <tr>
                            @if($isAdmin)
                                <td class="ps-3">
                                    <input type="checkbox" class="form-check-input attendance-select" value="{{ $attendance->id }}" onchange="updateBulkDeleteState()">
                                </td>
                            @endif
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-title rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($attendance->user->employee?->full_name ?? $attendance->user->name ?? 'K', 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="fw-semibold text-body d-block">{{ $attendance->user->employee?->full_name ?? $attendance->user->name }}</span>
                                        <span class="x-small text-muted">{{ $attendance->user->employee?->position ?? $attendance->user->role?->name ?? __('Karyawan') }}</span>
                                    </div>
                                </div>
                                @php $shift = $attendance->shift_info; @endphp
                                @if(!empty($shift['start']) && !empty($shift['end']))
                                    <div class="mt-1">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 x-small py-0 px-2" title="{{ __('Jadwal Shift') }}">
                                            <i class="fa-solid fa-business-time me-1"></i>{{ $shift['start'] }} - {{ $shift['end'] }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="small">
                                <span class="fw-medium text-body d-block">{{ ($attendance->work_date ?? $attendance->clock_in)?->translatedFormat('d M Y') }}</span>
                                <span class="x-small text-muted">{{ ($attendance->work_date ?? $attendance->clock_in)?->translatedFormat('l') }}</span>
                            </td>
                            <td>
                                @if($attendance->clock_in)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2" style="font-size: 0.82rem;">
                                        <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>{{ $attendance->clock_in->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted small">--:--</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->clock_out)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 py-1 px-2" style="font-size: 0.82rem;">
                                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i>{{ $attendance->clock_out->format('H:i') }}
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 py-1 px-2" style="font-size: 0.82rem;">
                                        <i class="fa-regular fa-clock me-1"></i>{{ __('Belum Pulang') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $stBadge = match($attendance->status) {
                                        'present' => ['bg' => 'success', 'text' => __('Hadir')],
                                        'late'    => ['bg' => 'warning', 'text' => __('Terlambat')],
                                        'leave'   => ['bg' => 'info',    'text' => __('Cuti')],
                                        'permit'  => ['bg' => 'primary', 'text' => __('Izin')],
                                        'sick'    => ['bg' => 'secondary','text' => __('Sakit')],
                                        'alpha'   => ['bg' => 'danger',  'text' => __('Alpha')],
                                        default   => ['bg' => 'secondary','text' => ucfirst($attendance->status)],
                                    };
                                @endphp
                                <span class="badge bg-{{ $stBadge['bg'] }} bg-opacity-10 text-{{ $stBadge['bg'] }} border border-{{ $stBadge['bg'] }} border-opacity-25 px-2 py-1">
                                    {{ $stBadge['text'] }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    {{-- Maps Link --}}
                                    @if($attendance->lat_clock_in && $attendance->lng_clock_in)
                                        <a href="https://maps.google.com/?q={{ $attendance->lat_clock_in }},{{ $attendance->lng_clock_in }}" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.72rem;" title="{{ __('Lihat Lokasi GPS') }}">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </a>
                                    @endif
                                    {{-- Photo Preview --}}
                                    @if($attendance->photo_clock_in)
                                        <a href="{{ Storage::url($attendance->photo_clock_in) }}" target="_blank" title="{{ __('Foto Masuk') }}">
                                            <img src="{{ Storage::url($attendance->photo_clock_in) }}" class="rounded border shadow-xs object-fit-cover" style="width: 32px; height: 32px;" alt="In" loading="lazy">
                                        </a>
                                    @endif
                                    @if($attendance->photo_clock_out)
                                        <a href="{{ Storage::url($attendance->photo_clock_out) }}" target="_blank" title="{{ __('Foto Pulang') }}">
                                            <img src="{{ Storage::url($attendance->photo_clock_out) }}" class="rounded border shadow-xs object-fit-cover" style="width: 32px; height: 32px;" alt="Out" loading="lazy">
                                        </a>
                                    @endif
                                </div>
                            </td>
                            @if($isAdmin)
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        {{-- WhatsApp Notification --}}
                                        <form method="POST" action="{{ route('attendance.notify', $attendance) }}" class="form-whatsapp d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm p-1 px-2" title="{{ __('Kirim Notifikasi WhatsApp') }}">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </button>
                                        </form>
                                        {{-- Delete Single Record --}}
                                        <form method="POST" action="{{ route('attendance.destroy', $attendance->id) }}" class="form-delete d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="{{ __('Hapus Presensi') }}">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 8 : 6 }}" class="text-center py-5">
                                <div class="py-4">
                                    <span class="avatar-title rounded-circle bg-secondary bg-opacity-10 text-secondary p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                                        <i class="fa-regular fa-folder-open fs-3"></i>
                                    </span>
                                    <h6 class="fw-semibold text-body mb-1">{{ __('Tidak ada data absensi') }}</h6>
                                    <p class="text-muted small mb-0">{{ __('Belum ada riwayat absensi yang cocok dengan kriteria filter.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="card-footer bg-transparent py-3 border-top">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="x-small text-muted">
                        {{ __('Menampilkan :from - :to dari :total data', ['from' => $attendances->firstItem() ?? 0, 'to' => $attendances->lastItem() ?? 0, 'total' => $attendances->total()]) }}
                    </span>
                    {{ $attendances->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal Absensi Manual -->
<div class="modal fade" id="manualAttendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-body">
                    <i class="fa-solid fa-plus-circle text-primary me-2"></i>{{ __('Tambah Absensi / Cuti Manual') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('attendance.storeManual') }}" method="POST" id="formManualAttendance">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">{{ __('Karyawan') }} <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select form-select-sm js-search-select-modal" required>
                            <option value="">{{ __('Pilih Karyawan') }}</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->employee?->full_name ?? $tech->name }} ({{ $tech->employee?->position ?? $tech->role->name ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">{{ __('Tanggal') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">{{ __('Status Kehadiran') }} <span class="text-danger">*</span></label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="present">{{ __('Hadir') }}</option>
                                <option value="late">{{ __('Terlambat') }}</option>
                                <option value="leave">{{ __('Cuti') }}</option>
                                <option value="permit">{{ __('Izin') }}</option>
                                <option value="sick">{{ __('Sakit') }}</option>
                                <option value="alpha">{{ __('Alpha') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted">{{ __('Keterangan / Catatan') }}</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Alasan cuti/izin/alfa..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3" id="btnSubmitManual">
                        <i class="fa-solid fa-floppy-disk me-1"></i>{{ __('Simpan Data') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bonus / Kasbon -->
<div class="modal fade" id="salaryAdjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-body">
                    <i class="fa-solid fa-money-bill-transfer text-success me-2"></i>{{ __('Input Bonus / Kasbon Karyawan') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('salary-adjustments.store') }}" method="POST" id="formSalaryAdjustment">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">{{ __('Karyawan') }} <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select form-select-sm js-search-select-modal" required>
                            <option value="">{{ __('Pilih Karyawan') }}</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->employee?->full_name ?? $tech->name }} ({{ $tech->employee?->position ?? $tech->role->name ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">{{ __('Tipe') }} <span class="text-danger">*</span></label>
                            <select name="type" id="adjustment_type" class="form-select form-select-sm" required onchange="updateAdjustmentCategories()">
                                <option value="bonus">{{ __('Bonus (+) Tambahan') }}</option>
                                <option value="kasbon">{{ __('Kasbon (-) Potongan') }}</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">{{ __('Kategori') }} <span class="text-danger">*</span></label>
                            <select name="category" id="adjustment_category" class="form-select form-select-sm" required onchange="updateAdjustmentDescription()">
                                <option value="disiplin">Bonus Disiplin</option>
                                <option value="tanggung jawab">Bonus Tanggung Jawab</option>
                                <option value="absensi">Bonus Absensi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">{{ __('Nominal (Rp)') }} <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control form-control-sm" required min="0" step="1000" placeholder="Contoh: 50000">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">{{ __('Tanggal') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted">{{ __('Keterangan di Slip Gaji') }}</label>
                        <textarea name="description" id="adjustment_description" class="form-control form-control-sm" rows="2" readonly>Bonus Disiplin</textarea>
                        <span class="x-small text-muted mt-1 d-block">{{ __('Keterangan terisi otomatis berdasarkan kategori yang dipilih.') }}</span>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                    <button type="submit" class="btn btn-success btn-sm px-3" id="btnSubmitAdjustment">
                        <i class="fa-solid fa-floppy-disk me-1"></i>{{ __('Simpan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden Form Actions --}}
<form id="form-recap-finance" action="{{ route('attendance.recap_finance') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
    <input type="hidden" name="month" value="{{ request('month') }}">
    <input type="hidden" name="date" value="{{ request('date') }}">
    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
</form>

<form id="bulkDeleteForm" action="{{ route('attendance.bulkDestroy') }}" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Select2
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        $('.js-search-select').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        $('#manualAttendanceModal').on('shown.bs.modal', function () {
            $('.js-search-select-modal', this).select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#manualAttendanceModal')
            });
        });

        $('#salaryAdjustmentModal').on('shown.bs.modal', function () {
            $('.js-search-select-modal', this).select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#salaryAdjustmentModal')
            });
        });
    }

    // 2. Loading State pada Form Submit
    const bindLoadingState = function(formId, btnId, loadingText) {
        const form = document.getElementById(formId);
        const btn = document.getElementById(btnId);
        if (form && btn) {
            form.addEventListener('submit', function() {
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${loadingText}`;
            });
        }
    };
    bindLoadingState('formManualAttendance', 'btnSubmitManual', 'Menyimpan...');
    bindLoadingState('formSalaryAdjustment', 'btnSubmitAdjustment', 'Menyimpan...');

    // 3. Konfirmasi Hapus Single (SweetAlert)
    document.querySelectorAll('.form-delete').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '{{ __('Hapus data absensi ini?') }}',
                    text: '{{ __('Data yang dihapus tidak dapat dipulihkan kembali.') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '{{ __('Ya, Hapus!') }}',
                    cancelButtonText: '{{ __('Batal') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            } else {
                if (confirm('Yakin ingin menghapus data absensi ini?')) form.submit();
            }
        });
    });

    // 4. Konfirmasi Kirim WhatsApp
    document.querySelectorAll('.form-whatsapp').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '{{ __('Kirim Notifikasi WhatsApp?') }}',
                    text: '{{ __('Sistem akan mengirimkan pesan ringkasan absensi ke nomor karyawan.') }}',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '{{ __('Kirim Sekarang') }}',
                    cancelButtonText: '{{ __('Batal') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            } else {
                if (confirm('Kirim notifikasi WhatsApp ke karyawan?')) form.submit();
            }
        });
    });
});

// 5. Catat Pengeluaran Gaji ke Keuangan
function confirmRecapFinance() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '{{ __('Catat Pengeluaran Gaji ke Kas?') }}',
            text: '{{ __('Sistem akan membuat transaksi pengeluaran payroll di modul Keuangan sesuai filter saat ini.') }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __('Ya, Catat Sekarang') }}',
            cancelButtonText: '{{ __('Batal') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-recap-finance').submit();
            }
        });
    } else {
        if (confirm('Catat pengeluaran gaji ke kas?')) {
            document.getElementById('form-recap-finance').submit();
        }
    }
}

// 6. Checkbox Seleksi Massal & Hapus Terpilih
function toggleSelectAll() {
    const master = document.getElementById('selectAllAttendance');
    const items = document.querySelectorAll('.attendance-select');
    items.forEach(cb => cb.checked = master.checked);
    updateBulkDeleteState();
}

function updateBulkDeleteState() {
    const checkedCount = document.querySelectorAll('.attendance-select:checked').length;
    const btn = document.getElementById('btnBulkDelete');
    const badge = document.getElementById('selectedCount');
    if (btn && badge) {
        badge.textContent = checkedCount;
        if (checkedCount > 0) {
            btn.classList.remove('d-none');
        } else {
            btn.classList.add('d-none');
        }
    }
}

function submitBulkDelete() {
    const selected = Array.from(document.querySelectorAll('.attendance-select:checked')).map(cb => cb.value);
    if (selected.length === 0) return;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '{{ __('Hapus :count data absensi terpilih?', ['count' => '']) }}'.replace(':count', selected.length),
            text: '{{ __('Semua data yang dicentang akan dihapus secara permanen.') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __('Ya, Hapus Semua') }}',
            cancelButtonText: '{{ __('Batal') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('bulkDeleteForm');
                form.innerHTML = '@csrf<input type="hidden" name="_method" value="DELETE">';
                selected.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                form.submit();
            }
        });
    } else {
        if (confirm('Hapus ' + selected.length + ' data terpilih?')) {
            const form = document.getElementById('bulkDeleteForm');
            form.innerHTML = '@csrf<input type="hidden" name="_method" value="DELETE">';
            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });
            form.submit();
        }
    }
}

// 7. Dynamic Dropdown Kategori Bonus / Kasbon
function updateAdjustmentCategories() {
    const type = document.getElementById('adjustment_type').value;
    const categorySelect = document.getElementById('adjustment_category');
    if (!categorySelect) return;
    categorySelect.innerHTML = '';

    if (type === 'bonus') {
        const options = [
            { value: 'disiplin', text: 'Bonus Disiplin' },
            { value: 'tanggung jawab', text: 'Bonus Tanggung Jawab' },
            { value: 'absensi', text: 'Bonus Absensi' }
        ];
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt.value;
            el.textContent = opt.text;
            categorySelect.appendChild(el);
        });
    } else if (type === 'kasbon') {
        const options = [
            { value: 'bon kantor', text: 'Bon Kantor' },
            { value: 'bon warung', text: 'Bon Warung' }
        ];
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt.value;
            el.textContent = opt.text;
            categorySelect.appendChild(el);
        });
    }
    updateAdjustmentDescription();
}

function updateAdjustmentDescription() {
    const categorySelect = document.getElementById('adjustment_category');
    const descriptionTextarea = document.getElementById('adjustment_description');
    if (categorySelect && descriptionTextarea && categorySelect.options.length > 0 && categorySelect.selectedIndex > -1) {
        descriptionTextarea.value = categorySelect.options[categorySelect.selectedIndex].text;
    }
}
</script>
@endpush
