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
        <form method="GET" action="{{ route('admin.asesor.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="search" name="q" value="{{ $searchQuery }}"
                   class="form-control form-control-sm" style="min-width:220px"
                   placeholder="Cari nama / no. register / keahlian">
            <select name="status" class="form-select form-select-sm" style="min-width:140px">
                <option value="">— Semua Status —</option>
                <option value="aktif"    @selected($selectedStatus === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($selectedStatus === 'nonaktif')>Nonaktif</option>
            </select>
            <select name="jenis_kelamin" class="form-select form-select-sm" style="min-width:130px">
                <option value="">— Semua JK —</option>
                <option value="L" @selected($selectedJenisKelamin === 'L')>Laki-laki</option>
                <option value="P" @selected($selectedJenisKelamin === 'P')>Perempuan</option>
            </select>
            @if ($skemas->isNotEmpty())
                <select name="skema" class="form-select form-select-sm" style="min-width:200px">
                    <option value="">— Semua Skema —</option>
                    @foreach ($skemas as $skema)
                        <option value="{{ $skema->id }}" @selected($selectedSkema === $skema->id)>
                            {{ $skema->kode_skema }} — {{ $skema->nama_skema }}
                        </option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-search"></i>
            </button>
            @if ($searchQuery !== '' || $selectedStatus !== '' || $selectedJenisKelamin !== '' || $selectedSkema !== '')
                <a href="{{ route('admin.asesor.index') }}" class="btn btn-sm btn-ghost-secondary" title="Reset filter">
                    <i class="ti ti-x"></i>
                </a>
            @endif
        </form>
    </x-slot:actions>

    @if ($asesors->isEmpty())
        @if ($searchQuery !== '' || $selectedStatus !== '' || $selectedJenisKelamin !== '' || $selectedSkema !== '')
            <x-tabler.empty title="Tidak ada hasil" description="Tidak ditemukan asesor yang cocok dengan filter." icon="ti ti-search-off" />
        @else
            <x-tabler.empty
                title="Belum ada profil asesor"
                description="Daftarkan pengguna berperan asesor ke dalam profil asesor kompetensi lembaga."
                icon="ti ti-user-plus"
            />
        @endif
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
