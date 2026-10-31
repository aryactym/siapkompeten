# 📊 Progress Tracker - SiapKompeten

> Diperbarui setiap akhir sesi kerja. Sumber fase: `docs/System_Design_Aplikasi_LSPKU.md` bagian 14.
> Standar keamanan wajib: `docs/SECURITY_STANDARDS_OWASP.md` (OWASP Top 10:2025).

## Status Ringkasan

- **Fase Saat Ini**: Fase 0–5 selesai — berikutnya Fase 6 (Hasil & Laporan). 64/64 feature tests + 7/7 Dusk passing
- **Status Overall**: In Progress
- **Terakhir Diperbarui**: 31 Oktober 2026

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

- [ ] CMS konten frontpage (kategori template, sanitasi HTML) — FR-CMS-01/02
- [ ] Halaman publik (skema, asesor, berita, kontak, daftar, masuk) — FR-CMS-03/04
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
