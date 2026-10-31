@extends('layouts.admin')

@section('title', 'Daftar Asesi')
@section('pretitle', 'Asesi & Pendaftaran')
@section('page-title', 'Daftar Asesi')

@php
    $tabs = [
        'semua' => 'Semua',
        'belum_terverifikasi' => 'Belum Terverifikasi',
        'terverifikasi' => 'Terverifikasi',
        'kompeten' => 'Kompeten',
        'belum_kompeten' => 'Belum Kompeten',
    ];
@endphp

@section('content')
<x-tabler.card>
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.asesi.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="col-auto">
                <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm"
                       placeholder="Cari nama / no. registrasi">
            </div>
            <div class="col-auto">
                <select name="provinsi_code" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Provinsi</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province->code }}" @selected($provinsiCode === $province->code)>{{ $province->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="kota_code" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Kota/Kab.</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->code }}" @selected($kotaCode === $city->code)>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="ti ti-search"></i></button>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.asesi.import.create') }}" class="btn btn-sm btn-success">
                    <i class="ti ti-file-import me-1"></i> Impor Data Asesi
                </a>
            </div>
        </form>
    </x-slot:actions>

    <ul class="nav nav-tabs card-header-tabs mb-3">
        @foreach ($tabs as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $tab === $key ? 'active' : '' }}"
                   href="{{ route('admin.asesi.index', array_filter(['tab' => $key, 'q' => $search, 'provinsi_code' => $provinsiCode, 'kota_code' => $kotaCode])) }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>

    @if ($asesis->isEmpty())
        <x-tabler.empty
            title="Belum ada asesi"
            description="Asesi dapat mendaftar mandiri melalui halaman registrasi atau ditambahkan oleh admin."
            icon="ti ti-users-off"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama Asesi</th>
                        <th>No. Registrasi</th>
                        <th>Wilayah</th>
                        <th>Pendidikan</th>
                        <th>Verifikasi</th>
                        <th>Kompetensi</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($asesis as $asesi)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $asesi->user->name }}</div>
                                <div class="text-secondary small">{{ $asesi->user->email }}</div>
                            </td>
                            <td class="text-secondary">{{ $asesi->no_registrasi }}</td>
                            <td class="text-secondary small">
                                {{ $asesi->city?->name ?? '—' }}
                                @if ($asesi->province)
                                    <div>{{ $asesi->province->name }}</div>
                                @endif
                            </td>
                            <td class="text-secondary small">{{ $asesi->pendidikan_terakhir ?: '—' }}</td>
                            <td><x-tabler.status-badge :status="$asesi->status_verifikasi" /></td>
                            <td><x-tabler.status-badge :status="$asesi->status_kompetensi" /></td>
                            <td>
                                <a href="{{ route('admin.asesi.show', $asesi) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $asesis->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
