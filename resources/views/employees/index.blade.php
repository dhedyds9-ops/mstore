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

    <form method="GET" action="{{ route('employees.print.cards') }}" target="_blank" id="printSelectedForm" class="d-none">
        <input type="hidden" name="search" value="{{ $search }}">
        <input type="hidden" name="department" value="{{ $department }}">
        <input type="hidden" name="position" value="{{ $position }}">
        <input type="hidden" name="role_id" value="{{ request('role_id') }}">
        <input type="hidden" name="status" value="{{ $status }}">
    </form>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-bottom-0">
            <x-table-toolbar 
                :route="route('employees.index')" 
                searchPlaceholder="Cari nama, NIK, jabatan..." 
                :showDepartmentFilter="true"
                :departments="$departments ?? collect()"
                :showRoleFilter="true" 
                :roles="$roles ?? collect()" 
                :showJobTitleFilter="true"
                :jobTitles="$positions ?? collect()"
                jobTitleParam="position"
                :showStatusFilter="true"
                :statuses="$statuses ?? []"
            >
                <div class="col-auto ms-auto d-flex align-items-center gap-1">
                    <button type="submit" form="printSelectedForm" class="btn btn-sm btn-outline-dark" id="printSelectedBtn" disabled title="{{ __('Print ID Card Karyawan Pilihan') }}">
                        <i class="fa-solid fa-id-card me-1"></i> {{ __('Print Pilihan') }}
                    </button>
                    <a href="{{ route('employees.print.cards', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="{{ __('Print Semua ID Card Sesuai Filter') }}">
                        <i class="fa-solid fa-print me-1"></i> {{ __('ID Cards') }}
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-file-export me-1"></i> {{ __('Ekspor') }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item py-2" href="{{ route('employees.export.pdf', request()->query()) }}"><i class="fa-regular fa-file-pdf me-2 text-danger"></i> PDF</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('employees.export.excel', request()->query()) }}"><i class="fa-regular fa-file-excel me-2 text-success"></i> Excel</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('employees.export.csv', request()->query()) }}"><i class="fa-solid fa-file-csv me-2 text-secondary"></i> CSV</a></li>
                        </ul>
                    </div>
                </div>
            </x-table-toolbar>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-mobile">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 text-uppercase small text-muted border-0" style="width:40px;">
                                <input type="checkbox" class="form-check-input" id="checkAllEmployees">
                            </th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Nama Karyawan') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('NIK') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Posisi / Dept') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('No HP') }}</th>
                            <th class="text-uppercase small text-muted border-0 text-end">{{ __('Gaji Bulanan') }}</th>
                            <th class="text-uppercase small text-muted border-0 text-end">{{ __('Gaji Harian') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Status') }}</th>
                            <th class="text-uppercase small text-muted border-0">{{ __('Integrasi') }}</th>
                            <th class="text-end pe-3 text-uppercase small text-muted border-0">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td class="ps-3">
                                    <input type="checkbox" class="form-check-input employee-check" name="selected_ids[]" value="{{ $employee->id }}" form="printSelectedForm">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2.5 gap-md-3">
                                        @php
                                            $empPhotoPath = $employee->id_card_photo_path ?? ($employee->user?->avatar ?? '');
                                            $empPhoto = null;
                                            if ($empPhotoPath !== '') {
                                                if (str_starts_with($empPhotoPath, 'http://') || str_starts_with($empPhotoPath, 'https://')) {
                                                    $empPhoto = $empPhotoPath;
                                                } elseif (str_starts_with($empPhotoPath, 'storage/') || str_starts_with($empPhotoPath, 'img/')) {
                                                    $empPhoto = asset($empPhotoPath);
                                                } else {
                                                    $empPhoto = asset('storage/' . $empPhotoPath);
                                                }
                                            }
                                        @endphp
                                        @if($empPhoto)
                                            <img src="{{ $empPhoto }}" alt="{{ $employee->full_name }}" class="rounded-circle border flex-shrink-0" style="width: 36px; height: 36px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center border border-primary-subtle flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark mb-0">{{ $employee->full_name }}</div>
                                            <div class="small text-muted">
                                                <i class="fa-solid fa-user me-1"></i>{{ $employee->user->username ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="small fw-medium">{{ $employee->nik }}</td>
                                <td>
                                    <div class="small text-primary fw-medium">
                                        <i class="fa-solid fa-briefcase me-1"></i>{{ $employee->position ?: '-' }}
                                    </div>
                                    @if($employee->department)
                                        <div class="small text-muted">
                                            <i class="fa-solid fa-building me-1"></i>{{ $employee->department }}
                                        </div>
                                    @endif
                                </td>
                                <td class="small">{{ $employee->phone ?: '-' }}</td>
                                <td class="small fw-medium text-end">{{ number_format($employee->monthly_salary ?? 0, 0, ',', '.') }}</td>
                                <td class="small fw-medium text-end">{{ number_format($employee->daily_salary ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    @if($employee->employment_status === 'Tetap')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ __('Tetap') }}</span>
                                    @elseif($employee->employment_status === 'Training')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ __('Training') }}</span>
                                    @elseif($employee->employment_status === 'Pendaftar')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">{{ __('Pendaftar') }}</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $employee->employment_status ?: '-' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($employee->user_id)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" title="User Account: {{ $employee->user->username ?? '' }}">
                                                <i class="fa-solid fa-user-check me-1"></i>User
                                            </span>
                                        @endif
                                        @if($employee->wash_employee_id)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                <i class="fa-solid fa-spray-can-sparkles me-1"></i>Layanan
                                            </span>
                                        @endif
                                        @if(!$employee->user_id && !$employee->wash_employee_id)
                                            <span class="badge bg-light text-muted border">Manual</span>
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
                                    {{ __('Belum ada data karyawan.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($employees->hasPages())
                <div class="mt-4">
                    {{ $employees->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
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
