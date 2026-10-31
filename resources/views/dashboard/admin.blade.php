@extends('layouts.admin')

@section('title', 'Dashboard Administrator')
@section('pretitle', 'Ringkasan & Metrik Operasional')
@section('page-title', 'Dashboard Lembaga')

@section('actions')
    <a href="{{ route('admin.jadwal.create') }}" class="btn btn-primary d-none d-sm-inline-block">
        <i class="ti ti-plus me-1"></i> Buat Jadwal Uji
    </a>
    <span class="btn btn-outline-primary d-none d-sm-inline-block disabled" aria-disabled="true">
        <i class="ti ti-certificate me-1"></i> Tambah Skema
        <span class="badge bg-secondary-lt ms-1">Segera</span>
    </span>
@endsection

@section('content')
<div class="row row-deck row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat 
            title="Skema Sertifikasi"
            value="{{ $stats['total_skema'] ?? 0 }}"
            icon="ti ti-certificate"
            color="primary"
            description="Skema aktif berlisensi BNSP"
        />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat 
            title="Total Asesi"
            value="{{ $stats['total_asesi'] ?? 0 }}"
            icon="ti ti-users"
            color="green"
            description="Asesi terdaftar di sistem"
        />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat 
            title="Jadwal Uji Aktif"
            value="{{ $stats['total_jadwal'] ?? 0 }}"
            icon="ti ti-calendar-event"
            color="yellow"
            description="Event & batch asesmen"
        />
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-tabler.stat 
            title="Asesor Kompetensi"
            value="{{ $stats['total_asesor'] ?? 0 }}"
            icon="ti ti-user-check"
            color="azure"
            description="Tersedia untuk penugasan"
        />
    </div>
</div>

<div class="row row-cards">
    <!-- Kolom Kiri: Ringkasan Lembaga Aktif & Dokumen -->
    <div class="col-lg-8">
        <x-tabler.card title="Dokumen & Portofolio Lembaga">
            <x-slot:actions>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-upload-doc">
                    <i class="ti ti-upload me-1"></i> Unggah Dokumen
                </button>
            </x-slot:actions>

            @if ($documents->isEmpty())
                <x-tabler.empty 
                    title="Belum ada dokumen lembaga"
                    description="Unggah SK lisensi BNSP, surat keputusan TUK, atau panduan mutu lembaga Anda di sini."
                    icon="ti ti-files"
                />
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-hover">
                        <thead>
                            <tr>
                                <th>Judul Dokumen</th>
                                <th>Format & Ukuran</th>
                                <th>Diunggah Oleh</th>
                                <th>Waktu</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($documents as $document)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-file-text fs-2 text-primary me-2"></i>
                                            <div>
                                                <a href="{{ route('documents.show', $document) }}" class="text-reset fw-semibold">
                                                    {{ $document->title }}
                                                </a>
                                                <div class="text-secondary small">{{ $document->original_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt text-uppercase">{{ pathinfo($document->original_name, PATHINFO_EXTENSION) }}</span>
                                        <span class="text-secondary small ms-1">{{ number_format($document->size / 1024, 1, ',', '.') }} KB</span>
                                    </td>
                                    <td class="text-secondary small">
                                        {{ $document->uploader?->name ?? 'Sistem' }}
                                    </td>
                                    <td class="text-secondary small">
                                        {{ $document->created_at?->diffForHumans() }}
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('documents.download', $document) }}" class="btn btn-sm btn-outline-secondary" title="Unduh">
                                                <i class="ti ti-download"></i>
                                            </a>
                                            <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer d-flex align-items-center py-2">
                    {{ $documents->links() }}
                </div>
            @endif
        </x-tabler.card>
    </div>

    <!-- Kolom Kanan: Status Kepatuhan & Informasi Sistem -->
    <div class="col-lg-4">
        <x-tabler.card title="Status Keamanan & Kepatuhan">
            <div class="divide-y">
                <div class="py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Standar Keamanan</div>
                        <div class="text-secondary small">OWASP Top 10:2025</div>
                    </div>
                    <span class="badge bg-green-lt"><i class="ti ti-shield-check me-1"></i> Aktif</span>
                </div>
                <div class="py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Isolasi Multitenant</div>
                        <div class="text-secondary small">BelongsToTenant Fail-Closed</div>
                    </div>
                    <span class="badge bg-green-lt"><i class="ti ti-check me-1"></i> Terisolasi</span>
                </div>
                <div class="py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Enkripsi NIK & Data Pribadi</div>
                        <div class="text-secondary small">AES-256 + HMAC Blind Index</div>
                    </div>
                    <span class="badge bg-green-lt"><i class="ti ti-lock me-1"></i> Enkripsi DB</span>
                </div>
                <div class="py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Format Formulir BNSP</div>
                        <div class="text-secondary small">APL-01, APL-02, MAK-01..03</div>
                    </div>
                    <span class="badge bg-blue-lt">Resmi BNSP</span>
                </div>
            </div>
        </x-tabler.card>

        <div class="card mt-3 bg-primary-lt border-0 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-primary"><i class="ti ti-info-circle me-1"></i> Bantuan & Dokumentasi</h3>
                <p class="text-secondary small mb-3">
                    Pelajari panduan integrasi sistem sertifikasi e-LSP sesuai standar BNSP dan alur kerja verifikasi asesi.
                </p>
                <span class="btn btn-sm btn-primary w-100 disabled" aria-disabled="true">
                    <i class="ti ti-book me-1"></i> Panduan Admin (Segera)
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Modal Unggah Dokumen -->
<div class="modal modal-blur fade" id="modal-upload-doc" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Unggah Dokumen Lembaga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Judul Dokumen</label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: SK Lisensi BNSP 2026" required maxlength="200">
                </div>
                <div class="mb-3">
                    <label class="form-label required">Berkas (PDF, JPG, PNG)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="form-hint">Maksimal ukuran berkas 5 MB. Disimpan aman pada disk privat.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary ms-auto">
                    <i class="ti ti-upload me-1"></i> Simpan & Unggah
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
