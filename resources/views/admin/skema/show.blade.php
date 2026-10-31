@extends('layouts.admin')

@section('title', 'Detail Skema')
@section('pretitle', 'Master Standar')
@section('page-title', 'Detail Skema · '.$skema->kode_skema)

@section('content')
<x-tabler.card title="{{ $skema->nama_skema }}" subtitle="Kode: {{ $skema->kode_skema }}">
    <dl class="row">
        <dt class="col-sm-3">Nomor Skema</dt>
        <dd class="col-sm-9">{{ $skema->nomor_skema ?? '—' }}</dd>
        <dt class="col-sm-3">Jenis</dt>
        <dd class="col-sm-9">{{ strtoupper($skema->jenis) }}</dd>
        <dt class="col-sm-3">Level KKNI</dt>
        <dd class="col-sm-9">{{ $skema->level_kkni ?? '—' }}</dd>
        <dt class="col-sm-3">Kategori</dt>
        <dd class="col-sm-9">{{ $skema->kategori ?? '—' }}</dd>
        <dt class="col-sm-3">Biaya</dt>
        <dd class="col-sm-9">Rp {{ number_format((float) $skema->biaya, 0, ',', '.') }}</dd>
        <dt class="col-sm-3">Status</dt>
        <dd class="col-sm-9">
            @if ($skema->is_active)
                <span class="badge bg-green-lt text-green">Aktif</span>
            @else
                <span class="badge bg-secondary-lt">Nonaktif</span>
            @endif
        </dd>
        @if ($skema->deskripsi)
            <dt class="col-sm-3">Deskripsi</dt>
            <dd class="col-sm-9">{{ $skema->deskripsi }}</dd>
        @endif
    </dl>
</x-tabler.card>

@if ($skema->persyaratans->isNotEmpty())
    <x-tabler.card title="Persyaratan Skema" class="mt-3">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr><th>Urutan</th><th>Nama Dokumen</th><th>Keterangan</th><th>Wajib</th></tr>
                </thead>
                <tbody>
                    @foreach ($skema->persyaratans as $p)
                        <tr>
                            <td>{{ $p->urutan }}</td>
                            <td>{{ $p->nama_dokumen }}</td>
                            <td class="text-secondary small">{{ $p->keterangan ?? '—' }}</td>
                            <td>{{ $p->is_required ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-tabler.card>
@endif

<x-tabler.card title="Unit Kompetensi (Tree View)" class="mt-3">
    @if ($skema->skemaUnits->isEmpty())
        <x-tabler.empty title="Belum ada unit yang dipetakan" icon="ti ti-list-check" />
    @else
        @foreach ($skema->skemaUnits as $su)
            @php $unit = $su->unitKompetensi; @endphp
            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <div class="fw-semibold">{{ $unit->kode_unit }} — {{ $unit->judul_unit }}</div>
                        <div class="text-secondary small">
                            Jenis: {{ $su->jenis }} · Urutan {{ $su->urutan }}
                            @if ($unit->skkni) · SKKNI: {{ $unit->skkni->kode }} @endif
                        </div>
                    </div>
                    <div class="card-actions ms-auto">
                        <a href="{{ route('admin.unit.show', $unit) }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($unit->elemenKompetensis->isEmpty())
                        <div class="text-secondary small">Belum ada elemen.</div>
                    @else
                        @foreach ($unit->elemenKompetensis as $elemen)
                            <div class="mb-2">
                                <div class="fw-semibold small">{{ $elemen->urutan }}. {{ $elemen->nama_elemen }}</div>
                                @if ($elemen->kriteriaUnjukKerjas->isNotEmpty())
                                    <ul class="mb-0 small text-secondary">
                                        @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                                            <li>{{ $kuk->urutan }} — {{ $kuk->pernyataan_kuk }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</x-tabler.card>

<div class="mt-3">
    <a href="{{ route('admin.skema.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection
