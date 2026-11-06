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
        <i class="ti ti-file-check me-1"></i> Unduh FR.AK.01 (MAK-01)
    </a>
    <a href="{{ route('admin.pendaftaran.mak03', $pendaftaran) }}" class="btn btn-outline-primary" target="_blank">
        <i class="ti ti-file-text me-1"></i> Unduh FR.AK.04 (MAK-03)
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
                    <div class="text-secondary small">Tujuan Asesmen</div>
                    <div class="fw-semibold">
                        {{ \App\Models\Pendaftaran::TUJUAN_LABELS[$pendaftaran->tujuan_asesmen] ?? '—' }}
                    </div>
                </div>
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
        <x-tabler.card title="Dokumen Pre-Asesmen" class="mt-3">
            <p class="text-secondary small mb-3">
                Berikut status dokumen pre-asesmen yang wajib disetujui asesi sebelum asesmen dimulai.
            </p>
            <table class="table table-vcenter card-table mb-0">
                <thead>
                    <tr>
                        <th>Dokumen</th>
                        <th>Status</th>
                        <th class="w-1">Tgl. Setuju</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (\App\Models\PersetujuanAsesmen::requiredJenis() as $jenis)
                        @php
                            $p = $pendaftaran->persetujuanAsesmens->where('jenis', $jenis)->first();
                        @endphp
                        <tr>
                            <td>{{ \App\Models\PersetujuanAsesmen::JENIS_LABELS[$jenis] }}</td>
                            <td>
                                @if ($p?->isApproved())
                                    <span class="badge bg-success-lt text-success">Disetujui</span>
                                @else
                                    <span class="badge bg-warning-lt text-warning">Belum</span>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                {{ $p?->disetujui_pada?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @foreach (\App\Models\PersetujuanAsesmen::requiredJenis() as $jenis)
                @php
                    $p = $pendaftaran->persetujuanAsesmens->where('jenis', $jenis)->first();
                    $d = $p?->data_json ?? [];
                @endphp
                @if ($p?->isApproved())
                    <x-tabler.card title="{{ \App\Models\PersetujuanAsesmen::JENIS_LABELS[$jenis] }} — Isian Asesi" class="mt-3">
                        @if ($jenis === 'ak01')
                            <div class="mb-3">
                                <label class="form-label small text-muted fw-semibold">Jenis Bukti</label>
                                <div class="text-secondary small">
                                    @php
                                        $bukti = $d['jenis_bukti'] ?? [];
                                        $buktiLabels = [
                                            'portofolio' => 'Hasil Verifikasi Portofolio',
                                            'reviu_produk' => 'Hasil Reviu Produk',
                                            'observasi' => 'Hasil Observasi Langsung',
                                            'kegiatan_terstruktur' => 'Hasil Kegiatan Terstruktur',
                                            'tertulis' => 'Hasil Pertanyaan Tertulis',
                                            'lisan' => 'Hasil Pertanyaan Lisan',
                                            'wawancara' => 'Hasil Pertanyaan Wawancara',
                                        ];
                                    @endphp
                                    @if (!empty($bukti))
                                        @foreach ($bukti as $b)
                                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $buktiLabels[$b] ?? $b }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </div>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="fw-semibold" style="width:30%">Asesi:</td>
                                    <td class="text-secondary small">
                                        Bahwa saya telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Asesor:</td>
                                    <td class="text-secondary small">
                                        Menyatakan tidak akan membuka hasil pekerjaan yang saya peroleh karena penugasan saya sebagai Asesor dalam pekerjaan Asesmen kepada siapapun atau organisasi apapun selain kepada pihak yang berwenang sehubungan dengan kewajiban saya sebagai Asesor yang ditugaskan oleh LSP.
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Asesi:</td>
                                    <td class="text-secondary small">
                                        Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya digunakan untuk pengembangan profesional dan hanya dapat diakses oleh orang tertentu saja.
                                    </td>
                                </tr>
                            </table>
                        @endif

                        @if ($jenis === 'ak04')
                            <table class="table table-bordered table-sm mb-3">
                                <thead>
                                    <tr>
                                        <th style="width:60%">Pertanyaan</th>
                                        <th class="text-center" style="width:20%">YA</th>
                                        <th class="text-center" style="width:20%">TIDAK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Apakah Proses Banding telah dijelaskan kepada Anda?</td>
                                        <td class="text-center">{{ ($d['proses_dijelaskan'] ?? null) === 'ya' ? '✓' : '✗' }}</td>
                                        <td class="text-center">{{ ($d['proses_dijelaskan'] ?? null) === 'tidak' ? '✓' : '✗' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Apakah Anda telah mendiskusikan Banding dengan Asesor?</td>
                                        <td class="text-center">{{ ($d['didiskusikan'] ?? null) === 'ya' ? '✓' : '✗' }}</td>
                                        <td class="text-center">{{ ($d['didiskusikan'] ?? null) === 'tidak' ? '✓' : '✗' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Apakah Anda mau melibatkan &ldquo;orang lain&rdquo; membantu Anda dalam Proses Banding?</td>
                                        <td class="text-center">{{ ($d['libatkan_orang_lain'] ?? null) === 'ya' ? '✓' : '✗' }}</td>
                                        <td class="text-center">{{ ($d['libatkan_orang_lain'] ?? null) === 'tidak' ? '✓' : '✗' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="mb-3">
                                <label class="form-label small text-muted fw-semibold">Skema Sertifikasi</label>
                                <div class="text-secondary small">{{ $d['skema_judul'] ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted fw-semibold">No. Skema Sertifikasi</label>
                                <div class="text-secondary small">{{ $d['skema_nomor'] ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted fw-semibold">Alasan Banding</label>
                                <div class="p-2 border rounded bg-light">{{ $d['alasan'] ?? '—' }}</div>
                            </div>
                        @endif

                        @if ($jenis === 'ak07')
                            <div class="mb-3">
                                <label class="form-label small text-muted fw-semibold">Potensi Asesi</label>
                                <div class="row g-2">
                                    @php
                                        $potensi = [
                                            'Hasil pelatihan dan / atau pendidikan, dimana Kurikulum dan fasilitas praktek mampu telusur terhadap standar kompetensi',
                                            'Hasil pelatihan dan / atau pendidikan, dimana kurikulum belum berbasis kompetensi.',
                                            'Pekerja berpengalaman, dimana berasal dari industri/tempat kerja yang dalam operasionalnya mampu telusur dengan standar kompetensi',
                                            'Pekerja berpengalaman, dimana berasal dari industri/tempat kerja yang dalam operasionalnya belum berbasis kompetensi.',
                                            'Pelatihan / belajar mandiri atau otodidak.',
                                        ];
                                        $potensiSelected = array_map(fn ($v) => (string) $v, $d['potensi'] ?? []);
                                    @endphp
                                    @foreach ($potensi as $i => $t)
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       value="{{ $i + 1 }}" id="potensi{{ $i + 1 }}"
                                                       {{ in_array((string) ($i + 1), $potensiSelected, true) ? 'checked' : '' }}
                                               disabled>
                                                <label class="form-check-label" for="potensi{{ $i + 1 }}">
                                                    {{ $t }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <table class="table table-bordered table-sm mb-3">
                                <thead>
                                    <tr>
                                        <th style="width:6%" class="text-center">No</th>
                                        <th>Karakteristik Asesi</th>
                                        <th style="width:22%" class="text-center">Butuh Penyesuaian</th>
                                        <th style="width:24%">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $rows = [
                                            1 => 'Keterbatasan asesi terhadap persyaratan bahasa, literasi, numerasi.',
                                            2 => 'Penyediaan dukungan pembaca, penerjemah, pelayan, penulis.',
                                            3 => 'Penggunaan teknologi adaptif atau peralatan khusus.',
                                            4 => 'Pelaksanaan asesmen secara fleksibel karena alasan keletihan atau keperluan pengobatan.',
                                            5 => 'Penyediaan peralatan asesmen berupa braille, audio/video-tape.',
                                            6 => 'Penyesuaian tempat fisik/lingkungan asesmen',
                                            7 => 'Pertimbangan umur/usia lanjut/gender asesi.',
                                            8 => 'Pertimbangan budaya/tradisi/agama.',
                                        ];
                                    @endphp
                                    @foreach ($rows as $k => $q)
                                        <tr>
                                            <td class="text-center fw-semibold">{{ $k }}</td>
                                            <td>{{ $q }}</td>
                                            <td class="text-center">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                           name="p{{ $k }}_perlu" value="ya"
                                                           {{ ($d['p'.$k.'_perlu'] ?? null) === 'ya' ? 'checked' : '' }}
                                                   disabled>
                                                    <label class="form-check-label">Ya</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio"
                                                           name="p{{ $k }}_perlu" value="tidak"
                                                           {{ ($d['p'.$k.'_perlu'] ?? null) === 'tidak' ? 'checked' : '' }}
                                                   disabled>
                                                    <label class="form-check-label">Tidak</label>
                                                </div>
                                            </td>
                                            <td>{{ $d['p'.$k.'_lainnya'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mb-3">
                                <label class="form-label small text-muted fw-semibold">Acuan Pembanding Asesmen</label>
                                <div class="text-secondary small">{{ $d['acuan'] ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted fw-semibold">Metode Asesmen</label>
                                <div class="text-secondary small">{{ $d['metode'] ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted fw-semibold">Instrumen Asesmen</label>
                                <div class="text-secondary small">{{ $d['instrumen'] ?? '—' }}</div>
                            </div>
                        @endif
                    </x-tabler.card>
                @endif
            @endforeach
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

        <x-tabler.card title="Presensi Asesi" class="mt-3">
            @php
                $presensi = $pendaftaran->presensi;
            @endphp
            @if ($presensi)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary">Status</span>
                    <x-tabler.status-badge
                        :status="$presensi->status"
                        :label="$presensi->statusLabel()"
                    />
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary">Dicatat</span>
                    <span class="small">
                        {{ $presensi->dicatat_pada?->translatedFormat('d M Y H:i') ?? '—' }}
                        @if ($presensi->pencatat)
                            <span class="text-secondary">· {{ $presensi->pencatat->name }}</span>
                        @endif
                    </span>
                </div>
                @if ($presensi->isLocked())
                    <div class="alert alert-secondary py-2 px-3 small mb-2">
                        <i class="ti ti-lock me-1"></i> Presensi terkunci sejak asesmen selesai.
                    </div>
                @endif
            @else
                <p class="text-secondary small">Presensi belum dicatat.</p>
            @endif

            <form method="POST" action="{{ route('admin.pendaftaran.presensi.catat', $pendaftaran) }}" class="mt-2">
                @csrf
                <div class="mb-2">
                    <label class="form-label small" for="presensi-status">Status presensi</label>
                    <select name="status" id="presensi-status" class="form-select form-select-sm" required>
                        @foreach (\App\Models\PresensiAsesi::STATUS_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected($presensi?->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small" for="presensi-catatan">Catatan (opsional)</label>
                    <input type="text" name="catatan" id="presensi-catatan" class="form-control form-control-sm"
                           value="{{ old('catatan', $presensi?->catatan) }}" maxlength="500">
                </div>
                <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                    <i class="ti ti-device-floppy me-1"></i> Simpan presensi
                </button>
            </form>
        </x-tabler.card>

        <x-tabler.card title="Umpan Balik Asesi (FR.AK.03)" class="mt-3">
            @if ($pendaftaran->umpanBalik?->isSubmitted())
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary">Status</span>
                    <span class="badge bg-success-lt text-success">Terkirim</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary">Dikirim</span>
                    <span class="small">{{ $pendaftaran->umpanBalik->disubmit_pada?->translatedFormat('d M Y H:i') ?? '—' }}</span>
                </div>
                @if ($pendaftaran->umpanBalik->pengisi)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-secondary">Pengisi</span>
                        <span class="small">{{ $pendaftaran->umpanBalik->pengisi->name }}</span>
                    </div>
                @endif
                <a href="{{ route('admin.pendaftaran.umpan-balik.pdf', $pendaftaran) }}"
                   class="btn btn-sm btn-outline-secondary w-100">
                    <i class="ti ti-file-pdf me-1"></i> Cetak FR.AK.03
                </a>
            @else
                <p class="text-secondary small mb-0">Asesi belum mengisi umpan balik.</p>
            @endif
        </x-tabler.card>

        <x-tabler.card title="Banding (FR.AK.04)" class="mt-3">
            @if ($pendaftaran->banding)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary">Status</span>
                    <span class="badge bg-primary-lt text-primary">
                        {{ \App\Models\Banding::STATUS_LABELS[$pendaftaran->banding->status] ?? $pendaftaran->banding->status }}
                    </span>
                </div>
                <a href="{{ route('admin.pendaftaran.banding.pdf', $pendaftaran) }}"
                   class="btn btn-sm btn-outline-secondary w-100">
                    <i class="ti ti-file-pdf me-1"></i> Cetak FR.AK.04
                </a>
            @else
                <p class="text-secondary small mb-0">Belum ada banding.</p>
            @endif
        </x-tabler.card>

        <x-tabler.card title="Validasi Asesmen (FR.VA)" class="mt-3">
            @php
                $validasi = $pendaftaran->validasiAsesmen;
            @endphp
            @if ($validasi?->isFinal())
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary">Status</span>
                    <span class="badge bg-success-lt text-success">Final</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary">Keputusan</span>
                    <span class="small">{{ \App\Models\ValidasiAsesmen::KEPUTUSAN_LABELS[$validasi->keputusan] ?? $validasi->keputusan ?? '—' }}</span>
                </div>
                <a href="{{ route('validasi.pdf', $pendaftaran) }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="ti ti-file-pdf me-1"></i> Cetak FR.VA
                </a>
            @elseif ($validasi)
                <p class="text-secondary small mb-2">Validasi masih draf.</p>
                <a href="{{ route('validasi.show', $pendaftaran) }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="ti ti-edit me-1"></i> Buka Validasi
                </a>
            @else
                <p class="text-secondary small mb-2">Belum ada validasi.</p>
                <a href="{{ route('validasi.show', $pendaftaran) }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="ti ti-plus me-1"></i> Buat Validasi
                </a>
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

        @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_DOKUMEN_DIAJUKAN)
            <x-tabler.card title="Verifikasi Dokumen" class="mt-3">
                <p class="text-secondary small mb-3">
                    Setujui seluruh dokumen persyaratan sekaligus. Dokumen yang belum diverifikasi akan disetujui dan status pendaftaran akan diperbarui.
                </p>
                <form method="POST" action="{{ route('admin.pendaftaran.verify-all', $pendaftaran) }}">
                    @csrf
                    <button type="submit"
                            class="btn btn-primary w-100"
                            onclick="return confirm('Setujui semua dokumen persyaratan?')">
                        <i class="ti ti-check me-1"></i> Verifikasi Semua Dokumen
                    </button>
                </form>
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
