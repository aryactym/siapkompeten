@extends('layouts.admin')

@section('title', 'Bundle Sertifikat')
@section('pretitle', 'Sertifikasi')
@section('page-title', 'Bundle Sertifikat per Jadwal')

@section('content')
<x-tabler.card title="Pilih Jadwal untuk Penerbitan Massal">
    <p class="text-secondary mb-4">
        Pilih jadwal asesmen yang sudah selesai untuk menerbitkan sertifikat bagi seluruh peserta kompeten sekaligus.
        Hanya jadwal berstatus <strong>selesai</strong> dengan peserta kompeten yang belum bersertifikat yang ditampilkan.
    </p>

    @if ($jadwals->isEmpty())
        <x-tabler.empty
            title="Tidak ada jadwal siap"
            description="Belum ada jadwal selesai dengan peserta kompeten yang belum diterbitkan sertifikatnya."
            icon="ti ti-calendar-check"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Jadwal</th>
                        <th>Event</th>
                        <th>Tanggal</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Siap Terbit</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jadwals as $jadwal)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $jadwal->skema?->nama_skema ?? '—' }}</div>
                                @if ($jadwal->gelombang)
                                    <div class="text-secondary small">{{ $jadwal->gelombang }}</div>
                                @endif
                            </td>
                            <td class="text-secondary">{{ $jadwal->event?->nama ?? '—' }}</td>
                            <td class="text-secondary">
                                {{ $jadwal->tanggal?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-lt">{{ $jadwal->total_selesai_count }}</span>
                            </td>
                            <td class="text-center">
                                @if ($jadwal->kompeten_count > 0)
                                    <span class="badge bg-success-lt text-success fw-semibold">{{ $jadwal->kompeten_count }}</span>
                                @else
                                    <span class="badge bg-secondary-lt text-secondary">0</span>
                                @endif
                            </td>
                            <td>
                                @if ($jadwal->kompeten_count > 0)
                                    <a href="{{ route('admin.sertifikat.bundle.create', $jadwal) }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="ti ti-certificate me-1"></i> Terbitkan
                                    </a>
                                @else
                                    <span class="text-secondary small">Semua sudah terbit</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $jadwals->links() }}
        </div>
    @endif
</x-tabler.card>
@endsection
