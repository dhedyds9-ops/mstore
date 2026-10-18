@extends('layouts.app')

@section('title', __('Shift & Sesi Kasir ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-cash-register me-1"></i>{{ __('Laci Kasir & Sesi') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Shift & Sesi Kasir ATK') }}</h1>
            <p class="text-muted small mb-0">{{ __('Kelola pembukaan dan penutupan shift kasir serta rekonsiliasi saldo kas laci harian.') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('atk.pos') }}" class="btn btn-sm btn-outline-secondary shadow-2xs">
                <i class="fa-solid fa-store me-1"></i>{{ __('Buka Kasir (POS)') }}
            </a>
            @if(!$activeRegister)
                <a href="{{ route('atk.cash-registers.create') }}" class="btn btn-sm btn-primary px-3 shadow-sm">
                    <i class="fa-solid fa-door-open me-1"></i>{{ __('Buka Shift Baru') }}
                </a>
            @endif
        </div>
    </div>

    {{-- Banner Shift Aktif --}}
    @if($activeRegister)
        <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-4 border-success bg-gradient-light">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-3 bg-success-subtle text-success fs-4 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                            <i class="fa-solid fa-cash-register"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h5 class="fw-bold text-gray-900 mb-0">{{ $activeRegister->name }}</h5>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small fw-semibold">
                                    <i class="fa-solid fa-circle-dot me-1"></i>{{ __('Shift Aktif Berjalan') }}
                                </span>
                            </div>
                            <div class="d-flex flex-wrap gap-3 text-muted small mt-1">
                                <span><i class="fa-solid fa-user-tie text-primary me-1"></i>{{ __('Kasir') }}: <strong>{{ $activeRegister->user->name }}</strong></span>
                                <span><i class="fa-solid fa-clock text-info me-1"></i>{{ __('Dibuka') }}: <strong>{{ $activeRegister->opened_at->format('d/m/Y H:i') }}</strong></span>
                                <span><i class="fa-solid fa-wallet text-success me-1"></i>{{ __('Saldo Awal') }}: <strong>Rp {{ number_format($activeRegister->opening_balance, 0, ',', '.') }}</strong></span>
                                <span><i class="fa-solid fa-money-bill-wave text-warning-emphasis me-1"></i>{{ __('Saldo Saat Ini') }}: <strong class="text-dark">Rp {{ number_format($activeRegister->closing_balance, 0, ',', '.') }}</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-danger px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
                            <i class="fa-solid fa-door-closed me-1"></i>{{ __('Tutup Shift Ini') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Tabel Riwayat Shift --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-primary"></i>
                <span>{{ __('Riwayat Seluruh Shift Kasir') }}</span>
            </h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">
                {{ $registers->count() }} {{ __('Data Shift') }}
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="min-width: 140px;" class="py-3">{{ __('Nama Shift') }}</th>
                        <th style="min-width: 130px;" class="py-3">{{ __('Kasir Pembuka') }}</th>
                        <th style="min-width: 140px;" class="py-3">{{ __('Waktu Buka') }}</th>
                        <th style="min-width: 140px;" class="py-3">{{ __('Waktu Tutup') }}</th>
                        <th style="min-width: 120px;" class="py-3 text-end">{{ __('Saldo Awal') }}</th>
                        <th style="min-width: 120px;" class="py-3 text-end">{{ __('Saldo Akhir') }}</th>
                        <th style="min-width: 100px;" class="py-3 text-center">{{ __('Status') }}</th>
                        <th style="min-width: 100px;" class="py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($registers as $register)
                        <tr>
                            <td>
                                <div class="fw-bold text-gray-900">{{ $register->name }}</div>
                            </td>
                            <td>
                                <span class="small text-muted"><i class="fa-regular fa-user me-1"></i>{{ $register->user->name ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="small text-gray-800">{{ $register->opened_at->translatedFormat('d M Y') }}</div>
                                <small class="text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ $register->opened_at->format('H:i') }}</small>
                            </td>
                            <td>
                                @if($register->closed_at)
                                    <div class="small text-gray-800">{{ $register->closed_at->translatedFormat('d M Y') }}</div>
                                    <small class="text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ $register->closed_at->format('H:i') }}</small>
                                @else
                                    <span class="text-muted small italic">{{ __('Masih Dibuka') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-medium text-dark">Rp {{ number_format($register->opening_balance, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success">Rp {{ number_format($register->closing_balance, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-center">
                                @if($register->status === 'open')
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-semibold">
                                        <i class="fa-solid fa-circle-check me-1"></i>{{ __('Aktif') }}
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small fw-semibold">
                                        {{ __('Ditutup') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('atk.cash-registers.edit', $register) }}" class="btn btn-outline-secondary" title="{{ __('Ubah') }}">
                                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                                    </a>
                                    <form method="POST" action="{{ route('atk.cash-registers.destroy', $register) }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan shift ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-secondary text-danger" title="{{ __('Hapus') }}">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-door-open fs-1 text-secondary-subtle mb-3 d-block"></i>
                                    <h6 class="fw-bold mb-1">{{ __('Belum Ada Riwayat Shift') }}</h6>
                                    <p class="small mb-3">{{ __('Buka shift kasir baru untuk memulai pencatatan sesi kas.') }}</p>
                                    @if(!$activeRegister)
                                    <a href="{{ route('atk.cash-registers.create') }}" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-door-open me-1"></i>{{ __('Buka Shift Baru') }}
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($activeRegister)
<div class="modal fade" id="closeShiftModal" tabindex="-1" aria-labelledby="closeShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('atk.cash-registers.close', $activeRegister) }}">
                @csrf
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold" id="closeShiftModalLabel">
                        <i class="fa-solid fa-door-closed text-danger me-2"></i>{{ __('Tutup Shift Kasir') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        {{ __('Pastikan saldo fisik pada laci kasir telah dihitung dan sesuai sebelum menutup sesi shift ini.') }}
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">{{ __('Saldo Akhir Kas Laci') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" name="closing_balance" class="form-control form-control-lg fw-bold" value="{{ old('closing_balance', $activeRegister->closing_balance) }}" required autofocus>
                        </div>
                        <small class="text-muted">{{ __('Masukkan nominal total uang fisik yang ada di laci saat ini.') }}</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                    <button type="submit" class="btn btn-sm btn-danger px-4">
                        <i class="fa-solid fa-check me-1"></i>{{ __('Konfirmasi Tutup Shift') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
