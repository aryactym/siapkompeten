# 📊 Progress Tracker - SiapKompeten

> Diperbarui setiap akhir sesi kerja. Sumber fase: `docs/System_Design_Aplikasi_LSPKU.md` bagian 14.
> Standar keamanan wajib: `docs/SECURITY_STANDARDS_OWASP.md` (OWASP Top 10:2025).

---

## 🌿 Informasi Branch

### Branch `single` — Mode 1 Aplikasi, 1 Lembaga

> **Dibuat**: 31 Oktober 2026 dari branch utama (main/master)

Branch ini adalah varian penyederhanaan arsitektur untuk deployment satu LSP per instalasi. Tujuannya adalah menghilangkan kerumitan session-based multitenancy agar lebih mudah diuji dan di-deploy.

**Perbedaan utama dari branch utama:**

| Aspek | Branch Utama | Branch `single` |
|---|---|---|
| `TenantContext` | Berbasis session, perlu `set()` eksplisit setelah login | Membaca langsung dari DB (institution aktif pertama), tidak perlu session |
| Institution switcher | Ada — user bisa pindah antar lembaga | Dihapus — hanya satu lembaga per instalasi |
| `accessibleInstitutions()` | Query pivot `institution_user` per user | Tidak kritis — satu institution selalu sama untuk semua user |
| Login flow | `TenantContext::set()` wajib dijalankan setelah login | Login tidak perlu menyentuh TenantContext |
| `EnsureTenantContext` middleware | Resolusi tenant kompleks + fallback pivot | Disederhanakan: cukup pastikan institution aktif tersedia dari config/DB |
| Registrasi asesi | Nama LSP ditampilkan dari `defaultInstitution()` | Sama — sudah sesuai pola single-tenant |
| `BelongsToTenant` trait | Fail-closed, bergantung pada session | Sama — tetap fail-closed, tapi `TenantContext::id()` selalu tersedia tanpa session |

**Rencana perubahan yang masih pending di branch ini:**
- [ ] `TenantContext::id()` / `institution()` / `name()` — baca dari DB, cache static property, tidak bergantung session
- [ ] `TenantContext::set()` dan `clear()` — dijadikan no-op (tidak dihapus agar interface tidak rusak)
- [ ] `EnsureTenantContext` middleware — disederhanakan, hapus logika `accessibleInstitutions()`
- [ ] `LoginController` — hapus blok `accessibleInstitutions()` dan `TenantContext::set()` setelah login
- [ ] `DashboardController::switchInstitution()` — route `tenant.switch` dihapus
- [ ] `header.blade.php` — hapus institution switcher dropdown
- [ ] Halaman login menampilkan nama LSP dari institution aktif (✅ **sudah dilakukan di sesi ini**)

**Yang tidak berubah di branch `single`:**
- Struktur database tetap sama (kolom `institution_id` di semua tabel, pivot `institution_user` tetap ada)
- `BelongsToTenant` trait tetap ada di 29+ model
- Semua fitur bisnis (pendaftaran, asesmen, pembayaran, dll.) tidak berubah
- Skema DB tetap siap multi-LSP untuk migrasi kembali ke branch utama

---

## Status Ringkasan

- **Fase Saat Ini**: Fase 0–5 selesai — berikutnya Fase 6 (Hasil & Laporan). 68/68 feature tests passing (Dusk tertunda: `laravel/dusk` belum terpasang di `vendor`)
- **Status Overall**: In Progress
- **Terakhir Diperbarui**: 4 November 2026

## Log Progress Per Fase

### Fase 0: Fondasi & Environment

**Environment (SELESAI)**

- [x] Ganti PHP CLI Laragon ke 8.4.23 — PHP 8.4.23 tervalidasi dan aktif
- [x] Buat database MySQL `siapkompeten` + user aplikasi hak minimum
- [x] Perbarui `.env`: `APP_NAME=SiapKompeten`, `APP_URL=http://siapkompeten.test`, `APP_LOCALE=id`, `APP_TIMEZONE=Asia/Jakarta`, koneksi MySQL
- [x] Instal Laravel Boost (`composer require laravel/boost --dev` + `php artisan boost:install`)
- [x] Verifikasi `php artisan migrate` berjalan

**Fondasi Aplikasi**

- [x] Base model UUID v7 (trait `HasUuids`) + konvensi migrasi `uuid`/`foreignUuid`
- [x] Pasang Tabler UI (`@tabler/core`, `@tabler/icons-webfont`) via npm + Vite
- [x] Layout `admin`, `asesor`, `asesi`, `auth`, `public`, `lock` (Blade + Tabler)
- [x] Komponen Blade reusable (`x-tabler.stat`, `status-badge`, `card`, `empty`, `modal`, `file-preview`, `header`)
- [x] Autentikasi per portal (username / NIK / email) + lock screen (FR-AUTH-01/02)
- [x] RBAC `spatie/laravel-permission` (UUID) — peran: super-admin, admin, asesor, asesi, pimpinan
- [x] Audit log `spatie/laravel-activitylog` (UUID causer/subject & tenant `institution_id`)
- [x] Middleware `EnsureRole`, `EnsureSetupCompleted`, `EnsureNotBlocked`, `EnsureNotLocked`
- [x] Seeder `RolePermissionSeeder`, `SuperAdminSeeder`, `DatabaseSeeder` (demo akun per peran)
- [x] Test Dusk smoke: login admin & asesi, alur jadwal/pendaftaran, akses ditolak lintas tenant (login asesor belum dicakup)

**Keamanan Baseline (OWASP Top 10:2025)**

- [x] Middleware security headers (CSP, nosniff, Referrer-Policy, Permissions-Policy, frame-ancestors) — A02
- [x] Konfigurasi produksi aman (`APP_DEBUG=false`, cookie Secure/HttpOnly/SameSite) + dokumentasi — A02
- [x] Rate limit login 5/menit per IP+akun, pesan galat generik — A07
- [x] Policy dasar + test 403 lintas peran/pemilik — A01
- [x] Disk privat untuk dokumen/foto + validasi MIME ketat (`finfo`) — A01/A04/A08
- [x] Enkripsi NIK (`encrypted` cast) + `nik_hash` HMAC-SHA256 blind index — A04
- [x] `composer audit` + `npm audit` di CI — A03 (0 vulnerabilities)
- [x] Handler eksepsi global + pesan generik produksi + ID korelasi — A10
- [ ] Checklist keamanan per fitur masuk template PR — A01–A10

### Fase 0.5: Kesiapan Multitenancy (desain: System Design §4.2)

- [x] Migrasi `institutions` sebagai root tenant (`slug`, `domain`, `status`, `is_utama`) + `InstitutionSeeder` 1 LSP MVP
- [x] `TenantContext` singleton + `config/tenancy.php` (strict, default_lsp, domain_suffix)
- [x] Trait `BelongsToTenant`: global scope `institution_id`, auto-fill saat create, `institution_id` immutable, `withoutTenancy()` eksplisit
- [x] Middleware `EnsureTenantContext` (resource tenant lain → 404, super-admin platform bypass)
- [x] Tambah `institution_id` + indeks komposit + unique `(tenant_key, ...)` di seluruh migrasi tenant
- [x] Aturan unique akun platform (`institution_id=NULL`): generated `tenant_key` virtual column (`COALESCE(institution_id, 'platform')`)
- [ ] FK komposit `(institution_id, parent_id)` untuk `pendaftarans`, `hasil_asesmens`, `sertifikats`
- [ ] Path storage `tenants/{institution_id}/...` + signed URL
- [ ] `TenantAwareJob` + `TenantAwareCommand` (`--lsp=` / `--all-tenants`, fail closed)
- [x] Feature test: akses lintas tenant 404/403, auto-fill `institution_id`, unique komposit, command tanpa tenant gagal
- [ ] Area `/platform` (CRUD LSP) — ditunda sampai aktivasi LSP kedua, route disembunyikan pada MVP
- [x] UI pemilih tenant (Tenant Switcher di header)

### Fase 1: Master Data

- [x] Setup awal & konfigurasi fleksibel (`app_settings` global & tenant override) — FR-SET-01/02
- [x] Data profil LSP (nomor lisensi BNSP, masa berlaku, kontak, alamat, logo) — FR-LSP-01..04
- [x] SKKNI (Standar Kompetensi Kerja Nasional Indonesia) — FR-STD-01
- [x] Skema Sertifikasi + biaya + aktif/nonaktif — FR-STD-02/03
- [x] Persyaratan dokumen per skema sertifikasi — FR-STD-04
- [x] Unit kompetensi mandiri di bawah SKKNI — FR-STD-05
- [x] Pemetaan Skema-Unit Many-to-Many via tabel perantara `skema_units` (inti/pilihan, urutan) — FR-STD-05b
- [x] Elemen kompetensi terhubung ke unit — FR-STD-06
- [x] Kriteria Unjuk Kerja (KUK) dengan soal observasi, lisan, tertulis — FR-STD-07
- [x] Tempat Uji Kompetensi (TUK) mandiri/sewaktu/tempat kerja — FR-SCH-01/02
- [x] Data Wilayah resmi Indonesia via package `laravolt/indonesia` (Provinsi, Kota/Kabupaten, Kecamatan, Desa)
- [x] Feature test komprehensif master data (isolasi tenant, Many-to-Many unit, relasi wilayah) — 33/33 tests passing

### Fase 2: Penjadwalan (SELESAI)

- [x] Event uji kompetensi — FR-SCH-03
- [x] Jadwal uji kompetensi + penugasan multi-asesor (utama, pendamping, observer untuk uji paralel) + surat tugas — FR-SCH-04..07
- [x] Validasi bentrok jadwal asesor (tanggal + irisan jam, saat penugasan dan ubah jadwal) — FR-SCH-10
- [x] Presensi cetak real-time (A4 print-ready, data peserta terkini) — FR-SCH-08 / FR-FRM-05
- [x] Ekspor PDF server-side (dompdf) presensi & surat tugas asesor — FR-FRM-05
- [x] Portal asesor dasar + dashboard statistik (jadwal, peserta, status) — FR-ASR-04..07
- [x] Notifikasi plotting asesor via outbox — FR-SCH-09

### Fase 3: Asesi & Pendaftaran (SELESAI)

- [x] Registrasi mandiri + profil + dokumen pokok (validasi NIK 16 angka, NIK terenkripsi + blind index, limit & MIME berkas) — FR-ASI-01/02
- [x] Widget Panduan Penggunaan Sistem & Alur Kerja Sertifikasi — FR-ASI-11
- [x] Strict Phase Gating (profil -> dokumen -> verifikasi -> jadwal; unggah/ajukan ditolak di luar tahap) — FR-ASI-12
- [x] Daftar asesi + filter wilayah + tab status — FR-ASI-03..07
- [x] Impor Excel (template, preview, laporan galat, duplikat NIK) — FR-ASI-08..10
- [x] Pendaftaran skema + unggah dokumen persyaratan (disk privat, path per tenant) — FR-REG-01
- [x] Verifikasi dokumen (setuju/tolak + catatan wajib saat tolak, unggah ulang) — FR-REG-02..04
- [x] Persetujuan pendaftaran + plotting + validasi kuota/skema/asesor + notifikasi — FR-REG-05..07
- [x] Outbox SMS/Email + kirim ulang via `notifikasi:retry-gagal` — FR-NTF-01..04
- [x] SMS masuk (inbound webhook) — FR-NTF-05 — **dihapus dari lingkup** (keputusan pengguna: fokus email)

### Fase 4: Biaya & Pembayaran (SELESAI)

- [x] Rekening bank — FR-PAY-01
- [x] Biaya per skema (jenis, nominal) — FR-PAY-02/03
- [x] Tagihan + unggah bukti bayar — FR-PAY-04
- [x] Validasi pembayaran + catatan — FR-PAY-05/06

### Fase 5: Asesmen & Formulir (SELESAI, PDF APL-01/MAK-01/MAK-03 menyusul)

- [x] APL-02 oleh asesi — FR-ASM-01
- [x] Verifikasi APL-02 oleh asesor — FR-ASM-02..04
- [x] MAK-02 per KUK/unit/metode + rekomendasi + persentase — FR-ASM-05..08
- [x] Draf/final + penguncian + koreksi admin beraudit — FR-ASM-10
- [x] Status kompetensi otomatis — FR-ASM-12
- [ ] PDF APL-01, MAK-01, MAK-03 (sesuai template resmi BNSP) — FR-FRM-01/03/05
- [x] PDF APL-02, MAK-02, presensi, surat tugas — FR-FRM-02/04/05

### Fase 6: Hasil & Laporan

- [ ] Dashboard admin (statistik + grafik) — FR-RPT-01/02
- [ ] Laporan + ekspor Excel/PDF — FR-RPT-03
- [ ] Sertifikat Kompetensi BNSP: pemisahan dua nomor unik (`no_seri_blangko` kertas fisik BNSP + `no_registrasi` lisensi nasional) & validasi regex BNSP `^[A-Z0-9\-\.]+\.[A-Z0-9\-\.]+\.\d{5,}\.\d{4}$` + QR verifikasi — FR-RPT-04 / FR-CMS-06

### Fase 7: CMS & Publik

- [x] CMS konten frontpage (kategori template, sanitasi HTML) — FR-CMS-01/02
- [x] Halaman publik (skema, asesor, berita, kontak, daftar, masuk) — FR-CMS-03/04
- [ ] Meta SEO dari setup awal — FR-CMS-05

### Fase 8: Hardening & Go-Live

- [ ] Audit keamanan menyeluruh berbasis OWASP Top 10:2025
- [ ] Uji performa (10.000 asesi) + tuning indeks
- [ ] UAT + dokumentasi pengguna
- [ ] Backup terenkripsi + uji restore
- [ ] Checklist rilis (audit dependensi, SBOM, header, `APP_DEBUG=false`)

## 📝 Catatan Sesi Kerja

- **30 Oktober 2026 — Sesi 1**
  - Membaca seluruh dokumen acuan: `docs/BRD_Aplikasi_LSPKU.md`, `docs/System_Design_Aplikasi_LSPKU.md`.
  - Menetapkan `docs/SECURITY_STANDARDS_OWASP.md` (OWASP Top 10:2025) sebagai standar keamanan wajib; ringkasannya ditambahkan ke `AGENTS.md`.
  - Membuat `PROGRESS_TRACKER.md`.

- **30 Oktober 2026 — Sesi 2**
  - **Environment**: Mengaktifkan PHP 8.4.23 Laragon di PATH. Membuat database MySQL `siapkompeten`.
  - **Laravel Boost**: Berhasil instalasi `laravel/boost` + Boost MCP + guidelines + skills.
  - **RBAC & Audit**: Instalasi `spatie/laravel-permission` dan `spatie/laravel-activitylog` dengan migrasi kustom UUID v7 dan isolasi tenant (`institution_id`). Membuat model `Role`, `Permission`, dan `Activity` (UUID).
  - **Model User & Keamanan NIK**: Menambahkan `HasRoles`, mutator/accessor `casts` encrypted NIK, blind index `nik_hash` (HMAC-SHA256 dengan pepper `APP_KEY`), helper masking NIK, status checking (`isActive()`, `isBlocked()`), virtual column `tenant_key` untuk keunikan akun platform vs tenant.
  - **Middleware Keamanan Baseline**:
    - `SecurityHeaders` (A02: CSP, nosniff, frame-ancestors, Referrer-Policy, Permissions-Policy).
    - `EnsureNotBlocked` (A07: pembatalan sesi instan saat akun terblokir/nonaktif).
    - `EnsureRole` (A01: otorisasi portal ketat, 403 denied, super-admin override).
    - `EnsureSetupCompleted` (A06: wizard gating).
    - `EnsureTenantContext` diperbarui agar super-admin platform dapat beroperasi tanpa terjebak keanggotaan institusi tunggal.
  - **Testing**: 13 automated tests berjalan dan 100% lulus (`TenantIsolationTest`, `SecurityHeadersTest`, `AccountStatusTest`, `NikSecurityTest`, `RbacAccessTest`, `ExampleTest`).
  - **Tabler UI**: Menginstal `@tabler/core` dan `@tabler/icons-webfont` via npm (0 vulnerabilities). Menghubungkan CSS & ikon ke `resources/css/app.css` dan `resources/js/app.js`.
  - **Template MAK BNSP**: Mengekstrak template resmi Word BNSP (`Template_Formulir_MAK_BNSP.docx`) dan mendokumentasikan strukturnya di `docs/TEMPLATE_MAK_BNSP.md`.
  - **Arsitektur & Business Rules Terkini**:
    - *Unit Kompetensi ↔ Skema*: Many-to-Many melalui tabel junction `skema_units` (unit mandiri, fleksibilitas materi uji per skema).
    - *Multi-Asesor*: Satu jadwal dapat menampung lebih dari 1 asesor (utama, pendamping, observer) agar pengujian dapat berjalan paralel.
    - *Sertifikat BNSP*: Blangko fisik resmi eksklusif BNSP; sistem memisahkan dua nomor unik `no_seri_blangko` (kertas fisik BNSP) dan `no_registrasi` (register nasional) dengan validasi regex standar BNSP `^[A-Z0-9\-\.]+\.[A-Z0-9\-\.]+\.\d{5,}\.\d{4}$`.
    - *Portal Asesi*: Strict phase gating (anti-lompat tahapan: APL-01 -> verifikasi -> bayar -> jadwal -> APL-02), widget panduan sistem & alur kerja, validasi NIK 16 digit, dan validasi MIME payload.

- **31 Oktober 2026 — Sesi 3**
  - **Fase 2 — Penjadwalan**: CRUD event & jadwal, penugasan multi-asesor (utama/pendamping/observer), validasi bentrok asesor berbasis irisan jam, presensi A4 print-ready, surat tugas, dan portal asesor (daftar jadwal, peserta, dashboard). Notifikasi penugasan masuk outbox.
  - **Fase 3 — Asesi & Pendaftaran**: registrasi mandiri (NIK 16 digit, enkripsi + blind index), profil asesi + gating, pendaftaran skema, unggah dokumen ke disk privat `tenants/{institution_id}/pendaftaran/...`, verifikasi dokumen, persetujuan + plotting dengan lock kuota, dan outbox notifikasi.
  - **Keamanan**: policy per model (`JadwalAsesmen`, `Asesi`, `Pendaftaran`, `PendaftaranDokumen`), validasi `Rule::exists` ber-tenant, route binding global scope tenant, operasi multi-tabel dalam transaksi DB, dan audit log aktivitas.
  - **Testing**: `PenjadwalanTest`, `PendaftaranTest`, `NotifikasiOutboxTest` — total 47/47 feature tests lulus. Dusk: `AlurPenjadwalanDanPendaftaranTest` (admin buat jadwal + tugaskan asesor; asesi registrasi + buat pendaftaran) dan `TenantIsolationTest` — 3/3 lulus. Perbaikan bug: `whereDate()` pada deteksi bentrok agar nilai `date` MySQL/SQLite konsisten; selector form login Dusk lama disesuaikan (`login`, tombol “Masuk Sekarang”).
  - **Catatan**: ekspor PDF server-side (presensi/surat tugas) masih print-ready HTML; menunggu persetujuan penambahan dependensi dompdf.

- **31 Oktober 2026 — Sesi 4**
  - **Perbaikan navigasi (bug kritis)**: `<x-tabler.header />` berada sebagai anak langsung `.page` bersama `<aside class="navbar-vertical">`, sehingga aturan Tabler `html:not([data-bs-navbar-position=vertical]) .page:has(>[class*=navbar-expand]:not(.navbar-vertical))>.navbar-vertical { display:none }` menyembunyikan sidebar admin. Header dipindahkan ke dalam `.page-wrapper` pada layout `admin`, `asesi`, dan `asesor`. Terverifikasi lewat Dusk + screenshot (`NavigasiAdminTest`).
  - **Menu bar disesuaikan per peran**: item yang fiturnya sudah ada (Impor Asesi, Rekening Bank, Biaya Skema, Validasi Pembayaran, APL-02, MAK-02) ditautkan ke route aslinya; item yang belum dibangun ditandai disabled `Segera`; tautan mati `#` di dashboard (Buat Jadwal Uji, Panduan) diarahkan/dinonaktifkan; SMS Masuk dihapus.
  - **Fase 4 — Biaya & Pembayaran (SELESAI)**: migrasi `rekening_banks`, `biayas`, `pembayarans`; model + controller admin (`RekeningBankController`, `BiayaController`, `PembayaranController`) dan portal asesi (`Asesi\PembayaranController`); upload bukti ke disk privat `tenants/{id}/pembayaran/...` via `BuktiBayarStorage`; alur `menunggu_pembayaran -> pembayaran_diajukan -> valid/tidak_valid -> menunggu_jadwal`; format Rupiah otomatis (FR-PAY-06); config `pendaftaran.payment_before_scheduling` default `true`.
  - **Fase 5 — Asesmen & Formulir (SELESAI)**: migrasi `apl02_penilaians`, `apl02_jawabans`, `hasil_asesmens`, `hasil_asesmen_kuks`, `hasil_asesmen_units`, `hasil_asesmen_metodes`; `AsesmenService` (draf/serah APL-02, verifikasi 4C, simpan & finalkan MAK-02); policy `fillApl02`/`verifyApl02`/`inputMak02`; finalisasi mengunci laporan, menghitung persentase KUK, memperbarui `asesis.status_kompetensi` dan status pendaftaran ke `selesai`.
  - **PDF**: `barryvdh/laravel-dompdf` dipakai untuk presensi, surat tugas, APL-02, dan MAK-02.
  - **Testing**: 64/64 feature tests (`PembayaranTest`, `AsesmenTest` termasuk); Dusk 7/7 (`NavigasiAdminTest`, `AlurFase4Dan5Test` — menu admin, upload bukti asesi, akses APL-02/MAK-02 asesor).
  - **Sisa lingkup**: PDF APL-01/MAK-01/MAK-03, monitoring APL-02 & rekap admin, CMS, area platform.

- **31 Oktober 2026 — Sesi 5**
  - **Standar runtime**: wajib **PHP 8.4 Laragon** (`D:\laragon\bin\php\php-8.4.23-Win32-vs17-x64\php.exe`); `php` di PATH masih 8.3.30 sehingga `php artisan` polos gagal di `composer/platform_check.php` (>= 8.4.1). Dicatat di `AGENTS.md` bagian "Standar Runtime PHP (Wajib)".
  - **Sidebar admin dirapikan** (`resources/views/layouts/admin.blade.php`):
    - Menu jadi satu sumber data (array `$adminMenu`) + komponen baru `x-menu-item` (`resources/views/components/menu-item.blade.php`); markup item tidak lagi duplikat 20+ kali.
    - Item aktif via pola `request()->routeIs()`; grup berisi item aktif otomatis terbuka (`aria-expanded="true"` + `show`) — sebelumnya submenu aktif selalu tertutup.
    - Route belum ada / halaman masih stub (501) dirender nonaktif `aria-disabled` + badge "Segera" (tanpa tautan mati `#`): Data Skema, TUG, Profil Lembaga, Lingkup Kompetensi, Undang Keanggotaan, Bundle, Sertifikat, Template Presensi, Data Laporan, SIUP User, Lampiran, Kategori Prestasi, Tim & Persetujuan.
    - Diperbaiki: pola aktif salah (`admin.tuk.bundle.create,frame` ber- coma dalam satu string, daftar raksasa di "Tim & Persetujuan" yang ikut menyala di halaman lain, `admin.asesi.impor`/`admin.asesor.impor` yang tidak ada); ikon tidak tersedia di font Tabler (`ti-stamp`, `ti-presence`, `ti-file-compressed`, `ti-cloud-arrow-up` → `ti-certificate`, `ti-clipboard-check`, `ti-file-zip`, `ti-cloud-upload`); chevron/icon Dashboard yang memakai sprite SVG dari berkas `.woff2` (tidak valid) → `<i class="ti ti-*">`.
    - Menu fitur yang sudah jadi tetapi hilang dari sidebar ditambahkan: grup **Biaya & Pembayaran** (Biaya Skema, Rekening Bank, Validasi Pembayaran) dan item **Outbox Notifikasi**.
  - **Bug route kritis diperbaiki**: `routes/web.php` memakai 10 controller (`AdminSkemaController`, `AdminStkkniController`, `AdminTukController`, `AdminInstitutionController`, `AdminUnitKompetensiController`, `AdminUnitMappingController`, `AdminElemenKompetensiController`, `AdminProvinsiController`, `AdminKotaController`, `AdminWilayahController`) tanpa `use` → route-nya 500 dan `php artisan route:list` crash. 10 import ditambahkan; halaman tersebut sekarang konsisten 501 "Belum diimplementasi" (stub), bukan 500.
  - **Testing**: `SidebarMenuTest` baru (tautan fitur siap, item "Segera" tanpa tautan mati, grup auto-expand); perbaikan typo `Dasboard` → `Dashboard Lembaga` (`dashboard/admin.blade.php`) dan asersi basi `Portal Administrator` → `Admin Portal` di `DashboardTest`. Total **67/67 feature tests lulus**, `pint --dirty` bersih.
  - **Layout sidebar mengikuti Tabler vertical resmi** (https://preview.tabler.io/layout-vertical.html):
    - `<html data-bs-navbar-position="vertical">` + `<aside class="navbar navbar-vertical navbar-expand-lg">` + `<nav class="collapse navbar-collapse" id="sidebar-menu">` + `<ul class="navbar-nav pt-lg-3">` dengan `<li class="nav-section-title">` (Ringkasan, Sertifikasi, Keuangan, Data & Integrasi, Sistem) dan grup `dropdown`/`dropdown-menu` sesuai demo Tabler.
    - `x-menu-item` mendapat `variant` ('nav' untuk link berikon, 'dropdown' untuk item submenu tanpa ikon); item "Segera" dirender `dropdown-item disabled` + badge.
    - `resources/css/app.css`: seluruh hack CSS lama dihapus (`.sidebar*`, `.nav-dropdown*`, `.page-wrapper{margin-left:...!important}` paksa) — sekaligus memperbaiki layout asesi/asesor yang bergeser 16rem karena aturan paksa itu; Tabler native `~ .page-wrapper{margin-inline-start}` yang menangani offset sidebar.
    - Font: link CSS hardcoded `assets/fonts-<hash>.css` diganti directive `@fonts` (bawaan Laravel 13 + `laravel-vite-plugin/fonts`) sehingga hash tidak lagi dikunci manual; `npm run build` ulang (app-`B9bSszS0`.css).
    - Test: `SidebarMenuTest` diperbarui ke markup dropdown + test baru `test_sidebar_follows_tabler_vertical_layout_structure`; **68/68 feature tests lulus**.
  - **Ketahuan basi (belum dikerjakan)**: test Dusk `NavigasiAdminTest` & `AlurFase4Dan5Test` memakai selector menu lama (`a.dropdown-toggle[href="#sidebar-standar"]`, `#sidebar-komunikasi`, `#sidebar-lsp`, teks item yang tidak ada, variabel `$skema` belum didefinisikan) — perlu penulisan ulang; perlu penulisan ulang; shim `vendor/bin/dusk` juga tidak ada (paket `laravel/dusk` ada di vendor), sehingga suite Dusk tidak bisa dijalankan saat ini.

- **31 Oktober 2026 — Sesi 5**
  - **Perbaikan bug route Rekening Bank (RouteNotFoundException)**: resource route `rekening-bank` menghasilkan nama `admin.rekening-bank.*`, sedangkan view `admin/rekening/index.blade.php`, `RekeningBankController`, dan `PembayaranTest` memakai `admin.rekening.*`. Route diberi `->names('rekening')` (URI tetap `/admin/rekening-bank`) dan `->except(['show', 'create', 'edit'])` karena create/edit tidak dipakai (form via modal).
  - **Route cache basi**: `bootstrap/cache/routes-v7.php` masih menyimpan nama lama dan action `AdminStkkniController` tanpa namespace; `php artisan route:clear` menghapus cache dan `route:list` kembali normal.
  - **Testing**: `PembayaranTest` 5/5 lulus (18 assertion pada `test_admin_can_manage_rekening_bank`).
  - **Kendala**: Dusk `AlurFase4Dan5Test` masih menargetkan menu `#sidebar-lsp` / tautan "Rekening Bank LSP" yang belum ada pada layout admin hasil revisi terbaru.

- **31 Oktober 2026 — Sesi 6 (Branch: `single`)**
  - **Branch `single` dibuat** dari branch utama sebagai varian 1 aplikasi = 1 lembaga untuk memudahkan pengujian dan deployment sederhana.
  - **Halaman login menampilkan nama LSP**: `LoginController::create()` sekarang mengambil institution aktif dari DB dan meneruskan ke view (`compact('institution')`), sehingga nama LSP tampil sebagai subtitle di bawah judul "Masuk ke Portal" — pola yang sama dengan halaman register.
  - **Dokumentasi branch `single`** ditambahkan ke bagian "Informasi Branch" di `PROGRESS_TRACKER.md`: tabel perbedaan arsitektur dari branch utama, daftar perubahan pending, dan batasan scope.
  - **Rencana pending** (belum dikerjakan di sesi ini): penyederhanaan `TenantContext` agar tidak bergantung session, sederhanakan `EnsureTenantContext` middleware, hapus institution switcher dari header dan route `tenant.switch`, hapus logika `accessibleInstitutions()` dari alur login.

- **1 November 2026 — Sesi 7 (Branch: `single`)**
  - **Bug fix: tombol "Finalkan" FR.AK.02 / MAK-02 tidak menyimpan & error tak terlihat** (`resources/views/asesor/mak02/show.blade.php`).
    - Masalah: setelah submit form di dalam modal, validation error gagal ditampilkan karena (a) tidak ada `@error('rekomendasi')` di dalam modal, (b) error bisnis dari `finalkan()` (`hasil`, `kuks`) tidak memicu script buka-ulang modal, (c) tidak ada error alert umum di atas modal form. Akibatnya halaman redirect kembali, modal tertutup, dan user tak tahu penyebabnya.
    - Perbaikan: tambah `@error('rekomendasi')` di bawah select rekomendasi; tambah error alert `@error` untuk `rekomendasi`/`umpan_balik`/`konfirmasi`/`hasil`/`kuks` di atas isi modal; perpanjang kondisi script buka-ulang modal menjadi `['rekomendasi', 'umpan_balik', 'konfirmasi', 'hasil', 'kuks']`.
    - Test: tambah `test_asesor_finalisasi_mak02_gagal_validasi_field_wajib` di `tests/Feature/AsesmenTest.php`; seluruh `AsesmenTest` 5/5 lulus; `pint --dirty --format agent` bersih.

  - **Bug fix: tombol "Simpan Draf" FR.AK.02 / MAK-02 gagal diam-diam saat "Digunakan" dicentang** (`resources/views/asesor/mak02/show.blade.php`).
    - Masalah: setiap baris metode punya `<input type="hidden" name="...[digunakan]" value="0">` + checkbox dengan nama yang sama. Saat dicentang, browser kirim keduanya → PHP terima array `["0","1"]` → gagal validasi `boolean` → redirect tanpa simpan. Error nested (`units.*.metode.*.digunakan`) juga tidak pernah ditampilkan karena view hanya cek `@error('units')` top-level. Akibatnya halaman reload tanpa flash error dan data tersimpan — "terjebak di halaman ini".
    - Perbaikan: hapus 4 hidden input per baris metode (`digunakan`, `bukti_langsung`, `bukti_tidak_langsung`, `bukti_tambahan`). Unchecked → tidak terkirim → `nullable` → null → `simpanMak02` treats as false (benar). Tambah alert validasi di atas form yang tangkap nested error `units.*` / `kuks.*` beserta top-level `hasil`/`kuks`/`units`. Hapus blok error duplikat di bawah tombol.
    - Test: tambah `test_asesor_simpan_mak02_metode_tidak_digunakan` di `tests/Feature/AsesmenTest.php` (digunakan = '0'); seluruh `AsesmenTest` 6/6 lulus; `pint --dirty --format agent` bersih.

- **1 November 2026 — Sesi 8 (Branch: `single`)**
  - **FR.AK.02 / MAK-02 — optimasi UI + perbaikan "Simpan Draf tidak tersimpan"**:
    - **Akar masalah "Semua tercapai tidak tersimpan"**: `HasilAsesmenController@store` mewajibkan `units` (`required`). Asesor yang baru menilai per elemen (memilih "Semua tercapai") tidak bisa menyimpan draf sebelum unit keputusan diisi → submit selalu gagal validasi. `units` kini `nullable` di store; final tetap mewajibkan kelengkapan via validasi `finalkan()`.
    - **Error tak tampil**: view hanya cek wildcard `units.*` / `kuks.*` via `hasAny` → error nested (mis. `units.<uuid>.keputusan`) tidak pernah dirender. Ganti dengan `$errors->any()` + loop `$errors->all()`.
    - **UI disederhanakan**: accordion Bootstrap dihapus → semua unit tampil langsung sebagai kartu terbuka (lebih sederhana, tidak ada field di collapsed yang "hilang").
    - **Alert sukses**: ditambahkan banner `session('success')` di atas form (user tidak pernah lihat konfirmasi "Draf berhasil disimpan").
    - **Dead code**: hapus JS toggle metode per-unit (sudah simplified ke default-only).
  - **Testing**: `AsesmenTest` 7/7 lulus (tambah `test_asesor_simpan_draf_mak02_dan_tanpa_unit_keputusan`); full Feature suite **89/89 lulus**; `pint --dirty` bersih.
  - **Commit**: `da0932c` fix: MAK-02 simpan draf + UI sederhana.

- **1 November 2026 — Sesi 9 (Branch: `single`)**
  - **FR.AK.02 / MAK-02 — "Terpilih tercapai" hilang setelah refresh**: investigasi + fix.
    - Store redirect sekarang pakai `->withInput()` (plain `->with()` tidak flash old input).
    - Perbaikan sebelumnya (Sesi 8): `units` nullable di store; error display generic; accordion dihapus (semua unit tampil langsung); alert sukses; dead JS metode dihapus.
    - Test baru `test_asesor_mak02_refresh_shows_tercapai_terpilih`: setelah store + GET, radio elemen "Semua tercapai" (value="1" + checked) harus muncul → **refresh state terkonfirmasi**.
  - **Testing**: `AsesmenTest` 8/8; full Feature suite **90/90** lulus; `pint --dirty` bersih.
  - **Commit**: `a3f3baf` fix: refresh MAK-02 simpan draf + UI sederhana.

- **1 November 2026 — Sesi 10 (Branch: `single`)**
  - **FK violation APL-02 verifikasi fixed** (`f934074`): form key `verifikasi[{kukId}]` sesuai backend. View pakai `$elemenJawaban->kuk_id` (first KUK per elemen).
  - **Modal finalisasi rapih** (`b547336`): pindah dari `@section('content')` → `@push('modals')` di luar `.page`. Layout asesor tambah `@stack('modals')`. Blok `@php` ($keputusanLabels, $rekomendasiSaran) pindah ke atas file. Modal render hanya jika `! $sudahFinal`.
  - **Modal scroll vertikal + size lg** (`7ec7e66`): form flex container, tabel ringkas (max-height 260px, sticky header), Str::limit 40 char.
  - **Duplikat umpan balik hapus** (`2969b06`): hanya di modal finalisasi, form utama dihapus.
  - **Notifikasi ganda hapus** (`fbbfdce`): banner alert final dihapus, hanya session alert.
  - **Testing**: 9/9 APL-02 lulus, full Feature 91/91 hijau.
  - **Commits**: `f934074`, `f3d7b61`, `a937e29`, `b547336`, `92f03db`, `7ec7e66`, `2969b06`, `fbbfdce`.

- **1 November 2026 — Sesi 10 Awal (Branch: `single`)**
  - **APL-02 asesi restore approval & bukti dari DB** (`63d2b5d`):
    - Bug: radio `elemen_approval` tanpa `@checked` — selalu kosong saat load. Bukti pakai `->where('elemen_id', ...)` (field tidak ada, hanya `kuk_id`) — null.
    - Fix: key per kuk_id pertama elemen, restore via `old()` → DB `jawaban_asesi`.
  - **MAK-02 tombol Semua Tercapai** (`a5064ee`, `1a5797e`):
    - Tombol di bawah form (dekat Simpan Draf) — centang semua radio `elemenTercapai[id] = "1"` (hanya centang, tidak submit).
  - **MAK-02 Eloquent Collection `only()` bug** (`7bef2fd`):
    - Root cause: `$hasil->kuks()->get()->keyBy('kuk_id')` = Eloquent Collection. Method `only()` di-override filter by **primary key `id` baris**, bukan array key `kuk_id` → `$hasilKuks->only($kukIds)` = 0 → semua elemen tampil "belum dinilai" padahal data ada.
    - Bug nyata di DB real (tinker: `$hasilKuks->only(...)` = 0, `array_intersect` = 4).
    - Fix: wrap jadi base Collection: `collect($hasil->kuks()->get()->keyBy('kuk_id')->all())`.
    - Regression test + helper `extractInputTag()` menangkap radio "Semua tercapai" ter-check.
  - **Testing**: full Feature **91/91** lulus, `pint` bersih.
  - **Commits akhir sesi 10**: `63d2b5d`, `a5064ee`, `1a5797e`, `7bef2fd`.
  - **Total sesi 10**: 8 commits, 3 bug fix utama (FK violation APL-02, APL-02 asesi restore, MAK-02 `only()` bug).

- **3 November 2026 — Sesi 11 (Branch: `feature/redesain-pdf-frak02`)**
  - **Commit `0be8c1a`**: logo lembaga (BNSP/LSP), tanda tangan asesi/asesor, penyematan tanda tangan di PDF, MAK-02 ringkas, migrasi enum `demonstrasi_simulasi`, `.gitignore` artefak PDF.
  - **Audit gap BNSP**: dibuat `docs/GAP_ANALYSIS_ASESMEN_BNSP.md` — peta 10 tahap alur, matriks 26 formulir, temuan struktural, rekomendasi P0/P1/P2, serta rencana kerja 9 fase (Fase 0–8, termasuk honor asesor per asesi) dan urutan rilis `v0.6.1`–`v1.0.0`. Keputusan honor sudah ditetapkan: tarif utama per skema, observer boleh 0, basis presensi hadir + MAK-02 final, batch per event, PPh di luar sistem, uji ulang sesi baru, banding honor peninjau terpisah.
  - **Hasil singkat**: 1 tahap siap, 3 sebagian, 6 belum; 2 formulir siap, 3 sebagian, 21 belum. Inti pelaksanaan uji (FR.IA) dan tata kelola pleno/banding belum ada.
  - **Temuan blocking**: test suite **91/92**; PDF APL-02 500 karena `$penilaian->asesor->user` tanpa null-safe (regresi `0be8c1a`). **Sudah diperbaiki** dengan null-safe di `pdf/apl02` dan `pdf/mak02-ringkas`.
  - **Utang dokumentasi**: `PROGRESS_TRACKER.md` sebelumnya basi (berhenti di 1 November 2026).
  - **Navigasi portal disatukan**: `layouts/portal.blade.php` + `x-portal-sidebar`; admin, asesor, dan asesi kini memakai sidebar vertikal Tabler yang sama. Menu per peran dipusatkan di `App\Support\PortalNavigation` (menggantikan array menu di dalam layout admin).
  - **Body layout**: admin tetap default (tanpa `layout-boxed`), asesor dan asesi memakai `bg-light` tanpa `layout-boxed`.
  - **Testing sesi 11 (akhir)**: full suite **99/99** lulus, 534 assertions; `pint` bersih; semua compiled view lolos `php -l`. Dusk `tests/Browser/NavigasiPortalSidebarTest.php` ditambahkan untuk asesor & asesi (belum dijalankan; shim Dusk belum tersedia).
  - **File navigasi**: `app/Support/PortalNavigation.php`, `resources/views/components/portal-sidebar.blade.php`, `resources/views/layouts/portal.blade.php`, `layouts/admin|asesor|asesi.blade.php`, `tests/Feature/SidebarMenuTest.php`.
  - **Fase 0 selesai (0.1–0.7)**: label formulir dikoreksi (`FR.IA.08` untuk MAK-01, Berita Acara Asesmen tanpa `FR.AK.04`, `FR.APL.01`/`FR.APL.02`), penandatangan MAK-01 diambil dari asesor jadwal, APL-01 memakai `adminLsp` eksplisit, dan `App\Support\PdfFile` + `x-pdf.signature` membuat kegagalan `storage:link` terlihat. File: `app/Support/PdfFile.php`, `resources/views/components/pdf/signature.blade.php`, `Admin\PendaftaranController`, `pdf/{mak01,mak03,apl01,apl02,mak02,mak02-ringkas}`, `admin/pendaftaran/show`, `tests/Feature/PdfFormulirTest.php`.
  - **Bug Blade diperbaiki**: `@php(...)` inline (5 file) dipasangkan compiler dengan `@endphp` berikutnya sehingga blok sesudahnya tidak dikompilasi; muncul ParseError `unexpected token "endif"` di `asesi/pendaftaran/show`. Semua inline diganti `@php ... @endphp`. File: `asesi/pendaftaran/{show,create}`, `asesor/jadwal/peserta`, `dashboard/asesi`, `admin/pendaftaran/show`, `asesi/panduan`.
  - **Catatan relasi**: `JadwalAsesmen::asesors()` adalah belongsToMany langsung ke `Asesor`, sehingga jalur yang benar `jadwal.asesors.user` (bukan `jadwal.asesors.asesor.user`).


- **4 November 2026 — Sesi 12**
  - Pengguna memperbarui acuan: `docs/Dokumentasi_Asesmen_Kompetensi_BNSP_v2.md` (alur swimlane, gerbang pre-asesmen) dan menyediakan template standar resmi di `docs/template form/MUK/`.
  - `docs/GAP_ANALYSIS_ASESMEN_BNSP.md` disesuaikan ke v2 + template MUK: acuan primer v2, tambah FR.AK.07 (penyesuaian wajar dan beralasan) ke matriks formulir, tahap 4 diperluas (AK.01/AK.04/AK.07), dan Fase 2 mendapat pekerjaan FR.AK.07 + gating pre-asesmen.
  - Belum ada perubahan kode; sinkronisasi formulir aplikasi ke template MUK (mulai FR.APL.01) menjadi pekerjaan berikutnya.


- **4 November 2026 — Sesi 13**
  - Implementasi gerbang pre-asesmen (FR.AK.01/FR.AK.04/FR.AK.07): migrasi `persetujuan_asesmens`, model `PersetujuanAsesmen`, controller asesi (lihat & setujui) dan asesor (lihat status), route `asesi.pre-asesmen.*` dan `asesor.pre-asesmen.*`.
  - Menu asesi menambah item “Dokumen Pre-Asesmen”; pendaftaran detail menautkan ke halaman pre-asesmen; peserta asesor menambah tombol pre-asesmen; MAK-02 menampilkan peringatan dan memblokir finalisasi bila belum lengkap.
  - Testing: suite tetap 100/100 lulus (penambahan persetujuan otomatis di setUp AsesmenTest).


- **4 November 2026 — Sesi 14**
  - Ganti judul menu asesi 'Status Pendaftaran' → 'Riwayat Uji Kompetensi' (sidebar + judul halaman).
  - Pre-asesmen asesi berubah menjadi formulir isian langsung dari template MUK (FR.AK.01, FR.AK.04, FR.AK.07 HTML) — asesi mengisi data, centang bukti, Ya/Tidak banding, alasan, dan tanda tangan, bukan sekedar ceklis umum.
  - Kolom `data_json` (JSON) ditambahkan ke tabel `persetujuan_asesmens` untuk menyimpan isian form.
  - Halaman admin `/admin/pendaftaran/{id}` menampilkan status FR.AK.01/04/07 (disetujui/belum) agar admin LSP tahu asesi sudah setujui sebelum plotting asesmen.
  - Testing: suite 101/101 lulus (termasuk SidebarMenuTest diperbarui).

- **4 November 2026 — Sesi 15**
  - Pre-asesmen asensi berubah menjadi modal read-only berbasis Tabler UI (tanpa input field): FR.AK.01, FR.AK.04, FR.AK.07 ditampilkan dalam modal masing-masing dengan isi default dari sistem/template MUK (skema, TUK, nama asesor/asesi, bukti, Ya/Tidak banding, alasan, ceklis penyesuaian, tanda tangan).
  - PDF generation FR.AK.01/04/07 dari `data_json` via route `asesor/pendaftaran/{id}/pre-asesmen/{jenis}/pdf` (view `pdf/frak01`, `frak04`, `frak07`).
  - Testing: suite 101/101 lulus, `pint` bersih.

- **4 November 2026 — Sesi 16**
  - Perbaikan tampilan modal pre-asesmen asensi: modal dipindahkan ke `@stack('modals')` (di luar `.container-xl`, bukan nested) agar backdrop/layar penuh berfungsi; badge status dipindah dari atribut `title` card (yang di-escaped Blade) ke `x-slot:actions`; tombol "Buka Formulir" membuka modal masing-masing (FR.AK.01/04/07).
  - Testing: suite 102/102 lulus, `pint` bersih.

- **4 November 2026 — Sesi 17**
  - Modal pre-asesmen ditambahi konfirmasi setuju: checkbox "Saya telah membaca dan menyetujui isi formulir ini" + tombol Setujui (hanya muncul bila belum disetujui); tanda tangan dihapus dari tampilan — approve dianggap setuju dan tanda tangan.
  - Controller approve sederhana: hanya membutuhkan `setuju` (accepted), lalu tandai `disetujui` dengan waktu, IP, user agent, dan hash dokumen.
  - Redirect setelah approve ke halaman show pre-asesmen (bukan `back()` agar stabil di uji).
  - Test baru `test_asesi_can_setujui_pre_asesmen`: setujui sukses + validasi gagal bila tidak centang setuju.
  - Testing: suite 103/103 lulus, `pint` bersih.

- **4 November 2026 — Sesi 18**
  - Modal FR.AK.07 (Penyesuaian Wajar dan Beralasan) jadi bisa diisi & disimpan: header (skema, nomor, TUK, nama asesor/asesi, tanggal), ceklis potensi asesi, tabel 8 baris (Ya/Tidak radio + opsi checkbox + keterangan teks), acuan/metode/instrumen, dan nama asesor/asesi — semakin disimpan ke kolom `data_json` saat disetujui.
  - Test baru `test_asesi_can_setujui_frak07_with_data_json`: verifikasi data_json tersimpan lengkap (acuan, metode, instrumen, potensi, p1_perlu, p1_opsi, p1_lainnya).
  - Testing: suite 104/104 lulus, `pint` bersih.

- **4 November 2026 — Sesi 19**
  - Rapihkan modal FR.AK.07: ganti tata letak tabel barisan menjadi kartu Tabler (`x-tabler.card`) per seksi (Data Formulir, Nama, Potensi Asesi, Penyesuaian yang Diperlukan, Hasil Penyesuaian yang Disepakati); radio Ya/Tidak pakai badge inline; opsi checkbox pakai badge border; input keterangan rapi di bawah opsi; tombol Setujui memvalidasi centang konfirmasi sebelum submit.
  - Testing: suite 104/104 lulus, `pint` bersih.

- **4 November 2026 — Sesi 20**
  - Gabungkan AK.01 + AK.07 dalam satu modal sesuai layout pengguna (identitas asesmen, potensi asesi, 8 baris penyesuaian, persetujuan asesmen, konfirmasi setuju + tanggal); AK.04 (Banding) tetap terpisah.
  - Controller approve disesuaikan: menerima `setuju_ak01`/`setuju_ak04`/`setuju_ak07`, menyimpan seluruh input form ke `data_json`, tandai disetujui dengan waktu/IP/user agent/hash. Jika tidak ada yang dicentang → error manual.
  - Test disesuaikan ke field baru (`setuju_ak01/04/07`).
  - Testing: suite 104/104 lulus, `pint` bersih.

- **4 November 2026 — Sesi 21**
  - FR.AK.04 (Banding) sekarang dapat diisi: input Nama Asesi, Nama Asesor, Tanggal Asesmen, 3 pertanyaan Ya/Tidak (radio), Skema Sertifikasi, No. Skema Sertifikasi, Alasan Banding (textarea), konfirmasi setuju + tanggal. Semua pakai `old()` fallback agar data muncul saat dibuka kembali.
  - AK.01/AK.07 modal ditambahkan `old()` fallback untuk potensi asesi dan semua input agar data tidak hilang saat dibuka kembali.
  - Testing: suite 104/104 lulus, `pint` bersih.

- **4 November 2026 — Sesi 22**
  - Gabungkan AK.07 + AK.01 + Informasi Banding dalam satu modal dengan judul kartu "Form Penyesuaian (AK.07), Persetujuan (AK.01) dan Informasi Mengenai Banding Asesmen".
  - Hapus form AK.04 terpisah — diganti dengan satu kartu informasi: "Informasi prosedur banding asesmen telah dijelaskan dan dibaca sebelum asesmen dimulai" (alert info).
  - Konfirmasi setuju mencakup 3 checkbox: AK.07, AK.01, dan Informasi Mengenai Banding Asesmen.
  - Testing: suite 104/104 lulus, `pint` bersih.

- **4 November 2026 — Sesi 23**
  - Perbaiki checkbox Potensi Asesi tidak muncul saat dibuka kembali: nilai dari form submit adalah string (mis. "1"), sedangkan loop menghasilkan integer ($i + 1). `in_array(..., true)` strict comparison gagal. Keduanya dikast ke string sebelum dibandingkan.
  - Testing: suite 104/104 lulus, `pint` bersih.

- **4 November 2026 — Sesi 24**
  - PDF cetak dokumen pre-asesmen gabungan (AK.07 + AK.01 + Informasi Banding): view `pdf/pre-asesmen-gabungan.blade.php`, route `asesi.pre-asesmen.pdf.gabungan` dan `asesor.pre-asesmen.pdf.gabungan`, controller `pdfGabungan` di kedua controller.
  - Tombol "Cetak PDF" ditambahkan ke kartu dokumen pre-asesmen di portal asesi.
  - `docs/GAP_ANALYSIS_ASESMEN_BNSP.md` diperbarui: tambah item PDF cetak pre-asesmen ke daftar P1.
  - Testing: suite 104/104 lulus, `pint` bersih.

- **4 November 2026 — Sesi 25**
  - Halaman admin `/admin/pendaftaran`: tambah filter skema, event, TUK, waktu (tanggal jadwal) di samping search yang ada. Pagination (`paginate(15)` + links) sudah ada, dipertahankan dengan `withQueryString` agar filter tidak hilang saat pindah halaman.
  - Dropdown filter diambil dari `Skema::aktif()`, `EventAsesmen`, `Tuk`.
  - Tombol Reset untuk menghapus semua filter.
  - Tab link mempertahankan nilai filter saat pindah tab.
  - Testing: suite 104/104 lulus, `pint` bersih.

- **4 November 2026 — Sesi 26 (Fase 1: MAPA.01 & MAPA.02)**
  - Komponen `x-date-range`: satu komponen Blade dengan dua input date (dari/sampai) dalam satu wrapper, dipakai di filter admin pendaftaran mengganti dua input terpisah.
  - Migrasi baru: `rencana_asesmens`, `rencana_asesmen_units`, `peta_instrumens` — semua dengan `institution_id` dan `BelongsToTenant`, unique constraints, indeks komposit.
  - Model baru: `RencanaAsesmen`, `RencanaAsesmenUnit`, `PetaInstrumen` — relasi ke Pendaftaran, Asesor, UnitKompetensi, KriteriaUnjukKerja.
  - Policy `RencanaAsesmenPolicy`: viewAny/view/create/update/finalize dengan RBAC (super-admin/admin/asesor), asesor hanya own data, final tidak bisa diubah.
  - Controller `Asesor\RencanaAsesmenController`: index, create, store, edit, update, finalize, pdf (MAPA.01), pdfMapa02 (MAPA.02). Workflow: buat rencana draf → pilih unit + peta instrumen per KUK → finalisasi (kunci).
  - Route `asesor.rencana-asesmen.*` di prefix `asesor`, menu sidebar asesor ditambahkan item "Rencana Asesmen (MAPA)".
  - View: index (daftar rencana), create (identitas rencana), edit (unit + peta instrumen tabel, finalisasi tombol).
  - PDF `pdf/mapa01.blade.php` dan `pdf/mapa02.blade.php`, ditampilkan sebagai tombol cetak setelah final.
  - Gerbang MAK-02: `HasilAsesmenController@final` menolak bila `PersetujuanAsesmen` belum lengkap ATAU `RencanaAsesmen` belum final.
  - `AppServiceProvider`: register `RencanaAsesmenPolicy`.
  - Test baru `tests/Feature/RencanaAsesmenTest.php`: create+finalize rencana, asesi tidak bisa akses, MAK-02 blocked bila rencana belum final, MAK-02 diizinkan bila rencana final.
  - Testing: suite 108/108 lulus, `pint` bersih.

- **4 November 2026 — Sesi 27 (Sinkronisasi data pre-asesmen)**
  - Perbaikan asesor `/asesor/pendaftaran/{id}/pre-asesmen`: menampilkan isi formulir lengkap dari `data_json` per jenis (AK.01: jenis bukti + 3 pernyataan; AK.04: 3 pertanyaan Ya/Tidak + skema + alasan; AK.07: potensi asesi + 8 baris Ya/Tidak + keterangan + acuan/metode/instrumen). Ditambahkan tombol Cetak PDF gabungan.
  - Perbaikan admin `/admin/pendaftaran/{id}`: card "Dokumen Pre-Asesmen" menampilkan detail isian formulir per jenis yang approved.
  - Hapus route PDF individual (`asesor.pre-asesmen.pdf`) karena sekarang pakai PDF gabungan (`pdf.pre-asesmen-gabungan`). Hapus tombol cetak per jenis di view asesor.
  - Test baru `test_asesor_can_see_asesi_persetujuan_data`: verifikasi data asesi muncul saat asesor buka halaman pre-asesmen.
  - Testing: suite 109/109 lulus, `pint` bersih.
  - Tambah route redirect dari `/pre-asesmen/{jenis}/pdf` (lama) ke `/pre-asesmen/pdf` (gabungan) agar bookmark lama tidak 404.

- **5 November 2026 — Sesi 28 (Perbaikan radio FR.AK.07 di PDF pre-asesmen)**
  - Akar masalah: di `resources/views/pdf/pre-asesmen-gabungan.blade.php`, ekspresi `$data['p'.$k.'_perlu'] ?? '' === 'ya'` salah presedensi operator. PHP mengevaluasi `??` lebih dulu daripada `===`, sehingga kondisi menjadi `$data[...] ?? false`; nilai `"ya"`/`"tidak"` sama-sama truthy → kedua radio tercentang.
  - Fix: bungkus operand kiri — `($data['p'.$k.'_perlu'] ?? '') === 'ya'` dan `=== 'tidak'`. Kini hanya satu pilihan yang tercentang; baris tanpa data tidak tercentang.
  - Revert eksperimen KUK-UUID pada form asesi (`p{UUID}_perlu`): FR.AK.07 memakai 8 baris karakteristik tetap (1–8) sesuai formulir BNSP, bukan per KUK. Form asesi kembali memakai kunci `p1_perlu`…`p8_perlu`, konsisten dengan view asesor, admin, PDF, dan test.
  - Hapus eager-load `skema.skemaUnits...` yang tidak lagi dipakai di `Asesi\PersetujuanAsesmenController@show`.
  - Hapus kolom `peta_instrumens.perlu` (migration `2026_11_04_092809` di-rollback lalu file dihapus) dan buang penyimpanan `perlu` dari `Asesor\RencanaAsesmenController@update` — data penyesuaian hidup di `persetujuan_asesmens.data_json`, bukan di peta instrumen.
  - Rapikan `resources/views/pdf/frak07.blade.php` (dead code per-form) agar juga memakai baris 1–8 dan kolom Ya/Tidak terpisah; tidak lagi mereferensikan `$rencana` yang tak pernah dikirim.
  - Regression test: `test_asesor_can_see_asesi_persetujuan_data` kini me-render view PDF dan memastikan tidak ada baris yang mencentang Ya **dan** Tidak sekaligus, baris 1 hanya `ya`, baris 2 tanpa centang.
  - Testing: suite 109/109 lulus (613 assertions), `pint` bersih.

- **5 November 2026 — Sesi 29 (Presensi digital + transisi status asesmen)**
  - Migrasi baru `presensi_asesis`: `institution_id`, `pendaftaran_id` (unique), `jadwal_id`, status `hadir|izin|tidak_hadir`, catatan, `dicatat_oleh`, `dicatat_pada`, `terkunci_pada`; model `PresensiAsesi` dengan `BelongsToTenant`.
  - `Pendaftaran::presensi()` (hasOne) ditambahkan.
  - `PresensiService`: `catat()` (upsert + audit log), `mulaiAsesmen()` (transisi `terjadwal` → `asesmen_berjalan`, wajib FR.AK.01/04/07 disetujui), `kunci()` (isi `terkunci_pada`).
  - `AsesmenService::finalkan()` kini mengunci seluruh presensi pendaftaran saat status menjadi `selesai`.
  - Controller `Asesor\PresensiController` (`catat`, `mulai`) + route `asesor.presensi.catat` dan `asesor.pendaftaran.mulai-asesmen`; method admin `catatPresensi` + route `admin.pendaftaran.presensi.catat` untuk koreksi.
  - Policy `PendaftaranPolicy`: `catatPresensi` dan `mulaiAsesmen` (asesor pemilik jadwal atau admin).
  - UI asesor peserta: kolom Presensi (badge + select simpan, terkunci saat selesai) dan tombol Mulai Asesmen (nonaktif bila pre-asesmen belum lengkap). UI admin: card "Presensi Asesi" dengan status, pencatat, catatan, dan form koreksi.
  - Test baru `tests/Feature/PresensiAsesmenTest.php` (10 test, 35 assertions): pencatatan asesor, 403 asesor lain, gating mulai asesmen, transisi status, presensi terkunci, koreksi admin, kunci via service, render halaman asesor/admin.
  - Bonus: `routes/web.php` tidak mengimpor `WilayahController`, sehingga `php artisan route:list` gagal (`ReflectionClass("WilayahController")`). Import ditambahkan; endpoint publik `/wilayah/provinsi/{code}/kota` kini terdaftar benar.
  - Testing: suite 119/119 lulus (648 assertions), `pint` bersih. Dusk belum dijalankan (shim `vendor/bin/dusk` dan `phpunit.dusk.xml` tidak tersedia).

- **5 November 2026 — Sesi 30 (Instrumen inti FR.IA.01 & FR.IA.02)**
  - Migrasi `instrumen_asesmens` (header per pendaftaran + jenis, unik `pendaftaran_id`+`jenis`, status `draf|final`, `finalized_at`, `finalized_by`) dan `instrumen_asesmen_items` (satu baris per KUK, unik `instrumen_asesmen_id`+`kuk_id`, `unit_kompetensi_id`, `urutan`, `hasil` `tercapai|belum_tercapai`, `catatan`).
  - Model `InstrumenAsesmen` (`JENIS_IA01`, `JENIS_IA02`, `JENIS_LABELS`, relasi `items`/`finalizer`, `isFinal()`, `label()`) dan `InstrumenAsesmenItem` (`HASIL_LABELS`, relasi `kuk`/`unitKompetensi`, `isTercapai()`); keduanya memakai `BelongsToTenant`.
  - `InstrumenAsesmenService`: `ensure()` membuat header + item dari KUK skema (idempoten via `firstOrCreate`), `simpan()` per item, `finalkan()` menolak bila masih ada item belum dinilai, `rekap()` menghitung total/tercapai/belum tercapai/belum dinilai.
  - `Asesor\InstrumenAsesmenController` (`show`, `store`, `final`, `pdf`) + route `asesor.instrumen.show/store/final/pdf`; policy `PendaftaranPolicy@inputInstrumen`.
  - Gating: isi/final hanya saat status `asesmen_berjalan`; instrumen `final` mengunci seluruh input. Jenis di luar `ia01|ia02` → 404.
  - UI `asesor/instrumen/show.blade.php`: dikelompokkan per unit, radio Tercapai/Belum + catatan per KUK, catatan umum, rekap, tombol final; tombol akses IA.01/IA.02 ditambahkan di halaman peserta asesor.
  - PDF `pdf/instrumen-asesmen.blade.php`: header skema/asesi/asesor/jadwal/TUK, tabel per unit dengan kolom Tercapai/Belum, rekap, tanda tangan.
  - Test baru `tests/Feature/InstrumenAsesmenTest.php` (9 test, 38 assertions): generate item per KUK, simpan hasil, final ditolak bila belum lengkap, final mengunci edit, gating status, 403 asesor lain, 404 jenis tak dikenal, render PDF, dan rekap instrumen tampil di FR.AK.02.
  - FR.AK.02 (`asesor/mak02/show`) kini menampilkan card "Sumber Bukti Instrumen" berisi status draf/final, rekap tercapai/belum/belum dinilai, tautan buka + cetak per instrumen (read-only; keputusan tetap manual).
  - Testing: suite 128/128 lulus (686 assertions), `pint` bersih.

- **5 November 2026 — Sesi 32 (Calendar view jadwal asesor)**
  - Tambah tampilan kalender bulanan untuk `/asesor/jadwal`: route `asesor.jadwal.calendar` / `asesor.jadwal.kalender`, method `JadwalController::calendar`, view `asesor/jadwal/calendar.blade.php`.
  - Navigasi bulan sebelum/sesudah, tautan balik ke daftar, dan tombol "Kalender" di tampilan daftar.
  - Index juga mendukung `?view=calendar&month=YYYY-MM`.
  - Test baru `tests/Feature/AsesorJadwalCalendarTest.php` (3 test) lulus.

- **5 November 2026 — Sesi 31 (FR.AK.03 umpan balik terstruktur)**
  - Migrasi `umpan_balik_asesmens`: `institution_id`, `pendaftaran_id` (unique), `komponen_json`, `catatan`, `disubmit_pada`, `disubmit_oleh`; model `UmpanBalikAsesmen` dengan `KOMPONEN` (10 butir template MUK), `JAWABAN_LABELS`, `isSubmitted()`, `jawaban()`.
  - `Pendaftaran::umpanBalik()` (hasOne).
  - `UmpanBalikService::submit()`: hanya saat status `selesai`, wajib 10 komponen `ya|tidak`, sekali kirim (setelah itu terkunci), audit log; `rekap()` menghitung Ya/Tidak.
  - `Asesi\UmpanBalikController` (`show`, `store`, `pdf`) + route `asesi.umpan-balik.show/store/pdf`; PDF juga tersedia untuk asesor (`asesor.umpan-balik.pdf`) dan admin (`admin.pendaftaran.umpan-balik.pdf`) lewat policy `view`.
  - UI asesi `asesi/umpan-balik/show`: radio Ya/Tidak per komponen, catatan umum, rekap setelah kirim, tombol PDF; form terkunci sebelum keputusan dan setelah dikirim.
  - Integrasi: tombol "Isi Umpan Balik" di halaman pendaftaran asesi saat `selesai`; card status FR.AK.03 di FR.AK.02 asesor dan detail pendaftaran admin.
  - PDF `pdf/ak03.blade.php`: header skema/asesi/asesor/TUK/tanggal, tabel komponen dengan centang, rekap, catatan, tanda tangan.
  - Boolean `umpan_balik_diberikan` pada `hasil_asesmens` dipertahankan sebagai penanda finalisasi MAK-02; detail kini ada di FR.AK.03.
  - Test baru `tests/Feature/UmpanBalikAsesmenTest.php` (12 test, 45 assertions): kirim setelah keputusan, blokir sebelum keputusan, validasi 10 komponen, tidak bisa kirim dua kali, 403 asesi lain, form terkunci, tautan halaman pendaftaran, PDF asesor/admin, 404 sebelum kirim, card di MAK-02 dan admin.
  - Testing: suite 140/140 lulus (731 assertions), `pint` bersih.

- **5 November 2026 — Sesi 32 (FR.AK.05 laporan asesmen)**
  - Migrasi `laporan_asesmens`: `institution_id`, `jadwal_id` (unique), `asesor_id`, `aspek_positif`, `aspek_negatif`, `pencatatan_penolakan`, `saran_perbaikan`, `catatan`, status `draf|final`, `finalized_at`, `finalized_by`; model `LaporanAsesmen`.
  - `LaporanAsesmenService`: `rekapAsesi()` menurunkan baris K/BK dari FR.AK.02 final + daftar unit BK; `penghambat()` mendeteksi pendaftaran yang belum `selesai` atau hasil belum final; `simpan()` menolak laporan final; `finalkan()` memblokir bila masih ada penghambat, mencatat audit log.
  - `Asesor\LaporanAsesmenController` (`show`, `store`, `final`, `pdf`) + route `asesor.laporan-asesmen.show/store/final/pdf`; PDF admin via `admin.jadwal.laporan-asesmen.pdf`.
  - UI `asesor/laporan-asesmen/show`: info jadwal, rekap asesi (nama, K, BK, unit BK), lima field narasi, tombol simpan/final, peringatan penghambat; tombol "Laporan FR.AK.05" di halaman peserta.
  - PDF `pdf/ak05.blade.php`: rekap K/BK, keterangan unit, aspek positif/negatif, penolakan, saran, catatan, status draf/final, tanda tangan.
  - Test baru `tests/Feature/LaporanAsesmenTest.php` (9 test, 31 assertions): buka laporan, 403 asesor lain, simpan narasi, blokir final bila belum selesai, final mengunci, 404 sebelum dibuat, PDF asesor/admin, tautan halaman peserta.
  - Testing: suite 149/149 lulus (762 assertions), `pint` bersih.

- **5 November 2026 — Sesi 33 (FR.AK.06 tinjauan proses asesmen)**
  - Migrasi `tinjauan_proses_asesmens`: `institution_id`, `jadwal_id` (unique), `asesor_id`, `proses_json`, `rekomendasi_proses`, `dimensi_json`, `rekomendasi_dimensi`, `komentar`, status `draf|final`, `finalized_at/by`; model `TinjauanProsesAsesmen` dengan konstanta `PROSES` (5), `PRINSIP` (4), `DIMENSI` (5).
  - `TinjauanProsesService`: `simpan()` membersihkan matriks sesuai kunci yang dikenal, `finalkan()` memakai `LaporanAsesmenService::penghambat()` (semua pendaftaran harus selesai + hasil final), audit log.
  - `Asesor\TinjauanProsesController` (`show`, `store`, `final`, `pdf`) + route `asesor.tinjauan-proses.show/store/final/pdf`; PDF admin `admin.jadwal.tinjauan-proses.pdf`.
  - UI `asesor/tinjauan-proses/show`: matriks 5 prosedur × 4 prinsip (input teks bukti/instrumen), matriks 5 dimensi kompetensi, rekomendasi + komentar, tombol simpan/final, peringatan penghambat.
  - PDF `pdf/ak06.blade.php` (landscape): dua matriks, rekomendasi, komentar, status draf/final, tanda tangan.
  - Tombol "Tinjauan FR.AK.06" ditambahkan di halaman peserta asesor.
  - Test baru `tests/Feature/TinjauanProsesAsesmenTest.php` (9 test, 34 assertions): buka halaman, 403 asesor lain, simpan matriks, blokir final, final mengunci, 404, PDF asesor/admin, tautan.
  - Testing: suite 158/158 lulus (796 assertions), `pint` bersih.

- **5 November 2026 — Sesi 34 (FR.AK.04 banding asesmen)**
  - Migrasi `bandings` (`institution_id`, `pendaftaran_id` unique, `pengaju_id`, `alasan`, status `diajukan|ditinjau|diterima|ditolak|selesai`, `keputusan`, `ditinjau_oleh`, `ditinjau_pada`) dan `banding_status_histories` (`banding_id`, `status`, `catatan`, `created_by`).
  - Model `Banding` (`STATUS_LABELS`, `isClosed()`, relasi `histories`/`pengaju`/`peninjau`) dan `BandingStatusHistory`.
  - `Pendaftaran::banding()` (hasOne).
  - `BandingService`: `ajukan()` (hanya saat `selesai`, blokir bila ada banding aktif, audit via history, notifikasi asesor/admin), `tinjau()` (validasi status, wajib keputusan untuk diterima/ditolak, notifikasi asesi, efek reopen: pendaftaran→`asesmen_berjalan` dan hasil→`draf` bila diterima), `pdf()`.
  - `Asesi\BandingController` (`show`, `store`, `pdf`) + route `asesi.banding.show/store/pdf`; `Asesor\BandingController` (`show`, `review`, `pdf`) + route `asesor.banding.show/review/pdf`; PDF admin via `admin.pendaftaran.banding.pdf`.
  - UI asesi: form ajukan alasan, status + riwayat, PDF; tombol "Ajukan Banding" di halaman pendaftaran saat `selesai`.
  - UI asesor: detail banding, form tinjau (status + keputusan), riwayat, PDF; tombol "Banding" per peserta.
  - UI admin: card "Banding (FR.AK.04)" + PDF di detail pendaftaran.
  - PDF `pdf/ak04.blade.php`: header, alasan, keputusan, riwayat status, tanda tangan.
  - Test baru `tests/Feature/BandingAsesmenTest.php` (16 test, 54 assertions): ajukan setelah keputusan, blokir sebelum, alasan wajib, tidak bisa kirim dua kali, 403 asesi lain, tautan, tinjau, keputusan wajib, reopen, halaman asesor, PDF asesi/asesor/admin, 404, card admin, tautan peserta.
  - Testing: suite 174/174 lulus (850 assertions), `pint` bersih.

- **5 November 2026 — Sesi 35 (FR.IA.08 portofolio via modul instrumen)**
  - Tambah jenis `ia08` ke `InstrumenAsesmen::JENIS_LABELS` (FR.IA.08 — Ceklis Verifikasi Portofolio) dan migrasi `allow_ia08_in_instrumen_asesmens` untuk memperluas enum `jenis`.
  - Tombol akses FR.IA.08 ditambahkan di halaman peserta asesor; modul generik (item per KUK, hasil, final, PDF) langsung berlaku.
  - Test `InstrumenAsesmenTest::test_ia08_instrument_is_available` (buka + PDF).
  - Testing: suite 175/175 lulus (855 assertions), `pint` bersih.

- **5 November 2026 — Sesi 36 (FR.VA validasi asesmen + gating sertifikat)**
  - Migrasi `validasi_asesmens`: `institution_id`, `pendaftaran_id` (unique), `validator_id`, `aspek_validitas`, `aspek_reliabilitas`, `aspek_keadilan`, `aspek_kesesuaian`, `keputusan` (`disetujui|revisi|ditolak`), `catatan`, status `draf|final`, `finalized_at/by`; model `ValidasiAsesmen` (`isApproved()`).
  - `Pendaftaran::validasiAsesmen()` (hasOne).
  - `ValidasiAsesmenService`: `simpan()`, `finalkan()` (wajib keputusan), audit log.
  - `Admin\ValidasiAsesmenController` (`show`, `store`, `final`, `pdf`) + route group `validasi.*` (role komite-teknis/admin/super-admin/manajer-sertifikasi); PDF admin.
  - UI `admin/validasi-asesmen/show`: 4 aspek, keputusan, catatan, simpan/final; card status di detail pendaftaran admin.
  - PDF `pdf/va.blade.php`.
  - Gating: `SertifikatController@store` dan `BundleSertifikatController@store` menolak penerbitan bila validasi belum disetujui (final). `SertifikatBundleTest` diperbarui: helper membuat validasi disetujui.
  - Role `komite-teknis` dan `manajer-sertifikasi` ditambahkan ke `RolePermissionSeeder`.
  - Test baru `tests/Feature/ValidasiAsesmenTest.php` (7 test, 22 assertions): buka halaman, simpan+final, final tanpa keputusan, 404, PDF, blokir sertifikat tanpa validasi, izin dengan validasi disetujui.
  - Testing: suite 182/182 lulus (877 assertions), `pint` bersih.

- **5 November 2026 — Sesi 37 (Pleno + berita acara asesmen)**
  - Migrasi `plenos` (`institution_id`, `jadwal_id` unique, `nomor_ba`, `tanggal`, `keputusan` `disetujui|ditolak`, `catatan`, status `draf|final`, `finalized_at/by`) dan `pleno_pesertas` (`pleno_id`, `pendaftaran_id`, `keputusan` `kompeten|belum_kompeten`, `catatan`).
  - Model `Pleno` (`isFinal()`, `isApproved()`, relasi `pesertas`/`jadwal`/`finalizer`) dan `PlenoPeserta`; `JadwalAsesmen::pleno()` (hasOne).
  - `PlenoService`: `ensure()` (buat pleno draf + peserta dari pendaftaran selesai dengan keputusan awal dari FR.AK.02), `simpan()`, `finalkan()` (wajib keputusan pleno + keputusan per peserta).
  - `Admin\PlenoController` (`show`, `store`, `final`, `pdf`) + route group `pleno.*` (role komite-teknis/admin/super-admin/manajer-sertifikasi).
  - UI `admin/pleno/show`: data BA, peserta dengan radio K/BK + catatan, simpan/final, peringatan penghambat.
  - PDF `pdf/ba-pleno.blade.php`: BA pleno, tabel peserta, catatan, tanda tangan.
  - Gating penerbitan sertifikat kini juga mensyaratkan pleno disetujui (final + keputusan disetujui).
  - Test baru `tests/Feature/PlenoAsesmenTest.php` (5 test, 18 assertions).
  - Testing: suite 190/190 lulus (905 assertions), `pint` bersih.

- **5 November 2026 — Sesi 38 (QR sertifikat + perbaikan export laporan)**
  - Tambah dependensi `chillerlan/php-qrcode` (^6.0) dan generate QR PNG (data URI) di `SertifikatController::pdf`, ditampilkan di `pdf/sertifikat.blade.php` (mengarah ke `sertifikat.verifikasi`).
  - Perbaiki `PendaftaranExport`: load `hasilAsesmen`; kolom "Status Kompetensi" kini diturunkan dari hasil asesmen final (Kompeten/Belum Kompeten) bila ada, bukan hanya `asesi.status_kompetensi` global — menghindari tampilan "kompeten" untuk pendaftaran yang belum dinilai.
  - Test baru `tests/Feature/QrSertifikatPdfTest.php` (1 test) memverifikasi generator QR menghasilkan data URI PNG.
  - Testing: suite 191/191 lulus (907 assertions), `pint` bersih.

- **5 November 2026 — Sesi 39 (FR.IA.03 PMO)**
  - Migrasi `ia03_asesmens` + `ia03_items` (pertanyaan, tanggapan, pencapaian, catatan, umpan balik, status, finalisasi).
  - `Pendaftaran::ia03Asesmen()` (hasOne).
  - `Ia03Service`: ensure, tambah/hapus pertanyaan, simpan (tanggapan + pencapaian Ya/Tidak + catatan + umpan balik), finalkan (minimal satu pertanyaan, semua dinilai).
  - `Asesor\Ia03Controller` (`show`, `tambah`, `hapus`, `store`, `final`, `pdf`) + route `asesor.ia03.*`; policy `inputInstrumen`.
  - UI `asesor/ia03/show`: tambah pertanyaan, tabel pertanyaan dengan input pencapaian/tanggapan/catatan, umpan balik, simpan/final; tombol di halaman peserta.
  - PDF `pdf/ia03.blade.php`.
  - Test baru `tests/Feature/Ia03AsesmenTest.php` (5 test, 15 assertions).
  - Testing: suite 196/196 lulus (922 assertions), `pint` bersih.

- **5 November 2026 — Sesi 40 (FR.IA.07 pertanyaan lisan)**
  - Migrasi `ia07_asesmens` + `ia07_items`; model `Ia07Asesmen`/`Ia07Item`; `Pendaftaran::ia07Asesmen()`.
  - `Ia07Service` (ensure, tambah/hapus, simpan, finalkan) + `Asesor\Ia07Controller` + route `asesor.ia07.*` + policy `inputInstrumen`.
  - UI `asesor/ia07/show` + PDF `pdf/ia07.blade.php`; tombol di halaman peserta.
  - Testing: suite 196/196 lulus (922 assertions), `pint` bersih.

- **5 November 2026 — Sesi 41 (FR.IA.11 reviu produk via modul instrumen)**
  - Tambah jenis `ia11` ke `InstrumenAsesmen::JENIS_LABELS` + migrasi `allow_ia11_in_instrumen_asesmens`.
  - Tombol akses FR.IA.11 ditambahkan di halaman peserta asesor.
  - Testing: suite 196/196 lulus (922 assertions), `pint` bersih.

- **5 November 2026 — Sesi 42 (FR.IA.09 wawancara via modul pertanyaan)**
  - Migrasi `ia09_asesmens` + `ia09_items`; model `Ia09Asesmen`/`Ia09Item`; `Pendaftaran::ia09Asesmen()`.
  - `Ia09Service` + `Asesor\Ia09Controller` + route `asesor.ia09.*` + policy `inputInstrumen`.
  - UI `asesor/ia09/show` + PDF `pdf/ia09.blade.php`; tombol di halaman peserta.
  - Testing: suite 196/196 lulus (922 assertions), `pint` bersih.

- **5 November 2026 — Sesi 43 (FR.IA.10 klarifikasi pihak ketiga)**
  - Migrasi `ia10_asesmens`; model `Ia10Asesmen`; `Pendaftaran::ia10Asesmen()`.
  - `Ia10Service` + `Asesor\Ia10Controller` + route `asesor.ia10.*` + policy `inputInstrumen`.
  - UI `asesor/ia10/show` + PDF `pdf/ia10.blade.php`; tombol di halaman peserta.
  - Testing: suite 196/196 lulus (922 assertions), `pint` bersih.

- **5 November 2026 — Sesi 44 (FR.APL.01 tujuan asesmen)**
  - Migrasi `add_tujuan_asesmen_to_pendaftarans` + `add_pkt_to_tujuan_asesmen`: kolom `tujuan_asesmen` (enum sertifikasi/PKT/RPL/penyetaraan/lainnya) dan `catatan_tujuan`.
  - `Pendaftaran::TUJUAN_LABELS`; fillable diperbarui.
  - Form create pendaftaran asesi kini meminta tujuan asesmen; store menyimpannya.
  - PDF `pdf/apl01` menampilkan tujuan asesmen; admin detail pendaftaran menampilkan tujuan.
  - Testing: suite 196/196 lulus (922 assertions), `pint` bersih.

- **5 November 2026 — Sesi 45 (FR.IA.04 proyek singkat / kegiatan terstruktur)**
  - Migrasi `ia04_asesmens` + `ia04_items`; model `Ia04Asesmen`/`Ia04Item`; `Pendaftaran::ia04Asesmen()`.
  - `Ia04Service` (ensure, tambah/hapus butir, simpan, finalkan) + `Asesor\Ia04Controller` + route `asesor.ia04.*`.
  - UI `asesor/ia04/show`: FR.IA.04.A (skenario, hal dipersiapkan, hasil presentasi, umpan balik) + FR.IA.04.B (butir pertanyaan per lingkup, tanggapan, pencapaian, rekomendasi).
  - PDF `pdf/ia04` dua halaman (A dan B); tombol di halaman peserta asesor.
  - Perbaikan bug kapitalisasi view `asesor.Ia07`/`Ia09` → `asesor.ia07`/`ia09` (akan gagal di Linux).
  - Testing: suite 202/202 lulus (941 assertions), `pint` bersih.

- **5 November 2026 — Sesi 46 (FR.IA.05 pilihan ganda)**
  - Migrasi `ia05_asesmens` + `ia05_soals`; model `Ia05Asesmen`/`Ia05Soal`; `Pendaftaran::ia05Asesmen()`.
  - `Ia05Service` (ensure, tambah/hapus soal, simpan, finalkan) + `Asesor\Ia05Controller` + route `asesor.ia05.*`.
  - UI `asesor/ia05/show`: bank soal, opsi A–D, kunci, jawaban asesi, skor benar otomatis, umpan balik.
  - PDF `pdf/ia05` tiga bagian: A soal, B kunci jawaban, C lembar jawaban.
  - Testing: suite 213/213 lulus (975 assertions), `pint` bersih.

- **5 November 2026 — Sesi 47 (FR.IA.06 esai)**
  - Migrasi `ia06_asesmens` + `ia06_soals`; model `Ia06Asesmen`/`Ia06Soal`; `Pendaftaran::ia06Asesmen()`.
  - `Ia06Service` (ensure, tambah/hapus soal, simpan, finalkan) + `Asesor\Ia06Controller` + route `asesor.ia06.*`.
  - UI `asesor/ia06/show`: pertanyaan esai, kunci/rambu jawaban, jawaban asesi, pencapaian Ya/Tidak, umpan balik.
  - PDF `pdf/ia06` tiga bagian: A soal, B kunci, C lembar jawaban.
  - Testing: suite 213/213 lulus (975 assertions), `pint` bersih.

- **5 November 2026 — Sesi 48 (CMS publik, sanitasi HTML, verifikasi sertifikat)**
  - `App\Support\HtmlSanitizer`: sanitasi DOM berbasis allowlist (tag berbahaya dibuang beserta isi, tag tak dikenal di-unwrap, atribut `on*`/URL berbahaya dihapus, `target=_blank` diberi `rel`).
  - `CmsContent::setIsiAttribute` memakai sanitizer; 8 unit test payload XSS di `tests/Unit/HtmlSanitizerTest.php`.
  - Landing publik `/beranda` dirender dari CMS: `slide_banner`, `welcome`, `layanan`, `portfolio`, `berita`, `hubungi_kami`, `google_maps`, `footer_menu`; anchor `skema`, `layanan`, `jadwal`, `asesor`, `tuk`, `portfolio`, `berita`, `verifikasi-sertifikat`, `daftar`, `kontak`.
  - Navbar/footer publik memakai nama lembaga + menu footer CMS dengan filter skema URL; tombol daftar ke `route('register')`.
  - Root `/` menampilkan landing untuk tamu (auth tetap ke dashboard); `ExampleTest` disesuaikan.
  - Verifikasi sertifikat publik: form `/verifikasi-sertifikat`, lookup berdasarkan `no_registrasi`/`no_seri_blangko`/`qr_token`, throttle 30/1 dan 10/1.
  - `tests/Feature/CmsPublicTest.php` (7 test): landing, seksi CMS, konten nonaktif, sanitasi XSS, buat konten admin, lookup, form verifikasi.
  - Testing: suite 228/228 lulus (1021 assertions), `pint` bersih.

- **5 November 2026 — Sesi 49 (patch keamanan dependensi)**
  - `composer audit` menemukan 7 advisory (4 paket Symfony `v8.1.10`).
  - Patch ke `v8.1.11`: `symfony/http-foundation`, `symfony/http-kernel`, `symfony/routing`, `symfony/yaml` via PHP 8.4 (composer bawaan memakai PHP 8.3).
  - `composer audit` bersih; `npm audit` 0 kerentanan.
  - `phpunit.dusk.xml` ditambahkan (stub Dusk) + `tests/Browser/CmsPublicTest.php` (landing CMS, form verifikasi).
  - Dusk dapat dijalankan via `php artisan dusk` selama `php artisan serve --port=8123` aktif; 2 test Dusk lulus (10 assertions).
  - Testing ulang: suite 228/228 lulus (1021 assertions).

- **6 November 2026 — Sesi 33 (Tanda tangan terenkripsi di database)**
  - Migration `2026_11_06_025432_add_signature_content_columns.php`: kolom `signature_data/mime/hash/uploaded_at` di `users`, `tanda_tangan_data/mime/hash/uploaded_at` di `institutions`, backfill tanda tangan legacy dari disk setelah validasi/re-enkripsi PNG, transaksi DB.
  - `App\Support\SignatureImage`: validasi gambar, re-encode PNG, hash SHA-256, generate data URI untuk DomPDF.
  - `SignatureController` dan `AdminInstitutionController` menyimpan tanda tangan terenkripsi di DB; controller menambahkan throttle dan activity log; tidak lagi menulis disk publik.
  - Semua PDF/komponen yang menampilkan tanda tangan (APL-01, APL-02, MAK-01, MAK-02, MAK-02 ringkas/redesign, MAK-03, sertifikat) memakai data dari DB, bukan path file publik. Sertifikat memakai logo LSP yang benar dan tanda tangan Ketua LSP/penerbit; QR disatukan.
  - UI baru: menu "Tanda Tangan" di portal admin dan "Tanda Tangan Ketua LSP" di Profil Lembaga (tab Logo).
  - `storage/app/public/signatures` dipindahkan ke `storage/app/private/signatures-legacy-20261106`; akses publik ditutup.
  - Test baru `SignatureDatabaseTest` (4 test) dan `SertifikatPdfTest` (1 test); suite **235/235 lulus (1051 assertions)**; `pint` bersih.

- **6 November 2026 — Sesi 34 (PDF APL-01, MAK-01, MAK-03 resmi BNSP)**
  - `pdf.apl01` dibuat ulang mengikuti FR.APL.01: data pribadi, pekerjaan, skema, tujuan asesmen, daftar unit kompetensi dari DB, tabel bukti dasar/administratif dari dokumen aktual, rekomendasi LSP, tanda tangan dari database.
  - `pdf.mak01` menjadi FR.AK.01 Persetujuan Asesmen dan Kerahasiaan sesuai template MUK; `frak01` menjadi include resmi yang sama.
  - `pdf.mak03` menjadi FR.AK.04 Banding Asesmen sesuai template MUK; `frak04` menjadi include resmi yang sama.
  - Controller memuat relasi unit/KUK/skkni dan dokumen persyaratan; admin LSP PDF memakai user yang menyetujui pendaftaran (fallback user yang mencetak).
  - Tombol admin berubah menjadi “Unduh FR.AK.01 (MAK-01)” dan “Unduh FR.AK.04 (MAK-03)”.
  - Style BNSP disatukan di `pdf/partials/official-styles.blade.php`.
  - Test `PdfFormulirTest` diperbarui; suite **235/235 lulus (1052 assertions)**; `pint` bersih.

- **5 November 2026 — Sesi 50 (optimasi UI/UX registrasi asesi & kebijakan privasi)**
  - `resources/views/auth/register.blade.php`: form dibagi menjadi 4 section (Akun, Data Pribadi, Alamat, Pendidikan) dengan judul jelas, grid 2 kolom, error per field, summary alert, serta tampilan lega (`container-md`, max ±720px, padding card ±32px desktop / ±16px mobile).
  - `resources/css/register.css`: style terisolasi, font 15px, label 14px, hint 12.5px, section-title 16px, gap field ±16px, jarak section ±32px, password strength satu progress bar, focus-visible/outlined controls, 44px tap targets, dark-mode compatible.
  - `resources/js/register.js`: NIK digit-only dengan counter `0/16`; HP Indonesia validasi (`08`, `+62`, `62`); toggle password; autocomplete; select provinsi/kota searchable ringan tanpa library; daftar kota diisi via endpoint yang sudah ada; reset kota saat provinsi berubah; alamat counter `/1000`; tanggal lahir max hari ini; konfirmasi password; double-submit disabled + spinner; auto-scroll ke field pertama salah.
  - Perbaikan bug kritis: pemilih opsi tidak memicu event `change` pada native select, sehingga daftar kota tidak termuat; sekarang event `change` dikirim, provinsi → kota bekerja.
  - `/kebijakan-privasi` (publik) tersedia; link consent membuka tab baru dengan `rel="noopener noreferrer"`.
  - Verifikasi: screenshot 360/768/1280 px, cek tanpa horizontal overflow; flow provinsi → kota berfungsi; validasi client + server tetap; Dusk `tests/Browser/RegisterPageTest.php` 2 test lulus; feature `RegisterPageTest.php` 2 test lulus; Dusk terkait tetap hijau; suite 230/230 lulus (1034 assertions); `npm run build`, `pint` bersih.
