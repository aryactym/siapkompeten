<?php

namespace App\Support;

/**
 * Sumber tunggal menu sidebar untuk seluruh portal.
 *
 * Setiap portal (admin, asesor, asesi) memakai struktur data yang sama:
 * `title` grup + `items` berisi label, ikon, nama route, dan pola `match`
 * untuk menandai item aktif. Layout `layouts/portal` merender data ini
 * melalui komponen `x-portal-sidebar`.
 *
 * Status item:
 * - `planned`: route belum dibangun; hanya tampil di lingkungan lokal.
 * - `soon`: route ada tetapi halamannya masih stub; ditampilkan nonaktif.
 */
class PortalNavigation
{
    /**
     * Label portal untuk judul, subtitle brand, dan aria-label sidebar.
     *
     * @var array<string, string>
     */
    private const PORTAL_LABELS = [
        ActivePortal::PORTAL_ADMIN => 'Admin Portal',
        ActivePortal::PORTAL_ASESOR => 'Portal Asesor',
        ActivePortal::PORTAL_ASESI => 'Portal Peserta',
    ];

    /** Label portal berdasarkan kunci portal. */
    public static function label(string $portal): string
    {
        return self::PORTAL_LABELS[$portal] ?? self::PORTAL_LABELS[ActivePortal::PORTAL_ASESI];
    }

    /**
     * Struktur menu sidebar untuk portal tertentu.
     *
     * @return array<int, array{
     *   title: string,
     *   items: array<int, array{
     *     label: string,
     *     icon?: string,
     *     route?: string,
     *     match?: string|array<int, string>,
     *     soon?: bool,
     *     planned?: bool,
     *     query?: string,
     *     children?: array<int, array{label: string, route?: string, match?: string|array<int, string>, soon?: bool, planned?: bool, query?: string}>
     *   }>
     * }>
     */
    public static function sections(string $portal): array
    {
        return match ($portal) {
            ActivePortal::PORTAL_ADMIN => self::adminSections(),
            ActivePortal::PORTAL_ASESOR => self::asesorSections(),
            default => self::asesiSections(),
        };
    }

    /**
     * Menu Portal Pengelola (admin, super-admin, pimpinan).
     *
     * Item `planned` sengaja disembunyikan di produksi karena route belum ada
     * atau halaman masih stub. Catatan keputusan ada di docs/PROGRESS.md.
     *
     * @return array<int, array{title: string, items: array<int, array<string, mixed>>}>
     */
    private static function adminSections(): array
    {
        return [
            ['title' => 'Ringkasan', 'items' => [
                ['label' => 'Dashboard', 'icon' => 'ti-layout-dashboard', 'route' => 'dashboard', 'match' => 'dashboard'],
            ]],

            ['title' => 'Sertifikasi', 'items' => [
                ['label' => 'Asesi & Pendaftaran', 'icon' => 'ti-users', 'children' => [
                    ['label' => 'Asesi', 'route' => 'admin.asesi.index', 'match' => ['admin.asesi.index', 'admin.asesi.show', 'admin.asesi.verify']],
                    ['label' => 'Daftar Pendaftaran', 'route' => 'admin.pendaftaran.index', 'match' => 'admin.pendaftaran.*'],
                    ['label' => 'Impor Asesi (Excel)', 'route' => 'admin.asesi.import.create', 'match' => 'admin.asesi.import.*'],
                    ['label' => 'Verifikasi Dokumen', 'route' => 'admin.pendaftaran.index', 'match' => ['admin.pendaftaran.index', 'admin.pendaftaran.show', 'admin.pendaftaran.dokumen.*', 'admin.pendaftaran.approve', 'admin.pendaftaran.reject', 'admin.pendaftaran.apl01', 'admin.pendaftaran.mak01', 'admin.pendaftaran.mak03']],
                ]],
                ['label' => 'Asesor', 'icon' => 'ti-certificate', 'children' => [
                    ['label' => 'Data Asesor', 'route' => 'admin.asesor.index', 'match' => ['admin.asesor.index', 'admin.asesor.create', 'admin.asesor.store', 'admin.asesor.edit', 'admin.asesor.update']],
                    ['label' => 'Lingkup Kompetensi', 'route' => 'admin.asesor.skema.index', 'match' => ['admin.asesor.skema.*']],
                    ['label' => 'Undang Keanggotaan', 'route' => 'admin.asesor.invite.index', 'match' => ['admin.asesor.invite.*']],
                ]],
                ['label' => 'Penjadwalan', 'icon' => 'ti-calendar', 'children' => [
                    ['label' => 'Jadwal Asesmen', 'route' => 'admin.jadwal.index', 'match' => 'admin.jadwal.*'],
                    ['label' => 'Event', 'route' => 'admin.event.index', 'match' => 'admin.event.*'],
                    ['label' => 'Pleno Asesmen', 'route' => 'pleno.index', 'match' => 'pleno.*'],
                ]],
                ['label' => 'Sertifikat', 'icon' => 'ti-award', 'children' => [
                    ['label' => 'Data Sertifikat', 'route' => 'admin.sertifikat.index', 'match' => ['admin.sertifikat.*']],
                    ['label' => 'Bundle Sertifikat', 'route' => 'admin.sertifikat.bundle.index', 'match' => 'admin.sertifikat.bundle.*'],
                    ['label' => 'Template Presensi', 'route' => 'admin.template_presensi.index', 'match' => 'admin.template_presensi.*', 'planned' => true],
                ]],
            ]],

            ['title' => 'Keuangan', 'items' => [
                ['label' => 'Biaya & Pembayaran', 'icon' => 'ti-coins', 'children' => [
                    ['label' => 'Biaya Skema', 'route' => 'admin.biaya.index', 'match' => 'admin.biaya.*'],
                    ['label' => 'Rekening Bank', 'route' => 'admin.rekening.index', 'match' => 'admin.rekening.*'],
                    ['label' => 'Validasi Pembayaran', 'route' => 'admin.pembayaran.index', 'match' => 'admin.pembayaran.*'],
                ]],
            ]],

            ['title' => 'Master Data', 'items' => [
                ['label' => 'Standar Kompetensi', 'icon' => 'ti-book', 'children' => [
                    ['label' => 'SKKNI', 'route' => 'admin.stkkni.index', 'match' => 'admin.stkkni.*'],
                    ['label' => 'Skema Sertifikasi', 'route' => 'admin.skema.index', 'match' => 'admin.skema.*'],
                    ['label' => 'Unit Kompetensi', 'route' => 'admin.unit.index', 'match' => 'admin.unit.*'],
                    ['label' => 'Elemen & KUK', 'route' => 'admin.elemen.index', 'match' => 'admin.elemen.*'],
                    ['label' => 'TUK', 'route' => 'admin.tuk.index', 'match' => 'admin.tuk.*'],
                ]],
                ['label' => 'Pelaporan', 'icon' => 'ti-receipt', 'children' => [
                    ['label' => 'Data Laporan', 'route' => 'admin.pelaporan.index', 'match' => 'admin.pelaporan.*'],
                ]],
            ]],

            ['title' => 'Komunikasi', 'items' => [
                ['label' => 'Outbox Notifikasi', 'icon' => 'ti-bell-ringing', 'route' => 'admin.notifikasi.index', 'match' => 'admin.notifikasi.*'],
            ]],

            ['title' => 'Konten & Publik', 'items' => [
                ['label' => 'CMS Konten', 'icon' => 'ti-file-text', 'route' => 'admin.cms.index', 'match' => 'admin.cms.*'],
            ]],

            ['title' => 'Sistem', 'items' => [
                ['label' => 'Manajemen Pengguna', 'icon' => 'ti-users', 'route' => 'admin.user.index', 'match' => 'admin.user.*'],
                ['label' => 'Tanda Tangan', 'icon' => 'ti-pencil', 'route' => 'admin.signature.show', 'match' => 'admin.signature.*'],
                ['label' => 'Pengaturan', 'icon' => 'ti-settings', 'children' => [
                    ['label' => 'Profil Lembaga', 'route' => 'admin.institution.index', 'match' => 'admin.institution.*'],
                    ['label' => 'Logo Lembaga', 'route' => 'admin.institution.index', 'match' => 'admin.institution.*', 'query' => 'tab=logo'],
                    ['label' => 'Tim & Persetujuan', 'route' => 'admin.pengaturan.group.show', 'match' => 'admin.pengaturan.group.*'],
                ]],
            ]],
        ];
    }

    /**
     * Menu Portal Asesor: penugasan, penilaian pra-asesmen, dan hasil.
     *
     * @return array<int, array{title: string, items: array<int, array<string, mixed>>}>
     */
    private static function asesorSections(): array
    {
        return [
            ['title' => 'Ringkasan', 'items' => [
                ['label' => 'Dashboard', 'icon' => 'ti-layout-dashboard', 'route' => 'dashboard', 'match' => ['dashboard']],
            ]],

            ['title' => 'Asesmen', 'items' => [
                ['label' => 'Jadwal Uji Saya', 'icon' => 'ti-calendar-event', 'route' => 'asesor.jadwal.index', 'match' => ['asesor.jadwal.index', 'asesor.jadwal.peserta']],
                ['label' => 'Penilaian', 'icon' => 'ti-clipboard-check', 'children' => [
                    ['label' => 'Pra-Asesmen (APL-02)', 'route' => 'asesor.jadwal.index', 'match' => ['asesor.apl02.show', 'asesor.apl02.verifikasi']],
                    ['label' => 'Hasil Asesmen (FR.AK.02)', 'route' => 'asesor.jadwal.index', 'match' => ['asesor.mak02.show', 'asesor.mak02.store', 'asesor.mak02.final', 'asesor.mak02.pdf', 'asesor.mak02.pdf2', 'asesor.mak02.pdf-redesign']],
                    ['label' => 'Rencana Asesmen (MAPA)', 'route' => 'asesor.rencana-asesmen.index', 'match' => ['asesor.rencana-asesmen.*']],
                ]],
            ]],

            ['title' => 'Akun', 'items' => [
                ['label' => 'Tanda Tangan', 'icon' => 'ti-pencil', 'route' => 'asesor.signature.show', 'match' => ['asesor.signature.show', 'asesor.signature.store', 'asesor.signature.delete']],
            ]],
        ];
    }

    /**
     * Menu Portal Peserta (asesi): pendaftaran, asesmen mandiri, hasil.
     *
     * @return array<int, array{title: string, items: array<int, array<string, mixed>>}>
     */
    private static function asesiSections(): array
    {
        return [
            ['title' => 'Ringkasan', 'items' => [
                ['label' => 'Dashboard Saya', 'icon' => 'ti-layout-dashboard', 'route' => 'dashboard', 'match' => ['dashboard']],
            ]],

            ['title' => 'Pendaftaran', 'items' => [
                ['label' => 'Kirim Pendaftaran Baru', 'icon' => 'ti-file-plus', 'route' => 'asesi.pendaftaran.create', 'match' => ['asesi.pendaftaran.create', 'asesi.pendaftaran.store']],
                ['label' => 'Riwayat Uji Kompetensi', 'icon' => 'ti-file-certificate', 'route' => 'asesi.pendaftaran.index', 'match' => ['asesi.pendaftaran.index', 'asesi.pendaftaran.show', 'asesi.pendaftaran.submit', 'asesi.pendaftaran.dokumen.*', 'asesi.pendaftaran.apl02.*']],
                ['label' => 'Biaya & Pembayaran', 'icon' => 'ti-cash', 'route' => 'asesi.pendaftaran.index', 'match' => ['asesi.pembayaran.*']],
                ['label' => 'Asesmen Mandiri (APL-02)', 'icon' => 'ti-checklist', 'route' => 'asesi.pendaftaran.index', 'match' => ['asesi.apl02.*']],
                ['label' => 'Dokumen Pre-Asesmen', 'icon' => 'ti-file-check', 'route' => 'asesi.pendaftaran.index', 'match' => ['asesi.pre-asesmen.*']],
            ]],

            ['title' => 'Hasil', 'items' => [
                ['label' => 'Hasil & Sertifikat', 'icon' => 'ti-award', 'route' => 'asesi.sertifikat.index', 'match' => ['asesi.sertifikat.*']],
                ['label' => 'Panduan Peserta', 'icon' => 'ti-help-circle', 'route' => 'asesi.panduan', 'match' => ['asesi.panduan']],
            ]],

            ['title' => 'Akun', 'items' => [
                ['label' => 'Profil Saya', 'icon' => 'ti-user-edit', 'route' => 'asesi.profil.edit', 'match' => ['asesi.profil.edit', 'asesi.profil.update']],
                ['label' => 'Tanda Tangan', 'icon' => 'ti-pencil', 'route' => 'asesi.signature.show', 'match' => ['asesi.signature.show', 'asesi.signature.store', 'asesi.signature.delete']],
            ]],
        ];
    }
}
