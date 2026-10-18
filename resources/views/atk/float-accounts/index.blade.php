@extends('layouts.app')

@section('title', __('Akun Float Kasir ATK'))

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    {{-- Header Halaman --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill small fw-semibold">
                    <i class="fa-solid fa-credit-card me-1"></i>{{ __('Rekening & E-Wallet') }}
                </span>
                <span class="text-muted small">
                    <i class="fa-regular fa-calendar-days me-1"></i>{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="h3 fw-bold text-gray-800 mb-0">{{ __('Akun Float Kasir (Bank & E-Wallet)') }}</h1>
            <p class="text-muted small mb-0">{{ __('Kelola modal saldo digital yang dipakai untuk layanan transfer uang, top up e-wallet, dan pembayaran PPOB.') }}</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('atk.dashboard') }}" class="btn btn-sm btn-outline-secondary shadow-2xs">
                <i class="fa-solid fa-gauge me-1"></i>{{ __('Dasbor') }}
            </a>
            <a href="{{ route('atk.float-accounts.create') }}" class="btn btn-sm btn-primary px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i>{{ __('Tambah Akun Baru') }}
            </a>
        </div>
    </div>

    {{-- Ringkasan Metrik Saldo Float --}}
    @php
        $totalFloat = \App\Models\AtkFloatAccount::where('status', 'active')->sum('current_balance');
        $activeCount = \App\Models\AtkFloatAccount::where('status', 'active')->count();
        $bankBalance = \App\Models\AtkFloatAccount::where('status', 'active')->where(function($q) {
            $q->where('account_type', 'bank')->orWhere('account_type', 'like', '%bank%');
        })->sum('current_balance');
        $ewalletBalance = $totalFloat - $bankBalance;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-4 border-info">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-info-subtle text-info fs-4">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Total Saldo Seluruh Float') }}</div>
                        <div class="fs-4 fw-bold text-gray-900">Rp {{ number_format($totalFloat, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary-subtle text-primary fs-4">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('Rekening Bank') }}</div>
                        <div class="fs-5 fw-bold text-primary">Rp {{ number_format($bankBalance, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success-subtle text-success fs-4">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">{{ __('E-Wallet & PPOB') }} ({{ $activeCount }} {{ __('Akun Aktif') }})</div>
                        <div class="fs-5 fw-bold text-success">Rp {{ number_format($ewalletBalance, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Akun Float --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-gray-800 d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-primary"></i>
                <span>{{ __('Daftar Akun Float Kasir') }}</span>
            </h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">
                {{ $accounts->total() }} {{ __('Akun Terdaftar') }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light-subtle text-secondary small text-uppercase fw-semibold border-bottom">
                    <tr>
                        <th style="min-width: 120px;" class="py-3">{{ __('Kode Akun') }}</th>
                        <th style="min-width: 200px;" class="py-3">{{ __('Nama Akun / Provider') }}</th>
                        <th style="min-width: 140px;" class="py-3">{{ __('Tipe Akun') }}</th>
                        <th style="min-width: 150px;" class="py-3 text-end">{{ __('Saldo Saat Ini') }}</th>
                        <th style="min-width: 100px;" class="py-3 text-center">{{ __('Status') }}</th>
                        <th style="min-width: 120px;" class="py-3 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($accounts as $account)
                    <tr>
                        <td>
                            <a href="{{ route('atk.float-accounts.show', $account) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                {{ $account->code }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-light border text-muted fs-6 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    @if(str_contains(strtolower($account->account_type ?? ''), 'bank'))
                                        <i class="fa-solid fa-building-columns text-primary"></i>
                                    @else
                                        <i class="fa-solid fa-wallet text-success"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold text-gray-900">{{ $account->name }}</div>
                                    @if($account->account_number)
                                        <small class="text-muted font-monospace">{{ $account->account_number }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="small text-muted text-uppercase fw-semibold">{{ $account->account_type }}</span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-gray-900 fs-6">
                                Rp {{ number_format($account->current_balance, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($account->status === 'active')
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-semibold">
                                    <i class="fa-solid fa-circle-check me-1"></i>{{ __('Aktif') }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small fw-semibold">
                                    {{ __('Nonaktif') }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('atk.float-accounts.show', $account) }}" class="btn btn-outline-secondary" title="{{ __('Riwayat Saldo') }}">
                                    <i class="fa-solid fa-eye text-primary"></i>
                                </a>
                                <a href="{{ route('atk.float-accounts.create-transaction', $account) }}" class="btn btn-outline-secondary" title="{{ __('Top Up / Mutasi Saldo') }}">
                                    <i class="fa-solid fa-plus-minus text-success"></i>
                                </a>
                                <a href="{{ route('atk.float-accounts.edit', $account) }}" class="btn btn-outline-secondary" title="{{ __('Ubah') }}">
                                    <i class="fa-solid fa-pen text-secondary"></i>
                                </a>
                                <form action="{{ route('atk.float-accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun float ini?')">
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
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-credit-card fs-1 text-secondary-subtle mb-3 d-block"></i>
                                <h6 class="fw-bold mb-1">{{ __('Belum Ada Akun Float') }}</h6>
                                <p class="small mb-3">{{ __('Tambahkan rekening bank atau akun e-wallet (DANA, OVO, GoPay) untuk modal transaksi.') }}</p>
                                <a href="{{ route('atk.float-accounts.create') }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-plus me-1"></i>{{ __('Tambah Akun Baru') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Card Footer & Paginasi --}}
        <div class="card-footer bg-white border-top py-3 px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <div class="text-muted small">
                {{ __('Menampilkan') }} <span class="fw-semibold text-dark">{{ $accounts->firstItem() ?? 0 }}</span> - <span class="fw-semibold text-dark">{{ $accounts->lastItem() ?? 0 }}</span> {{ __('dari') }} <span class="fw-semibold text-dark">{{ $accounts->total() }}</span> {{ __('akun') }}
            </div>
            <div>
                {{ $accounts->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
