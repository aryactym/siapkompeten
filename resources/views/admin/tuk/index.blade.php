@extends('layouts.admin')

@section('title', 'TUK')
@section('pretitle', 'Master Standar')
@section('page-title', 'Tempat Uji Kompetensi (TUK)')

@section('actions')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tuk-baru">
        <i class="ti ti-plus me-1"></i> Tambah TUK
    </button>
@endsection

@section('content')
<x-tabler.card title="Daftar TUK" subtitle="Tempat pelaksanaan uji kompetensi (sewaktu/mandiri/tempat kerja).">
    @if ($tuks->isEmpty())
        <x-tabler.empty title="Belum ada TUK" description="Tambahkan TUK." icon="ti ti-building" />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama TUK</th>
                        <th>Jenis</th>
                        <th>Penanggung Jawab</th>
                        <th>Wilayah</th>
                        <th class="text-center">Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tuks as $tuk)
                        <tr>
                            <td class="fw-semibold">{{ $tuk->kode_tuk }}</td>
                            <td>{{ $tuk->nama_tuk }}</td>
                            <td><span class="badge bg-blue-lt text-blue">{{ $tuk->jenis }}</span></td>
                            <td>{{ $tuk->penanggung_jawab }}</td>
                            <td>{{ $tuk->city?->name ?? '-' }}</td>
                            <td class="text-center">
                                @if ($tuk->is_active)
                                    <span class="badge bg-green-lt text-green">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modal-tuk-{{ $tuk->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.tuk.destroy', $tuk) }}"
                                          onsubmit="return confirm('Hapus TUK ini?')">
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

<div class="modal modal-blur fade" id="modal-tuk-baru" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form method="POST" action="{{ route('admin.tuk.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah TUK</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('admin.tuk._form', ['tuk' => $newTuk, 'provinces' => $provinces, 'cities' => $cities])
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

@foreach ($tuks as $tuk)
    <div class="modal modal-blur fade" id="modal-tuk-{{ $tuk->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <form method="POST" action="{{ route('admin.tuk.update', $tuk) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Ubah TUK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('admin.tuk._form', ['tuk' => $tuk, 'provinces' => $provinces, 'cities' => $cities])
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
