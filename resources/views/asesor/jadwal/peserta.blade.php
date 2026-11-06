@extends('layouts.asesor')

@section('title', 'Peserta Jadwal Uji')
@section('pretitle', 'Asesor')
@section('page-title', 'Peserta '.$jadwal->skema->nama_skema)

@section('actions')
    <a href="{{ route('asesor.jadwal.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('asesor.laporan-asesmen.show', $jadwal) }}" class="btn btn-outline-primary">
        <i class="ti ti-report me-1"></i> Laporan FR.AK.05
    </a>
    <a href="{{ route('asesor.tinjauan-proses.show', $jadwal) }}" class="btn btn-outline-primary">
        <i class="ti ti-checklist me-1"></i> Tinjauan FR.AK.06
    </a>
@endsection

@section('content')
<x-tabler.card title="Informasi Jadwal" class="mb-3">
    <div class="row g-3">
        <div class="col-md-3">
            <div class="text-secondary small">Tanggal</div>
            <div class="fw-semibold">{{ $jadwal->tanggal->translatedFormat('d F Y') }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-secondary small">Waktu</div>
            <div class="fw-semibold">
                {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }} WIB
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-secondary small">TUK</div>
            <div class="fw-semibold">{{ $jadwal->tuk->nama_tuk }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-secondary small">Status</div>
            <x-tabler.status-badge :status="$jadwal->status" />
        </div>
    </div>
</x-tabler.card>

<x-tabler.card title="Daftar Peserta">
    @if ($jadwal->pendaftarans->isEmpty())
        <x-tabler.empty
            title="Belum ada peserta"
            description="Peserta yang telah diplotting ke jadwal ini akan muncul di sini."
            icon="ti ti-users-off"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th>Wilayah</th>
                        <th>Pendidikan</th>
                        <th>Dokumen</th>
                        <th>Presensi</th>
                        <th>Status</th>
                    <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jadwal->pendaftarans as $pendaftaran)
                        @php
                            $asesi = $pendaftaran->asesi;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $asesi->user->name }}</div>
                                <div class="text-secondary small">{{ $asesi->no_registrasi }}</div>
                            </td>
                            <td class="text-secondary small">
                                {{ $asesi->city?->name ?? '—' }}
                                @if ($asesi->province)
                                    <div>{{ $asesi->province->name }}</div>
                                @endif
                            </td>
                            <td class="text-secondary small">{{ $asesi->pendidikan_terakhir ?: '—' }}</td>
                            <td>
                                <span class="badge bg-azure-lt">
                                    {{ $pendaftaran->dokumens->where('status', 'disetujui')->count() }} disetujui
                                </span>
                            </td>
                            @php
                                $presensi = $pendaftaran->presensi;
                                $presensiColor = match ($presensi?->status) {
                                    \App\Models\PresensiAsesi::STATUS_HADIR => 'success',
                                    \App\Models\PresensiAsesi::STATUS_IZIN => 'warning',
                                    \App\Models\PresensiAsesi::STATUS_TIDAK_HADIR => 'danger',
                                    default => 'secondary',
                                };
                                $presensiTerkunci = $presensi?->isLocked()
                                    || $pendaftaran->status === \App\Models\Pendaftaran::STATUS_SELESAI;
                                $approvalLengkap = $pendaftaran->persetujuanAsesmens
                                    ->where('status', \App\Models\PersetujuanAsesmen::STATUS_DISETUJUI)
                                    ->pluck('jenis')
                                    ->unique()
                                    ->count() === count(\App\Models\PersetujuanAsesmen::requiredJenis());
                            @endphp
                            <td>
                                @if ($presensi)
                                    <span class="badge bg-{{ $presensiColor }}-lt text-{{ $presensiColor }}">
                                        {{ $presensi->statusLabel() }}
                                    </span>
                                @else
                                    <span class="text-secondary small">Belum dicatat</span>
                                @endif

                                @unless ($presensiTerkunci)
                                    <form method="POST"
                                          action="{{ route('asesor.presensi.catat', $pendaftaran) }}"
                                          class="d-flex gap-1 mt-1">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm" style="width:auto" aria-label="Status presensi">
                                            @foreach (\App\Models\PresensiAsesi::STATUS_LABELS as $value => $label)
                                                <option value="{{ $value }}" @selected($presensi?->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Simpan presensi">
                                            <i class="ti ti-device-floppy"></i>
                                        </button>
                                    </form>
                                @else
                                    <div class="text-secondary small mt-1">
                                        <i class="ti ti-lock"></i> Terkunci
                                    </div>
                                @endunless
                            </td>
                            <td><x-tabler.status-badge :status="$pendaftaran->status" /></td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_TERJADWAL)
                                        <form method="POST" action="{{ route('asesor.pendaftaran.mulai-asesmen', $pendaftaran) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="{{ $approvalLengkap ? 'Mulai asesmen' : 'Dokumen pre-asesmen belum lengkap' }}"
                                                    @disabled(! $approvalLengkap)>
                                                <i class="ti ti-player-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('asesor.pre-asesmen.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-secondary" title="Dokumen Pre-Asesmen">
                                        <i class="ti ti-file-check"></i>
                                    </a>
                                    <a href="{{ route('asesor.ia03.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.IA.03 PMO">
                                        <i class="ti ti-help-circle"></i>
                                    </a>
                                    <a href="{{ route('asesor.ia04.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.IA.04 Proyek Singkat">
                                        <i class="ti ti-briefcase"></i>
                                    </a>
                                    <a href="{{ route('asesor.ia05.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.IA.05 Pilihan Ganda">
                                        <i class="ti ti-list-numbers"></i>
                                    </a>
                                    <a href="{{ route('asesor.ia06.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.IA.06 Esai">
                                        <i class="ti ti-writing"></i>
                                    </a>
                                    <a href="{{ route('asesor.ia07.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.IA.07 Pertanyaan Lisan">
                                        <i class="ti ti-message-circle"></i>
                                    </a>
                                    <a href="{{ route('asesor.ia09.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.IA.09 Wawancara">
                                        <i class="ti ti-messages"></i>
                                    </a>
                                    <a href="{{ route('asesor.ia10.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.IA.10 Pihak Ketiga">
                                        <i class="ti ti-users"></i>
                                    </a>
                                    <a href="{{ route('asesor.instrumen.show', [$pendaftaran, \App\Models\InstrumenAsesmen::JENIS_IA02]) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.IA.02 Tugas Praktik">
                                        <i class="ti ti-clipboard-list"></i>
                                    </a>
                                    <a href="{{ route('asesor.instrumen.show', [$pendaftaran, \App\Models\InstrumenAsesmen::JENIS_IA08]) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.IA.08 Verifikasi Portofolio">
                                        <i class="ti ti-folder-check"></i>
                                    </a>
                                    <a href="{{ route('asesor.instrumen.show', [$pendaftaran, \App\Models\InstrumenAsesmen::JENIS_IA11]) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.IA.11 Reviu Produk">
                                        <i class="ti ti-clipboard-check"></i>
                                    </a>
                                    <a href="{{ route('asesor.banding.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-secondary" title="FR.AK.04 Banding">
                                        <i class="ti ti-gavel"></i>
                                    </a>
                                    <a href="{{ route('asesor.apl02.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-primary" title="Verifikasi APL-02">
                                        <i class="ti ti-list-check"></i>
                                    </a>
                                    <a href="{{ route('asesor.mak02.show', $pendaftaran) }}"
                                       class="btn btn-sm btn-outline-primary" title="Input FR.AK.02">
                                        <i class="ti ti-file-analytics"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-tabler.card>
@endsection
