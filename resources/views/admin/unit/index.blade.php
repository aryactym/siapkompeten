@extends('layouts.admin')

@section('title', 'Unit Kompetensi')
@section('pretitle', 'Master Standar')
@section('page-title', 'Unit Kompetensi')

@section('actions')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-unit-baru">
        <i class="ti ti-plus me-1"></i> Tambah Unit
    </button>
@endsection

@section('content')
<x-tabler.card title="Daftar Unit Kompetensi" subtitle="Unit kompetensi (mandiri, dapat dibagi antar skema).">
    @if ($units->isEmpty())
        <x-tabler.empty title="Belum ada unit kompetensi" description="Tambahkan unit kompetensi." icon="ti ti-list-check" />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode Unit</th>
                        <th>Judul Unit</th>
                        <th>SKKNI</th>
                        <th class="text-center">Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td class="fw-semibold">{{ $unit->kode_unit }}</td>
                            <td>{{ $unit->judul_unit }}</td>
                            <td>{{ $unit->skkni?->judul ?? '-' }}</td>
                            <td class="text-center">
                                @if ($unit->is_active)
                                    <span class="badge bg-green-lt text-green">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.unit.show', $unit) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modal-unit-{{ $unit->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.unit.destroy', $unit) }}"
                                          onsubmit="return confirm('Hapus unit ini?')">
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

<div class="modal modal-blur fade" id="modal-unit-baru" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.unit.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Unit Kompetensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('admin.unit._form', ['unit' => $newUnit, 'skknis' => $skknis])
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

@foreach ($units as $unit)
    <div class="modal modal-blur fade" id="modal-unit-{{ $unit->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('admin.unit.update', $unit) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Unit Kompetensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('admin.unit._form', ['unit' => $unit, 'skknis' => $skknis])
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
