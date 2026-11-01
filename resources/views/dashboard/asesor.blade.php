@extends('layouts.asesor')

@section('title', 'Dashboard Asesor Kompetensi')
@section('pretitle', 'Penugasan & Uji Kompetensi')
@section('page-title', 'Selamat Datang, ' . auth()->user()->name)

@section('actions')
    <a href="{{ route('asesor.jadwal.index') }}" class="btn btn-primary d-none d-sm-inline-block">
        <i class="ti ti-calendar me-1"></i> Lihat Jadwal Uji
    </a>
@endsection

@section('content')
<div class="row row-deck row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat
            label="Jadwal Penugasan"
            value="{{ $stats['total_jadwal'] ?? 0 }}"
            icon="calendar-event"
            color="primary"
        />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat
            label="Verifikasi APL-02"
            value="{{ $stats['pending_apl02'] ?? 0 }}"
            icon="checklist"
            color="yellow"
        />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat
            label="Asesi Dinilai"
            value="{{ $stats['asesi_dinilai'] ?? 0 }}"
            icon="user-check"
            color="green"
        />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat
            label="FR.AK.02 Diselesaikan"
            value="{{ $stats['laporan_mak02'] ?? 0 }}"
            icon="file-analytics"
            color="azure"
        />
    </div>
</div>

<div class="row row-cards">
    <div class="col-lg-8">
        <x-tabler.card title="Penugasan Uji Kompetensi Terdekat">
            @if ($jadwals->isEmpty())
                <x-tabler.empty 
                    title="Belum ada penugasan uji terdekat"
                    description="Ketika administrator LSP menugaskan Anda pada jadwal uji kompetensi (sebagai Asesor Utama, Pendamping, atau Observer), data jadwal akan muncul di sini."
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
                                    <td class="small">
                                        {{ $jadwal->tanggal->translatedFormat('d M Y') }}
                                        <div class="text-secondary">
                                            {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}
                                        </div>
                                    </td>
                                    <td class="text-secondary small">{{ $jadwal->tuk->nama_tuk }}</td>
                                    <td><span class="badge bg-green-lt">{{ $jadwal->pendaftarans_count }}</span></td>
                                    <td>
                                        <a href="{{ route('asesor.jadwal.peserta', $jadwal) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-users"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-tabler.card>

        <div class="card mt-3 shadow-sm border-0">
            <div class="card-header bg-transparent">
                <h3 class="card-title"><i class="ti ti-book-2 me-2 text-primary"></i>Panduan Pelaksanaan Asesmen BNSP</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="fw-bold text-dark mb-1"><i class="ti ti-file-check me-1 text-primary"></i> FR.AK.01</div>
                            <div class="text-secondary small">Persetujuan Asesmen & Kerahasiaan sebelum uji dilaksanakan.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="fw-bold text-dark mb-1"><i class="ti ti-file-text me-1 text-success"></i> FR.AK.02</div>
                            <div class="text-secondary small">Rekaman Asesmen Kompetensi (Kompeten / Belum Kompeten).</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="fw-bold text-dark mb-1"><i class="ti ti-message-report me-1 text-warning"></i> FR.AK.04</div>
                            <div class="text-secondary small">Formulir Banding Asesmen jika asesi mengajukan sanggahan hasil.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <x-tabler.card title="Profil Asesor Kompetensi">
            <div class="text-center mb-3">
                <span class="avatar avatar-xl rounded-circle bg-azure-lt text-azure fw-bold fs-1">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </span>
                <h3 class="mt-2 mb-0">{{ auth()->user()->name }}</h3>
                <div class="text-secondary small">{{ auth()->user()->email }}</div>
            </div>

            <div class="divide-y">
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">No. Reg MET (BNSP)</span>
                    <span class="fw-semibold">{{ $asesor?->no_register ?? 'Belum terdaftar' }}</span>
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Status Lisensi</span>
                    <span class="badge bg-green-lt"><i class="ti ti-check me-1"></i> Aktif</span>
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Lembaga Terdaftar</span>
                    <span class="fw-semibold">{{ session(\App\Support\TenantContext::SESSION_NAME) ?? 'LSP SiapKompeten' }}</span>
                </div>
            </div>

            <div class="mt-3">
                <a href="#profil-asesor" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="ti ti-edit me-1"></i> Perbarui Profil & Portofolio
                </a>
            </div>
        </x-tabler.card>
    </div>
</div>
@endsection
