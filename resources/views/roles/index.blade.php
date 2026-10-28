@extends('layouts.app')

@section('title', __('Manajemen Peran & Hak Akses'))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-user-shield text-primary me-2"></i>{{ __('Manajemen Peran & Hak Akses') }}
            </h4>
            <p class="text-body-secondary small mb-0">{{ __('Kelola definisi peran pengguna sistem, pembagian perizinan akses modul, dan hak akses keamanan.') }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm shadow-sm text-nowrap">
                <i class="fa-solid fa-plus me-1"></i> {{ __('Tambah Peran Baru') }}
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-bottom-0">
            <x-table-toolbar 
                :route="route('roles.index')" 
                searchPlaceholder="Cari peran..." 
            />
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-mobile">
                    <thead class="bg-light">
                        <tr>
                            @php
                                $currentSort = request('sort', 'created_at');
                                $currentDirection = request('direction', 'desc');
                            @endphp
                            <th scope="col" class="ps-3 text-uppercase small text-muted border-0">
                                <a href="{{ route('roles.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => $currentSort === 'name' && $currentDirection === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted">
                                    {{ __('Kode Sistem (Slug)') }}
                                    @if($currentSort === 'name')
                                        <i class="fa-solid fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1 text-primary"></i>
                                    @else
                                        <i class="fa-solid fa-sort ms-1 opacity-25"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="text-uppercase small text-muted border-0">
                                <a href="{{ route('roles.index', array_merge(request()->query(), ['sort' => 'label', 'direction' => $currentSort === 'label' && $currentDirection === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted">
                                    {{ __('Nama Peran (Label)') }}
                                    @if($currentSort === 'label')
                                        <i class="fa-solid fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1 text-primary"></i>
                                    @else
                                        <i class="fa-solid fa-sort ms-1 opacity-25"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="text-uppercase small text-muted border-0">
                                <a href="{{ route('roles.index', array_merge(request()->query(), ['sort' => 'users_count', 'direction' => $currentSort === 'users_count' && $currentDirection === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted">
                                    {{ __('Jumlah Pengguna') }}
                                    @if($currentSort === 'users_count')
                                        <i class="fa-solid fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1 text-primary"></i>
                                    @else
                                        <i class="fa-solid fa-sort ms-1 opacity-25"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="text-uppercase small text-muted border-0">
                                <a href="{{ route('roles.index', array_merge(request()->query(), ['sort' => 'permissions_count', 'direction' => $currentSort === 'permissions_count' && $currentDirection === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-muted">
                                    {{ __('Hak Akses') }}
                                    @if($currentSort === 'permissions_count')
                                        <i class="fa-solid fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ms-1 text-primary"></i>
                                    @else
                                        <i class="fa-solid fa-sort ms-1 opacity-25"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="text-end pe-3 text-uppercase small text-muted border-0">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        @php
                            $systemNames = \App\Models\Role::allSystemRoleNames();
                            $isSystemRole = in_array($role->name, $systemNames, true) || (bool) $role->is_system;
                        @endphp
                        <tr>
                            <td class="ps-3 fw-medium">
                                <code class="text-primary fw-bold">{{ $role->name }}</code>
                                @if($isSystemRole)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-2 rounded-pill px-2 py-0.5 small">
                                        <i class="fa-solid fa-shield-halved me-1"></i>{{ __('Sistem') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $role->label }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="fa-solid fa-users me-1"></i>{{ $role->users_count }} {{ __('Pengguna') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="fa-solid fa-key me-1"></i>{{ $role->permissions_count }} {{ __('Izin') }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Ubah Peran') }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    
                                    @if(!$isSystemRole)
                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus peran ini?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Hapus Peran') }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-shield-halved fa-2x mb-2 d-block opacity-25"></i>
                                {{ __('Belum ada data peran ditemukan.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $roles->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
