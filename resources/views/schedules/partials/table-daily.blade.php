{{-- ========== DAILY MODE ========== --}}
@php
    $rangeStart = isset($dailyRangeStart) 
        ? $dailyRangeStart->copy() 
        : \Carbon\Carbon::createFromDate((int) $year, (int) $month, 1)->startOfDay();
    $rangeEnd = isset($dailyRangeEnd) 
        ? $dailyRangeEnd->copy() 
        : \Carbon\Carbon::createFromDate((int) $year, (int) $month, 1)->endOfMonth()->startOfDay();
    
    $days = [];
    for ($d = $rangeStart->copy(); $d->lte($rangeEnd); $d->addDay()) {
        $days[] = $d->copy();
    }
    $canManage = Auth::user()->hasAnyRole(['super-admin', 'manager', 'finance']) || Auth::user()->hasPermission('schedule.manage');
@endphp

{{-- Active Date Range & Horizontal Scroll Info Bar --}}
<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between px-3 py-2 bg-light bg-opacity-75 border-bottom text-muted small gap-2">
    <div>
        <i class="fa-regular fa-calendar-days text-primary me-1"></i>
        <span>{{ __('Rentang Tanggal Aktif:') }}</span>
        <strong class="text-dark font-monospace">{{ $rangeStart->translatedFormat('d M Y') }} — {{ $rangeEnd->translatedFormat('d M Y') }}</strong>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2">{{ count($days) }} {{ __('Hari') }}</span>
    </div>
    <div class="d-none d-md-block">
        <i class="fa-solid fa-arrows-left-right text-muted me-1"></i>{{ __('Geser / scroll horizontal untuk melihat seluruh tanggal') }}
    </div>
</div>

{{-- Bulk Selection / Multi-Set Bar --}}
@if($canManage)
<div class="bulk-set-bar bg-light border-bottom p-3">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-wand-magic-sparkles text-primary"></i>
            <span class="fw-bold small">Set Masal:</span>
        </div>
        
        <div style="min-width: 200px;">
            <select id="bulkTechSelect" class="form-select form-select-sm select2-tech">
                <option value="">-- Pilih Teknisi --</option>
                <option value="all">-- Semua Teknisi --</option>
                @foreach($groups as $group)
                    @if(($group['users'] ?? collect())->count() > 0)
                        <optgroup label="{{ $group['label'] }}">
                            @foreach($group['users'] as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="d-flex align-items-center gap-2">
            <input type="date" id="bulkDateStart" class="form-control form-control-sm" value="{{ $rangeStart->format('Y-m-d') }}">
            <span class="text-muted small">s/d</span>
            <input type="date" id="bulkDateEnd" class="form-control form-control-sm" value="{{ $rangeEnd->format('Y-m-d') }}">
        </div>

        <div style="width: 120px;">
            <select id="bulkShiftSelect" class="form-select form-select-sm">
                <option value="{{ \App\Models\TechnicianDailySchedule::STATUS_OFF }}">OFF</option>
                <option value="{{ \App\Models\TechnicianDailySchedule::STATUS_PIKET }}">S1 (Piket)</option>
                <option value="{{ \App\Models\TechnicianDailySchedule::STATUS_BACKUP }}">S2 (Backup)</option>
                <option value="{{ \App\Models\TechnicianDailySchedule::STATUS_LONGSHIFT }}">LS (Long)</option>
            </select>
        </div>

        <button type="button" id="btnApplyBulkSet" class="btn btn-primary btn-sm px-3">
            <i class="fa-solid fa-bolt me-1"></i>Terapkan Ke Tabel
        </button>
        
        <div class="text-muted small ms-2">
            <i class="fa-solid fa-info-circle me-1"></i>Klik "Simpan Grup" di bawah setelah selesai mengatur.
        </div>
    </div>
</div>
@endif

{{-- Daily Schedule Tables by Group --}}
@if($canManage)
    <form action="{{ route('schedules.daily.bulkStore') }}" method="POST" id="bulkDailyScheduleForm">
        @csrf
@endif

@foreach($groups as $group)
    @if(($group['users'] ?? collect())->count() > 0)
        <div class="schedule-group-section mb-4">
            <div class="schedule-group-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="group-badge">{{ ($group['users'] ?? collect())->count() }}</span>
                    <span class="group-label">{{ $group['label'] }}</span>
                </div>
                @if($canManage)
                    <button type="submit" class="btn btn-success btn-xs shadow-sm">
                        <i class="fa-solid fa-save me-1"></i>Simpan Grup Ini
                    </button>
                @endif
            </div>
            
            <div class="table-responsive schedule-daily-scroll">
                <table class="table table-hover align-middle schedule-month-table schedule-daily-table mb-0">
                    <thead>
                        <tr>
                            <th class="schedule-name-col">
                                <span class="small text-muted fw-semibold">Karyawan</span>
                            </th>
                            @foreach($days as $day)
                                @php $isToday = $day->isToday(); @endphp
                                <th class="schedule-day-col text-center {{ $day->isSunday() ? 'sunday-col' : '' }} {{ $isToday ? 'today-col' : '' }}">
                                    <div class="day-number">{{ $day->translatedFormat('d') }}</div>
                                    <div class="day-name {{ $day->isSunday() ? 'text-danger' : '' }}">{{ $day->translatedFormat('D') }}</div>
                                    @if($isToday) <div class="today-badge">TODAY</div> @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group['users'] as $tech)
                            <tr class="schedule-row">
                                <td class="schedule-name-col">
                                    <div class="employee-name">{{ $tech->schedule_name ?? $tech->name }}</div>
                                    <div class="employee-info">
                                        {{ $tech->schedule_position ?? ($tech->role?->label ?? 'Karyawan') }}
                                        @if(!empty($tech->schedule_department))
                                            <span class="employee-divider">•</span>{{ $tech->schedule_department }}
                                        @endif
                                    </div>
                                </td>
                                @foreach($days as $day)
                                    @php
                                        $dayKey = $day->format('Y-m-d');
                                        $row = $dailySchedules->get($dayKey)?->get($tech->id);
                                        $status = $row?->status ?? \App\Models\TechnicianDailySchedule::STATUS_OFF;
                                        $cellClass = match($status) {
                                            \App\Models\TechnicianDailySchedule::STATUS_PIKET  => 'shift-cell piket',
                                            \App\Models\TechnicianDailySchedule::STATUS_BACKUP => 'shift-cell backup',
                                            \App\Models\TechnicianDailySchedule::STATUS_LONGSHIFT => 'shift-cell longshift',
                                            default  => 'shift-cell off'
                                        };
                                    @endphp
                                    <td class="text-center {{ $day->isSunday() ? 'sunday-col' : '' }} {{ $day->isToday() ? 'today-col' : '' }}">
                                        @if($canManage)
                                            <select name="schedules[{{ $tech->id }}][{{ $dayKey }}]" class="form-select form-select-sm shift-select {{ $cellClass }}">
                                                <option value="{{ \App\Models\TechnicianDailySchedule::STATUS_OFF }}" {{ $status === \App\Models\TechnicianDailySchedule::STATUS_OFF ? 'selected' : '' }}>OFF</option>
                                                <option value="{{ \App\Models\TechnicianDailySchedule::STATUS_PIKET }}" {{ $status === \App\Models\TechnicianDailySchedule::STATUS_PIKET ? 'selected' : '' }}>S1</option>
                                                <option value="{{ \App\Models\TechnicianDailySchedule::STATUS_BACKUP }}" {{ $status === \App\Models\TechnicianDailySchedule::STATUS_BACKUP ? 'selected' : '' }}>S2</option>
                                                <option value="{{ \App\Models\TechnicianDailySchedule::STATUS_LONGSHIFT }}" {{ $status === \App\Models\TechnicianDailySchedule::STATUS_LONGSHIFT ? 'selected' : '' }}>LS</option>
                                            </select>
                                        @else
                                            <span class="shift-badge {{ str_contains($cellClass, 'piket') ? 'piket' : (str_contains($cellClass, 'backup') ? 'backup' : (str_contains($cellClass, 'longshift') ? 'longshift' : 'off')) }}">
                                                {{ $status === \App\Models\TechnicianDailySchedule::STATUS_PIKET ? 'S1' : ($status === \App\Models\TechnicianDailySchedule::STATUS_BACKUP ? 'S2' : ($status === \App\Models\TechnicianDailySchedule::STATUS_LONGSHIFT ? 'LS' : 'Off')) }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endforeach

@if($canManage)
    </form>
@endif
