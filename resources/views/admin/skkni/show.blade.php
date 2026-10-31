@extends('layouts.admin')

@section('title', 'Detail SKKNI')
@section('pretitle', 'Master Standar')
@section('page-title', 'Detail SKKNI · '.$skkni->kode)

@section('content')
<x-tabler.card title="{{ $skkni->judul }}" subtitle="Kode: {{ $skkni->kode }} · Tahun {{ $skkni->tahun }}">
    <dl class="row">
        <dt class="col-sm-3">Nomor Penetapan</dt>
        <dd class="col-sm-9">{{ $skkni->nomor_penetapan ?? '—' }}</dd>
        <dt class="col-sm-3">Status</dt>
        <dd class="col-sm-9">
            @if ($skkni->is_active)
                <span class="badge bg-green-lt text-green">Aktif</span>
            @else
                <span class="badge bg-secondary-lt">Nonaktif</span>
            @endif
        </dd>
    </dl>
</x-tabler.card>

<x-tabler.card title="Unit Kompetensi di bawah SKKNI ini" class="mt-3">
    @if ($skkni->unitKompetensis->isEmpty())
        <x-tabler.empty title="Belum ada unit" icon="ti ti-list-check" />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode Unit</th>
                        <th>Judul Unit</th>
                        <th class="text-center">Elemen</th>
                        <th class="text-center">Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($skkni->unitKompetensis as $unit)
                        <tr>
                            <td class="fw-semibold">{{ $unit->kode_unit }}</td>
                            <td>{{ $unit->judul_unit }}</td>
                            <td class="text-center">{{ $unit->elemenKompetensis->count() }}</td>
                            <td class="text-center">
                                @if ($unit->is_active)
                                    <span class="badge bg-green-lt text-green">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.unit.show', $unit) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-tabler.card>

<div class="mt-3">
    <a href="{{ route('admin.stkkni.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
</div>
@endsection
