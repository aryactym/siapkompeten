@extends('layouts.asesor')

@section('title', 'Jadwal Uji Saya')
@section('pretitle', 'Penugasan')
@section('page-title', 'Jadwal Uji Kompetensi Saya')

@section('content')
@if ($asesor === null)
    <div class="alert alert-warning">
        Profil asesor Anda belum dibuat oleh admin. Hubungi administrator lembaga untuk melengkapi profil asesor.
    </div>
@else
    <x-tabler.card title="Penugasan Uji">
        @if ($jadwals->isEmpty())
            <x-tabler.empty
                title="Belum ada penugasan"
                description="Jadwal yang ditugaskan kepada Anda akan muncul di sini."
                icon="ti ti-calendar-off"
            />
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Skema / Event</th>
                            <th>Waktu</th>
                            <th>TUK</th>
                            <th>Peserta</th>
                            <th>Status</th>
                            <th class="w-1">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwals as $jadwal)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $jadwal->skema->nama_skema }}</div>
                                    <div class="text-secondary small">{{ $jadwal->event->nama }}</div>
                                </td>
                                <td>
                                    <div>{{ $jadwal->tanggal->translatedFormat('d M Y') }}</div>
                                    <div class="text-secondary small">
                                        {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                                    </div>
                                </td>
                                <td class="text-secondary">{{ $jadwal->tuk->nama_tuk }}</td>
                                <td><span class="badge bg-green-lt">{{ $jadwal->pendaftarans_count }} peserta</span></td>
                                <td><x-tabler.status-badge :status="$jadwal->status" /></td>
                                <td>
                                    <a href="{{ route('asesor.jadwal.peserta', $jadwal) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-users me-1"></i> Peserta
                                    </a>
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
@endif
@endsection
