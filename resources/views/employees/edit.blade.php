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
        <div class="d-flex gap-2">
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Daftar') }}
            </a>
            <form action="{{ route('employees.sync-dsbilling', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Kirim dan sinkronkan data karyawan ini ke aplikasi dsBilling (https://mstore.mstore.id/admin/employee)?')">
                @csrf
                <button type="submit" class="btn btn-outline-info btn-sm shadow-sm">
                    <i class="fa-solid fa-satellite-dish me-1"></i> {{ __('Kirim ke dsBilling') }}
                </button>
            </form>
            <a href="{{ route('employees.id-card', $employee) }}" class="btn btn-outline-primary btn-sm shadow-sm" target="_blank">
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
        </div>
    </div>
</div>
@endsection
