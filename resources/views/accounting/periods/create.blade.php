@extends('layouts.app')
@section('title', 'Buat Periode')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-calendar-plus text-primary me-2"></i>Buat Periode Akuntansi</h4>
            <p class="text-secondary small mb-0">Tambahkan periode buku akuntansi baru untuk pencatatan transaksi keuangan.</p>
        </div>
        <div>
            <a href="{{ route('accounting.periods.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden col-lg-8 mx-auto">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-dark"><i class="fas fa-edit text-primary me-2"></i>Form Periode Pembukuan</h6>
        </div>
        <div class="card-body p-4">
            <form method="post" action="{{ route('accounting.periods.store') }}" class="row g-3">
                @csrf
                <div class="col-md-12">
                    <label class="form-label fw-semibold small text-secondary">Nama Periode (Contoh: 2026-03 atau Q1-2026)</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control form-control-sm" placeholder="2026-03" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('accounting.periods.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-times me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                        <i class="fas fa-save me-1"></i> Simpan Periode
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
