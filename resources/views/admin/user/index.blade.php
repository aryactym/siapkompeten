@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('pretitle', 'Sistem')
@section('page-title', 'Manajemen Pengguna')

@section('actions')
    <a href="{{ route('admin.user.create') }}" class="btn btn-primary">
        <i class="ti ti-user-plus me-1"></i> Tambah Pengguna
    </a>
@endsection

@section('content')
<x-tabler.card title="Daftar Pengguna">
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.user.index') }}" class="d-flex gap-2 flex-wrap">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama, email, username..."
                   value="{{ $search }}" style="width: 200px;">
            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Peran</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected($roleFilter === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif" @selected($statusFilter === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($statusFilter === 'nonaktif')>Nonaktif</option>
                <option value="diblokir" @selected($statusFilter === 'diblokir')>Diblokir</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-search"></i>
            </button>
        </form>
    </x-slot:actions>

    @if ($users->isEmpty())
        <x-tabler.empty
            title="Belum ada pengguna"
            description="Tambah pengguna baru untuk mengelola akses ke sistem."
            icon="ti ti-users"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email / Username</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                @if ($user->masked_nik)
                                    <div class="text-secondary small">NIK: {{ $user->masked_nik }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $user->email }}</div>
                                @if ($user->username)
                                    <div class="text-secondary small">{{ $user->username }}</div>
                                @endif
                            </td>
                            <td>
                                @foreach ($user->roles as $role)
                                    @if ($role->name === 'admin')
                                        <span class="badge bg-primary-lt text-primary">Admin</span>
                                    @elseif ($role->name === 'asesor')
                                        <span class="badge bg-azure-lt text-azure">Asesor</span>
                                    @elseif ($role->name === 'asesi')
                                        <span class="badge bg-green-lt text-green">Asesi</span>
                                    @elseif ($role->name === 'pimpinan')
                                        <span class="badge bg-orange-lt text-orange">Pimpinan</span>
                                    @elseif ($role->name === 'super-admin')
                                        <span class="badge bg-dark-lt">Super Admin</span>
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @if ($user->status === 'aktif')
                                    <span class="badge bg-success-lt text-success">Aktif</span>
                                @elseif ($user->status === 'nonaktif')
                                    <span class="badge bg-secondary-lt text-secondary">Nonaktif</span>
                                @else
                                    <span class="badge bg-danger-lt text-danger">Diblokir</span>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                @if ($user->last_login_at)
                                    {{ $user->last_login_at->translatedFormat('d M Y H:i') }}
                                @else
                                    Belum pernah
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.user.edit', $user) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.user.destroy', $user) }}"
                                          onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $users->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
