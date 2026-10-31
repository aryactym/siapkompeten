@extends('layouts.admin')

@section('title', 'Daftar Asesor')
@section('pretitle', 'Asesor & Penugasan')
@section('page-title', 'Daftar Asesor Kompetensi')

@section('actions')
    <a href="{{ route('admin.asesor.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> Tambah Asesor
    </a>
@endsection

@section('content')
<x-tabler.card title="Asesor Terdaftar">
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.asesor.index') }}" class="d-flex gap-2">
            <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm"
                   placeholder="Cari nama / no. register">
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-search"></i>
            </button>
        </form>
    </x-slot:actions>

    @if ($asesors->isEmpty())
        <x-tabler.empty
            title="Belum ada profil asesor"
            description="Daftarkan pengguna berperan asesor ke dalam profil asesor kompetensi lembaga."
            icon="ti ti-user-plus"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama Asesor</th>
                        <th>No. Register</th>
                        <th>Bidang Keahlian</th>
                        <th>Penugasan</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($asesors as $asesor)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $asesor->nama_lengkap }}</div>
                                <div class="text-secondary small">{{ $asesor->user?->email ?? '—' }}</div>
                            </td>
                            <td class="text-secondary">{{ $asesor->no_register }}</td>
                            <td class="text-secondary">{{ $asesor->bidang_keahlian ?: '—' }}</td>
                            <td><span class="badge bg-azure-lt">{{ $asesor->penugasan_count }} jadwal</span></td>
                            <td><x-tabler.status-badge :status="$asesor->status" /></td>
                            <td>
                                <a href="{{ route('admin.asesor.edit', $asesor) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $asesors->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
