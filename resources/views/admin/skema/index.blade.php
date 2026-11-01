@extends('layouts.admin')

@section('title', 'Skema Sertifikasi')
@section('pretitle', 'Master Standar')
@section('page-title', 'Skema Sertifikasi')

@section('actions')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-skema-baru">
        <i class="ti ti-plus me-1"></i> Tambah Skema
    </button>
@endsection

@section('content')
<x-tabler.card title="Daftar Skema" subtitle="Skema sertifikasi (okupasi/klaster/KKNI) yang ditawarkan LSP.">
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.skema.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="search" name="q" value="{{ $searchQuery }}"
                   class="form-control form-control-sm" style="min-width:220px"
                   placeholder="Cari kode / nama skema">
            <select name="jenis" class="form-select form-select-sm" style="min-width:150px">
                <option value="">— Semua Jenis —</option>
                <option value="kkni"     @selected($selectedJenis === 'kkni')>KKNI</option>
                <option value="okupasi"  @selected($selectedJenis === 'okupasi')>Okupasi</option>
                <option value="klaster"  @selected($selectedJenis === 'klaster')>Klaster</option>
            </select>
            @if ($kategoriSkemas->isNotEmpty())
                <select name="kategori" class="form-select form-select-sm" style="min-width:180px">
                    <option value="">— Semua Kategori —</option>
                    @foreach ($kategoriSkemas as $kategori)
                        <option value="{{ $kategori }}" @selected($selectedKategori === $kategori)>
                            {{ $kategori }}
                        </option>
                    @endforeach
                </select>
            @endif
            <select name="status" class="form-select form-select-sm" style="min-width:140px">
                <option value="">— Semua Status —</option>
                <option value="aktif"    @selected($selectedStatus === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($selectedStatus === 'nonaktif')>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-search"></i>
            </button>
            @if ($searchQuery !== '' || $selectedJenis !== '' || $selectedKategori !== '' || $selectedStatus !== '')
                <a href="{{ route('admin.skema.index') }}" class="btn btn-sm btn-ghost-secondary" title="Reset filter">
                    <i class="ti ti-x"></i>
                </a>
            @endif
        </form>
    </x-slot:actions>

    @if ($skemas->isEmpty())
        @if ($searchQuery !== '' || $selectedJenis !== '' || $selectedKategori !== '' || $selectedStatus !== '')
            <x-tabler.empty title="Tidak ada hasil" description="Tidak ditemukan skema yang cocok dengan filter." icon="ti ti-search-off" />
        @else
            <x-tabler.empty title="Belum ada skema" description="Tambahkan skema sertifikasi." icon="ti ti-certificate" />
        @endif
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Skema</th>
                        <th>Jenis</th>
                        <th>Level KKNI</th>
                        <th class="text-end">Biaya</th>
                        <th class="text-center">Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($skemas as $skema)
                        <tr>
                            <td class="fw-semibold">{{ $skema->kode_skema }}</td>
                            <td>{{ $skema->nama_skema }}</td>
                            <td><span class="badge bg-blue-lt text-blue">{{ $skema->jenis }}</span></td>
                            <td class="text-center">{{ $skema->level_kkni ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format((float) $skema->biaya, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if ($skema->is_active)
                                    <span class="badge bg-green-lt text-green">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.skema.show', $skema) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.skema.persyaratan', $skema) }}" class="btn btn-sm btn-outline-info" title="Persyaratan">
                                        <i class="ti ti-file-check"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modal-skema-{{ $skema->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.skema.destroy', $skema) }}"
                                          onsubmit="return confirm('Hapus skema ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
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
    @endif
</x-tabler.card>

<div class="modal modal-blur fade" id="modal-skema-baru" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.skema.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Skema Sertifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('admin.skema._form', ['skema' => $newSkema])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary ms-auto">
                    <i class="ti ti-device-floppy me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@foreach ($skemas as $skema)
    <div class="modal modal-blur fade" id="modal-skema-{{ $skema->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('admin.skema.update', $skema) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Skema Sertifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('admin.skema._form', ['skema' => $skema])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary ms-auto">
                        <i class="ti ti-device-floppy me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection
