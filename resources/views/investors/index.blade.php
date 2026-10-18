@extends('layouts.app')

@section('title', 'Investor')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h4 mb-1 fw-bold text-body"><i class="fa-solid fa-hand-holding-dollar text-primary me-2"></i>{{ __('Manajemen Investor') }}</h1>
            <p class="text-body-secondary small mb-0">{{ __('Kelola data penanam modal, pembagian hasil, dan saldo investasi.') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end">
            <form method="GET" action="{{ route('investors.index') }}" class="d-inline-block">
                <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}" onchange="this.form.submit()">
            </form>
            <a href="{{ route('investors.export.excel', ['month' => request('month')]) }}" class="btn btn-sm btn-outline-success shadow-sm" data-bs-toggle="tooltip" title="{{ __('Export Excel') }}">
                <i class="fa-solid fa-file-excel me-1"></i> <span>Excel</span>
            </a>
            <a href="{{ route('investors.export.pdf', ['month' => request('month')]) }}" class="btn btn-sm btn-outline-danger shadow-sm" data-bs-toggle="tooltip" title="{{ __('Export PDF') }}" target="_blank">
                <i class="fa-solid fa-file-pdf me-1"></i> <span>PDF</span>
            </a>
            <a href="{{ route('investors.create') }}" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="tooltip" title="Tambah Investor">
                <i class="fa-solid fa-user-plus me-1"></i> <span>Tambah Investor</span>
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="table-responsive table-responsive-mobile">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase text-body-secondary">
                            <th class="ps-3">{{ __('Nama Investor') }}</th>
                            <th>{{ __('Pengurus / Unit') }}</th>
                            <th>{{ __('Akun Sistem') }}</th>
                            <th class="text-end">{{ __('Total Investasi') }}</th>
                            <th class="text-end">{{ __('Saldo Kas') }}</th>
                            <th class="text-end pe-3">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($investors as $investor)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle bg-warning-subtle text-warning-emphasis fw-bold d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-body">{{ $investor->name }}</div>
                                            @if($investor->phone)
                                                <div class="small text-body-secondary">{{ $investor->phone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        {{ $investor->coordinator->name ?? 'Pusat' }}
                                    </span>
                                </td>
                                <td>
                                    @if($investor->user)
                                        <div class="small fw-medium">{{ $investor->user->name }}</div>
                                        @if($investor->user->username)
                                            <div class="x-small text-muted">{{ $investor->user->username }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold font-monospace">
                                    Rp {{ number_format($investor->income_transactions_sum_amount ?? 0, 0, ',', '.') }}
                                </td>
                                @php
                                    $balance = ($investor->income_transactions_sum_amount ?? 0) - ($investor->expense_transactions_sum_amount ?? 0);
                                @endphp
                                <td class="text-end fw-bold font-monospace {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($balance, 0, ',', '.') }}
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1 justify-content-end">
                                        <a href="{{ route('investors.show', $investor) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('investors.edit', $investor) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('investors.destroy', $investor) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('Yakin ingin menghapus data investor ini?') }}')" data-bs-toggle="tooltip" title="{{ __('Hapus') }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-body-secondary">
                                    <i class="fa-solid fa-hand-holding-dollar fa-2x opacity-25 d-block mb-2"></i>
                                    Belum ada data investor ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $investors->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
