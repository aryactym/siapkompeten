@extends('layouts.admin')

@section('title', 'Data Sertifikat')
@section('pretitle', 'Sertifikasi')
@section('page-title', 'Sertifikat Kompetensi')

@section('content')
<x-tabler.card title="Daftar Sertifikat Diterbitkan">
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.sertifikat.index') }}" class="d-flex gap-2">
            <input type="search" name="q" value="{{ $search }}" class="form-control form-control-sm"
                   placeholder="Cari nama / no. seri / no. registrasi">
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-search"></i>
            </button>
        </form>
    </x-slot:actions>

    @if ($sertifikats->isEmpty())
        <x-tabler.empty
            title="Belum ada sertifikat"
            description="Terbitkan sertifikat dari halaman detail pendaftaran yang sudah selesai dan kompeten."
            icon="ti ti-certificate"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Asesi</th>
                        <th>Skema</th>
                        <th>No. Registrasi</th>
                        <th>No. Seri Blangko</th>
                        <th>Tanggal Terbit</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sertifikats as $sertifikat)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $sertifikat->asesi?->user?->name ?? '—' }}</div>
                            </td>
                            <td class="text-secondary small">{{ $sertifikat->skema?->nama_skema ?? '—' }}</td>
                            <td class="font-monospace small">{{ $sertifikat->no_registrasi }}</td>
                            <td class="font-monospace small">{{ $sertifikat->no_seri_blangko }}</td>
                            <td class="text-secondary small">
                                {{ $sertifikat->tanggal_terbit?->translatedFormat('d M Y') }}
                            </td>
                            <td><x-tabler.status-badge :status="$sertifikat->status" /></td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.sertifikat.show', $sertifikat) }}"
                                       class="btn btn-sm btn-outline-secondary" title="Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.sertifikat.pdf', $sertifikat) }}"
                                       target="_blank" class="btn btn-sm btn-outline-primary" title="Unduh PDF">
                                        <i class="ti ti-file-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $sertifikats->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
