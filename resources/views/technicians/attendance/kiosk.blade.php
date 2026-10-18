@extends('layouts.app')

@section('title', __('Kiosk Presensi Karyawan'))

@push('styles')
<style>
    /* Kiosk Dashboard Styling */
    .kiosk-container {
        max-width: 1300px;
        margin: 0 auto;
    }
    .kiosk-clock-box {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(13, 110, 253, 0.3);
    }
    .kiosk-digital-clock {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 800;
        letter-spacing: 2px;
    }
    .animate-pulse {
        animation: pulseAnimation 1.8s infinite;
    }
    @keyframes pulseAnimation {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.55; transform: scale(1.02); }
    }
    
    /* Viewfinder Camera Box */
    .scanner-viewport-wrapper {
        position: relative;
        background: #111827;
        border-radius: 1rem;
        overflow: hidden;
        min-height: 280px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);
    }
    #reader {
        width: 100% !important;
        background: transparent !important;
        border: none !important;
    }
    #reader video {
        border-radius: 0.85rem;
        object-fit: cover;
    }
    .scanner-corner {
        position: absolute;
        width: 32px;
        height: 32px;
        border-color: #10b981;
        border-style: solid;
        pointer-events: none;
        z-index: 5;
    }
    .scanner-corner.top-left { top: 18px; left: 18px; border-width: 4px 0 0 4px; border-top-left-radius: 8px; }
    .scanner-corner.top-right { top: 18px; right: 18px; border-width: 4px 4px 0 0; border-top-right-radius: 8px; }
    .scanner-corner.bottom-left { bottom: 18px; left: 18px; border-width: 0 0 4px 4px; border-bottom-left-radius: 8px; }
    .scanner-corner.bottom-right { bottom: 18px; right: 18px; border-width: 0 4px 4px 0; border-bottom-right-radius: 8px; }
    
    .scanner-laser {
        position: absolute;
        left: 10%;
        right: 10%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #10b981, transparent);
        box-shadow: 0 0 10px #10b981;
        top: 20%;
        animation: laserScan 2.5s ease-in-out infinite alternate;
        pointer-events: none;
        z-index: 4;
    }
    @keyframes laserScan {
        from { top: 15%; opacity: 0.3; }
        to { top: 85%; opacity: 1; }
    }

    /* Input & Sensor Card */
    .sensor-input-wrap {
        border: 2px solid #10b981;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.15);
        transition: all 0.25s ease-in-out;
    }
    .sensor-input-wrap:focus-within {
        border-color: #059669;
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
    }

    /* Recent Success Card */
    .recent-user-badge {
        transition: transform 0.2s ease;
    }
    .recent-user-badge:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div class="kiosk-container py-3">
    {{-- Header Section --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-tablet-screen-button me-1"></i>{{ __('Kiosk Terminal Mandiri') }}
                </span>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-wifi me-1"></i>{{ __('Sistem Siap (Online)') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Kiosk Presensi Karyawan') }}</h1>
            <p class="text-muted small mb-0">{{ __('Scan ID Card atau ketik kode NIK/RFID untuk mencatat absensi masuk dan pulang otomatis.') }}</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('attendance.daily') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-calendar-day me-1"></i>{{ __('Rekap Harian') }}
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm shadow-sm" id="btnToggleFullscreen" title="{{ __('Layar Penuh') }}">
                <i class="fa-solid fa-expand me-1"></i><span class="d-none d-sm-inline">{{ __('Layar Penuh') }}</span>
            </button>
            <a href="{{ route('attendance.kiosk') }}" class="btn btn-primary btn-sm shadow-sm" title="{{ __('Refresh Data') }}">
                <i class="fa-solid fa-rotate me-1"></i><span class="d-none d-sm-inline">{{ __('Refresh') }}</span>
            </a>
        </div>
    </div>

    {{-- Baris Jam Digital & KPI Cards --}}
    <div class="row g-3 mb-4">
        {{-- Live Clock Widget --}}
        <div class="col-12 col-lg-5">
            <div class="kiosk-clock-box p-3 p-md-4 h-100 d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase small fw-bold opacity-75">
                        <i class="fa-regular fa-clock me-1"></i>{{ __('Waktu Real-Time') }}
                    </span>
                    <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1 small">WIB (UTC+7)</span>
                </div>
                <div class="text-center my-2">
                    <div class="display-5 kiosk-digital-clock" id="kioskClockDisplay">00:00:00</div>
                    <div class="fs-6 opacity-90 mt-1" id="kioskDateDisplay">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-white border-opacity-25 small opacity-90">
                    <span><i class="fa-solid fa-store me-1"></i>{{ auth()->user()->company->name ?? 'MStore Business' }}</span>
                    <span><i class="fa-solid fa-shield-halved me-1"></i>Deteksi Otomatis</span>
                </div>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="col-12 col-lg-7">
            <div class="row g-3 h-100">
                <div class="col-6 col-sm-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-primary">
                        <div class="card-body p-3">
                            <span class="text-muted text-uppercase x-small fw-bold">{{ __('Total Hadir') }}</span>
                            <h3 class="fw-bold mb-0 text-primary mt-1" id="statTotalPresent">{{ $stats['total_present'] ?? 0 }}</h3>
                            <div class="text-muted small mt-1">Karyawan</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-success">
                        <div class="card-body p-3">
                            <span class="text-muted text-uppercase x-small fw-bold">{{ __('Tepat Waktu') }}</span>
                            <h3 class="fw-bold mb-0 text-success mt-1" id="statOnTime">{{ $stats['on_time'] ?? 0 }}</h3>
                            <div class="text-muted small mt-1">Disiplin</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-warning">
                        <div class="card-body p-3">
                            <span class="text-muted text-uppercase x-small fw-bold">{{ __('Terlambat') }}</span>
                            <h3 class="fw-bold mb-0 text-warning mt-1" id="statLate">{{ $stats['late'] ?? 0 }}</h3>
                            <div class="text-muted small mt-1">> Toleransi</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-info">
                        <div class="card-body p-3">
                            <span class="text-muted text-uppercase x-small fw-bold">{{ __('Sudah Pulang') }}</span>
                            <h3 class="fw-bold mb-0 text-info mt-1" id="statClockedOut">{{ $stats['clocked_out'] ?? 0 }}</h3>
                            <div class="text-muted small mt-1">Clock-Out</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Scanner & Input Workspace --}}
    <div class="row g-4 mb-4">
        {{-- Kolom Kiri: Kamera Scanner --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                    <div class="fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-camera text-primary"></i>
                        <span>{{ __('Kamera QR & Barcode') }}</span>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle small" id="camStatusBadge">
                        <i class="fa-solid fa-circle-dot me-1"></i>Standby
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    {{-- Viewfinder Container --}}
                    <div class="scanner-viewport-wrapper">
                        <div class="scanner-corner top-left"></div>
                        <div class="scanner-corner top-right"></div>
                        <div class="scanner-corner bottom-left"></div>
                        <div class="scanner-corner bottom-right"></div>
                        <div class="scanner-laser"></div>

                        <div id="reader"></div>

                        <div id="noCamFallback" class="text-center text-white-50 p-4 d-none">
                            <i class="fa-solid fa-camera-slash display-4 mb-2 d-block"></i>
                            <div class="small fw-semibold text-white">Kamera Belum Aktif / Tidak Terdeteksi</div>
                            <div class="x-small text-muted mt-1">Gunakan scanner RFID, barcode gun, atau input manual di samping.</div>
                        </div>
                    </div>

                    {{-- Camera Controls --}}
                    <div class="mt-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fa-solid fa-video text-muted"></i>
                            </span>
                            <select id="cameraSelect" class="form-select border-start-0" aria-label="Pilih Kamera"></select>
                            <button id="switchCameraBtn" class="btn btn-outline-secondary px-3" type="button" title="{{ __('Ganti Kamera') }}">
                                <i class="fa-solid fa-arrows-rotate me-1"></i>{{ __('Ganti') }}
                            </button>
                        </div>
                        <div class="form-text x-small text-muted mt-2 d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-circle-info me-1"></i>Arahkan QR Code ID Card ke tengah kotak.</span>
                            <span id="soundToggleBtn" class="text-primary text-decoration-none cursor-pointer" role="button">
                                <i class="fa-solid fa-volume-high me-1"></i>Suara: Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Card Reader & Feedback Hasil Absensi --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                    <div class="fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-id-card-clip text-success"></i>
                        <span>{{ __('Sensor ID Card & Input RFID') }}</span>
                    </div>
                    <span class="badge bg-success rounded-pill px-3 py-1 small animate-pulse">
                        <i class="fa-solid fa-bolt me-1"></i>Sensor Siap
                    </span>
                </div>
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                    <div>
                        {{-- Form Input / RFID Tap --}}
                        <form id="manualScanForm" onsubmit="event.preventDefault();">
                            <label class="form-label fw-bold text-dark small mb-2 d-flex justify-content-between align-items-center">
                                <span>{{ __('Scan ID Card / Input Kode NIK') }}</span>
                                <span class="text-muted fw-normal x-small">Tekan Enter otomatis setelah scan</span>
                            </label>
                            <div class="input-group input-group-lg rounded-4 overflow-hidden sensor-input-wrap">
                                <span class="input-group-text bg-white border-0 text-success ps-3">
                                    <i class="fa-solid fa-barcode fs-3"></i>
                                </span>
                                <input type="text" id="manualCode" class="form-control border-0 py-3 fs-5 fw-bold text-dark" placeholder="Tempel kartu RFID atau scan barcode..." autofocus autocomplete="off">
                                <button type="button" class="btn btn-success px-4 fw-semibold" id="btnSubmitScan">
                                    <i class="fa-solid fa-arrow-right me-1"></i>{{ __('Proses') }}
                                </button>
                            </div>
                        </form>

                        {{-- Status & Quick Instructions Pills --}}
                        <div class="d-flex flex-wrap gap-2 align-items-center mt-3 pt-1">
                            <span class="badge bg-light text-muted border py-2 px-3 fw-normal small">
                                <i class="fa-solid fa-right-to-bracket text-success me-1"></i><strong>Masuk:</strong> Otomatis (Pertama kali tap)
                            </span>
                            <span class="badge bg-light text-muted border py-2 px-3 fw-normal small">
                                <i class="fa-solid fa-right-from-bracket text-danger me-1"></i><strong>Pulang:</strong> Otomatis (Tap berikutnya)
                            </span>
                            <span class="badge bg-light text-muted border py-2 px-3 fw-normal small">
                                <i class="fa-solid fa-hourglass-half text-warning me-1"></i><strong>Cooldown:</strong> Min. 5 Menit
                            </span>
                        </div>
                    </div>

                    {{-- Dynamic Feedback Card (Hasil Scan Terakhir) --}}
                    <div class="mt-4 pt-3 border-top" id="recentScanContainer">
                        <div class="text-uppercase x-small fw-bold text-muted mb-2">
                            <i class="fa-solid fa-bell me-1 text-primary"></i>{{ __('Hasil Aktivitas Terakhir') }}
                        </div>

                        <div id="recentScanEmpty" class="border rounded-4 p-4 text-center bg-light-subtle">
                            <i class="fa-solid fa-id-card text-muted opacity-50 display-6 mb-2 d-block"></i>
                            <div class="fw-semibold text-secondary small">{{ __('Belum Ada Kartu yang Discan') }}</div>
                            <div class="text-muted x-small mt-1">{{ __('Tempelkan ID Card karyawan pada scanner untuk memulai pencatatan presensi.') }}</div>
                        </div>

                        <div id="recentScanSuccessCard" class="card border-0 shadow-sm rounded-4 p-3 border-start border-4 border-success bg-success bg-opacity-10 d-none recent-user-badge">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 50px; height: 50px;" id="resUserInitial">
                                        US
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-gray-900" id="resUserName">-</h5>
                                        <div class="text-muted small mt-1" id="resUserMessage">Absensi berhasil tercatat.</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success fs-6 px-3 py-2 rounded-pill fw-bold" id="resUserAction">CLOCK IN</span>
                                    <div class="small fw-semibold text-gray-700 mt-1" id="resUserTime">00:00:00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Riwayat Absensi Real-Time Hari Ini --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-primary"></i>
                    <span>{{ __('Log Presensi Karyawan Hari Ini') }}</span>
                    <span class="badge bg-secondary-subtle text-secondary small fw-normal ms-1" id="logCountBadge">{{ $todayLogs->count() }} Record</span>
                </h6>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="filterLogInput" class="form-control border-start-0 ps-0" placeholder="Cari nama karyawan...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="todayLogTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 60px;">#</th>
                        <th>{{ __('Karyawan') }}</th>
                        <th>{{ __('Jam Masuk') }}</th>
                        <th>{{ __('Jam Pulang') }}</th>
                        <th>{{ __('Status Presensi') }}</th>
                        <th class="text-end pe-4">{{ __('Keterangan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($todayLogs as $index => $row)
                        @php
                            $status = strtolower($row->status ?? 'present');
                            $badgeClass = match($status) {
                                'present' => 'bg-success text-white',
                                'late' => 'bg-warning text-dark',
                                'alpha' => 'bg-danger text-white',
                                'sick', 'permission' => 'bg-info text-white',
                                default => 'bg-secondary text-white'
                            };
                            $statusLabel = match($status) {
                                'present' => 'Tepat Waktu',
                                'late' => 'Terlambat',
                                'alpha' => 'Alpha',
                                'sick' => 'Sakit',
                                'permission' => 'Izin',
                                default => strtoupper($status)
                            };
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted small">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                        {{ strtoupper(substr($row->user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $row->user->name ?? '-' }}</div>
                                        <div class="text-muted x-small">{{ $row->user->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($row->clock_in)
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                        <i class="fa-regular fa-clock text-success me-1"></i>{{ $row->clock_in->format('H:i:s') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($row->clock_out)
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                        <i class="fa-regular fa-clock text-danger me-1"></i>{{ $row->clock_out->format('H:i:s') }}
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1">
                                        <i class="fa-solid fa-spinner fa-spin me-1"></i>Sedang Bekerja
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill fw-semibold">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="text-end pe-4 text-muted small">
                                {{ $row->notes ?: 'Kiosk Scan' }}
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-calendar-xmark display-6 mb-2 d-block text-secondary opacity-50"></i>
                                <div class="fw-semibold">{{ __('Belum Ada Aktivitas Presensi Hari Ini') }}</div>
                                <div class="x-small">{{ __('Data akan langsung muncul secara otomatis begitu karyawan melakukan scan ID Card.') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- 1. Live Digital Clock ---
    const clockEl = document.getElementById('kioskClockDisplay');
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        if (clockEl) {
            clockEl.textContent = `${h}:${m}:${s}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    // --- 2. Fullscreen Toggle ---
    const btnFullscreen = document.getElementById('btnToggleFullscreen');
    if (btnFullscreen) {
        btnFullscreen.addEventListener('click', function () {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.warn(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        });
    }

    // --- 3. Sound Audio Synthesis ---
    let soundEnabled = true;
    const soundToggle = document.getElementById('soundToggleBtn');
    if (soundToggle) {
        soundToggle.addEventListener('click', function () {
            soundEnabled = !soundEnabled;
            soundToggle.innerHTML = soundEnabled 
                ? '<i class="fa-solid fa-volume-high me-1"></i>Suara: Aktif' 
                : '<i class="fa-solid fa-volume-xmark me-1 text-danger"></i>Suara: Bisu';
        });
    }

    const playBeep = (type = 'success') => {
        if (!soundEnabled) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            if (type === 'success') {
                // Two-tone chime
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                osc.frequency.setValueAtTime(880, ctx.currentTime + 0.1); // A5
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                osc.start();
                osc.stop(ctx.currentTime + 0.35);
            } else {
                // Low buzz
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(220, ctx.currentTime);
                gain.gain.setValueAtTime(0.2, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                osc.start();
                osc.stop(ctx.currentTime + 0.4);
            }
        } catch (e) {
            // Audio context not allowed without interaction
        }
    };

    // --- 4. Elements & State ---
    const inputEl = document.getElementById('manualCode');
    const tableBody = document.querySelector('#todayLogTable tbody');
    const camSelect = document.getElementById('cameraSelect');
    const switchCamBtn = document.getElementById('switchCameraBtn');
    const camBadge = document.getElementById('camStatusBadge');
    const noCamBox = document.getElementById('noCamFallback');
    const btnSubmit = document.getElementById('btnSubmitScan');

    let scanLock = false;
    let html5QrCode = null;

    // --- 5. Submit Scan Core Function ---
    const submitScan = async (code) => {
        if (!code || scanLock) return;
        scanLock = true;

        Swal.fire({
            title: 'Memverifikasi ID Card...',
            html: `<div class="badge bg-primary px-3 py-2 font-monospace fs-6">${code}</div><div class="small text-muted mt-2">Mengecek otorisasi kehadiran...</div>`,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const resp = await fetch("{{ route('attendance.kiosk.scan') }}", {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}" 
                },
                body: JSON.stringify({ card_code: code })
            });

            const res = await resp.json();

            if (res.success) {
                playBeep('success');
                showRecentActivity(res.data, res.action, res.message);
                prependRowToTable(res.data, res.action);
                updateStats(res.action, res.data.status);

                Swal.fire({
                    icon: 'success',
                    title: res.action === 'clock_out' ? 'Absensi Pulang Berhasil!' : 'Absensi Masuk Berhasil!',
                    html: `
                        <div class="my-2">
                            <h4 class="fw-bold text-success mb-1">${res.data.name}</h4>
                            <div class="badge bg-light text-dark border px-3 py-2 fs-6">
                                ${res.action === 'clock_out' ? 'CLOCK-OUT' : 'CLOCK-IN'}: ${res.data.time}
                            </div>
                        </div>
                        <div class="text-muted small mt-2">${res.message}</div>
                    `,
                    timer: 2800,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            } else {
                playBeep('error');
                Swal.fire({
                    icon: 'error',
                    title: 'Verifikasi Gagal',
                    text: res.message || 'ID Card tidak valid atau belum terdaftar.',
                    timer: 3200,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }
        } catch (err) {
            playBeep('error');
            Swal.fire({
                icon: 'error',
                title: 'Gangguan Koneksi',
                text: 'Gagal menghubungi server kiosk. Silakan periksa jaringan.',
                timer: 3000,
                showConfirmButton: false
            });
        } finally {
            if (inputEl) {
                inputEl.value = '';
                inputEl.focus();
            }
            setTimeout(() => { scanLock = false; }, 1200);
        }
    };

    // --- 6. UI Update Helpers ---
    function showRecentActivity(data, action, message) {
        const emptyCard = document.getElementById('recentScanEmpty');
        const successCard = document.getElementById('recentScanSuccessCard');
        if (emptyCard) emptyCard.classList.add('d-none');
        if (successCard) {
            successCard.classList.remove('d-none');
            
            const initials = data.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            document.getElementById('resUserInitial').textContent = initials;
            document.getElementById('resUserName').textContent = data.name;
            document.getElementById('resUserMessage').textContent = message;
            
            const actionEl = document.getElementById('resUserAction');
            if (action === 'clock_out') {
                actionEl.textContent = 'CLOCK OUT';
                actionEl.className = 'badge bg-danger fs-6 px-3 py-2 rounded-pill fw-bold';
            } else {
                actionEl.textContent = 'CLOCK IN';
                actionEl.className = 'badge bg-success fs-6 px-3 py-2 rounded-pill fw-bold';
            }
            document.getElementById('resUserTime').textContent = data.time + ' WIB';
        }
    }

    function prependRowToTable(data, action) {
        const emptyRow = document.getElementById('emptyRow');
        if (emptyRow) emptyRow.remove();

        const badgeClass = data.status === 'late' ? 'bg-warning text-dark' : 'bg-success text-white';
        const statusLabel = data.status === 'late' ? 'Terlambat' : 'Tepat Waktu';

        const row = tableBody.insertRow(0);
        row.className = 'table-success';
        row.innerHTML = `
            <td class="ps-4 text-muted small"><i class="fa-solid fa-sparkles text-warning"></i></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                        ${data.name.substring(0, 2).toUpperCase()}
                    </div>
                    <div class="fw-bold text-dark">${data.name}</div>
                </div>
            </td>
            <td>
                <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                    <i class="fa-regular fa-clock text-success me-1"></i>${data.time}
                </span>
            </td>
            <td>
                ${action === 'clock_out' ? `
                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                        <i class="fa-regular fa-clock text-danger me-1"></i>${data.time}
                    </span>` : `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1"><i class="fa-solid fa-spinner fa-spin me-1"></i>Sedang Bekerja</span>`}
            </td>
            <td>
                <span class="badge ${badgeClass} px-2 py-1 rounded-pill fw-semibold">${statusLabel}</span>
            </td>
            <td class="text-end pe-4 text-success small fw-semibold">Baru Saja Scan</td>
        `;

        setTimeout(() => row.classList.remove('table-success'), 3000);

        // Update badge count
        const countBadge = document.getElementById('logCountBadge');
        if (countBadge) {
            const currentRows = tableBody.querySelectorAll('tr').length;
            countBadge.textContent = `${currentRows} Record`;
        }
    }

    function updateStats(action, status) {
        if (action === 'clock_in') {
            const elTotal = document.getElementById('statTotalPresent');
            if (elTotal) elTotal.textContent = parseInt(elTotal.textContent || '0') + 1;

            if (status === 'late') {
                const elLate = document.getElementById('statLate');
                if (elLate) elLate.textContent = parseInt(elLate.textContent || '0') + 1;
            } else {
                const elOnTime = document.getElementById('statOnTime');
                if (elOnTime) elOnTime.textContent = parseInt(elOnTime.textContent || '0') + 1;
            }
        } else if (action === 'clock_out') {
            const elOut = document.getElementById('statClockedOut');
            if (elOut) elOut.textContent = parseInt(elOut.textContent || '0') + 1;
        }
    }

    // --- 7. Input Event Listeners ---
    if (inputEl) {
        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = inputEl.value.trim();
                if (code) submitScan(code);
            }
        });
    }

    if (btnSubmit) {
        btnSubmit.addEventListener('click', function () {
            const code = inputEl.value.trim();
            if (code) submitScan(code);
        });
    }

    // Keep focus on input for barcode/RFID gun scanner
    document.addEventListener('click', function (e) {
        if (!e.target.closest('select, button, a, input')) {
            if (inputEl) inputEl.focus();
        }
    });

    // --- 8. Table Filter Search ---
    const filterInput = document.getElementById('filterLogInput');
    if (filterInput) {
        filterInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach(r => {
                if (r.id === 'emptyRow') return;
                const text = r.textContent.toLowerCase();
                r.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    // --- 9. Camera / QR Scanner Initialization ---
    const initCamera = async () => {
        try {
            if (typeof Html5Qrcode === 'undefined') {
                if (noCamBox) noCamBox.classList.remove('d-none');
                return;
            }

            html5QrCode = new Html5Qrcode("reader");
            const devices = await Html5Qrcode.getCameras();

            if (devices && devices.length > 0) {
                if (noCamBox) noCamBox.classList.add('d-none');
                if (camBadge) {
                    camBadge.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i>Aktif';
                    camBadge.className = 'badge bg-success-subtle text-success border border-success-subtle small';
                }

                camSelect.innerHTML = '';
                devices.forEach((d, i) => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.text = d.label || `Kamera ${i + 1}`;
                    camSelect.add(opt);
                });

                const startCam = async (deviceId) => {
                    if (html5QrCode.isScanning) {
                        await html5QrCode.stop();
                    }
                    await html5QrCode.start(
                        deviceId, 
                        { fps: 12, qrbox: { width: 220, height: 220 } }, 
                        (decodedText) => {
                            submitScan(decodedText);
                        },
                        (errorMessage) => {
                            // ignore per-frame decode errors
                        }
                    );
                };

                // Prioritize back/environment camera
                let selectedDevice = devices.find(d => 
                    d.label.toLowerCase().includes('back') || 
                    d.label.toLowerCase().includes('rear') ||
                    d.label.toLowerCase().includes('environment')
                ) || devices[0];

                camSelect.value = selectedDevice.id;
                startCam(selectedDevice.id);

                camSelect.addEventListener('change', (e) => startCam(e.target.value));

                if (switchCamBtn) {
                    switchCamBtn.addEventListener('click', () => {
                        const nextIdx = (camSelect.selectedIndex + 1) % devices.length;
                        camSelect.selectedIndex = nextIdx;
                        startCam(devices[nextIdx].id);
                    });
                }
            } else {
                if (noCamBox) noCamBox.classList.remove('d-none');
                if (camBadge) {
                    camBadge.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1"></i>Tidak Ada Kamera';
                    camBadge.className = 'badge bg-secondary-subtle text-secondary border small';
                }
            }
        } catch (err) {
            console.warn('Camera scanner exception:', err);
            if (noCamBox) noCamBox.classList.remove('d-none');
            if (camBadge) {
                camBadge.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i>Non-aktif';
                camBadge.className = 'badge bg-warning-subtle text-warning border small';
            }
        }
    };

    setTimeout(initCamera, 600);
});
</script>
@endpush
