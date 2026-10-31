@extends('layouts.asesi')

@section('title', 'Panduan & Alur Sertifikasi')
@section('pretitle', 'Panduan Penggunaan')
@section('page-title', 'Panduan & Alur Kerja Sertifikasi')

@section('content')
<div class="row row-cards">
    <div class="col-lg-8">
        <x-tabler.card title="Alur Kerja Sertifikasi Asesi">
            <div class="divide-y">
                @foreach ([
                    ['APL-01 & Profil', 'Lengkapi profil pokok, unggah dokumen persyaratan skema (KTP, ijazah, pas foto, dan dokumen pendukung lain).', 'dokumen'],
                    ['Verifikasi Admin', 'Admin memeriksa keabsahan dan kelengkapan dokumen. Dokumen yang ditolak harus diperbaiki dan diajukan ulang.', 'dokumen_ditolak'],
                    ['Pembayaran', 'Apabila skema berbayar, unggah bukti pembayaran sesuai tagihan yang diterbitkan LSP (modul pembayaran aktif pada Fase 4).', 'pembayaran'],
                    ['Penjadwalan', 'Admin memplotting asesi ke jadwal uji dan menugaskan asesor. Asesi menerima notifikasi jadwal.', 'terjadwal'],
                    ['APL-02 & Asesmen', 'Asesi mengisi formulir asesmen mandiri APL-02 sebelum pelaksanaan uji, lalu mengikuti asesmen bersama asesor.', 'apl02'],
                    ['Hasil & Sertifikat', 'Hasil asesmen final menentukan status kompeten. Sertifikat diterbitkan setelah laporan asesmen disetujui.', 'selesai'],
                ] as $index => [$title, $description, $stage])
                    <div class="py-3 d-flex gap-3">
                        <span class="avatar avatar-sm bg-primary-lt text-primary fw-bold">{{ $index + 1 }}</span>
                        <div>
                            <div class="fw-semibold">{{ $title }}</div>
                            <div class="text-secondary small">{{ $description }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-tabler.card>

        <x-tabler.card title="Ketentuan Dokumen" class="mt-3">
            <ul class="mb-0 text-secondary small">
                <li>NIK wajib 16 digit angka dan tidak dapat diubah sendiri setelah registrasi.</li>
                <li>Berkas hanya PDF, JPG, JPEG, atau PNG dengan ukuran maksimal 5 MB per berkas.</li>
                <li>Tipe berkas divalidasi di server berdasarkan isi berkas, bukan hanya ekstensi.</li>
                <li>Seluruh berkas disimpan pada penyimpanan privat dan hanya dapat diakses pengguna berwenang.</li>
                <li>Pastikan hasil pindai terbaca jelas dan sesuai dengan persyaratan skema.</li>
            </ul>
        </x-tabler.card>
    </div>

    <div class="col-lg-4">
        <x-tabler.card title="Tahapan Anda">
            <p class="text-secondary small">
                Sistem menerapkan <strong>strict phase gating</strong>: tahapan berikutnya tidak dapat dibuka
                sebelum tahapan sebelumnya disetujui di server. Ini mencegah lompatan proses dan menjaga
                integritas data asesmen.
            </p>
            <a href="{{ route('asesi.pendaftaran.index') }}" class="btn btn-primary w-100">
                <i class="ti ti-file-certificate me-1"></i> Lihat Pendaftaran Saya
            </a>
        </x-tabler.card>

        <x-tabler.card title="Kontak Lembaga" class="mt-3">
            @php($institution = \App\Support\TenantContext::institution())
            <div class="mb-2">
                <div class="text-secondary small">LSP Penyelenggara</div>
                <div class="fw-semibold">{{ $institution?->name ?? config('app.name') }}</div>
            </div>
            <div class="mb-2">
                <div class="text-secondary small">Email</div>
                <div class="fw-semibold">{{ $institution?->email ?? '—' }}</div>
            </div>
            <div>
                <div class="text-secondary small">Telepon</div>
                <div class="fw-semibold">{{ $institution?->telepon ?? '—' }}</div>
            </div>
        </x-tabler.card>
    </div>
</div>
@endsection
