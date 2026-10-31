@extends('layouts.admin')

@section('title', 'Pendaftaran Asesmen')
@section('pretitle', 'Asesi & Pendaftaran')
@section('page-title', 'Pendaftaran Asesmen')

@php
    $tabs = [
        'semua' => 'Semua',
        'perlu_verifikasi' => 'Perlu Verifikasi',
        'siap_plotting' => 'Siap Plotting',
        'terjadwal' => 'Terjadwal',
        'ditolak' => 'Ditolak',
    ];
@endphp

@section('content')
<x-tabler.card>
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.pendaftaran.index') }}" class="d-flex gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm"
                   placeholder="Cari no. pendaftaran / nama">
            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="ti ti-search"></i></button>
        </form>
    </x-slot:actions>

    <ul class="nav nav-tabs card-header-tabs mb-3">
        @foreach ($tabs as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $tab === $key ? 'active' : '' }}"
                   href="{{ route('admin.pendaftaran.index', array_filter(['tab' => $key, 'q' => $search])) }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>

    @if ($pendaftarans->isEmpty())
        <x-tabler.empty
            title="Belum ada pendaftaran"
            description="Pendaftaran asesi akan muncul di sini setelah asesi memilih skema sertifikasi."
            icon="ti ti-file-off"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Pendaftaran</th>
                        <th>Asesi</th>
                        <th>Skema</th>
                        <th>Dokumen</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendaftarans as $pendaftaran)
                        <tr>
                            <td class="fw-semibold">{{ $pendaftaran->no_pendaftaran }}</td>
                            <td>
                                <div>{{ $pendaftaran->asesi->user->name }}</div>
                                <div class="text-secondary small">{{ $pendaftaran->asesi->no_registrasi }}</div>
                            </td>
                            <td>{{ $pendaftaran->skema->nama_skema }}</td>
                            <td><span class="badge bg-azure-lt">{{ $pendaftaran->dokumens_count }} berkas</span></td>
                            <td class="text-secondary small">
                                @if ($pendaftaran->jadwal)
                                    {{ $pendaftaran->jadwal->tanggal->translatedFormat('d M Y') }}
                                    <div>{{ $pendaftaran->jadwal->tuk->nama_tuk }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td><x-tabler.status-badge :status="$pendaftaran->status" /></td>
                            <td>
                                <a href="{{ route('admin.pendaftaran.show', $pendaftaran) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $pendaftarans->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
