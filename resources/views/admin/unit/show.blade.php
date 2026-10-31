@extends('layouts.admin')

@section('title', 'Detail Unit Kompetensi')
@section('pretitle', 'Master Standar')
@section('page-title', 'Detail Unit · '.$unit->kode_unit)

@section('content')
<x-tabler.card title="{{ $unit->judul_unit }}" subtitle="Kode: {{ $unit->kode_unit }}">
    <dl class="row">
        <dt class="col-sm-3">SKKNI (induk)</dt>
        <dd class="col-sm-9">
            @if ($unit->skkni)
                <a href="{{ route('admin.stkkni.show', $unit->skkni) }}">{{ $unit->skkni->kode }} — {{ $unit->skkni->judul }}</a>
            @else
                —
            @endif
        </dd>
        <dt class="col-sm-3">Standar Kompetensi</dt>
        <dd class="col-sm-9">{{ $unit->standar_kompetensi ?? '—' }}</dd>
        <dt class="col-sm-3">Deskripsi</dt>
        <dd class="col-sm-9">{{ $unit->deskripsi ?? '—' }}</dd>
        <dt class="col-sm-3">Status</dt>
        <dd class="col-sm-9">
            @if ($unit->is_active)
                <span class="badge bg-green-lt text-green">Aktif</span>
            @else
                <span class="badge bg-secondary-lt">Nonaktif</span>
            @endif
        </dd>
        @if ($unit->skemas->isNotEmpty())
            <dt class="col-sm-3">Dipakai di Skema</dt>
            <dd class="col-sm-9">
                @foreach ($unit->skemas as $skema)
                    <a href="{{ route('admin.skema.show', $skema) }}" class="badge bg-blue-lt text-blue me-1">{{ $skema->kode_skema }}</a>
                @endforeach
            </dd>
        @endif
    </dl>
</x-tabler.card>

<x-tabler.card title="Elemen & KUK (Tree View)" class="mt-3">
    @if ($unit->elemenKompetensis->isEmpty())
        <x-tabler.empty title="Belum ada elemen" icon="ti ti-list" />
    @else
        @foreach ($unit->elemenKompetensis as $elemen)
            <div class="card mb-3">
                <div class="card-header">
                    <div class="fw-semibold">{{ $elemen->urutan }}. {{ $elemen->nama_elemen }}</div>
                </div>
                <div class="card-body">
                    @if ($elemen->kriteriaUnjukKerjas->isEmpty())
                        <div class="text-secondary small">Belum ada KUK.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                                <li class="list-group-item">
                                    <div class="fw-semibold small">{{ $kuk->urutan }}</div>
                                    <div class="small">{{ $kuk->pernyataan_kuk }}</div>
                                    @if ($kuk->pertanyaan_observasi)
                                        <div class="text-secondary small">Observasi: {{ $kuk->pertanyaan_observasi }}</div>
                                    @endif
                                    @if ($kuk->pertanyaan_lisan)
                                        <div class="text-secondary small">Lisan: {{ $kuk->pertanyaan_lisan }}</div>
                                    @endif
                                    @if ($kuk->pertanyaan_tertulis)
                                        <div class="text-secondary small">Tertulis: {{ $kuk->pertanyaan_tertulis }}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</x-tabler.card>

<div class="mt-3">
    <a href="{{ route('admin.unit.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection
