@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-users text-primary me-2"></i>Data Karyawan
            </h4>
            <p class="text-body-secondary small mb-0">Kelola data master biodata, penempatan jabatan, absensi, dan kartu ID Card karyawan.</p>
        </div>
        
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-success shadow-sm" data-bs-toggle="modal" data-bs-target="#registrationLinkModal" title="Bagikan Link Pendaftaran Calon Karyawan">
                <i class="fa-solid fa-link me-1"></i> Link Pendaftaran
            </button>
            <form action="{{ route('employees.sync') }}" method="POST" onsubmit="return confirm('Sinkronkan data sekarang?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary shadow-sm">
                    <i class="fa-solid fa-rotate me-1"></i> Sinkron
                </button>
            </form>
            <a href="{{ route('employees.create') }}" class="btn btn-sm btn-primary shadow-sm px-3">
                <i class="fa-solid fa-user-plus me-1"></i> Tambah Karyawan
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-3 p-md-4">

        {{-- Row 2: Filter & Search --}}
        <form method="GET" class="row g-2 align-items-center">
            <!-- Pagination per_page -->
            <div class="col-auto">
                <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="cursor: pointer; min-width: 90px;">
                    <option value="10" @selected(request('per_page') == '10' || !request()->has('per_page'))>10 Data</option>
                    <option value="20" @selected(request('per_page') == '20')>20 Data</option>
                    <option value="50" @selected(request('per_page') == '50')>50 Data</option>
                    <option value="100" @selected(request('per_page') == '100')>100 Data</option>
                    <option value="all" @selected(request('per_page') == 'all')>Semua</option>
                </select>
            </div>

            <div class="col-auto">
                <select name="department" class="form-select form-select-sm" onchange="this.form.submit()" style="cursor: pointer; min-width: 130px;">
                    <option value="">Semua Dept</option>
                    @foreach($departments as $dep)
                        <option value="{{ $dep }}" {{ $department === $dep ? 'selected' : '' }}>{{ $dep }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <select name="position" class="form-select form-select-sm" onchange="this.form.submit()" style="cursor: pointer; min-width: 130px;">
                    <option value="">Semua Jabatan</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos }}" {{ $position === $pos ? 'selected' : '' }}>{{ $pos }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <select name="role_id" class="form-select form-select-sm" onchange="this.form.submit()" style="cursor: pointer; min-width: 130px;">
                    <option value="">Semua Peran</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" @selected((string) request('role_id') === (string) $r->id)>{{ $r->label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="cursor: pointer; min-width: 120px;">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto flex-grow-1 flex-md-grow-0">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Cari nama, NIK, jabatan..." style="min-width: 160px;">
                    <button class="btn btn-primary px-3" type="submit" title="Cari">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @if(request('search') || request('department') || request('position') || request('role_id') || request('status') || request('per_page') || request('company_id'))
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary px-3" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Row 2 Right: Export Actions --}}
            <div class="col-auto ms-auto d-flex gap-1">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-file-export me-1"></i> Ekspor
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item py-2" href="{{ route('employees.export.pdf', request()->query()) }}"><i class="fa-regular fa-file-pdf me-2 text-danger"></i> PDF</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('employees.export.excel', request()->query()) }}"><i class="fa-regular fa-file-excel me-2 text-success"></i> Excel</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('employees.export.csv', request()->query()) }}"><i class="fa-solid fa-file-csv me-2 text-secondary"></i> CSV</a></li>
                    </ul>
                </div>
                <a href="{{ route('employees.print.cards', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                    <i class="fa-solid fa-print me-1"></i> ID Cards
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 overflow-hidden">
    <div class="card-header bg-transparent py-3 border-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">Daftar Karyawan</h6>
        <form method="GET" action="{{ route('employees.print.cards') }}" target="_blank" id="printSelectedForm" class="m-0">
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="department" value="{{ $department }}">
            <input type="hidden" name="position" value="{{ $position }}">
            <input type="hidden" name="role_id" value="{{ request('role_id') }}">
            <input type="hidden" name="status" value="{{ $status }}">
            <button type="submit" class="btn btn-xs btn-outline-dark" id="printSelectedBtn" disabled>
                <i class="fa-solid fa-id-card me-1"></i> Print Pilihan
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light-subtle">
                    <tr class="small text-uppercase text-muted">
                        <th class="ps-3" style="width:40px;">
                            <input type="checkbox" class="form-check-input" id="checkAllEmployees">
                        </th>
                        <th>Nama Karyawan</th>
                        <th>NIK</th>
                        <th>Posisi / Dept</th>
                        <th>No HP</th>
                        <th>Gaji Bulanan</th>
                        <th>Gaji Harian</th>
                        <th>Status</th>
                        <th>Integrasi</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td class="ps-3">
                                <input type="checkbox" class="form-check-input employee-check" name="selected_ids[]" value="{{ $employee->id }}" form="printSelectedForm">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($employee->id_card_photo_path)
                                        <img src="{{ asset('storage/'.$employee->id_card_photo_path) }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border text-muted" style="width: 32px; height: 32px;">
                                            <i class="fa-solid fa-user x-small"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark mb-0">{{ $employee->full_name }}</div>
                                        <div class="x-small text-muted">{{ $employee->user->username ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="small fw-medium">{{ $employee->nik }}</td>
                            <td>
                                <div class="small fw-semibold text-dark">{{ $employee->position }}</div>
                                <div class="x-small text-muted">{{ $employee->department }}</div>
                            </td>
                            <td class="small">{{ $employee->phone }}</td>
                            <td class="small fw-medium text-end">{{ number_format($employee->monthly_salary ?? 0, 0, ',', '.') }}</td>
                            <td class="small fw-medium text-end">{{ number_format($employee->daily_salary ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $employee->employment_status === 'Tetap' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} x-small px-2">
                                    {{ $employee->employment_status }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if($employee->user_id)
                                        <span class="badge bg-primary-subtle text-primary x-small border border-primary-subtle" title="User Account: {{ $employee->user->username }}">User</span>
                                    @endif
                                    @if($employee->wash_employee_id)
                                        <span class="badge bg-info-subtle text-info x-small border border-info-subtle">Layanan</span>
                                    @endif
                                    @if(!$employee->user_id && !$employee->wash_employee_id)
                                        <span class="text-muted x-small italic">Manual</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end gap-1">
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)($employee->phone ?? ''));
                                        if (str_starts_with($cleanPhone, '08')) {
                                            $cleanPhone = '628' . substr($cleanPhone, 2);
                                        } elseif (str_starts_with($cleanPhone, '8')) {
                                            $cleanPhone = '628' . substr($cleanPhone, 1);
                                        } elseif (str_starts_with($cleanPhone, '6208')) {
                                            $cleanPhone = '628' . substr($cleanPhone, 4);
                                        }
                                        $userUname = $employee->user?->username ?? ($employee->user?->email ?? $employee->email);
                                        $manualWaText = rawurlencode("Halo *{$employee->full_name}*,\n\nBerikut informasi akun login sistem Anda:\n━━━━━━━━━━━━━━━━━━━━\n🏢 *Aplikasi:* MStore\n👤 *Nama:* {$employee->full_name}\n💼 *Jabatan:* " . ($employee->position ?: 'Staff') . "\n🌐 *Link Login:* " . route('login') . "\n\n🔐 *Kredensial Login:*\n• Username: *{$userUname}*\n• Password: *Password123!*\n━━━━━━━━━━━━━━━━━━━━\nSilakan gunakan informasi di atas untuk masuk ke sistem. Demi keamanan, segera ganti password Anda setelah login pertama kali.\n\nTerima kasih.");
                                    @endphp
                                    @if($cleanPhone)
                                        <div class="dropdown d-inline">
                                            <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Kirim Akun via WhatsApp">
                                                <i class="fa-brands fa-whatsapp"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 small">
                                                <li>
                                                    <form action="{{ route('employees.send-whatsapp', $employee) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item py-2 text-success">
                                                            <i class="fa-solid fa-paper-plane me-2"></i> Kirim Otomatis via Gateway
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2 text-secondary" href="https://wa.me/{{ $cleanPhone }}?text={{ $manualWaText }}" target="_blank">
                                                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Buka WhatsApp Web Manual
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                    <form action="{{ route('employees.sync-dsbilling', $employee) }}" method="POST" class="d-inline form-sync-dsbilling" data-name="{{ $employee->full_name }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-info" type="button" onclick="confirmDsBillingSync(this)" title="Kirim ke dsBilling">
                                            <i class="fa-solid fa-satellite-dish"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('employees.id-card', $employee) }}" class="btn btn-sm btn-outline-dark" title="ID Card">
                                        <i class="fa-solid fa-id-card"></i>
                                    </a>
                                    @if($employee->document_path)
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ asset('storage/'.$employee->document_path) }}" target="_blank" title="Dokumen">
                                            <i class="fa-regular fa-file-lines"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fa-solid fa-user-slash fa-2x opacity-25 d-block mb-2"></i>
                                Belum ada data karyawan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($employees->hasPages())
        <div class="card-footer bg-transparent py-2 border-0">
            {{ $employees->appends(request()->query())->links() }}
        </div>
    @endif
    </div>
</div>

<!-- Modal Link Pendaftaran Calon Karyawan -->
<div class="modal fade" id="registrationLinkModal" tabindex="-1" aria-labelledby="registrationLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold" id="registrationLinkModalLabel">
                    <i class="fa-solid fa-link text-success me-2"></i>{{ __('Link Pendaftaran Calon Karyawan') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 rounded-3 mb-3 small">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    {{ __('Bagikan link ini kepada calon pelamar kerja. Calon karyawan dapat mengisi biodata, nomor KTP, posisi yang diminati, serta mengunggah foto KTP dan berkas CV secara mandiri.') }}
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">{{ __('Tautan Pendaftaran Online:') }}</label>
                    <div class="input-group">
                        <input type="text" id="employeePublicRegisterUrl" class="form-control bg-light font-monospace small" value="{{ route('employees.public.register.create') }}" readonly>
                        <button type="button" class="btn btn-primary" id="btnCopyRegisterLink" onclick="copyEmployeeRegisterLink()">
                            <i class="fa-solid fa-copy me-1"></i> <span id="btnCopyText">{{ __('Salin') }}</span>
                        </button>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2 pt-2 border-top">
                    <a href="https://api.whatsapp.com/send?text={{ rawurlencode('Halo! Silakan isi formulir pendaftaran calon karyawan di tautan berikut: ' . route('employees.public.register.create')) }}" target="_blank" class="btn btn-outline-success btn-sm w-100 py-2">
                        <i class="fa-brands fa-whatsapp me-1"></i> {{ __('Bagikan ke WhatsApp') }}
                    </a>
                    <a href="{{ route('employees.public.register.create') }}" target="_blank" class="btn btn-outline-secondary btn-sm w-100 py-2">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ __('Buka Tampilan Formulir Pendaftaran') }}
                    </a>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyEmployeeRegisterLink() {
    const input = document.getElementById('employeePublicRegisterUrl');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(function() {
        const btnText = document.getElementById('btnCopyText');
        btnText.textContent = 'Tersalin!';
        setTimeout(() => { btnText.textContent = 'Salin'; }, 2000);
    }).catch(function() {
        document.execCommand('copy');
        const btnText = document.getElementById('btnCopyText');
        btnText.textContent = 'Tersalin!';
        setTimeout(() => { btnText.textContent = 'Salin'; }, 2000);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAllEmployees');
    const checks = Array.from(document.querySelectorAll('.employee-check'));
    const printBtn = document.getElementById('printSelectedBtn');

    function refreshPrintButton() {
        const selectedCount = checks.filter((el) => el.checked).length;
        printBtn.disabled = selectedCount === 0;
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checks.forEach((el) => { el.checked = checkAll.checked; });
            refreshPrintButton();
        });
    }

    checks.forEach((el) => {
        el.addEventListener('change', function () {
            if (!el.checked && checkAll) {
                checkAll.checked = false;
            } else if (checkAll) {
                checkAll.checked = checks.length > 0 && checks.every((node) => node.checked);
            }
            refreshPrintButton();
        });
    });

    refreshPrintButton();
});

function confirmDsBillingSync(button) {
    const form = button.closest('form');
    const name = form.dataset.name || 'karyawan ini';
    
    if (typeof Swal === 'undefined') {
        if (confirm('Kirim dan sinkronkan data karyawan ' + name + ' ke aplikasi dsBilling?')) {
            form.submit();
        }
        return;
    }

    Swal.fire({
        icon: 'question',
        title: 'Sinkronisasi ke dsBilling',
        html: `Apakah Anda yakin ingin mengirim data karyawan <b>${name}</b> ke aplikasi dsBilling?<br><div class="mt-2 small text-muted">Data akan otomatis masuk ke menu Kepegawaian (<code>mstore.mstore.id/admin/employee</code>).</div>`,
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-satellite-dish me-1"></i> Ya, Kirim Sekarang',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0ea5e9',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup: 'rounded-4 shadow border-0',
            confirmButton: 'btn btn-info text-white px-4 py-2 rounded-3 shadow-sm',
            cancelButton: 'btn btn-secondary px-4 py-2 rounded-3 me-2'
        },
        buttonsStyling: false
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mengirim Data...',
                html: 'Sedang menghubungkan ke server dsBilling, mohon tunggu...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });
            form.submit();
        }
    });
}
</script>
@endpush
