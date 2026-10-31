@extends('layouts.asesi')

@section('title', 'Detail Pendaftaran')
@section('pretitle', 'Pendaftaran Sertifikasi')
@section('page-title', $pendaftaran->skema->nama_skema)

@php
    $stage = $pendaftaran->currentStage();
    $canUpload = $pendaftaran->canUploadDocuments();
    $missing = $pendaftaran->missingRequiredDocuments();
    $steps = [
        1 => 'APL-01 & Berkas',
        2 => 'Verifikasi Admin',
        3 => 'Pembayaran',
        4 => 'Penjadwalan',
        5 => 'APL-02',
        6 => 'Asesmen',
        7 => 'Selesai',
    ];
@endphp

@section('actions')
    <a href="{{ route('asesi.pendaftaran.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<x-tabler.card class="mb-3">
    <ul class="steps steps-counter steps-primary mb-0">
        @foreach ($steps as $number => $label)
            <li class="step-item {{ $stage['step'] === $number ? 'active' : ($stage['step'] > $number ? 'active' : '') }}">
                <div class="h4 m-0">{{ $number }}</div>
                <div class="text-secondary small">{{ $label }}</div>
            </li>
        @endforeach
    </ul>
</x-tabler.card>

@if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_DOKUMEN_DITOLAK)
    <div class="alert alert-danger">
        <i class="ti ti-alert-triangle me-1"></i>
        Sebagian dokumen ditolak. Perbaiki dokumen yang ditandai, lalu ajukan ulang.
    </div>
@elseif ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_DOKUMEN_DIAJUKAN)
    <div class="alert alert-info">
        <i class="ti ti-clock me-1"></i>
        Dokumen sedang menunggu verifikasi admin. Anda belum dapat mengubah dokumen pada tahap ini.
    </div>
@elseif ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_MENUNGGU_JADWAL)
    <div class="alert alert-success">
        <i class="ti ti-check me-1"></i>
        Dokumen telah disetujui. Menunggu admin memplotting Anda ke jadwal uji.
    </div>
@elseif ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_TERJADWAL)
    <div class="alert alert-success">
        <i class="ti ti-calendar-check me-1"></i>
        Anda telah terjadwal. Silakan cek detail jadwal di bawah.
    </div>
@elseif ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_DITOLAK)
    <div class="alert alert-danger">
        <i class="ti ti-x me-1"></i>
        Pendaftaran ditolak. {{ $pendaftaran->catatan_penolakan }}
    </div>
@endif

@if ($pendaftaran->isWaitingPayment())
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <i class="ti ti-cash me-1"></i>
            @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_PEMBAYARAN_DIAJUKAN)
                Bukti pembayaran sedang menunggu validasi admin.
            @else
                Silakan lakukan pembayaran dan unggah bukti pembayaran Anda.
            @endif
            Total tagihan Rp {{ number_format($pendaftaran->totalTagihan(), 0, ',', '.') }}.
        </div>
        <a href="{{ route('asesi.pembayaran.show', $pendaftaran) }}" class="btn btn-warning">
            <i class="ti ti-cash me-1"></i> Buka Pembayaran
        </a>
    </div>
@endif

@if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_TERJADWAL || $pendaftaran->status === \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN)
    <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <i class="ti ti-list-check me-1"></i>
            Anda terjadwal untuk uji kompetensi. Silakan isi Asesmen Mandiri APL-02 sebelum hari pelaksanaan.
        </div>
        <a href="{{ route('asesi.apl02.show', $pendaftaran) }}" class="btn btn-primary">
            <i class="ti ti-list-check me-1"></i> Buka APL-02
        </a>
    </div>
@endif

<div class="row row-cards">
    <div class="col-lg-8">
        <x-tabler.card title="Dokumen Persyaratan">
            <x-slot:actions>
                <span class="badge bg-azure-lt">{{ $pendaftaran->no_pendaftaran }}</span>
            </x-slot:actions>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Persyaratan</th>
                            <th>Status</th>
                            <th>Berkas</th>
                            <th class="w-1">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendaftaran->skema->persyaratans as $persyaratan)
                            @php($dokumen = $pendaftaran->dokumens->firstWhere('skema_persyaratan_id', $persyaratan->id))
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $persyaratan->nama_dokumen }}</div>
                                    <div class="text-secondary small">
                                        {{ $persyaratan->is_required ? 'Wajib' : 'Opsional' }}
                                        @if ($persyaratan->keterangan)
                                            · {{ $persyaratan->keterangan }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if ($dokumen)
                                        <x-tabler.status-badge :status="$dokumen->status" />
                                        @if ($dokumen->catatan)
                                            <div class="text-danger small mt-1">{{ $dokumen->catatan }}</div>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary-lt">Belum diunggah</span>
                                    @endif
                                </td>
                                <td class="text-secondary small">
                                    @if ($dokumen)
                                        {{ strtoupper(pathinfo($dokumen->file_path, PATHINFO_EXTENSION)) }}
                                        · {{ number_format(((int) $dokumen->file_size) / 1024, 1, ',', '.') }} KB
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($dokumen)
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('asesi.pendaftaran.dokumen.download', $dokumen) }}"
                                               class="btn btn-sm btn-outline-secondary" title="Unduh">
                                                <i class="ti ti-download"></i>
                                            </a>
                                            @if ($canUpload)
                                                <form method="POST"
                                                      action="{{ route('asesi.pendaftaran.dokumen.destroy', [$pendaftaran, $dokumen]) }}"
                                                      onsubmit="return confirm('Hapus dokumen ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($canUpload)
                                        <form method="POST" enctype="multipart/form-data"
                                              action="{{ route('asesi.pendaftaran.dokumen.store', $pendaftaran) }}"
                                              class="mt-2">
                                            @csrf
                                            <input type="hidden" name="skema_persyaratan_id" value="{{ $persyaratan->id }}">
                                            <div class="input-group input-group-sm">
                                                <input type="file" name="file" class="form-control" required
                                                       accept=".pdf,.jpg,.jpeg,.png">
                                                <button type="submit" class="btn btn-outline-primary">
                                                    {{ $dokumen ? 'Ganti' : 'Unggah' }}
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="form-hint mt-2">
                Format: PDF, JPG, JPEG, atau PNG. Maksimal {{ number_format((int) config('pendaftaran.document_max_size_kb') / 1024, 0) }} MB per berkas.
                Berkas disimpan pada penyimpanan privat.
            </div>
        </x-tabler.card>

        @if ($canUpload)
            <x-tabler.card title="Pengajuan Dokumen" class="mt-3">
                @if ($missing->isNotEmpty())
                    <div class="alert alert-warning mb-0">
                        Dokumen wajib berikut belum diunggah:
                        <strong>{{ $missing->pluck('nama_dokumen')->join(', ') }}</strong>.
                    </div>
                @else
                    <p class="text-secondary small">
                        Semua dokumen wajib telah diunggah. Ajukan sekarang agar dapat diverifikasi admin.
                    </p>
                    <form method="POST" action="{{ route('asesi.pendaftaran.submit', $pendaftaran) }}"
                          onsubmit="return confirm('Ajukan dokumen sekarang? Dokumen tidak dapat diubah selama proses verifikasi.')">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-send me-1"></i> Ajukan Dokumen
                        </button>
                    </form>
                @endif
            </x-tabler.card>
        @endif

        @if ($pendaftaran->jadwal)
            <x-tabler.card title="Jadwal Uji Kompetensi" class="mt-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-secondary small">Tanggal</div>
                        <div class="fw-semibold">{{ $pendaftaran->jadwal->tanggal->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-secondary small">Waktu</div>
                        <div class="fw-semibold">
                            {{ substr($pendaftaran->jadwal->jam_mulai, 0, 5) }}–{{ substr($pendaftaran->jadwal->jam_selesai, 0, 5) }} WIB
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-secondary small">TUK</div>
                        <div class="fw-semibold">{{ $pendaftaran->jadwal->tuk->nama_tuk }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-secondary small">Asesor</div>
                        @forelse ($pendaftaran->jadwal->asesors as $asesor)
                            <div class="small">
                                {{ $asesor->nama_lengkap }}
                                <span class="text-secondary">({{ ucfirst($asesor->pivot->peran) }})</span>
                            </div>
                        @empty
                            <div class="small">Belum ditugaskan</div>
                        @endforelse
                    </div>
                </div>
            </x-tabler.card>
        @endif
    </div>

    <div class="col-lg-4">
        <x-tabler.card title="Status Pendaftaran">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary">Status</span>
                <x-tabler.status-badge :status="$pendaftaran->status" />
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary">Tahapan</span>
                <span class="fw-semibold small">{{ $stage['label'] }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-secondary">Skema</span>
                <span class="fw-semibold small">{{ $pendaftaran->skema->kode_skema }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-secondary">Biaya</span>
                <span class="fw-semibold small">Rp {{ number_format((float) $pendaftaran->skema->biaya, 0, ',', '.') }}</span>
            </div>
        </x-tabler.card>

        <div class="card mt-3 bg-primary-lt border-0">
            <div class="card-body">
                <h3 class="card-title text-primary"><i class="ti ti-help-circle me-1"></i> Butuh bantuan?</h3>
                <p class="text-secondary small mb-2">
                    Pelajari alur sertifikasi dan ketentuan dokumen pada panduan asesi.
                </p>
                <a href="{{ route('asesi.panduan') }}" class="btn btn-sm btn-primary w-100">
                    <i class="ti ti-book me-1"></i> Buka Panduan Asesi
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
