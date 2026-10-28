@extends('layouts.app')

@section('title', __('Rekap Presensi Karyawan'))

@section('content')
@php
    $authUser = Auth::user();
    $isAdmin = $authUser && $authUser->hasAnyRole(['super-admin', 'manager', 'finance']);
@endphp

<div class="container-fluid py-4 px-3 px-md-4">
    {{-- Header Bersih & Rapi (Satu Baris) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-body-emphasis">
                    <i class="fa-solid fa-clipboard-user text-primary me-2"></i>{{ __('Rekap Presensi Karyawan') }}
                </h4>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                    <i class="fa-regular fa-calendar-check me-1"></i>{{ $periodLabel }}
                </span>
            </div>
            <p class="text-muted small mb-0">{{ __('Pantau riwayat kehadiran, jam kerja, status absensi, dan cut-off payroll karyawan.') }}</p>
        </div>

        {{-- Action Buttons & Navigation (Sejajar Satu Baris) --}}
        <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- Navigasi View --}}
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('attendance.index') }}" class="btn btn-primary fw-semibold px-2.5 active">
                    <i class="fa-solid fa-list me-1"></i>{{ __('Rekap') }}
                </a>
                <a href="{{ route('attendance.daily') }}" class="btn btn-outline-secondary px-2.5">
                    <i class="fa-solid fa-calendar-day me-1"></i>{{ __('Harian') }}
                </a>
                <a href="{{ route('attendance.kiosk') }}" class="btn btn-outline-secondary px-2.5">
                    <i class="fa-solid fa-barcode me-1"></i>{{ __('Kiosk') }}
                </a>
            </div>

            {{-- Tombol Utama Admin --}}
            @if($isAdmin)
                <button type="button" class="btn btn-sm btn-primary shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#manualAttendanceModal">
                    <i class="fa-solid fa-user-plus me-1"></i>{{ __('Tambah Absen') }}
                </button>
            @endif

            {{-- Dropdown Menu Aksi & Ekspor (Menggabungkan semua tombol agar tidak berantakan) --}}
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical me-1"></i>{{ __('Menu & Ekspor') }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2">
                    <li>
                        <a class="dropdown-item py-2 small" href="{{ route('attendance.payslip', request()->all()) }}" target="_blank">
                            <i class="fa-solid fa-receipt me-2 text-primary"></i>{{ __('Buka Slip Gaji Otomatis') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 small" href="{{ route('attendance.excel', array_merge(request()->all(), ['download' => 'details'])) }}" target="_blank">
                            <i class="fa-solid fa-file-excel me-2 text-success"></i>{{ __('Download Rekap Excel') }}
                        </a>
                    </li>
                    @if($isAdmin)
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 small" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#salaryAdjustmentModal">
                                <i class="fa-solid fa-money-bill-transfer me-2 text-info"></i>{{ __('Input Bonus / Kasbon') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 small text-warning-emphasis fw-semibold" href="javascript:void(0)" onclick="confirmRecapFinance()">
                                <i class="fa-solid fa-money-bill-wave me-2 text-warning"></i>{{ __('Catat Gaji ke Kas / Keuangan') }}
                            </a>
                        </li>
                    @endif
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item py-2 small text-muted" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#guideCollapse">
                            <i class="fa-solid fa-circle-question me-2"></i>{{ __('Petunjuk & Panduan') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Panduan Penggunaan (Collapsible) --}}
    <div class="collapse mb-3" id="guideCollapse">
        <div class="alert alert-info border-0 shadow-sm rounded-3 py-3 mb-0">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="fw-bold mb-0 text-info-emphasis d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-info"></i>{{ __('Panduan Rekap Presensi & Payroll') }}
                </h6>
                <button type="button" class="btn-close btn-sm" data-bs-toggle="collapse" data-bs-target="#guideCollapse" aria-label="Close"></button>
            </div>
            <div class="row g-3 small">
                <div class="col-12 col-md-4">
                    <span class="fw-bold text-primary d-block mb-1"><i class="fa-solid fa-calendar-check me-1"></i> {{ __('Filter Periode') }}</span>
                    <span class="text-body-secondary">{{ __('Pilih Bulan untuk siklus bulanan, atau buka "Rentang Tanggal" untuk menentukan cut-off payroll (misal: 26 Des - 25 Jan).') }}</span>
                </div>
                <div class="col-12 col-md-4">
                    <span class="fw-bold text-success d-block mb-1"><i class="fa-solid fa-receipt me-1"></i> {{ __('Slip Gaji Otomatis') }}</span>
                    <span class="text-body-secondary">{{ __('Kalkulasi kompensasi kehadiran harian serta penambahan bonus dan pemotongan kasbon karyawan secara otomatis.') }}</span>
                </div>
                <div class="col-12 col-md-4">
                    <span class="fw-bold text-warning d-block mb-1"><i class="fa-solid fa-money-bill-wave me-1"></i> {{ __('Catat Gaji ke Keuangan') }}</span>
                    <span class="text-body-secondary">{{ __('Membukukan beban gaji karyawan ke buku kas dan jurnal pengeluaran modul Keuangan secara otomatis.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Single Container Utama (Card Modern Tanpa Kotak Bertumpuk) --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        
        {{-- 1. Status Filter Tabs (Satu Baris di Atas Tabel) --}}
        @php
            $statTabs = [
                ''        => ['label' => __('Semua'), 'count' => $attendances->total(), 'color' => 'secondary'],
                'present' => ['label' => __('Hadir'), 'count' => $stats['present'] ?? 0, 'color' => 'success'],
                'late'    => ['label' => __('Terlambat'), 'count' => $stats['late'] ?? 0, 'color' => 'warning'],
                'leave'   => ['label' => __('Cuti'), 'count' => $stats['leave'] ?? 0, 'color' => 'info'],
                'permit'  => ['label' => __('Izin'), 'count' => $stats['permit'] ?? 0, 'color' => 'primary'],
                'sick'    => ['label' => __('Sakit'), 'count' => $stats['sick'] ?? 0, 'color' => 'secondary'],
                'alpha'   => ['label' => __('Alpha'), 'count' => $stats['alpha'] ?? 0, 'color' => 'danger'],
            ];
            $currentStatus = (string) request('status', '');
        @endphp
        <div class="bg-light-subtle border-bottom px-3 pt-2">
            <ul class="nav nav-tabs border-bottom-0 gap-1 flex-nowrap overflow-x-auto" style="scrollbar-width: thin;">
                @foreach($statTabs as $stVal => $stMeta)
                    @php
                        $isActive = ($currentStatus === (string)$stVal);
                        $targetUrl = $stVal === '' 
                            ? route('attendance.index', request()->except('status')) 
                            : route('attendance.index', array_merge(request()->query(), ['status' => $stVal]));
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link py-2 px-3 small d-flex align-items-center gap-1.5 border-0 rounded-top-3 text-nowrap {{ $isActive ? 'active bg-white fw-bold text-primary shadow-xs' : 'text-body-secondary bg-transparent' }}" 
                           href="{{ $targetUrl }}">
                            <span>{{ $stMeta['label'] }}</span>
                            <span class="badge {{ $isActive ? 'bg-primary text-white' : 'bg-secondary-subtle text-secondary-emphasis' }} rounded-pill" style="font-size: 0.72rem;">
                                {{ number_format($stMeta['count']) }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- 2. Filter Toolbar (Satu Baris Efisien, Ramping & Bersih) --}}
        <div class="p-3 bg-white border-bottom">
            <form action="{{ route('attendance.index') }}" method="GET" id="attendanceFilterForm" class="m-0">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                
                <div class="row g-2 align-items-center">
                    {{-- Dropdown Karyawan --}}
                    <div class="col-12 col-md-4 col-xl-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-end-0 text-muted">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <select name="user_id" class="form-select form-select-sm border-start-0 ps-0 js-search-select" onchange="this.form.submit()">
                                <option value="">{{ __('Semua Karyawan') }}</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ (string)request('user_id') === (string)$tech->id ? 'selected' : '' }}>
                                        {{ $tech->employee?->full_name ?? $tech->name }} ({{ $tech->employee?->position ?? $tech->role->name ?? __('Staff') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Filter Bulan --}}
                    <div class="col-6 col-md-3 col-xl-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent border-end-0 text-muted">
                                <i class="fa-solid fa-calendar"></i>
                            </span>
                            <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm border-start-0 ps-0" onchange="this.form.submit()" title="{{ __('Pilih Bulan') }}">
                        </div>
                    </div>

                    {{-- Tombol Toggle Rentang Tanggal Khusus --}}
                    <div class="col-6 col-md-auto">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-nowrap {{ (request('start_date') || request('end_date')) ? 'active border-primary text-primary' : '' }}" 
                                data-bs-toggle="collapse" data-bs-target="#customDateRangeCollapse" aria-expanded="{{ (request('start_date') || request('end_date')) ? 'true' : 'false' }}">
                            <i class="fa-solid fa-sliders me-1"></i>
                            {{ (request('start_date') || request('end_date')) ? __('Rentang Khusus: Aktif') : __('Rentang Tanggal') }}
                        </button>
                    </div>

                    {{-- Tombol Reset --}}
                    @if(request('user_id') || request('status') || request('start_date') || request('end_date') || (request('month') && request('month') !== now()->format('Y-m')))
                        <div class="col-auto">
                            <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-danger px-2.5" title="{{ __('Reset Semua Filter') }}">
                                <i class="fa-solid fa-rotate-left me-1"></i>{{ __('Reset') }}
                            </a>
                        </div>
                    @endif

                    {{-- Bulk Delete Button (Muncul jika ada baris dicentang) --}}
                    @if($isAdmin)
                        <div class="col-auto ms-auto">
                            <button type="button" class="btn btn-danger btn-sm d-none shadow-xs text-nowrap" id="btnBulkDelete" onclick="submitBulkDelete()">
                                <i class="fa-regular fa-trash-can me-1"></i>{{ __('Hapus') }} (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Rentang Tanggal Khusus (Collapsible agar tidak memakan ruang kosong) --}}
                <div class="collapse {{ (request('start_date') || request('end_date')) ? 'show' : '' }} mt-2 pt-2 border-top" id="customDateRangeCollapse">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto small text-muted fw-semibold">
                            <i class="fa-solid fa-calendar-week me-1"></i>{{ __('Cut-Off Payroll:') }}
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm" placeholder="{{ __('Dari Tanggal') }}" title="{{ __('Dari Tanggal') }}">
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm" placeholder="{{ __('Sampai Tanggal') }}" title="{{ __('Sampai Tanggal') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fa-solid fa-check me-1"></i>{{ __('Terapkan') }}
                            </button>
                        </div>
                        @if(request('start_date') || request('end_date'))
                            <div class="col-auto">
                                <a href="{{ route('attendance.index', array_merge(request()->except(['start_date', 'end_date']))) }}" class="btn btn-link btn-sm text-danger text-decoration-none small p-0 ms-1">
                                    <i class="fa-solid fa-xmark me-1"></i>{{ __('Hapus Rentang') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- 3. Tabel Riwayat Presensi (Rapi, Bersih, Proporsional) --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 820px;">
                <thead class="bg-light text-uppercase x-small text-muted fw-semibold border-bottom">
                    <tr>
                        @if($isAdmin)
                            <th class="ps-3 py-3 text-center" style="width: 38px;">
                                <input type="checkbox" class="form-check-input" id="selectAllAttendance" onclick="toggleSelectAll()">
                            </th>
                        @endif
                        <th class="py-3 text-center" style="width: 45px;">{{ __('No') }}</th>
                        <th class="py-3">{{ __('Karyawan') }}</th>
                        <th class="py-3">{{ __('Tanggal') }}</th>
                        <th class="py-3 text-center">{{ __('Jam Masuk') }}</th>
                        <th class="py-3 text-center">{{ __('Jam Pulang') }}</th>
                        <th class="py-3 text-center">{{ __('Status') }}</th>
                        <th class="py-3 text-center">{{ __('Lokasi & Foto') }}</th>
                        @if($isAdmin)
                            <th class="text-end pe-3 py-3" style="width: 100px;">{{ __('Aksi') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($attendances as $index => $attendance)
                        <tr>
                            @if($isAdmin)
                                <td class="ps-3 text-center">
                                    <input type="checkbox" class="form-check-input attendance-select" value="{{ $attendance->id }}" onchange="updateBulkDeleteState()">
                                </td>
                            @endif
                            <td class="text-center text-muted small fw-medium">
                                {{ $attendances->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="avatar-sm rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; font-size: 0.82rem;">
                                        {{ strtoupper(substr($attendance->user->employee?->full_name ?? $attendance->user->name ?? 'K', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-body-emphasis lh-sm mb-0.5">
                                            {{ $attendance->user->employee?->full_name ?? $attendance->user->name }}
                                        </div>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                            <span class="x-small text-muted">{{ $attendance->user->employee?->position ?? $attendance->user->role?->name ?? __('Karyawan') }}</span>
                                            @php $shift = $attendance->shift_info; @endphp
                                            @if(!empty($shift['start']) && !empty($shift['end']))
                                                <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 0.68rem;" title="{{ __('Jadwal Shift') }}">
                                                    <i class="fa-regular fa-clock me-1"></i>{{ $shift['start'] }} - {{ $shift['end'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold text-body-emphasis d-block small">{{ ($attendance->work_date ?? $attendance->clock_in)?->translatedFormat('d M Y') }}</span>
                                <span class="x-small text-muted">{{ ($attendance->work_date ?? $attendance->clock_in)?->translatedFormat('l') }}</span>
                            </td>
                            <td class="text-center">
                                @if($attendance->clock_in)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-semibold font-monospace" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>{{ $attendance->clock_in->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-muted small">--:--</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($attendance->clock_out)
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1 fw-semibold font-monospace" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-arrow-right-from-bracket me-1"></i>{{ $attendance->clock_out->format('H:i') }}
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 small fw-medium">
                                        <i class="fa-regular fa-clock me-1"></i>{{ __('Belum Pulang') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
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
                                <span class="badge bg-{{ $stBadge['bg'] }}-subtle text-{{ $stBadge['bg'] }}-emphasis border border-{{ $stBadge['bg'] }}-subtle px-2.5 py-1 rounded-pill small fw-semibold">
                                    {{ $stBadge['text'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    {{-- Maps Link --}}
                                    @if($attendance->lat_clock_in && $attendance->lng_clock_in)
                                        <a href="https://maps.google.com/?q={{ $attendance->lat_clock_in }},{{ $attendance->lng_clock_in }}" target="_blank" class="btn btn-sm btn-outline-primary p-0 d-inline-flex align-items-center justify-content-center rounded-2" style="width: 26px; height: 26px;" title="{{ __('Lihat Lokasi GPS') }}">
                                            <i class="fa-solid fa-location-dot" style="font-size: 0.72rem;"></i>
                                        </a>
                                    @endif
                                    {{-- Photo Preview --}}
                                    @if($attendance->photo_clock_in)
                                        <a href="{{ Storage::url($attendance->photo_clock_in) }}" target="_blank" title="{{ __('Foto Masuk') }}">
                                            <img src="{{ Storage::url($attendance->photo_clock_in) }}" class="rounded border shadow-xs object-fit-cover" style="width: 26px; height: 26px;" alt="In" loading="lazy">
                                        </a>
                                    @endif
                                    @if($attendance->photo_clock_out)
                                        <a href="{{ Storage::url($attendance->photo_clock_out) }}" target="_blank" title="{{ __('Foto Pulang') }}">
                                            <img src="{{ Storage::url($attendance->photo_clock_out) }}" class="rounded border shadow-xs object-fit-cover" style="width: 26px; height: 26px;" alt="Out" loading="lazy">
                                        </a>
                                    @endif
                                    @if(!$attendance->lat_clock_in && !$attendance->photo_clock_in && !$attendance->photo_clock_out)
                                        <span class="text-muted small">-</span>
                                    @endif
                                </div>
                            </td>
                            @if($isAdmin)
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        {{-- WhatsApp Notification --}}
                                        <form method="POST" action="{{ route('attendance.notify', $attendance) }}" class="form-whatsapp d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm p-1 px-2 rounded-2" title="{{ __('Kirim WhatsApp') }}">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </button>
                                        </form>
                                        {{-- Delete Single Record --}}
                                        <form method="POST" action="{{ route('attendance.destroy', $attendance->id) }}" class="form-delete d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2 rounded-2" title="{{ __('Hapus Presensi') }}">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 9 : 7 }}" class="text-center py-5">
                                <div class="py-4">
                                    <div class="rounded-circle bg-light text-muted p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                                        <i class="fa-regular fa-folder-open fs-4"></i>
                                    </div>
                                    <h6 class="fw-semibold text-body-emphasis mb-1">{{ __('Tidak ada data absensi') }}</h6>
                                    <p class="text-muted small mb-0">{{ __('Belum ada riwayat absensi yang cocok dengan kriteria filter saat ini.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Footer --}}
        @if($attendances->hasPages())
            <div class="p-3 bg-white border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="small text-muted">
                    {{ __('Menampilkan :from - :to dari :total data', ['from' => $attendances->firstItem() ?? 0, 'to' => $attendances->lastItem() ?? 0, 'total' => $attendances->total()]) }}
                </span>
                <div>
                    {{ $attendances->appends(request()->query())->links('pagination::bootstrap-5') }}
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
                <h5 class="modal-title fw-bold text-body-emphasis">
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
                <h5 class="modal-title fw-bold text-body-emphasis">
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
