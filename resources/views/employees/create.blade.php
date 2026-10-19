@extends('layouts.app')

@section('title', __('Tambah Data Karyawan'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-user-plus text-primary me-2"></i>{{ __('Tambah Data Karyawan') }}
            </h4>
            <p class="text-body-secondary small mb-0">{{ __('Daftarkan biodata lengkap karyawan, penempatan divisi, payroll, dan opsi akun login sistem.') }}</p>
        </div>
        <div>
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Kembali ke Daftar') }}
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
                @csrf
                @include('employees.partials.form', ['employee' => null])
            </form>
        </div>
    </div>
</div>
@endsection
