# Analisis Gap Aplikasi SiapKompeten terhadap Alur Asesmen BNSP

> **Tanggal audit:** 3 November 2026
> **Branch:** `feature/redesain-pdf-frak02`
> **Commit terakhir saat audit:** `0be8c1a` — feat(settings): tambah logo lembaga dan tanda tangan
> **Acuan:** [`docs/Dokumentasi_Asesmen_Kompetensi_BNSP.md`](Dokumentasi_Asesmen_Kompetensi_BNSP.md)
> **Metode:** telaah statis kode (routes, controller, model, migrasi, view PDF, seeder) + eksekusi test suite. Tidak mencakup UAT manual, pengujian Dusk, maupun verifikasi deployment.

---

## 1. Ringkasan Eksekutif

Aplikasi sudah kuat pada **administrasi pendaftaran sampai keputusan asesmen** (tahap 1, 2, dan sebagian 6), tetapi **belum memenuhi inti pelaksanaan uji BNSP**: perencanaan asesmen (FR.MAPA), instrumen FR.IA, persetujuan/kerahasiaan (FR.AK.01), banding (FR.AK.04), laporan asesmen (FR.AK.05), tinjauan proses (FR.AK.06), validasi/pleno (FR.VA), dan pelaporan ke BNSP.

| Kategori | Jumlah | Keterangan |
|---|---|---|
| Tahap alur BNSP siap | 1 dari 10 | Asesmen mandiri (APL-02) |
| Tahap sebagian | 3 dari 10 | Pendaftaran, keputusan/rekaman, pelaporan & sertifikat |
| Tahap belum ada | 6 dari 10 | Perencanaan, persiapan hari uji, pelaksanaan uji, banding, peninjauan proses, validasi/pleno |
| Formulir siap | 2 dari 26 | FR.APL.02, FR.AK.02 |
| Formulir sebagian | 3 dari 26 | FR.APL.01, FR.IA.08 (salah label), FR.AK.03 |
| Formulir belum ada | 21 dari 26 | FR.MAPA.01/02, FR.IA.01–IA.11 (kecuali IA.08), FR.AK.01/04/05/06, FR.VA |

**Temuan blocking:** test suite **91/92 lulus**; satu test gagal karena PDF APL-02 mengembalikan HTTP 500 (regresi commit `0be8c1a`).

---

## 2. Kondisi Aplikasi Saat Ini

### 2.1 Fitur yang sudah berjalan

| Area | Status | Bukti utama |
|---|---|---|
| Master data: SKKNI, skema, unit kompetensi, elemen, KUK, persyaratan, TUK, wilayah | Siap | migrasi `2026_10_31_000005`–`000011`, CRUD admin |
| Penjadwalan, event, multi-asesor, validasi bentrok, surat tugas, presensi | Siap | `Admin\JadwalController`, `pdf/presensi`, `pdf/surat-tugas` |
| Registrasi asesi, profil, dokumen privat, verifikasi dokumen, impor Excel | Siap | `Auth\RegisterController`, `Admin\AsesiController`, `Admin\PendaftaranController` |
| Pembayaran: rekening, biaya, tagihan, bukti, validasi | Siap | `Admin\PembayaranController`, `Asesi\PembayaranController` |
| APL-02 asesi + verifikasi 4C asesor + PDF | Siap | `Asesi\Apl02Controller`, `Asesor\Apl02Controller`, `pdf/apl02` |
| FR.AK.02 (MAK-02) per KUK/unit/metode, finalisasi, status kompetensi | Siap | `Asesor\HasilAsesmenController`, `AsesmenService`, `pdf/mak02*` |
| Sertifikat: dua nomor BNSP, regex, token verifikasi publik | Sebagian | `Admin\SertifikatController`, `SertifikatVerifikasiController`, `pdf/sertifikat` |
| Dashboard, laporan internal, ekspor Excel | Siap | `DashboardController`, `Admin\PelaporanController` |
| CMS admin dan halaman publik | Sebagian | resource `admin/cms`, route `/beranda` |

### 2.2 Status pengujian

```
php artisan test --compact
92 tests, 91 passed, 488 assertions, 1 failed
```

Kegagalan: `Tests\Feature\AsesmenTest::test_apl02_and_mak02_can_be_printed_as_pdf`

```
ErrorException: Attempt to read property "user" on null
View: resources/views/pdf/apl02.blade.php
```

**Penyebab:** blok tanda tangan asesor memakai `$penilaian->asesor->user` tanpa null-safe. APL-02 yang belum diverifikasi asesor (atau tanpa asesor) mengalami 500 saat dicetak.

**Dampak:** asesi tidak bisa mengunduh APL-02 sebelum verifikasi; ini regresi dari commit `0be8c1a`.

---

## 3. Peta 10 Tahap Alur BNSP

| No | Tahap | Status | Keterangan |
|---|---|---|---|
| 1 | Pendaftaran (FR.APL.01 + lampiran) | Sebagian | FR.APL.01 hanya dicetak admin dari data profil. Tidak ada form APL-01 yang diisi asesi; tidak ada field tujuan asesmen (sertifikasi/RPL/penyetaraan) |
| 2 | Pra-asesmen: asesmen mandiri (FR.APL.02) | Siap | Per KUK, bukti teks, verifikasi 4C, PDF. Bukti belum berupa lampiran file |
| 3 | Pra-asesmen: perencanaan (FR.MAPA.01/02) | Belum | Tidak ada dokumen/UI rencana asesmen maupun peta instrumen |
| 4 | Persiapan hari uji (FR.AK.01) | Belum | Tidak ada form persetujuan & kerahasiaan |
| 5 | Pelaksanaan uji (FR.IA.01–IA.11) | Belum | Hanya teks pertanyaan di master KUK; tidak ada instance instrumen, jawaban, skor, bukti |
| 6 | Keputusan & rekaman (FR.AK.02, AK.03, AK.05) | Sebagian | AK.02 siap; AK.03 hanya boolean; AK.05 belum ada |
| 7 | Banding (FR.AK.04) | Belum | Tidak ada tabel, model, route, alur, status |
| 8 | Peninjauan proses (FR.AK.06) | Belum | Tidak ada |
| 9 | Validasi & pleno (FR.VA + Berita Acara Pleno) | Belum | Tidak ada role komite/manajer sertifikasi; sertifikat langsung diterbitkan admin |
| 10 | Pelaporan & sertifikat | Sebagian | Sertifikat ada; laporan ke BNSP dan surat pengantar belum ada; QR di PDF belum ada |

---

## 4. Matriks Formulir BNSP

| Kode | Nama | Status | Bukti / Catatan |
|---|---|---|---|
| FR.APL.01 | Permohonan Sertifikasi Kompetensi | Sebagian | `pdf/apl01` (cetak admin, data dari profil) |
| FR.APL.02 | Asesmen Mandiri | Siap | `pdf/apl02`, `Asesi\Apl02Controller`, `Asesor\Apl02Controller` |
| FR.MAPA.01 | Merencanakan Aktivitas dan Proses Asesmen | Belum | — |
| FR.MAPA.02 | Peta Instrumen Asesmen | Belum | — |
| FR.IA.01 | Ceklis Observasi | Belum | — |
| FR.IA.02 | Tugas Praktik Demonstrasi | Belum | — |
| FR.IA.03 | Pertanyaan Pendukung Observasi | Belum | — |
| FR.IA.04A | Penjelasan Proyek/Kegiatan Terstruktur | Belum | — |
| FR.IA.04B | Daftar Pertanyaan Lisan | Belum | — |
| FR.IA.05A/B/C | Pertanyaan Tertulis Pilihan Ganda | Belum | — |
| FR.IA.06A/B | Pertanyaan Tertulis Esai | Belum | — |
| FR.IA.07 | Pertanyaan Lisan | Belum | — |
| FR.IA.08 | Ceklis Verifikasi Portofolio | Sebagian | Isi ada di `pdf/mak01`, tetapi **salah label** menjadi FR.AK.01 |
| FR.IA.09 | Pertanyaan Wawancara | Belum | — |
| FR.IA.10 | Klarifikasi Bukti Pihak Ketiga | Belum | — |
| FR.IA.11 | Ceklis Tinjauan Instrumen | Belum | — |
| FR.AK.01 | Persetujuan Asesmen dan Kerahasiaan | Belum | Tidak ada form/alur persetujuan sebelum uji |
| FR.AK.02 | Rekaman Asesmen Kompetensi | Siap | `pdf/mak02`, `pdf/mak02-ringkas`, `pdf/mak02-redesign` |
| FR.AK.03 | Umpan Balik dan Catatan Asesmen | Sebagian | Hanya `umpan_balik_diberikan` (boolean), tanpa form/catatan |
| FR.AK.04 | Banding Asesmen | Belum | `pdf/mak03` berlabel FR.AK.04, tetapi isinya Berita Acara Asesmen |
| FR.AK.05 | Laporan Asesmen | Belum | — |
| FR.AK.06 | Meninjau Proses Asesmen | Belum | — |
| FR.VA | Formulir Validasi Asesmen | Belum | — |

---

## 5. Temuan Struktural

### 5.1 Kode formulir resmi salah label (risiko kepatuhan)

| File | Label sekarang | Seharusnya |
|---|---|---|
| `resources/views/pdf/mak01.blade.php` | FR.AK.01 — Checklist Verifikasi Portofolio | FR.IA.08 — Ceklis Verifikasi Portofolio |
| `resources/views/pdf/mak03.blade.php` | FR.AK.04 — Berita Acara Asesmen | Berita Acara Pleno (tanpa kode FR.AK.04); FR.AK.04 = Banding |
| `resources/views/pdf/apl01.blade.php` | APL-01 | FR.APL.01 |
| `resources/views/pdf/apl02.blade.php` | APL-02 | FR.APL.02 |

Dokumen tercetak dengan kode salah berpotensi ditolak dalam audit BNSP.

### 5.2 Inti pelaksanaan uji belum ada

Sistem menilai KUK langsung pada MAK-02. Belum ada pengumpulan bukti per instrumen (FR.IA.01–IA.11), padahal dokumen acuan menempatkan instrumen sebagai dasar keputusan. Ini gap terbesar terhadap alur BNSP dan terhadap prinsip asesmen berbasis bukti.

### 5.3 Tata kelola keputusan belum berlapis

Alur sekarang: asesor finalisasi MAK-02 → status asesi menjadi `kompeten` → admin menerbitkan sertifikat.

Belum ada:
- validasi perangkat/proses oleh komite teknis (FR.VA);
- berita acara pleno;
- peran manajer sertifikasi/pleno;
- gating penerbitan sertifikat setelah pleno.

Role yang tersedia: `super-admin`, `admin`, `asesor`, `asesi`, `pimpinan`. Tidak ada role komite teknis/manajer sertifikasi.

### 5.4 Status asesmen tidak lengkap

`Pendaftaran::STATUS_ASESMEN_BERJALAN` didefinisikan, tetapi tidak ada aksi/route untuk memulai atau menyelesaikan asesmen. Pendaftaran melompat dari `terjadwal` ke `selesai` saat finalisasi MAK-02.

### 5.5 Banding tidak ada

Tidak ada tabel, model, route, status, maupun UI banding. Dokumen acuan menempatkan banding sebagai jalur wajib ketika asesi tidak puas (FR.AK.04).

### 5.6 Sertifikat

Sudah ada:
- pemisahan `no_seri_blangko` dan `no_registrasi`;
- validasi regex BNSP `^[A-Z0-9\-\.]+\.[A-Z0-9\-\.]+\.\d{5,}\.\d{4}$`;
- token verifikasi publik (`/sertifikat/{token}`).

Belum ada:
- gambar QR di PDF (hanya teks URL);
- laporan/rekap ke BNSP dan surat pengantar blanko;
- notifikasi ke asesi saat sertifikat terbit;
- pembatalan/kedaluwarsa sertifikat dari UI.

### 5.7 Penandatangan PDF berpotensi salah orang

- `pdf/mak01.blade.php` menandatangani `auth()->user()` sebagai "Asesor/Verifikator". Jika yang mencetak admin, tanda tangan dan nama admin muncul sebagai asesor.
- `pdf/apl01.blade.php` juga memakai `auth()->user()` untuk blok "Admin LSP".
- Sebaiknya gunakan asesor yang ditugaskan pada jadwal (`jadwal.asesors`), bukan user yang sedang login.

**Status Fase 0:** selesai. MAK-01 memakai asesor jadwal (`jadwal.asesors.user`); APL-01 memakai variabel `adminLsp` eksplisit dari controller. Catatan: relasi `jadwal.asesors` langsung mengembalikan model `Asesor`, jadi jalurnya `->asesors->first()?->user`, bukan `->asesor->user`.

### 5.8 Ketahanan deployment

Semua pengecekan tanda tangan memakai `file_exists(public_path('storage/...'))`. Jika `php artisan storage:link` belum dijalankan di server, tanda tangan hilang diam-diam tanpa error. Perlu health check atau fallback eksplisit.

**Status Fase 0:** selesai. `App\Support\PdfFile` melempar `RuntimeException` dengan instruksi `php artisan storage:link` bila `public/storage` tidak ada; komponen `x-pdf.signature` dipakai seluruh formulir yang sudah dimigrasikan.

### 5.9 Dokumentasi progres basi

`PROGRESS_TRACKER.md` terakhir diperbarui 1 November 2026, sedangkan kode sudah memuat fitur 2–3 November 2026 (logo lembaga, tanda tangan, MAK-02 ringkas, sertifikat). Perlu pembaruan.

### 5.10 Cakupan test belum mengikuti fitur

- Tidak ada feature test untuk unggah logo lembaga dan tanda tangan.
- Tidak ada test untuk penerbitan/verifikasi sertifikat dan PDF-nya.
- Test Dusk belum dapat dijalankan (shim `vendor/bin/dusk` tidak tersedia) dan sebagian selector sudah basi.

---

## 6. Rekomendasi Berprioritas

### P0 — Perbaikan blocking (segera)

| # | Tindakan | Kriteria selesai |
|---|---|---|
| 1 | Null-safe tanda tangan asesor di `pdf/apl02.blade.php` | Test PDF APL-02 lulus; APL-02 belum diverifikasi tetap bisa dicetak |
| 2 | Koreksi label FR.AK.01 → FR.IA.08 pada `pdf/mak01`, dan label FR.AK.04 pada `pdf/mak03` | Tidak ada kode formulir yang bertentangan dengan katalog BNSP |
| 3 | Samakan penomoran judul APL-01/APL-02 menjadi FR.APL.01/FR.APL.02 | Judul PDF sesuai katalog |

### P1 — Fondasi alur asesmen

| # | Tindakan | Ketergantungan |
|---|---|---|
| 1 | Form FR.AK.01 persetujuan & kerahasiaan + tanda tangan + gating sebelum uji | Skema, jadwal, asesi |
| 2 | FR.MAPA.01 rencana asesmen dan FR.MAPA.02 peta instrumen per unit/KUK | Master KUK dan metode |
| 3 | Form FR.AK.03 umpan balik terstruktur | Hasil asesmen |
| 4 | FR.AK.05 laporan asesmen (turunan final MAK-02) | MAK-02 final |
| 5 | Modul banding FR.AK.04: tabel, model, status, route asesi/asesor/admin, PDF | Hasil asesmen final |
| 6 | Gambar QR pada PDF sertifikat | Token verifikasi (sudah ada) |

### P2 — Tata kelola dan pelaporan

| # | Tindakan |
|---|---|
| 1 | Role komite teknis/manajer sertifikasi + FR.VA + berita acara pleno + gating penerbitan sertifikat |
| 2 | FR.AK.06 tinjauan proses asesmen |
| 3 | Instrumen FR.IA.01–IA.11 bertahap, mulai IA.01 (observasi), IA.02 (praktik), IA.08 (portofolio) |
| 4 | Laporan/rekap ke BNSP + surat pengantar blanko |
| 5 | Transisi status `asesmen_berjalan` (mulai/selesai asesmen) |
| 6 | Feature test logo, tanda tangan, sertifikat, dan PDF; perbaikan suite Dusk |
| 7 | Pembaruan `PROGRESS_TRACKER.md` |

---

## 7. Bukti Pendukung

| Komponen | Lokasi |
|---|---|
| Routes utama | `routes/web.php` |
| Alur asesmen | `app/Services/AsesmenService.php`, `app/Http/Controllers/Asesor/HasilAsesmenController.php` |
| APL-02 | `app/Http/Controllers/Asesi/Apl02Controller.php`, `app/Http/Controllers/Asesor/Apl02Controller.php` |
| Sertifikat | `app/Http/Controllers/Admin/SertifikatController.php`, `app/Models/Sertifikat.php` |
| Template PDF | `resources/views/pdf/*.blade.php` |
| Role & permission | `database/seeders/RolePermissionSeeder.php` |
| Status pendaftaran | `app/Models/Pendaftaran.php` |
| Test | `tests/Feature/*`, `tests/Browser/*` |

---

## 8. Batasan Audit

- Tidak dilakukan pengujian manual end-to-end seluruh peran.
- Suite Dusk tidak dijalankan (shim `vendor/bin/dusk` tidak tersedia; sebagian selector basi).
- Tidak ada pengujian performa, keamanan dinamis, atau verifikasi konfigurasi produksi.
- Status "siap" berarti fitur dan route/PDF tersedia serta tercakup test, bukan jaminan kesesuaian penuh dengan perangkat BNSP versi LSP tertentu.

---

## 9. Rencana Kerja Bertahap

Urutan fase mengikuti ketergantungan alur BNSP: koreksi dulu, lalu perencanaan, persiapan, pelaksanaan, keputusan, banding, validasi/pleno, pelaporan. Setiap fase harus selesai dan dirilis sebelum fase berikutnya yang bergantung padanya dimulai.

### Ringkasan fase

| Fase | Nama | Perkiraan | Ketergantungan | Hasil utama |
|---|---|---|---|---|
| 0 | Stabilisasi & koreksi dokumen | 1–2 hari | — | Test hijau, label formulir benar |
| 1 | Perencanaan asesmen | 4–6 hari | Fase 0 | FR.MAPA.01, FR.MAPA.02 |
| 2 | Persiapan hari uji | 3–4 hari | Fase 1 | FR.AK.01, status asesmen berjalan |
| 3A | Instrumen inti | 1,5–2 minggu | Fase 2 | FR.IA.01, IA.02, IA.08 |
| 3B | Instrumen pendukung | 2–3 minggu | Fase 3A | FR.IA.03–IA.07, IA.09–IA.11 |
| 4 | Keputusan & umpan balik | 1–1,5 minggu | Fase 3A | FR.AK.03, AK.05, AK.06 |
| 5 | Banding | 1 minggu | Fase 4 | FR.AK.04 end-to-end |
| 6 | Validasi, pleno, sertifikat | 1,5–2 minggu | Fase 4, 5 | FR.VA, berita acara pleno, QR sertifikat |
| 7 | Pelaporan BNSP & hardening | 1–1,5 minggu | Fase 6 | Laporan BNSP, Dusk, audit keamanan |
| 8 | Honor asesor per asesi | 1–1,5 minggu | Fase 4 | Tarif, tagihan honor, pembayaran, slip |

Fase 5 dapat berjalan paralel dengan Fase 4. Fase 7 tidak boleh dimulai sebelum Fase 6 selesai karena menyangkut keputusan sertifikasi.

### Definisi selesai (berlaku semua fase)

- Migrasi + model + relasi + seeder bila perlu, dengan `institution_id` dan `BelongsToTenant`.
- Policy + `authorize()` di server; deny by default; akses lintas tenant diuji.
- Validasi request ketat; unggahan di disk privat; tidak ada PII di log.
- Route bernama, UI sesuai pola Tabler yang ada, audit log untuk aksi keputusan.
- PDF (bila ada formulir) memakai data asesor yang ditugaskan, bukan `auth()->user()`.
- Feature test baru + regression test; seluruh suite lulus.
- `pint` bersih, `PROGRESS_TRACKER.md` diperbarui, Dusk ditambah untuk alur utama.

---

### Fase 0 — Stabilisasi & koreksi dokumen (P0)

**Tujuan:** menghilangkan blocking dan kesalahan label sebelum menambah fitur.

**Status per 3 November 2026:** selesai — 0.1–0.7 tuntas.

| # | Pekerjaan | File utama | Status |
|---|---|---|---|
| 0.1 | Null-safe tanda tangan asesor APL-02 + regression test | `resources/views/pdf/apl02.blade.php`, `tests/Feature/AsesmenTest.php` | Selesai (termasuk `pdf/mak02` dan `pdf/mak02-ringkas`) |
| 0.2 | Ganti label `FR.AK.01` → `FR.IA.08` pada MAK-01 | `resources/views/pdf/mak01.blade.php` | Selesai (judul, `<h1>`, nama unduhan `FORM-IA-08`) |
| 0.3 | Ganti label `FR.AK.04` pada MAK-03 menjadi Berita Acara Asesmen (tanpa kode AK.04) | `resources/views/pdf/mak03.blade.php` | Selesai (nama unduhan `FORM-BERITA-ACARA`) |
| 0.4 | Seragamkan judul `FR.APL.01` / `FR.APL.02` | `pdf/apl01`, `pdf/apl02` | Selesai |
| 0.5 | Penandatangan MAK-01 dari asesor jadwal; APL-01 memakai petugas LSP eksplisit (bukan `auth()` di view) | `Admin\PendaftaranController`, `pdf/mak01`, `pdf/apl01` | Selesai |
| 0.6 | Cek/abort jelas bila `storage:link` belum ada | `App\Support\PdfFile`, `x-pdf.signature` | Selesai |
| 0.7 | Satukan navigasi portal (admin/asesor/asesi) ke satu sidebar | `App\Support\PortalNavigation`, `x-portal-sidebar`, `layouts/portal` | Selesai |

**Temuan tambahan yang diperbaiki:** `@php(...)` inline di Blade dipasangkan compiler dengan `@endphp` berikutnya sehingga seluruh blok sesudahnya tidak dikompilasi (ParseError `unexpected token "endif"` pada detail pendaftaran asesi). Semua pemakaian inline diganti bentuk `@php ... @endphp`.

**Gate:** seluruh suite lulus **99/99** (534 assertions); semua compiled view lolos `php -l`; tidak ada kode formulir yang bertentangan dengan katalog BNSP.

---

### Fase 1 — Perencanaan asesmen (FR.MAPA.01 & FR.MAPA.02)

**Tujuan:** asesor merencanakan asesmen sebelum hari uji, sesuai tahap 3.

| # | Pekerjaan |
|---|---|
| 1.1 | Tabel `rencana_asesmens`: pendaftaran, asesor, pendekatan, tujuan, ruang lingkup, tanggal, status draf/final |
| 1.2 | Tabel `rencana_asesmen_units` dan `peta_instrumens` (relasi KUK → jenis instrumen + metode + bukti) |
| 1.3 | UI asesor: susun rencana, pilih metode, petakan instrumen per KUK, finalisasi + kunci |
| 1.4 | PDF FR.MAPA.01 dan FR.MAPA.02 |
| 1.5 | Gating lunak: peringatan bila MAK-02 diisi tanpa rencana final |
| 1.6 | Test: isolasi tenant, otorisasi asesor pemilik jadwal, finalisasi mengunci rencana |

**Gate:** rencana dan peta instrumen tercetak untuk skema uji nyata; asesor lain tidak bisa mengubah.

---

### Fase 2 — Persiapan hari uji (FR.AK.01) & status asesmen

**Tujuan:** persetujuan dan kerahasiaan ditandatangani sebelum uji; status pendaftaran mencerminkan pelaksanaan.

| # | Pekerjaan |
|---|---|
| 2.1 | Tabel `persetujuan_asesmens`: pernyataan, asesi, asesor, waktu, IP, user agent, hash dokumen |
| 2.2 | Form + tanda tangan asesi/asesor, terkunci setelah ditandatangani |
| 2.3 | PDF FR.AK.01 |
| 2.4 | Transisi status `terjadwal` → `asesmen_berjalan` → `selesai`, dengan aksi eksplisit |
| 2.5 | Gating: MAK-02 final ditolak bila FR.AK.01 belum ditandatangani |
| 2.6 | Presensi digital asesi: hadir/tidak hadir/izin, dicatat asesor/admin, terkunci setelah asesmen selesai |
| 2.7 | Test: gating, transisi status, ketidakbisaan ubah setelah tanda tangan, presensi terkunci |

**Gate:** alur hari uji berjalan tanpa lompat status; AK.01 terarsip; presensi tersedia sebagai basis honor.

---

### Fase 3A — Instrumen inti (FR.IA.01, IA.02, IA.08)

**Tujuan:** bukti dikumpulkan per instrumen, bukan langsung per KUK.

| # | Pekerjaan |
|---|---|
| 3A.1 | Tabel `instrumen_asesmens` (definisi per skema/jenis), `instrumen_items`, `jawaban_instrumens`, `bukti_asesmens` |
| 3A.2 | Generator instrumen dari KUK (`pertanyaan_observasi/lisan/tertulis` yang sudah ada) |
| 3A.3 | UI asesor: isi ceklis observasi, tugas praktik, verifikasi portofolio; unggah bukti privat |
| 3A.4 | Skor/keputusan per item + rekap otomatis ke MAK-02 |
| 3A.5 | PDF FR.IA.01, FR.IA.02, FR.IA.08 |
| 3A.6 | Test: instrumen hanya untuk pendaftaran yang berhak, bukti tidak dapat diakses lintas tenant |

**Gate:** minimal satu skema nyata dapat diuji penuh dengan tiga instrumen ini; MAK-02 menampilkan sumber bukti.

---

### Fase 3B — Instrumen pendukung (FR.IA.03–IA.07, IA.09–IA.11)

| # | Pekerjaan |
|---|---|
| 3B.1 | FR.IA.03 & IA.07 (lisan), IA.09 (wawancara) |
| 3B.2 | FR.IA.05A/B/C (PG) & IA.06A/B (esai) dengan bank soal dan kunci |
| 3B.3 | FR.IA.04A/B (proyek/studi kasus) |
| 3B.4 | FR.IA.10 (klarifikasi pihak ketiga) |
| 3B.5 | FR.IA.11 (tinjauan instrumen) |
| 3B.6 | PDF tiap formulir + test |

**Gate:** seluruh instrumen pada katalog tersedia; metode di MAK-02 konsisten dengan instrumen yang dipakai.

---

### Fase 4 — Keputusan, umpan balik, laporan, tinjauan (FR.AK.03, AK.05, AK.06)

| # | Pekerjaan |
|---|---|
| 4.1 | Form umpan balik terstruktur (FR.AK.03) menggantikan boolean |
| 4.2 | FR.AK.05 laporan asesmen, diturunkan dari MAK-02 final + instrumen |
| 4.3 | FR.AK.06 tinjauan proses oleh asesor/LSP |
| 4.4 | PDF ketiga formulir + arsip |
| 4.5 | Test alur keputusan dan akses berbasis peran |

**Gate:** keputusan asesmen dapat direkonstruksi dari bukti, umpan balik, dan laporan.

---

### Fase 5 — Banding (FR.AK.04)

| # | Pekerjaan |
|---|---|
| 5.1 | Tabel `bandings` + riwayat tindak lanjut, status (diajukan → ditinjau → diterima/ditolak → selesai) |
| 5.2 | UI asesi mengajukan, asesor/LSP meninjau, keputusan + alasan wajib |
| 5.3 | Notifikasi outbox tiap perubahan status |
| 5.4 | Efek ke status pendaftaran/hasil (uji ulang atau keputusan final) |
| 5.5 | PDF FR.AK.04 + test alur dan SLA waktu |

**Gate:** banding tidak dapat dihapus, hanya ditutup dengan keputusan; jejak audit lengkap.

---

### Fase 6 — Validasi, pleno, dan sertifikat (FR.VA)

| # | Pekerjaan |
|---|---|
| 6.1 | Role `komite_teknis` dan `manajer_sertifikasi` + permission |
| 6.2 | Tabel `validasi_asesmens` (FR.VA) dan `plenos` + peserta pleno |
| 6.3 | Berita acara pleno + PDF |
| 6.4 | Gating: sertifikat hanya terbit setelah pleno menyetujui |
| 6.5 | QR image di PDF sertifikat (library QR) + test scan/URL |
| 6.6 | Notifikasi ke asesi saat sertifikat terbit; UI pembatalan/kedaluwarsa |
| 6.7 | Test RBAC role baru dan gating sertifikat |

**Gate:** tidak ada sertifikat tanpa FR.VA dan keputusan pleno; QR mengarah ke halaman verifikasi yang valid.

---

### Fase 7 — Pelaporan BNSP & hardening

| # | Pekerjaan |
|---|---|
| 7.1 | Rekap asesi kompeten per periode/skema untuk BNSP |
| 7.2 | Surat pengantar blanko + ekspor laporan |
| 7.3 | Job kedaluwarsa sertifikat + pengingat |
| 7.4 | Perbaikan suite Dusk yang basi + shim `vendor/bin/dusk` |
| 7.5 | Test logo/tanda tangan/sertifikat yang belum ada |
| 7.6 | Audit OWASP Top 10:2025 + performa + pembaruan dokumentasi |

**Gate:** laporan BNSP dapat dihasilkan dari data nyata; seluruh suite (feature + Dusk) hijau.

---

### Fase 8 — Honor Asesor per Asesi

**Tujuan:** menghitung, menyetujui, dan membayar honor asesor berdasarkan jumlah asesi yang benar-benar diasesmen, dengan jejak audit dan pencegahan pembayaran ganda. Fase ini dapat dimulai setelah Fase 4 (hasil asesmen final tersedia) dan berjalan paralel dengan Fase 5–6.

**Prinsip utama:**

- Honor dihitung **per asesi per asesor per peran** pada satu jadwal, bukan per jadwal.
- Nominal diambil dari tarif aktif saat generate, lalu **disnapshot** ke baris honor. Perubahan tarif tidak boleh mengubah tagihan lama.
- Satu asesi hanya menghasilkan satu baris honor untuk kombinasi jadwal + asesor + peran (unique constraint).
- Tarif, tagihan, dan pembayaran terpisah dari pembayaran asesi. Jangan mencampur tabel `pembayarans`.

**Rancangan data:**

| Tabel | Isi |
|---|---|
| `tarif_honors` | `institution_id`, `skema_id` (wajib untuk peran utama; nullable = default LSP), `peran` (utama/pendamping/observer/peninjau_banding/klarifikasi_banding), `nominal` (bigInteger rupiah), `persen_uji_ulang` (default 100), `berlaku_dari`, `berlaku_sampai`, `is_active` |
| `honor_asesors` | `institution_id`, `pendaftaran_id`, `jadwal_id`, `asesor_id`, `jadwal_asesor_id`, `peran`, `jenis_sesi` (awal/uji_ulang), `sesi_ke`, `tarif_id`, `nominal` (snapshot), `status`, `catatan`, `dihasilkan_pada` |
| `honor_bandings` | `institution_id`, `banding_id`, `asesor_id`, `jenis` (peninjau/klarifikasi), `nominal`, `status`, `catatan` |
| `pembayaran_honors` | `institution_id`, `asesor_id`, `event_asesmen_id`, `total`, `status`, `catatan_potongan`, `bukti_path`, `dibayar_pada`, `disetujui_oleh`, `referensi_transfer` |
| `pembayaran_honor_items` | penghubung batch pembayaran ke baris `honor_asesors` dan `honor_bandings` |

Status baris honor: `draf` → `diajukan` → `disetujui` → `dibayar`; alternatif `tidak_dibayar` (nominal 0), `ditolak`, `dibatalkan`.
Status batch pembayaran: `draf` → `dicairkan`.
Unique baris honor: `(institution_id, jadwal_id, pendaftaran_id, asesor_id, peran, sesi_ke)`. Unique batch: `(institution_id, asesor_id, event_asesmen_id)`.

**Aturan bisnis:**

1. Generate honor otomatis saat MAK-02 final, untuk setiap asesor pada `jadwal_asesors` dan setiap asesi pada jadwal tersebut.
2. Syarat eligible: presensi asesi `hadir` **dan** hasil MAK-02 `final`. Asesi tidak hadir, batal, atau hasil belum final → tidak dihasilkan.
3. Hasil K/BK tidak memengaruhi nominal. Asesor tetap dibayar meski asesi dinyatakan belum kompeten.
4. Tarif dicari berurutan: `(skema_id, peran)` → `(NULL, peran)` default LSP. Tarif utama tidak boleh memakai default jika skema belum punya tarif.
5. Peran utama/pendamping/observer memakai tarif berbeda. Observer dengan nominal 0 tetap dibuat barisnya berstatus `tidak_dibayar`; tidak masuk batch.
6. Uji ulang membuat baris baru dengan `jenis_sesi = uji_ulang` dan `sesi_ke` bertambah. Nominal = tarif × `persen_uji_ulang` (default 100%). Uji ulang pada jadwal berbeda dihitung sebagai sesi baru secara alami.
7. Banding: honor peninjau dihitung flat per kasus pada `honor_bandings`, bukan per asesi. Peninjau tidak boleh asesor yang menilai pendaftaran tersebut (ditegakkan di service/policy).
8. Bila banding berujung uji ulang, honor uji ulang mengikuti aturan nomor 6. Honor klarifikasi untuk asesor asal bersifat opsional dan flat per kasus.
9. Bila hasil dibuka ulang atau pendaftaran dibatalkan, baris honor `draf`/`diajukan` otomatis dibatalkan; baris `dibayar` tidak diubah, harus lewat penyesuaian tertulis.
10. Asesor pengganti: baris honor mengikuti asesor yang tercatat pada hasil final, bukan asesor yang awalnya ditugaskan.
11. Persetujuan honor tidak boleh dilakukan oleh orang yang membuat/mengubah tagihan (pemisahan tugas).
12. Pembayaran hanya boleh untuk baris `disetujui`; satu baris tidak boleh masuk dua batch.
13. Batch dikelompokkan per asesor + event. Total batch = jumlah baris `disetujui` yang belum masuk batch lain.
14. Nominal disimpan sebagai integer rupiah (bukan float). PPh dan potongan lain di luar sistem; hanya dicatat di `catatan_potongan`.

**Pekerjaan:**

| # | Pekerjaan |
|---|---|
| 8.0 | Prasyarat: presensi digital asesi (hadir/tidak hadir) tersedia dari Fase 2 |
| 8.1 | Migrasi + model: tarif honor (per skema + fallback), honor asesor, honor banding, batch per event, item batch |
| 8.2 | Seeder tarif contoh: utama per skema, pendamping/observer default, peninjau/klarifikasi banding |
| 8.3 | Service `HonorAsesorService`: generate idempoten, lookup tarif berjenjang, snapshot nominal, persen uji ulang, pembatalan, rekalkulasi, validasi peninjau banding |
| 8.4 | UI admin/keuangan: kelola tarif, daftar tagihan per event/asesor/skema, setujui/tolak, buat batch per event, unggah bukti transfer, catatan potongan |
| 8.5 | Portal asesor: halaman "Honor Saya" (read-only) + unduh slip PDF per event, termasuk honor banding |
| 8.6 | Widget dashboard: total honor belum dibayar, honor per event |
| 8.7 | Rekap + ekspor Excel honor per asesor/event/skema + rekap honor banding |
| 8.8 | Notifikasi outbox saat tagihan disetujui dan saat dibayar |
| 8.9 | Policy + authorize: admin keuangan, pimpinan, asesor hanya melihat miliknya; isolasi tenant; peninjau banding ≠ asesor asal |
| 8.10 | Test: generate idempoten, unique constraint, tarif per skema vs fallback, observer nominal 0 tidak masuk batch, uji ulang, banding independen, RBAC, lintas tenant, pembayaran ganda ditolak, pembatalan hasil, total batch per event |

**Gate:** untuk satu event dengan N asesi hadir dan 2 asesor, total honor = N × tarif per peran; asesi no-show tidak menghasilkan baris; observer nominal 0 tidak masuk batch; uji ulang menambah baris sesi baru; honor banding terpisah per kasus; tidak ada duplikasi saat finalisasi diulang; slip dan rekap cocok dengan batch pembayaran per event.

**Catatan integrasi:** presensi digital adalah **prasyarat**, bukan opsional. Bila Fase 2 belum menyediakan presensi hadir/tidak hadir, Fase 8 harus membangunnya lebih dulu (tabel `presensi_asesis` + UI asesor/admin).

**Keputusan yang sudah ditetapkan:**

| # | Keputusan | Implikasi implementasi |
|---|---|---|
| 1 | Tarif utama berbeda per skema | Tarif utama wajib punya `skema_id`; pendamping/observer boleh default LSP. Lookup: tarif `(skema_id, peran)` → fallback `(NULL, peran)` |
| 2 | Observer dibayar, nominal dapat 0 | Baris honor observer tetap dibuat. Nominal 0 berstatus `tidak_dibayar` dan tidak masuk batch |
| 3 | Dasar hitung: presensi hadir + MAK-02 final | Butuh presensi digital (hadir/tidak hadir); no-show tidak dihitung. Hasil K/BK tidak memengaruhi honor |
| 4 | Pencairan per event | Batch pembayaran dikelompokkan per `event_asesmen`, bukan per bulan |
| 5 | PPh di luar sistem | Nominal di sistem = honor bruto; tidak ada kolom pajak. Catatan potongan disimpan di `catatan` batch |
| 6 | Uji ulang = sesi baru; banding = honor peninjau terpisah | Honor uji ulang penuh per sesi (dapat dikonfigurasi). Peninjau banding honor flat per kasus, tidak boleh asesor asal |

**Dasar perhitungan (praktik umum LSP):** honor dibayar atas **jasa pelaksanaan asesmen**, bukan atas hasil K/BK. Asesi hadir dan asesmen terlaksana → berhak; hasil final dipakai sebagai pemicu administrasi agar bukti lengkap. Asesi tidak hadir/batal → tidak dihitung. Bila asesor sudah datang tetapi asesi no-show, biaya transport/kehadiran dibayar terpisah, bukan honor per asesi.

**Uji ulang (praktik umum):** dihitung sebagai sesi asesmen baru. Sebagian LSP membayar penuh karena perencanaan, instrumen, dan pelaporan diulang; sebagian membayar 50–75% bila hanya menguji ulang unit yang belum kompeten. Sistem memakai `persen_uji_ulang` (default 100%) yang dapat diubah per tarif/skema.

**Banding (praktik umum):** peninjau banding adalah pihak independen, bukan asesor yang menilai. Honor dihitung **flat per kasus**, bukan per asesi. Bila banding berujung uji ulang, honor uji ulang dibayar normal. Bila asesor asal diminta klarifikasi, dapat dibayar honor klarifikasi flat terpisah.

---

## 10. Urutan Rilis yang Disarankan

| Rilis | Isi | Alasan |
|---|---|---|
| `v0.6.1` | Fase 0 | Hotfix blocking, cepat |
| `v0.7.0` | Fase 1 + 2 | Alur pra-uji lengkap (MAPA + AK.01) |
| `v0.8.0` | Fase 3A | Bukti per instrumen inti; MAK-02 jadi lebih kredibel |
| `v0.8.1` | Fase 3B | Kelengkapan instrumen |
| `v0.9.0` | Fase 4 + 5 | Keputusan, laporan, banding |
| `v0.9.1` | Fase 8 | Honor asesor per asesi (dapat rilis paralel) |
| `v1.0.0-rc` | Fase 6 | Validasi/pleno + sertifikat ber-QR |
| `v1.0.0` | Fase 7 | Pelaporan BNSP + hardening |

## 11. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Skema DB instrumen berubah saat Fase 3 | Migrasi data & rework | Finalkan desain `instrumen_*` di akhir Fase 2 sebelum UI |
| Scope creep FR.IA (11 formulir) | Fase 3 mundur | Rilis 3A dulu; 3B per formulir |
| Perbedaan perangkat BNSP antar-LSP | Formulir tidak cocok | Validasi template dengan LSP pengguna sebelum Fase 1 |
| Duplikasi data MAK-02 vs instrumen | Data tidak konsisten | MAK-02 menjadi agregator, bukan input manual, setelah Fase 3A |
| QR & nomor sertifikat | Sertifikat tidak sah | Uji dengan blanko nyata dan regex BNSP di Fase 6 |
| Role baru mengubah RBAC | Regresi akses | Test RBAC menyeluruh tiap penambahan role |
| Honor dobel bayar | Kerugian finansial | Unique constraint per sesi + idempotensi generate + larangan baris masuk dua batch |
| Tarif berubah setelah tagihan | Sengketa honor | Snapshot nominal di baris honor; tarif hanya berlaku untuk generate baru |
| Hasil asesmen dibuka ulang | Honor sudah terlanjur dibayar | Batalkan hanya status draf/diajukan; status dibayar lewat penyesuaian tertulis |
| Presensi tidak akurat | Honor dibayar untuk asesi tidak hadir | Presensi digital terkunci setelah asesmen + audit log + koreksi hanya oleh admin |
| Tarif utama per skema belum diisi | Generate honor gagal | Validasi kelengkapan tarif sebelum jadwal dipublikasikan; fallback hanya untuk pendamping/observer |
| Observer nominal 0 dianggap tidak hadir | Data honor tidak lengkap | Baris tetap dibuat berstatus `tidak_dibayar`; rekap menampilkan peran observer |
| Peninjau banding = asesor asal | Konflik kepentingan | Validasi di service + policy; tolak jika asesor pernah menilai pendaftaran |
| Uji ulang dobel | Honor berlebih | Kunci `(jadwal, pendaftaran, asesor, peran, sesi_ke)`; sesi_ke naik eksplisit |
