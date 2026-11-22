@extends('layouts.app')

@section('title', 'Data Member Layanan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h4 class="mb-1">Member Pelanggan Layanan</h4>
            <div class="text-muted">Database member digital, level, kendaraan, dan spending.</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMemberModal">
                <i class="fas fa-plus me-1"></i> Tambah Member Baru
            </button>
            <a href="{{ route('wash.members.levels') }}" class="btn btn-outline-primary">Membership Level</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" placeholder="Cari nomor member / nama / WhatsApp / plat" value="{{ $q }}">
                </div>
                <div class="col-md-3">
                    <select name="level" class="form-select">
                        <option value="">Semua Level</option>
                        @foreach($levels as $levelOption)
                            <option value="{{ $levelOption->code }}" @selected($level === $levelOption->code)>{{ $levelOption->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                        <option value="blacklist" @selected($status === 'blacklist')>Blacklist</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Level</th>
                            <th>Kendaraan</th>
                            <th class="text-end">Transaksi</th>
                            <th class="text-end">Kunjungan</th>
                            <th class="text-end">Spending</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $member->name }}</div>
                                    <div class="small text-muted">{{ $member->member_number }} | {{ $member->whatsapp }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $member->level?->name ?? 'Bronze Member' }}</span>
                                </td>
                                <td>
                                    @if($member->vehicles->count() > 0)
                                        {{ $member->vehicles->pluck('vehicle_plate')->join(', ') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format((int) $member->total_transactions, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((int) $member->total_visits, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format((float) $member->total_spending, 0, ',', '.') }}</td>
                                <td>
                                    @php
                                        $badge = $member->status === 'active' ? 'success' : ($member->status === 'inactive' ? 'secondary' : 'danger');
                                        $label = $member->status === 'active' ? 'Aktif' : ($member->status === 'inactive' ? 'Tidak Aktif' : 'Blacklist');
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('wash.members.show', $member) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                            <i class="fas fa-eye me-1"></i> Detail
                                        </a>
                                        <form action="{{ route('wash.members.destroy', $member) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus member {{ $member->member_number }} ({{ $member->name }})?\n\nMember yang dihapus tidak dapat dipulihkan kembali.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Member">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada member wash.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $members->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    <!-- Modal Tambah Member Baru -->
    <div class="modal fade" id="createMemberModal" tabindex="-1" aria-labelledby="createMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('wash.members.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="createMemberModalLabel">
                            <i class="fas fa-user-plus me-1"></i> Pendaftaran Member Baru
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="new_member_name" class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="new_member_name" name="name" placeholder="Contoh: Budi Santoso" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_member_whatsapp" class="form-label fw-bold">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="new_member_whatsapp" name="whatsapp" placeholder="Contoh: 081234567890" required>
                            <small class="text-muted">Nomor ini digunakan untuk identifikasi member dan pengiriman pesan WhatsApp.</small>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="new_vehicle_plate" class="form-label fw-bold">Plat Kendaraan</label>
                                <input type="text" class="form-control text-uppercase" id="new_vehicle_plate" name="vehicle_plate" placeholder="B 1234 ABC">
                            </div>
                            <div class="col-6">
                                <label for="new_vehicle_brand" class="form-label fw-bold">Merek / Model</label>
                                <input type="text" class="form-control" id="new_vehicle_brand" name="vehicle_brand" placeholder="Avanza / PCX">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="new_vehicle_type" class="form-label fw-bold">Tipe Kendaraan</label>
                                <select class="form-select" id="new_vehicle_type" name="vehicle_type">
                                    <option value="mobil">Mobil</option>
                                    <option value="motor">Motor</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="new_member_level" class="form-label fw-bold">Tier / Level Awal</label>
                                <select class="form-select" id="new_member_level" name="wash_member_level_id">
                                    @foreach($levels as $levelOption)
                                        <option value="{{ $levelOption->id }}">{{ $levelOption->name }} (Diskon {{ $levelOption->discount_percent }}%)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_member_email" class="form-label">Email (Opsional)</label>
                            <input type="email" class="form-control" id="new_member_email" name="email" placeholder="email@contoh.com">
                        </div>
                        <div class="mb-2">
                            <label for="new_member_address" class="form-label">Alamat (Opsional)</label>
                            <textarea class="form-control" id="new_member_address" name="address" rows="2" placeholder="Alamat domisili"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="fas fa-save me-1"></i> Simpan Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

