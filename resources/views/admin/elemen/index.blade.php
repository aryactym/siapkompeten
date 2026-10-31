@extends('layouts.admin')

@section('title', 'Elemen Kompetensi')
@section('pretitle', 'Master Standar')
@section('page-title', 'Elemen & Kriteria Unjuk Kerja (KUK)')

@section('actions')
    <form method="GET" action="{{ route('admin.elemen.index') }}" class="d-flex gap-2">
        <select name="unit" class="form-select form-select-sm" style="width: 18rem;" onchange="this.form.submit()">
            <option value="">Semua unit</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected((string) request('unit') === (string) $unit->id)>
                    {{ $unit->kode_unit }} — {{ $unit->judul_unit }}
                </option>
            @endforeach
        </select>
    </form>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-elemen-baru">
        <i class="ti ti-plus me-1"></i> Tambah Elemen
    </button>
@endsection

@section('content')
@if ($unitTerpilih)
    <div class="alert alert-info">Menampilkan elemen untuk unit: <strong>{{ $unitTerpilih->kode_unit }} — {{ $unitTerpilih->judul_unit }}</strong></div>
@endif

<x-tabler.card title="Daftar Elemen Kompetensi" subtitle="Elemen dan KUK per unit kompetensi.">
    @if ($elemens->isEmpty())
        <x-tabler.empty title="Belum ada elemen" description="Tambahkan elemen kompetensi." icon="ti ti-list" />
    @else
        @foreach ($elemens as $elemen)
            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <div class="fw-semibold">{{ $elemen->urutan }}. {{ $elemen->nama_elemen }}</div>
                        <div class="text-secondary small">{{ $elemen->unitKompetensi?->kode_unit }} — {{ $elemen->unitKompetensi?->judul_unit }}</div>
                    </div>
                    <div class="card-actions ms-auto btn-list">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#modal-elemen-{{ $elemen->id }}">
                            <i class="ti ti-edit"></i>
                        </button>
                        <form method="POST" action="{{ route('admin.elemen.destroy', $elemen) }}" onsubmit="return confirm('Hapus elemen ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                        </form>
                        <button type="button" class="btn btn-sm btn-outline-success"
                                data-bs-toggle="modal" data-bs-target="#modal-kuk-{{ $elemen->id }}">
                            <i class="ti ti-plus"></i> KUK
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if ($elemen->kriteriaUnjukKerjas->isEmpty())
                        <div class="text-secondary small">Belum ada KUK.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold small">{{ $kuk->urutan }}. {{ $kuk->pernyataan_kuk }}</div>
                                        @if ($kuk->pertanyaan_observasi)
                                            <div class="text-secondary small">Observasi: {{ $kuk->pertanyaan_observasi }}</div>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('admin.kuk.destroy', $kuk) }}" onsubmit="return confirm('Hapus KUK ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Modal Ubah Elemen -->
            <div class="modal modal-blur fade" id="modal-elemen-{{ $elemen->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <form method="POST" action="{{ route('admin.elemen.update', $elemen) }}" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Ubah Elemen</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('admin.elemen._form', ['elemen' => $elemen, 'units' => $units])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto"><i class="ti ti-device-floppy me-1"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Tambah KUK -->
            <div class="modal modal-blur fade" id="modal-kuk-{{ $elemen->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <form method="POST" action="{{ route('admin.elemen.kuk.store', $elemen) }}" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah KUK — {{ $elemen->nama_elemen }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('admin.elemen._kuk_form', ['elemen' => $elemen])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary ms-auto"><i class="ti ti-device-floppy me-1"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
</x-tabler.card>

<div class="modal modal-blur fade" id="modal-elemen-baru" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.elemen.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Elemen Kompetensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('admin.elemen._form', ['elemen' => $newElemen, 'units' => $units])
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
@endsection
