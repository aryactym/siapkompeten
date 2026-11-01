@extends('layouts.admin')

@section('title', 'SKKNI')
@section('pretitle', 'Master Standar')
@section('page-title', 'Standar Kompetensi Kerja Nasional Indonesia (SKKNI)')

@section('actions')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-skkni-baru">
        <i class="ti ti-plus me-1"></i> Tambah SKKNI
    </button>
@endsection

@section('content')
<x-tabler.card title="Daftar SKKNI" subtitle="Payung hukum/standar kompetensi yang menaungi unit kompetensi.">
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.stkkni.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
            <input type="search" name="q" value="{{ $searchQuery }}"
                   class="form-control form-control-sm" style="min-width:220px"
                   placeholder="Cari kode / judul SKKNI">
            @if ($tahunList->isNotEmpty())
                <select name="tahun" class="form-select form-select-sm" style="min-width:120px">
                    <option value="">— Semua Tahun —</option>
                    @foreach ($tahunList as $tahun)
                        <option value="{{ $tahun }}" @selected($selectedTahun === (string) $tahun)>
                            {{ $tahun }}
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
            @if ($searchQuery !== '' || $selectedTahun !== '' || $selectedStatus !== '')
                <a href="{{ route('admin.stkkni.index') }}" class="btn btn-sm btn-ghost-secondary" title="Reset filter">
                    <i class="ti ti-x"></i>
                </a>
            @endif
        </form>
    </x-slot:actions>

    @if ($skknis->isEmpty())
        @if ($searchQuery !== '' || $selectedTahun !== '' || $selectedStatus !== '')
            <x-tabler.empty title="Tidak ada hasil" description="Tidak ditemukan SKKNI yang cocok dengan filter." icon="ti ti-search-off" />
        @else
            <x-tabler.empty title="Belum ada SKKNI" description="Tambahkan SKKNI sebagai induk unit kompetensi." icon="ti ti-certificate" />
        @endif
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Judul</th>
                        <th>Nomor Penetapan</th>
                        <th class="text-center">Tahun</th>
                        <th class="text-center">Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($skknis as $skkni)
                        <tr>
                            <td class="fw-semibold">{{ $skkni->kode }}</td>
                            <td>{{ $skkni->judul }}</td>
                            <td>{{ $skkni->nomor_penetapan ?? '-' }}</td>
                            <td class="text-center">{{ $skkni->tahun }}</td>
                            <td class="text-center">
                                @if ($skkni->is_active)
                                    <span class="badge bg-green-lt text-green">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.stkkni.show', $skkni) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modal-skkni-{{ $skkni->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.stkkni.destroy', $skkni) }}"
                                          onsubmit="return confirm('Hapus SKKNI ini?')">
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

<!-- Modal Tambah -->
<div class="modal modal-blur fade" id="modal-skkni-baru" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.stkkni.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah SKKNI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('admin.skkni._form', ['skkni' => $newSkkni])
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

<!-- Modal Ubah -->
@foreach ($skknis as $skkni)
    <div class="modal modal-blur fade" id="modal-skkni-{{ $skkni->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('admin.stkkni.update', $skkni) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Ubah SKKNI</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('admin.skkni._form', ['skkni' => $skkni])
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
