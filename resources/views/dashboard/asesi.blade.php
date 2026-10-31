@extends('layouts.asesi')

@section('title', 'Portal Peserta Sertifikasi')
@section('pretitle', 'Lembaga Sertifikasi Profesi')
@section('page-title', 'Selamat Datang, ' . auth()->user()->name)

@php
    $stage = $activePendaftaran?->currentStage();
    $profileComplete = $asesi?->isProfileComplete() ?? false;
@endphp

@section('actions')
    <a href="{{ route('asesi.pendaftaran.create') }}" class="btn btn-primary d-none d-sm-inline-block">
        <i class="ti ti-plus me-1"></i> Daftar Skema Baru
    </a>
@endsection

@section('content')
@unless ($profileComplete)
    <div class="alert alert-warning">
        <i class="ti ti-alert-triangle me-1"></i>
        Profil pokok Anda belum lengkap.
        <a href="{{ route('asesi.profil.edit') }}" class="alert-link">Lengkapi profil sekarang</a>
        agar dapat mendaftar skema sertifikasi.
    </div>
@endunless

<!-- Alur Sertifikasi / Phase Gating Visual Tracker -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-transparent border-bottom">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div>
                <h3 class="card-title mb-0"><i class="ti ti-git-fork me-2 text-primary"></i>Alur Proses Sertifikasi Kompetensi (Phase-Gating)</h3>
                <div class="text-secondary small mt-1">Sistem menerapkan verifikasi berjenjang. Tahapan berikutnya akan terbuka secara otomatis setelah tahapan sebelumnya selesai diverifikasi.</div>
            </div>
            @if ($stage)
                <span class="badge bg-blue-lt">Tahap {{ $stage['step'] }}: {{ $stage['label'] }}</span>
            @else
                <span class="badge bg-blue-lt">Tahap 1: Pendaftaran</span>
            @endif
        </div>
    </div>
    <div class="card-body py-4">
        <ul class="steps steps-counter steps-primary">
            @foreach (['APL-01', 'Verifikasi', 'Pembayaran', 'Jadwal & TUK', 'APL-02', 'Uji Asesmen', 'Sertifikat'] as $index => $label)
                <li class="step-item {{ $stage && $stage['step'] >= $index + 1 ? 'active' : '' }}">
                    <div class="h4 m-0">{{ $label }}</div>
                    <div class="text-secondary small">
                        {{ ['Formulir & Berkas', 'Admin LSP', 'Konfirmasi Biaya', 'Plotting Ujian', 'Asesmen Mandiri', 'Bersama Asesor', 'Lisensi BNSP'][$index] }}
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="row row-cards">
    <!-- Kolom Kiri: Pendaftaran Aktif & Checklist Berkas -->
    <div class="col-lg-8">
        <x-tabler.card title="Status Sertifikasi Aktif Anda">
            <x-slot:actions>
                @if ($activePendaftaran)
                    <x-tabler.status-badge :status="$activePendaftaran->status" />
                @else
                    <span class="badge bg-yellow-lt"><i class="ti ti-clock me-1"></i> Belum Mengajukan Skema</span>
                @endif
            </x-slot:actions>

            @if ($activePendaftaran === null)
                <div class="text-center py-4">
                    <div class="mb-3">
                        <span class="avatar avatar-xl rounded-circle bg-primary-lt text-primary">
                            <i class="ti ti-certificate fs-1"></i>
                        </span>
                    </div>
                    <h3>Anda Belum Terdaftar Pada Skema Sertifikasi</h3>
                    <p class="text-secondary max-w-md mx-auto mb-3">
                        Pilih skema sertifikasi yang sesuai dengan kualifikasi dan kompetensi profesional Anda untuk memulai proses asesmen BNSP.
                    </p>
                    <a href="{{ route('asesi.pendaftaran.create') }}" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i> Jelajahi Skema Sertifikasi Tersedia
                    </a>
                </div>
            @else
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="text-secondary small">Skema</div>
                        <div class="fw-semibold">{{ $activePendaftaran->skema->nama_skema }}</div>
                        <div class="text-secondary small">{{ $activePendaftaran->skema->kode_skema }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-secondary small">Nomor Pendaftaran</div>
                        <div class="fw-semibold">{{ $activePendaftaran->no_pendaftaran }}</div>
                        <div class="text-secondary small">{{ $stage['label'] }}</div>
                    </div>
                    @if ($activePendaftaran->jadwal)
                        <div class="col-md-6">
                            <div class="text-secondary small">Jadwal Uji</div>
                            <div class="fw-semibold">{{ $activePendaftaran->jadwal->tanggal->translatedFormat('d F Y') }}</div>
                            <div class="text-secondary small">
                                {{ substr($activePendaftaran->jadwal->jam_mulai, 0, 5) }} WIB · {{ $activePendaftaran->jadwal->tuk->nama_tuk }}
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <div class="text-secondary small">Biaya Skema</div>
                        <div class="fw-semibold">Rp {{ number_format((float) $activePendaftaran->skema->biaya, 0, ',', '.') }}</div>
                    </div>
                </div>
                <a href="{{ route('asesi.pendaftaran.show', $activePendaftaran) }}" class="btn btn-primary">
                    <i class="ti ti-arrow-right me-1"></i> Lanjutkan Tahapan
                </a>
            @endif
        </x-tabler.card>

        <!-- Portofolio & Dokumen Pendukung -->
        <div class="card mt-3 shadow-sm border-0">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="ti ti-folder-check me-2 text-primary"></i>Kelengkapan Dokumen Persyaratan</h3>
                <span class="text-secondary small">Wajib dilengkapi sebelum verifikasi</span>
            </div>
            <div class="card-body p-0">
                @if ($activePendaftaran === null)
                    <div class="p-4 text-center text-secondary small">
                        Pilih skema terlebih dahulu untuk melihat daftar dokumen persyaratan.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Jenis Dokumen</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                    <th class="w-1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activePendaftaran->skema->persyaratans as $persyaratan)
                                    @php($dokumen = $activePendaftaran->dokumens->firstWhere('skema_persyaratan_id', $persyaratan->id))
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $persyaratan->nama_dokumen }}</div>
                                            <div class="text-secondary small">{{ $persyaratan->is_required ? 'Wajib' : 'Opsional' }}</div>
                                        </td>
                                        <td>
                                            @if ($dokumen)
                                                <x-tabler.status-badge :status="$dokumen->status" />
                                            @else
                                                <span class="badge bg-yellow-lt"><i class="ti ti-alert-circle me-1"></i> Belum Diunggah</span>
                                            @endif
                                        </td>
                                        <td class="text-secondary small">
                                            {{ $dokumen ? strtoupper(pathinfo($dokumen->file_path, PATHINFO_EXTENSION)).' · '.number_format(((int) $dokumen->file_size) / 1024, 1, ',', '.').' KB' : 'PDF/JPG/PNG maks. 5 MB' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('asesi.pendaftaran.show', $activePendaftaran) }}" class="btn btn-sm btn-outline-primary">
                                                {{ $dokumen ? 'Lihat' : 'Unggah' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Widget Panduan & Bantuan -->
    <div class="col-lg-4">
        <!-- Widget Panduan Sistem Sesuai Permintaan User -->
        <div class="card shadow-sm border-0 bg-primary-lt">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <span class="avatar avatar-md bg-primary text-white rounded-3 me-3">
                        <i class="ti ti-book-2 fs-2"></i>
                    </span>
                    <div>
                        <h3 class="card-title text-primary mb-0">Panduan Asesi</h3>
                        <div class="text-secondary small">Buku Petunjuk Operasional</div>
                    </div>
                </div>
                <p class="text-secondary small">
                    Pelajari langkah demi langkah pengisian formulir APL-01, verifikasi bukti APL-02, serta tata tertib asesmen tatap muka maupun daring.
                </p>
                <div class="d-grid gap-2">
                    <a href="{{ route('asesi.panduan') }}" class="btn btn-primary">
                        <i class="ti ti-download me-1"></i> Unduh Panduan Sistem (PDF)
                    </a>
                    <a href="{{ route('asesi.panduan') }}" class="btn btn-outline-primary">
                        <i class="ti ti-help-circle me-1"></i> Tanya Jawab (FAQ)
                    </a>
                </div>
            </div>
        </div>

        <!-- Informasi Helpdesk LSP -->
        <x-tabler.card title="Kontak & Helpdesk Lembaga" class="mt-3">
            <div class="divide-y">
                <div class="py-2">
                    <div class="text-secondary small">LSP Penyelenggara:</div>
                    <div class="fw-bold">{{ session(\App\Support\TenantContext::SESSION_NAME) ?? 'LSP SiapKompeten' }}</div>
                </div>
                <div class="py-2">
                    <div class="text-secondary small">Alamat Surel Layanan:</div>
                    <div class="fw-semibold">{{ \App\Support\TenantContext::institution()?->email ?? 'layanan@siapkompeten.id' }}</div>
                </div>
                <div class="py-2">
                    <div class="text-secondary small">Jam Operasional:</div>
                    <div class="fw-semibold">Senin - Jumat (08.00 - 17.00 WIB)</div>
                </div>
            </div>
        </x-tabler.card>
    </div>
</div>
@endsection
