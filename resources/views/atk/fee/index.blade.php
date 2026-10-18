@extends('layouts.app')

@section('title', __('Manajemen Tarif & Fee ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-coins me-1"></i>{{ __('Tarif & Komisi') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-calendar-days me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Manajemen Tarif & Profil Fee ATK') }}</h1>
            <p class="text-muted small mb-0">{{ __('Atur skema penentuan biaya admin/fee untuk transaksi transfer bank, tarik tunai, top up e-wallet, dan PPOB.') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('atk.dashboard') }}" class="btn btn-sm btn-outline-secondary shadow-2xs">
                <i class="fa-solid fa-gauge me-1"></i>{{ __('Dasbor') }}
            </a>
            <a href="{{ route('atk.fee.create') }}" class="btn btn-sm btn-primary px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i>{{ __('Buat Profil Fee Baru') }}
            </a>
        </div>
    </div>

    {{-- Tabel Profil Fee --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-primary"></i>
                <span>{{ __('Daftar Skema Fee Terdaftar') }}</span>
            </h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">
                {{ count($profiles) }} {{ __('Skema Fee') }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="min-width: 180px;" class="py-3">{{ __('Nama Skema Fee') }}</th>
                        <th style="min-width: 140px;" class="py-3">{{ __('Jenis Layanan') }}</th>
                        <th style="min-width: 150px;" class="py-3">{{ __('Mode Perhitungan') }}</th>
                        <th style="min-width: 100px;" class="py-3 text-center">{{ __('Status') }}</th>
                        <th style="min-width: 130px;" class="py-3">{{ __('Terakhir Diubah') }}</th>
                        <th style="min-width: 110px;" class="py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($profiles as $profile)
                    <tr>
                        <td>
                            <a href="{{ route('atk.fee.show', $profile->id) }}" class="fw-bold text-gray-900 text-decoration-none">
                                {{ $profile->name }}
                            </a>
                        </td>
                        <td>
                            @php
                            $types = [
                                'bank' => 'Transfer Bank',
                                'cash_out' => 'Tarik Tunai',
                                'top_up' => 'Top Up E-Wallet',
                                'ppob' => 'PPOB & Tagihan',
                                'qris' => 'QRIS Kasir',
                                'custom' => 'Layanan Kustom'
                            ];
                            @endphp
                            <span class="badge rounded-pill bg-light text-dark border px-2 py-1 small fw-medium">
                                {{ $types[$profile->transaction_type] ?? $profile->transaction_type }}
                            </span>
                        </td>
                        <td>
                            @php
                            $modes = [
                                'fixed' => 'Biaya Flat (Tetap)',
                                'percentage' => 'Persentase (%)',
                                'fixed_percentage' => 'Flat + Persentase',
                                'tier' => 'Bertingkat (Tier Nominal)',
                                'cost_plus' => 'Cost Plus Markup',
                                'custom' => 'Rumus Formula Kustom'
                            ];
                            @endphp
                            <span class="small text-gray-700 fw-medium">{{ $modes[$profile->fee_mode] ?? $profile->fee_mode }}</span>
                        </td>
                        <td class="text-center">
                            @if($profile->is_active)
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-semibold">
                                    <i class="fa-solid fa-circle-check me-1"></i>{{ __('Aktif') }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small fw-semibold">
                                    {{ __('Nonaktif') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="small text-muted font-monospace"><i class="fa-regular fa-clock me-1"></i>{{ $profile->updated_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('atk.fee.show', $profile->id) }}" class="btn btn-outline-secondary" title="{{ __('Lihat Detail') }}">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                </a>
                                <a href="{{ route('atk.fee.edit', $profile->id) }}" class="btn btn-outline-secondary" title="{{ __('Ubah') }}">
                                    <i class="fa-solid fa-pen-to-square text-secondary"></i>
                                </a>
                                <form action="{{ route('atk.fee.destroy', $profile->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus profil fee ini?')">
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
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-coins fs-1 text-secondary-subtle mb-3 d-block"></i>
                            <h6 class="fw-bold mb-1">{{ __('Belum Ada Profil Fee') }}</h6>
                            <p class="small mb-3">{{ __('Buat skema fee untuk layanan transfer bank, tarik tunai, top up, atau PPOB.') }}</p>
                            <a href="{{ route('atk.fee.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-plus me-1"></i>{{ __('Buat Fee Profile') }}
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
