@extends('layouts.admin')

@section('title', 'Detail Asesi')
@section('pretitle', 'Asesi & Pendaftaran')
@section('page-title', $asesi->user->name)

@section('actions')
    @if ($asesi->status_verifikasi === 'belum_terverifikasi')
        <form method="POST" action="{{ route('admin.asesi.verify', $asesi) }}"
              onsubmit="return confirm('Verifikasi data asesi ini?')">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="ti ti-check me-1"></i> Verifikasi Asesi
            </button>
        </form>
    @endif
@endsection

@section('content')
<div class="row row-cards">
    <div class="col-lg-4">
        <x-tabler.card title="Identitas Asesi">
            <div class="text-center mb-3">
                <span class="avatar avatar-xl bg-blue-lt text-primary fw-bold">
                    {{ strtoupper(substr($asesi->user->name, 0, 2)) }}
                </span>
                <h3 class="mt-2 mb-0">{{ $asesi->user->name }}</h3>
                <div class="text-secondary small">{{ $asesi->no_registrasi }}</div>
            </div>
            <div class="divide-y">
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">NIK</span>
                    <span class="fw-semibold">{{ $asesi->user->masked_nik ?? '—' }}</span>
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Email</span>
                    <span class="fw-semibold">{{ $asesi->user->email }}</span>
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Telepon</span>
                    <span class="fw-semibold">{{ $asesi->user->phone ?: '—' }}</span>
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Jenis Kelamin</span>
                    <span class="fw-semibold">{{ $asesi->jenis_kelamin === 'L' ? 'Laki-laki' : ($asesi->jenis_kelamin === 'P' ? 'Perempuan' : '—') }}</span>
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Tanggal Lahir</span>
                    <span class="fw-semibold">{{ $asesi->tanggal_lahir?->translatedFormat('d F Y') ?? '—' }}</span>
                </div>
                <div class="py-2">
                    <div class="text-secondary">Alamat</div>
                    <div class="fw-semibold">{{ $asesi->alamat_rumah ?: '—' }}</div>
                    <div class="text-secondary small">
                        {{ $asesi->city?->name ?? '' }}{{ $asesi->province ? ', '.$asesi->province->name : '' }}
                    </div>
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Pendidikan Terakhir</span>
                    <span class="fw-semibold">{{ $asesi->pendidikan_terakhir ?: '—' }}</span>
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Pekerjaan</span>
                    <span class="fw-semibold">{{ $asesi->pekerjaan_instansi ?: '—' }}</span>
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Status Verifikasi</span>
                    <x-tabler.status-badge :status="$asesi->status_verifikasi" />
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Status Kompetensi</span>
                    <x-tabler.status-badge :status="$asesi->status_kompetensi" />
                </div>
                <div class="py-2 d-flex justify-content-between">
                    <span class="text-secondary">Sumber Data</span>
                    <span class="badge bg-secondary-lt">{{ ucfirst($asesi->sumber) }}</span>
                </div>
            </div>
        </x-tabler.card>
    </div>

    <div class="col-lg-8">
        <x-tabler.card title="Riwayat Pendaftaran Skema">
            @if ($asesi->pendaftarans->isEmpty())
                <x-tabler.empty
                    title="Belum ada pendaftaran"
                    description="Asesi belum mendaftar pada skema sertifikasi mana pun."
                    icon="ti ti-file-off"
                />
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>No. Pendaftaran</th>
                                <th>Skema</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($asesi->pendaftarans as $pendaftaran)
                                <tr>
                                    <td>{{ $pendaftaran->no_pendaftaran }}</td>
                                    <td>{{ $pendaftaran->skema->nama_skema }}</td>
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
            @endif
        </x-tabler.card>
    </div>
</div>
@endsection
