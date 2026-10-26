@extends('layouts.app')

@section('title', __('Ubah Data Karyawan - ') . $employee->full_name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-user-pen text-primary me-2"></i>{{ __('Ubah Data Karyawan') }}
            </h4>
            <p class="text-body-secondary small mb-0">{{ __('Perbarui informasi profil personal, status jabatan, payroll, dan akses login :name', ['name' => $employee->full_name]) }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Daftar') }}
            </a>
            @if(empty($employee->user_id))
                <form action="{{ route('employees.create-user', $employee) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                        <i class="fa-solid fa-user-plus me-1"></i> {{ __('+ Buat Akun Login Langsung') }}
                    </button>
                </form>
            @endif
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
                    <button class="btn btn-outline-success btn-sm dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fa-brands fa-whatsapp me-1"></i> {{ __('Kirim Akun via WA') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 small">
                        <li>
                            <form action="{{ route('employees.send-whatsapp', $employee) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-success">
                                    <i class="fa-solid fa-paper-plane me-2"></i> {{ __('Kirim Otomatis via Gateway') }}
                                </button>
                            </form>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 text-secondary" href="https://wa.me/{{ $cleanPhone }}?text={{ $manualWaText }}" target="_blank">
                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> {{ __('Buka WhatsApp Web Manual') }}
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
            <form action="{{ route('employees.sync-dsbilling', $employee) }}" method="POST" class="d-inline form-sync-dsbilling" data-name="{{ $employee->full_name }}">
                @csrf
                <button type="button" class="btn btn-outline-info btn-sm shadow-sm" onclick="confirmDsBillingSync(this)">
                    <i class="fa-solid fa-satellite-dish me-1"></i> {{ __('Kirim ke dsBilling') }}
                </button>
            </form>
            <a href="{{ route('employees.id-card', $employee) }}" class="btn btn-outline-dark btn-sm shadow-sm" target="_blank">
                <i class="fa-solid fa-id-badge me-1"></i> {{ __('Cetak ID Card') }}
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('employees.partials.form', ['employee' => $employee])
            </form>

            <form id="formCreateUserAccount" action="{{ route('employees.create-user', $employee) }}" method="POST" style="display:none;">
                @csrf
            </form>
            <form id="formSendWaCredentials" action="{{ route('employees.send-whatsapp', $employee) }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
@endsection
