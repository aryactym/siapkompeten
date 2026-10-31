@extends('layouts.admin')

@section('title', 'Detail Jadwal Uji')
@section('pretitle', 'Penjadwalan')
@section('page-title', $jadwal->skema->nama_skema)

@section('actions')
    <a href="{{ route('admin.jadwal.presensi', $jadwal) }}" target="_blank" class="btn btn-outline-secondary">
        <i class="ti ti-printer me-1"></i> Presensi
    </a>
    <a href="{{ route('admin.jadwal.edit', $jadwal) }}" class="btn btn-primary">
        <i class="ti ti-edit me-1"></i> Edit Jadwal
    </a>
@endsection

@section('content')
<div class="row row-cards mb-3">
    <div class="col-lg-8">
        <x-tabler.card title="Informasi Jadwal">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-secondary small">Event</div>
                    <div class="fw-semibold">{{ $jadwal->event->nama }} ({{ $jadwal->event->tahun }})</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">TUK</div>
                    <div class="fw-semibold">{{ $jadwal->tuk->nama_tuk }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Tanggal</div>
                    <div class="fw-semibold">{{ $jadwal->tanggal->translatedFormat('d F Y') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Waktu</div>
                    <div class="fw-semibold">
                        {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Kuota</div>
                    <div class="fw-semibold">{{ $jadwal->pendaftarans->count() }} / {{ $jadwal->kuota }} peserta</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Gelombang</div>
                    <div class="fw-semibold">{{ $jadwal->gelombang ?: '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Lingkup Asesor</div>
                    <div class="fw-semibold">{{ ucfirst($jadwal->lingkup_asesor) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Surat Tugas</div>
                    <div class="fw-semibold">{{ $jadwal->no_surat_tugas ?: '—' }}</div>
                </div>
            </div>
        </x-tabler.card>
    </div>

    <div class="col-lg-4">
        <x-tabler.card title="Status Jadwal">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-secondary">Status</span>
                <x-tabler.status-badge :status="$jadwal->status" />
            </div>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-secondary">Asesor</span>
                <span class="fw-semibold">{{ $jadwal->penugasan->count() }} orang</span>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-secondary">Sisa Kuota</span>
                <span class="fw-semibold">{{ $jadwal->sisaKuota() }} peserta</span>
            </div>

            @if ($jadwal->pendaftarans->isEmpty())
                <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}" class="mt-4"
                      onsubmit="return confirm('Hapus jadwal ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="ti ti-trash me-1"></i> Hapus Jadwal
                    </button>
                </form>
            @endif
        </x-tabler.card>
    </div>
</div>

<div class="row row-cards">
    <div class="col-lg-5">
        <x-tabler.card title="Penugasan Asesor">
            @if ($jadwal->penugasan->isEmpty())
                <x-tabler.empty
                    title="Belum ada asesor"
                    description="Jadwal harus memiliki minimal satu asesor sebelum dapat menerima peserta."
                    icon="ti ti-user-off"
                />
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Asesor</th>
                                <th>Peran</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jadwal->penugasan as $penugasan)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $penugasan->asesor->nama_lengkap }}</div>
                                        <div class="text-secondary small">{{ $penugasan->asesor->no_register }}</div>
                                        @if ($penugasan->notified_at)
                                            <span class="badge bg-green-lt mt-1">Notifikasi terkirim</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($penugasan->peran) }}</td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('admin.jadwal.surat-tugas', [$jadwal, $penugasan]) }}"
                                               target="_blank" class="btn btn-sm btn-outline-secondary" title="Surat Tugas">
                                                <i class="ti ti-file-text"></i>
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('admin.jadwal.asesor.destroy', [$jadwal, $penugasan]) }}"
                                                  onsubmit="return confirm('Lepas penugasan asesor ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($asesors->isNotEmpty())
                <form method="POST" action="{{ route('admin.jadwal.asesor.store', $jadwal) }}" class="row g-2 mt-3">
                    @csrf
                    <div class="col-md-7">
                        <select name="asesor_id" class="form-select @error('asesor_id') is-invalid @enderror" required>
                            <option value="">Pilih asesor aktif</option>
                            @foreach ($asesors as $asesor)
                                <option value="{{ $asesor->id }}">{{ $asesor->nama_lengkap }} — {{ $asesor->no_register }}</option>
                            @endforeach
                        </select>
                        @error('asesor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <select name="peran" class="form-select">
                                <option value="utama">Utama</option>
                                <option value="pendamping">Pendamping</option>
                                <option value="observer">Observer</option>
                            </select>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i> Tugaskan
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </x-tabler.card>
    </div>

    <div class="col-lg-7">
        <x-tabler.card title="Peserta Terplotting">
            @if ($jadwal->pendaftarans->isEmpty())
                <x-tabler.empty
                    title="Belum ada peserta"
                    description="Peserta muncul setelah pendaftaran disetujui dan diplotting ke jadwal ini."
                    icon="ti ti-users-off"
                />
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Peserta</th>
                                <th>No. Pendaftaran</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jadwal->pendaftarans as $pendaftaran)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $pendaftaran->asesi->user->name }}</div>
                                        <div class="text-secondary small">{{ $pendaftaran->asesi->no_registrasi }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.pendaftaran.show', $pendaftaran) }}" class="text-decoration-none">
                                            {{ $pendaftaran->no_pendaftaran }}
                                        </a>
                                    </td>
                                    <td><x-tabler.status-badge :status="$pendaftaran->status" /></td>
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
