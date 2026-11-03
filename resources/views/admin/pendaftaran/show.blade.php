@extends('layouts.admin')

@section('title', 'Verifikasi Pendaftaran')
@section('pretitle', 'Asesi & Pendaftaran')
@section('page-title', 'Pendaftaran '.$pendaftaran->no_pendaftaran)

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.pendaftaran.apl01', $pendaftaran) }}" class="btn btn-outline-primary" target="_blank">
        <i class="ti ti-file-text me-1"></i> Unduh FR.APL.01 (PDF)
    </a>
    <a href="{{ route('admin.pendaftaran.mak01', $pendaftaran) }}" class="btn btn-outline-primary" target="_blank">
        <i class="ti ti-file-check me-1"></i> Unduh FR.IA.08 (PDF)
    </a>
    <a href="{{ route('admin.pendaftaran.mak03', $pendaftaran) }}" class="btn btn-outline-primary" target="_blank">
        <i class="ti ti-file-text me-1"></i> Unduh Berita Acara Asesmen (PDF)
    </a>
</div>
<div class="row row-cards">
    <div class="col-lg-8">
        <x-tabler.card title="Data Pendaftar">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-secondary small">Nama Asesi</div>
                    <div class="fw-semibold">{{ $pendaftaran->asesi->user->name }}</div>
                    <div class="text-secondary small">{{ $pendaftaran->asesi->no_registrasi }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">NIK (terenkripsi)</div>
                    <div class="fw-semibold">{{ $pendaftaran->asesi->user->masked_nik ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Skema</div>
                    <div class="fw-semibold">{{ $pendaftaran->skema->nama_skema }}</div>
                    <div class="text-secondary small">{{ $pendaftaran->skema->kode_skema }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Wilayah</div>
                    <div class="fw-semibold">
                        {{ $pendaftaran->asesi->city?->name ?? '—' }}
                        {{ $pendaftaran->asesi->province ? ', '.$pendaftaran->asesi->province->name : '' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Pendidikan Terakhir</div>
                    <div class="fw-semibold">{{ $pendaftaran->asesi->pendidikan_terakhir ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-secondary small">Biaya Skema</div>
                    <div class="fw-semibold">Rp {{ number_format((float) $pendaftaran->skema->biaya, 0, ',', '.') }}</div>
                </div>
                <div class="col-12">
                    <a href="{{ route('admin.asesi.show', $pendaftaran->asesi) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-user me-1"></i> Lihat Profil Asesi
                    </a>
                </div>
            </div>
        </x-tabler.card>

        <x-tabler.card title="Dokumen Persyaratan" class="mt-3">
            @if ($pendaftaran->dokumens->isEmpty())
                <x-tabler.empty
                    title="Belum ada dokumen"
                    description="Asesi belum mengunggah dokumen persyaratan."
                    icon="ti ti-file-off"
                />
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Persyaratan</th>
                                <th>Berkas</th>
                                <th>Status</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendaftaran->dokumens as $dokumen)
                                @php
                                    $persyaratan = $dokumen->persyaratan;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $persyaratan->nama_dokumen }}</div>
                                        <div class="text-secondary small">
                                            {{ $persyaratan->is_required ? 'Wajib' : 'Opsional' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-secondary small">
                                            {{ strtoupper(pathinfo($dokumen->file_path, PATHINFO_EXTENSION)) }}
                                            · {{ number_format(((int) $dokumen->file_size) / 1024, 1, ',', '.') }} KB
                                        </div>
                                        @if ($dokumen->catatan)
                                            <div class="text-danger small">Catatan: {{ $dokumen->catatan }}</div>
                                        @endif
                                        @if ($dokumen->verifikator)
                                            <div class="text-secondary small">Oleh {{ $dokumen->verifikator->name }}</div>
                                        @endif
                                    </td>
                                    <td><x-tabler.status-badge :status="$dokumen->status" /></td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('admin.pendaftaran.dokumen.preview', $dokumen) }}"
                                               target="_blank" class="btn btn-sm btn-outline-secondary" title="Lihat">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.pendaftaran.dokumen.download', $dokumen) }}"
                                               class="btn btn-sm btn-outline-secondary" title="Unduh">
                                                <i class="ti ti-download"></i>
                                            </a>
                                        </div>

                                        @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_DOKUMEN_DIAJUKAN)
                                            <details class="mt-2">
                                                <summary class="text-primary small" style="cursor:pointer;">Verifikasi</summary>
                                                <form method="POST"
                                                      action="{{ route('admin.pendaftaran.dokumen.verify', [$pendaftaran, $dokumen]) }}"
                                                      class="mt-2">
                                                    @csrf
                                                    <select name="status" class="form-select form-select-sm mb-1" required>
                                                        <option value="disetujui">Setujui</option>
                                                        <option value="ditolak">Tolak</option>
                                                    </select>
                                                    <textarea name="catatan" class="form-control form-control-sm mb-1" rows="2"
                                                              placeholder="Catatan (wajib bila ditolak)" maxlength="1000"></textarea>
                                                    <button type="submit" class="btn btn-sm btn-primary w-100">Simpan</button>
                                                </form>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-tabler.card>
    </div>{{-- /col-lg-8 --}}

    <div class="col-lg-4">
        <x-tabler.card title="Status Pendaftaran">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary">Status</span>
                <x-tabler.status-badge :status="$pendaftaran->status" />
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary">Tahapan Aktif</span>
                <span class="fw-semibold small">{{ $pendaftaran->currentStage()['label'] }}</span>
            </div>
            @if ($pendaftaran->jadwal)
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-secondary">Jadwal</span>
                    <span class="fw-semibold small">
                        {{ $pendaftaran->jadwal->tanggal->translatedFormat('d M Y') }}
                    </span>
                </div>
            @endif
            @if ($pendaftaran->catatan_penolakan)
                <div class="alert alert-danger mt-3 mb-0 small">{{ $pendaftaran->catatan_penolakan }}</div>
            @endif
        </x-tabler.card>

        @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_MENUNGGU_JADWAL)
            <x-tabler.card title="Plotting ke Jadwal" class="mt-3">
                @if ($jadwalOptions->isEmpty())
                    <div class="alert alert-warning mb-0 small">
                        Belum ada jadwal tersedia untuk skema ini. Buat jadwal dan tugaskan asesor terlebih dahulu.
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.pendaftaran.approve', $pendaftaran) }}">
                        @csrf
                        <label class="form-label required">Pilih Jadwal Uji</label>
                        <select name="jadwal_id" class="form-select mb-3" required>
                            <option value="">Pilih jadwal</option>
                            @foreach ($jadwalOptions as $jadwal)
                                <option value="{{ $jadwal->id }}">
                                    {{ $jadwal->tanggal->translatedFormat('d M Y') }}
                                    · {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}
                                    · {{ $jadwal->tuk->nama_tuk }}
                                    ({{ $jadwal->pendaftarans_count }}/{{ $jadwal->kuota }})
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-success w-100"
                                onclick="return confirm('Setujui pendaftaran dan plotting ke jadwal ini?')">
                            <i class="ti ti-check me-1"></i> Setujui & Plotting
                        </button>
                    </form>
                @endif
            </x-tabler.card>
        @endif

        @if (! in_array($pendaftaran->status, [
            \App\Models\Pendaftaran::STATUS_DITOLAK,
            \App\Models\Pendaftaran::STATUS_DIBATALKAN,
            \App\Models\Pendaftaran::STATUS_SELESAI,
            \App\Models\Pendaftaran::STATUS_TERJADWAL,
        ], true))
            <x-tabler.card title="Tolak Pendaftaran" class="mt-3">
                <form method="POST" action="{{ route('admin.pendaftaran.reject', $pendaftaran) }}">
                    @csrf
                    <label class="form-label required">Alasan Penolakan</label>
                    <textarea name="catatan_penolakan" class="form-control mb-2" rows="3" required maxlength="1000"></textarea>
                    <button type="submit" class="btn btn-outline-danger w-100"
                            onclick="return confirm('Tolak pendaftaran ini?')">
                        <i class="ti ti-x me-1"></i> Tolak Pendaftaran
                    </button>
                </form>
            </x-tabler.card>
        @endif

        @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_SELESAI && $pendaftaran->asesi?->status_kompetensi === 'kompeten')
            @php
                $sudahAdaSertifikat = $pendaftaran->sertifikat()->exists();
            @endphp
            @if ($sudahAdaSertifikat)
                <x-tabler.card title="Sertifikat Kompetensi" class="mt-3">
                    <div class="alert alert-success mb-3">
                        <i class="ti ti-award me-1"></i>
                        Sertifikat sudah diterbitkan.
                    </div>
                    <a href="{{ route('admin.sertifikat.show', $pendaftaran->sertifikat) }}"
                       class="btn btn-outline-success w-100">
                        <i class="ti ti-eye me-1"></i> Lihat Sertifikat
                    </a>
                </x-tabler.card>
            @else
                <x-tabler.card title="Terbitkan Sertifikat" class="mt-3">
                    <p class="text-secondary small mb-3">
                        Asesi dinyatakan <strong class="text-success">kompeten</strong>.
                        Masukkan nomor seri blangko BNSP untuk menerbitkan sertifikat.
                    </p>
                    <form method="POST" action="{{ route('admin.sertifikat.store') }}">
                        @csrf
                        <input type="hidden" name="pendaftaran_id" value="{{ $pendaftaran->id }}">
                        <div class="mb-3">
                            <label class="form-label required">No. Seri Blangko BNSP</label>
                            <input type="text" name="no_seri_blangko"
                                   class="form-control font-monospace @error('no_seri_blangko') is-invalid @enderror"
                                   value="{{ old('no_seri_blangko') }}"
                                   placeholder="Contoh: LSP.TI.12345.2026"
                                   required maxlength="100">
                            <div class="form-hint">Format: BAGIAN1.BAGIAN2.NNNNN.TAHUN</div>
                            @error('no_seri_blangko')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @error('pendaftaran_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-success w-100"
                                onclick="return confirm('Terbitkan sertifikat kompetensi ini?')">
                            <i class="ti ti-award me-1"></i> Terbitkan Sertifikat
                        </button>
                    </form>
                </x-tabler.card>
            @endif
        @endif

        @if ($pendaftaran->jadwal)
            <x-tabler.card title="Jadwal & Asesor" class="mt-3">
                <div class="mb-2">
                    <div class="text-secondary small">TUK</div>
                    <div class="fw-semibold">{{ $pendaftaran->jadwal->tuk->nama_tuk }}</div>
                </div>
                <div class="mb-2">
                    <div class="text-secondary small">Waktu</div>
                    <div class="fw-semibold">
                        {{ substr($pendaftaran->jadwal->jam_mulai, 0, 5) }}–{{ substr($pendaftaran->jadwal->jam_selesai, 0, 5) }} WIB
                    </div>
                </div>
                <div>
                    <div class="text-secondary small">Asesor</div>
                    @foreach ($pendaftaran->jadwal->asesors as $asesor)
                        <div class="small">
                            {{ $asesor->nama_lengkap }}
                            <span class="text-secondary">({{ ucfirst($asesor->pivot->peran) }})</span>
                        </div>
                    @endforeach
                </div>
            </x-tabler.card>
        @endif
    </div>
</div>
@endsection
