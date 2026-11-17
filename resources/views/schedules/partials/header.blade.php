{{-- ============================================ 
     PAGE HEADER & ACTION TOOLBAR 
     ============================================ --}}
@php
    $currentMode    = $mode ?? 'weekly';
    $currentGroup   = $selectedGroup ?? 'all';
    $currentShift   = $selectedShift ?? 'all';
    $canManage      = Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']) || Auth::user()->hasPermission('schedule.manage');
    $exportParams   = array_filter([
        'month'      => $month,
        'year'       => $year,
        'mode'       => $currentMode,
        'group'      => $selectedGroup !== 'all' ? $selectedGroup : null,
        'shift'      => $selectedShift !== 'all' ? $selectedShift : null,
        'start_date' => ($currentMode === 'daily' && isset($dailyRangeStart)) ? $dailyRangeStart->format('Y-m-d') : null,
        'end_date'   => ($currentMode === 'daily' && isset($dailyRangeEnd)) ? $dailyRangeEnd->format('Y-m-d') : null,
    ]);
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="fa-solid fa-calendar-days text-primary me-2"></i>{{ __('Jadwal Shift Karyawan') }}
        </h4>
        <p class="text-body-secondary small mb-0">
            {{ __('Atur dan pantau jadwal kerja operasional, penugasan shift mingguan/harian, serta hari libur (Off) staf.') }}
        </p>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2">
        {{-- Mode Switcher (Mingguan vs Harian) --}}
        <div class="btn-group btn-group-sm shadow-sm" role="group">
            <a href="{{ route('schedules.index', array_filter([
                'month' => $month, 'year' => $year, 'mode' => 'weekly',
                'group' => $selectedGroup !== 'all' ? $selectedGroup : null,
                'shift' => $selectedShift !== 'all' ? $selectedShift : null,
            ])) }}" 
               class="btn btn-sm {{ $currentMode === 'weekly' ? 'btn-primary active fw-semibold' : 'btn-outline-primary' }}">
                <i class="fa-solid fa-calendar-week me-1"></i>{{ __('Mingguan') }}
            </a>
            <a href="{{ route('schedules.index', array_filter([
                'month' => $month, 'year' => $year, 'mode' => 'daily',
                'group' => $selectedGroup !== 'all' ? $selectedGroup : null,
                'shift' => $selectedShift !== 'all' ? $selectedShift : null,
                'start_date' => isset($dailyRangeStart) ? $dailyRangeStart->format('Y-m-d') : null,
                'end_date' => isset($dailyRangeEnd) ? $dailyRangeEnd->format('Y-m-d') : null,
            ])) }}" 
               class="btn btn-sm {{ $currentMode === 'daily' ? 'btn-primary active fw-semibold' : 'btn-outline-primary' }}">
                <i class="fa-solid fa-calendar-day me-1"></i>{{ __('Harian') }}
            </a>
        </div>

        {{-- Export Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-file-export me-1"></i> {{ __('Ekspor') }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li>
                    <a class="dropdown-item py-2" href="{{ route('schedules.export.pdf', $exportParams) }}" target="_blank">
                        <i class="fa-regular fa-file-pdf text-danger me-2"></i> {{ __('Unduh PDF') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="{{ route('schedules.export.excel', $exportParams) }}">
                        <i class="fa-regular fa-file-excel text-success me-2"></i> {{ __('Unduh Excel') }}
                    </a>
                </li>
            </ul>
        </div>

        {{-- Admin Management Actions --}}
        @if($canManage)
            <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#importScheduleModal" title="Import Jadwal dari Excel">
                <i class="fa-solid fa-file-import me-1"></i> {{ __('Import') }}
            </button>
            @if($currentMode === 'daily')
                <button type="button" class="btn btn-dark btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#autoDailyScheduleModal" title="Otomatis buat jadwal harian">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> {{ __('Auto Generate') }}
                </button>
            @else
                <button type="button" class="btn btn-dark btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#autoScheduleModal" title="Otomatis buat jadwal mingguan">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> {{ __('Auto Generate') }}
                </button>
            @endif
        @endif
    </div>
</div>

{{-- ============================================ 
     FILTER CARD 
     ============================================ --}}
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body p-3 p-md-4">
        <form action="{{ route('schedules.index') }}" method="GET" class="schedule-filter-form">
            <input type="hidden" name="mode" value="{{ $currentMode }}">

            <div class="row g-2 g-md-3 align-items-end">
                <div class="col-6 col-sm-4 col-lg-2">
                    <label class="form-label small fw-semibold text-body-secondary mb-1">
                        <i class="fa-regular fa-calendar me-1"></i>{{ __('Bulan') }}
                    </label>
                    <select name="month" class="form-select form-select-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-6 col-sm-4 col-lg-2">
                    <label class="form-label small fw-semibold text-body-secondary mb-1">
                        <i class="fa-regular fa-calendar-check me-1"></i>{{ __('Tahun') }}
                    </label>
                    <select name="year" class="form-select form-select-sm">
                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                @if($currentMode === 'daily')
                    <div class="col-6 col-sm-4 col-lg-2">
                        <label class="form-label small fw-semibold text-body-secondary mb-1">
                            <i class="fa-solid fa-play me-1"></i>{{ __('Dari Tanggal') }}
                        </label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                               value="{{ isset($dailyRangeStart) ? $dailyRangeStart->format('Y-m-d') : '' }}">
                    </div>

                    <div class="col-6 col-sm-4 col-lg-2">
                        <label class="form-label small fw-semibold text-body-secondary mb-1">
                            <i class="fa-solid fa-stop me-1"></i>{{ __('Sampai Tanggal') }}
                        </label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                               value="{{ isset($dailyRangeEnd) ? $dailyRangeEnd->format('Y-m-d') : '' }}">
                    </div>
                @endif

                <div class="col-6 col-sm-4 {{ $currentMode === 'daily' ? 'col-lg-2' : 'col-lg-3' }}">
                    <label class="form-label small fw-semibold text-body-secondary mb-1">
                        <i class="fa-solid fa-users-viewfinder me-1"></i>{{ __('Divisi / Grup') }}
                    </label>
                    <select name="group" class="form-select form-select-sm">
                        <option value="all" {{ $currentGroup === 'all' ? 'selected' : '' }}>{{ __('Semua Divisi') }}</option>
                        <option value="teknisi" {{ $currentGroup === 'teknisi' ? 'selected' : '' }}>{{ __('Shift Teknisi') }}</option>
                        <option value="wash" {{ $currentGroup === 'wash' ? 'selected' : '' }}>{{ __('Operator Wash') }}</option>
                        <option value="lainnya" {{ $currentGroup === 'lainnya' ? 'selected' : '' }}>{{ __('Divisi Lainnya') }}</option>
                    </select>
                </div>

                <div class="col-6 col-sm-4 {{ $currentMode === 'daily' ? 'col-lg-2' : 'col-lg-3' }}">
                    <label class="form-label small fw-semibold text-body-secondary mb-1">
                        <i class="fa-solid fa-clock me-1"></i>{{ __('Status Shift') }}
                    </label>
                    <select name="shift" class="form-select form-select-sm">
                        <option value="all" {{ $currentShift === 'all' ? 'selected' : '' }}>{{ __('Semua Shift') }}</option>
                        <option value="piket" {{ $currentShift === 'piket' ? 'selected' : '' }}>Shift 1 (Piket)</option>
                        <option value="backup" {{ $currentShift === 'backup' ? 'selected' : '' }}>Shift 2 (Backup)</option>
                        <option value="longshift" {{ $currentShift === 'longshift' ? 'selected' : '' }}>Longshift</option>
                        <option value="off" {{ $currentShift === 'off' ? 'selected' : '' }}>Off (Libur)</option>
                    </select>
                </div>

                <div class="col-12 col-sm-auto d-flex gap-2 ms-auto mt-2 mt-md-0">
                    <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">
                        <i class="fa-solid fa-filter me-1"></i>{{ __('Terapkan') }}
                    </button>
                    <a href="{{ route('schedules.index', ['month' => $month, 'year' => $year, 'mode' => $currentMode]) }}"
                       class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                        <i class="fa-solid fa-rotate-left me-1"></i>{{ __('Reset') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
