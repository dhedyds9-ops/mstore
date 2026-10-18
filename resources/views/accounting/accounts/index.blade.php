@extends('layouts.app')
@section('title', 'Bagan Akun (Chart of Accounts)')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 fw-bold text-gray-800">Bagan Akun (Chart of Accounts)</h1>
            <p class="text-muted small mb-0">Daftar kode akun standar akuntansi (PSAK) untuk klasifikasi transaksi & pelaporan keuangan.</p>
        </div>
        <button type="button" class="btn btn-primary btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#createAccountModal">
            <i class="fas fa-plus me-1"></i> Tambah Akun Baru
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-muted">Cari Kode atau Nama</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Contoh: 1-1000 atau Kas">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted">Klasifikasi Akun</label>
                    <select name="type" class="form-select">
                        <option value="">Semua Tipe Akun</option>
                        @foreach($accountTypes as $k => $v)
                            <option value="{{ $k }}" @selected($type === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if($type || $search)
                        <a href="{{ route('accounting.accounts.index') }}" class="btn btn-outline-secondary ms-1">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Kode Akun</th>
                            <th>Nama Akun</th>
                            <th style="width: 20%;">Klasifikasi / Tipe</th>
                            <th style="width: 12%;" class="text-center">Status</th>
                            <th style="width: 15%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $acc)
                        <tr>
                            <td class="font-monospace fw-bold text-primary">{{ $acc->code }}</td>
                            <td class="fw-semibold">{{ $acc->name }}</td>
                            <td>
                                @php
                                    $badgeClass = match($acc->type) {
                                        'asset' => 'bg-info text-dark',
                                        'liability' => 'bg-warning text-dark',
                                        'equity' => 'bg-primary',
                                        'revenue' => 'bg-success',
                                        'expense' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1">
                                    {{ strtoupper($acc->type) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($acc->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary me-1" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editAccountModal{{ $acc->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="post" action="{{ route('accounting.accounts.destroy', $acc) }}" class="d-inline" onsubmit="return confirm('Hapus akun {{ $acc->code }} - {{ $acc->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                                <!-- Modal Edit -->
                                <div class="modal fade text-start" id="editAccountModal{{ $acc->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post" action="{{ route('accounting.accounts.update', $acc) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Akun #{{ $acc->code }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Kode Akun</label>
                                                        <input type="text" name="code" value="{{ $acc->code }}" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nama Akun</label>
                                                        <input type="text" name="name" value="{{ $acc->name }}" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Tipe Akun</label>
                                                        <select name="type" class="form-select" required>
                                                            @foreach($accountTypes as $k => $v)
                                                                <option value="{{ $k }}" @selected($acc->type === $k)>{{ $v }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-check form-switch mt-3">
                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active{{ $acc->id }}" @checked($acc->is_active)>
                                                        <label class="form-check-label fw-semibold" for="active{{ $acc->id }}">Status Akun Aktif</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data akun yang sesuai filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $accounts->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Akun -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('accounting.accounts.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Akun Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode Akun <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="Contoh: 1-1001" required>
                        <small class="text-muted">Gunakan nomor urut standar (1=Aset, 2=Kewajiban, 3=Modal, 4=Pendapatan, 5=Beban)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Akun <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Kas Kasir ATK" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipe Akun <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="">Pilih Tipe</option>
                            @foreach($accountTypes as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createActive" checked>
                        <label class="form-check-label fw-semibold" for="createActive">Status Akun Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
