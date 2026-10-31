# Standar Keamanan Aplikasi SiapKompeten

## Berbasis OWASP Top 10:2025

| Atribut         | Isi                                                                                   |
| --------------- | ------------------------------------------------------------------------------------- |
| Aplikasi        | **SiapKompeten** — Sistem Informasi Lembaga Sertifikasi Profesi                  |
| Dokumen         | Standar Keamanan & Checklist Rekayasa (wajib bagi AI agent dan developer)             |
| Versi           | 1.1                                                                                   |
| Tanggal         | 30 Oktober 2026                                                                       |
| Acuan utama     | [OWASP Top 10:2025](https://owasp.org/Top10/2025/)                                   |
| Acuan pendukung | BRD `docs/BRD_Aplikasi_LSPKU.md` (NFR-01…NFR-16), System Design bagian 10            |
| Status          | **Wajib (Mandatory)** — tidak boleh dilewati tanpa persetujuan tertulis pemilik produk |

---

## 1. Tujuan

Dokumen ini menetapkan **standar keamanan minimum** yang harus diterapkan pada setiap baris kode, migrasi, tampilan, konfigurasi server, dan integrasi pihak ketiga di aplikasi SiapKompeten. Standar disusun dari **OWASP Top 10:2025** dan dipetakan langsung ke risiko khas aplikasi LSP: data pribadi (NIK, dokumen identitas), hasil asesmen yang bersifat final, sertifikat, serta portal multi-peran (Admin, Asesor, Asesi, Publik).

Dokumen ini melengkapi — bukan menggantikan — NFR pada BRD dan kontrol keamanan pada System Design bagian 10.

---

## 2. Aturan Pemakaian

1. **Definition of Done (DoD) setiap fitur wajib menyertakan checklist keamanan** (bagian 5). Fitur tanpa checklist dianggap belum selesai.
2. Setiap kategori A01–A10 harus dinilai pada setiap fitur. Bila kategori tidak relevan, tuliskan alasannya di deskripsi pekerjaan/PR.
3. Kontrol keamanan harus **di server** (otorisasi, validasi, transisi status). UI hanya lapisan kenyamanan, bukan pengaman.
4. **Deny by default**: route, policy, dan izin baru tertutup sampai diberikan secara eksplisit.
5. Temuan keamanan ditangani sesuai SLA pada bagian 8. Temuan **Critical/High** memblokir rilis.
6. AI agent **wajib** membaca dokumen ini di awal sesi bersama `docs/` lain dan `PROGRESS_TRACKER.md`.

---

## 3. Matriks OWASP Top 10:2025 → Kontrol Wajib SiapKompeten

### A01:2025 — Broken Access Control

| Aspek              | Ketentuan wajib                                                                                                                       |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------------- |
| Otorisasi          | Setiap aksi controller wajib melalui Policy (`$this->authorize()`) atau FormRequest `authorize()`. Tidak ada pengecekan hanya di Blade. |
| Peran              | Middleware peran pada seluruh route portal (`admin`, `asesor`, `asesi`, `pimpinan`). Super Admin tidak otomatis dipakai untuk operasi rutin. |
| Kepemilikan data   | Asesor hanya jadwal/APL-02/MAK-02/SMS miliknya (BR-RULE-18). Asesi hanya pendaftaran, dokumen, dan hasil miliknya.                     |
| Isolasi tenant     | Setiap query pada model tenant-scoped melewati scope `institution_id`; `institution_id` tidak boleh berasal dari input pengguna; resource tenant lain → 404. |
| Lintas tenant      | Pada MVP (satu domain, konteks sesi) tidak ada jalur lintas tenant sama sekali. Area `/platform` untuk Super Admin lintas tenant adalah fase lanjutan dan wajib diaudit. Tidak ada route tenant yang menerima `institution_id` sebagai parameter bebas. |
| IDOR               | UUID **bukan** pengganti otorisasi. Setiap akses resource by-ID wajib memverifikasi kepemilikan/penugasan.                            |
| Berkas privat      | Unduhan dokumen melalui controller dengan Policy + signed URL berumur pendek; tidak ada URL storage publik untuk NIK/dokumen.          |
| Aksi khusus        | Blokir akun, koreksi hasil final, pembatalan pendaftaran, dan perubahan peran hanya untuk peran berwenang + tercatat audit.            |
| Endpoint publik    | Hanya daftar skema aktif, asesor, berita, kontak, verifikasi sertifikat. Tidak ada data pribadi pada endpoint publik.                  |
| Metode HTTP        | Aksi yang mengubah data memakai POST/PUT/PATCH/DELETE + CSRF. Tidak ada perubahan data via GET.                                       |
| Bukti uji          | Feature test 403 untuk lintas peran dan lintas pemilik (contoh AC-08). Uji akses langsung URL tanpa penugasan.                         |

### A02:2025 — Security Misconfiguration

| Aspek              | Ketentuan wajib                                                                                                                                     |
| ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| Lingkungan         | Produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` unik dan dirotasi bila bocor. Error ditampilkan sebagai halaman generik.              |
| Header keamanan    | Wajib: `Content-Security-Policy`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, `X-Frame-Options`/`frame-ancestors`. |
| Transport          | HTTPS wajib, HSTS, cookie `Secure`, `HttpOnly`, `SameSite=Lax`.                                                                                     |
| Storage            | Disk `private` untuk dokumen, foto, PDF; `public` hanya aset statis. Direktori tanpa listing.                                                       |
| Rahasia            | `.env` di luar web root dan repo; tidak ada kredensial di kode, seeder produksi, atau log.                                                          |
| Fitur debug        | Telescope/Debugbar/Pail hanya lokal/staging dan di belakang autentikasi.                                                                            |
| Akun bawaan        | Tidak ada akun default dengan sandi default. Super Admin dibuat dari `.env`/perintah aman dan wajib ganti sandi.                                    |
| Database           | MySQL tidak terbuka ke publik; user DB aplikasi memakai hak minimum (tanpa `DROP`/`GRANT`).                                                         |
| Bukti uji          | Test header respons; audit konfigurasi `.env.example` vs `.env`; pemeriksaan `php artisan about`.                                                   |

### A03:2025 — Software Supply Chain Failures

| Aspek              | Ketentuan wajib                                                                                                                     |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------------- |
| Dependensi         | Hanya pustaka terpelihara dan relevan. Setiap penambahan dependensi harus disetujui dan dicatat di PR (alasan, lisensi, ukuran).   |
| Lock file          | `composer.lock` dan `package-lock.json` wajib di-commit; build memakai versi terkunci.                                              |
| Audit              | `composer audit` dan `npm audit` dijalankan sebelum rilis dan pada CI. Temuan High/Critical wajib diperbaiki.                       |
| Sumber             | Hanya registry resmi (Packagist, npm) + HTTPS. Tidak ada paket dari URL acak/zip tanpa verifikasi.                                  |
| Pembaruan          | Patch keamanan Laravel/PHP diterapkan ≤ 7 hari untuk High/Critical. PHP minimal 8.4 (sesuai platform check proyek).                 |
| CI/CD              | Build dari commit yang ditandai; artefak tidak dapat diubah setelah rilis; secret tidak ditulis ke artefak.                         |
| SBOM               | Hasilkan `composer sbom`/SBOM setara pada rilis; simpan bersama catatan rilis.                                                     |
| Bukti uji          | Log audit dependensi pada catatan rilis; pemeriksaan `composer validate --strict`.                                                 |

### A04:2025 — Cryptographic Failures

| Aspek              | Ketentuan wajib                                                                                                                                          |
| ------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Sandi              | Hash `argon2id` (utama) atau `bcrypt` cost ≥ 12. Tidak ada enkripsi dua arah untuk sandi.                                                               |
| NIK                | Disimpan dengan cast `encrypted` + kolom `nik_hash` (HMAC-SHA256 dengan pepper dari `APP_KEY`/env) untuk pencarian & unik. Tampilan dimasking.         |
| Token              | Token reset sandi, verifikasi email, dan `verification_code` sertifikat memakai random generator kriptografis; disimpan sebagai hash bila berupa token akses. |
| Berkas             | Nama berkas diacak (UUID), tidak memakai nama asli pengguna; MIME diverifikasi dari isi berkas (finfo), bukan hanya ekstensi.                             |
| Transport          | Tidak ada data sensitif pada URL (NIK, sandi, token) yang tercatat di access log.                                                                        |
| Backup             | Backup database & berkas terenkripsi; kunci backup terpisah dari kunci aplikasi.                                                                         |
| Log                | Tidak mencatat NIK penuh, sandi, token, isi dokumen, atau kredensial gateway.                                                                            |
| Kriptografi kustom | Dilarang membuat algoritma enkripsi/hash sendiri. Gunakan fasilitas Laravel/PHP.                                                                        |
| Bukti uji          | Test bahwa NIK tersimpan tidak terbaca polos; pemeriksaan log bebas PII; uji rotasi kunci pada staging.                                                  |

### A05:2025 — Injection

| Aspek              | Ketentuan wajib                                                                                                                                       |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| SQL                | Eloquent/Query Builder dengan binding. `DB::raw`/`whereRaw` hanya dengan binding dan nilai yang telah divalidasi.                                     |
| XSS                | Blade `{{ }}` untuk semua output. `{!! !!}` hanya untuk HTML yang sudah disanitasi allowlist (HTMLPurifier) — khusus konten CMS.                     |
| CMS                | Sanitasi HTML di server dengan allowlist tag/atribut; blokir `script`, `on*`, `javascript:`, `data:` berbahaya.                                        |
| Google Maps        | Hanya menerima URL `https://www.google.com/maps/embed...`; validasi & normalisasi sebelum disimpan.                                                   |
| Excel/CSV          | Cegah formula injection pada ekspor: sel yang diawali `= + - @ TAB CR` diberi prefiks `'`.                                                            |
| Email/SMS          | Header injection dicegah (buang CR/LF dari subjek/tujuan); tujuan divalidasi sesuai format.                                                            |
| Berkasa/Path       | Tidak memakai input pengguna langsung pada path filesystem; gunakan nama acak + validasi ekstensi.                                                    |
| Deserialisasi      | Dilarang `unserialize()` data pengguna; gunakan JSON + validasi skema.                                                                                 |
| Bukti uji          | Test XSS tersimpan pada CMS, test SQLi pada filter/pencarian, test formula injection pada ekspor.                                                     |

### A06:2025 — Insecure Design

| Aspek              | Ketentuan wajib                                                                                                                                          |
| ------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Aturan bisnis      | Semua aturan (kuota, urutan status, larangan hapus jadwal berisi peserta, finalitas hasil) ditegakkan di Action/Service dalam transaksi DB.              |
| State machine      | Transisi status hanya melalui Action resmi; tidak ada update status liar dari controller/komponen tampilan.                                              |
| Race condition     | Plotting memakai `lockForUpdate()`; kuota diperiksa di dalam transaksi.                                                                                   |
| Imutabilitas       | Hasil MAK-02 final terkunci; koreksi hanya admin dengan alasan + audit (BR-RULE-21).                                                                     |
| Least privilege    | Peran minimum untuk setiap tugas; pemisahan tugas verifikasi vs plotting bila memungkinkan.                                                              |
| Abuse case         | Registrasi & impor dilindungi rate limit, batas ukuran berkas, batas jumlah baris, dan CAPTCHA/antispam bila perlu.                                      |
| Default aman       | Skema nonaktif tidak dapat dipilih; pendaftaran ganda dicegah unique constraint; berkas wajib lolos validasi sebelum diproses.                          |
| Desain tenant      | Shared schema + `institution_id`; unique komposit per tenant; FK komposit untuk tabel bernilai tinggi; job/command wajib membawa tenant (fail closed).          |
| Threat model       | Setiap modul baru wajib menuliskan 3–5 skenario penyalahgunaan dan mitigasinya pada deskripsi pekerjaan.                                                 |
| Bukti uji          | Test kuota penuh, test double submit, test hapus jadwal berisi peserta, test koreksi hasil final.                                                       |

### A07:2025 — Authentication Failures

| Aspek              | Ketentuan wajib                                                                                                                                     |
| ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| Kebijakan sandi    | Minimal 8 karakter (disarankan 12), cek daftar sandi umum, tolak sandi = NIK/username/email.                                                       |
| Login              | Rate limit 5 percobaan/menit per IP+akun, backoff bertingkat, pesan gagal generik (cegah enumerasi akun).                                          |
| Sesi              | Regenerasi session saat login, invalidasi saat logout, batas idle, lock screen (FR-AUTH-02). Cookie aman (A02).                                    |
| Blokir akun        | Status `diblokir` diperiksa saat login **dan** pada setiap request (`EnsureNotBlocked`); sesi aktif diputus.                                        |
| Reset sandi        | Token sekali pakai, kedaluwarsa, tautan tidak membocorkan keberadaan akun; perubahan sandi memerlukan sandi lama.                                  |
| Remember me        | Token dirotasi; dicabut saat logout/sandi berubah/blokir.                                                                                           |
| MFA                | Direkomendasikan untuk Super Admin dan Admin LSP (fase hardening).                                                                                  |
| Login NIK          | Pencarian melalui `nik_hash`; tidak melakukan dekripsi massal; tidak menampilkan NIK pada pesan galat.                                              |
| Bukti uji          | Test rate limit, test akun diblokir (AC-10), test session fixation, test reset token kedaluwarsa.                                                   |

### A08:2025 — Software or Data Integrity Failures

| Aspek              | Ketentuan wajib                                                                                                                                       |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| Unggahan           | Validasi MIME sebenarnya + ekstensi + ukuran; simpan di luar web root; tolak berkas ganda/berbahaya; opsi pindai antivirus.                         |
| Impor Excel        | Berkas diproses sebagai data (bukan dieksekusi); batas ukuran/baris; validasi per baris; transaksi per chunk; laporan galat.                          |
| Webhook            | Endpoint SMS gateway wajib memverifikasi signature/token; idempoten; simpan payload mentah untuk audit.                                              |
| Integritas data    | Operasi multi-tabel dalam transaksi; unique constraint untuk mencegah duplikasi; foreign key `restrictOnDelete` untuk referensi.                     |
| Audit trail        | Log aktivitas tidak dapat diubah pengguna aplikasi; akses tulis hanya lewat ActivityLog; retensi sesuai kebijakan.                                    |
| Aset eksternal     | CDN/script pihak ketiga memakai SRI atau di-host sendiri; pin versi.                                                                                  |
| Rilis              | Migrasi & seeder terversi; tidak ada perubahan manual pada DB produksi tanpa catatan.                                                                 |
| Bukti uji          | Test unggah berkas palsu (ekstensi diubah), test webhook tanpa signature, test duplikasi NIK.                                                         |

### A09:2025 — Security Logging and Alerting Failures

| Aspek              | Ketentuan wajib                                                                                                                                       |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| Peristiwa wajib    | Login sukses/gagal, logout, blokir/buka blokir, perubahan peran, CRUD master, keputusan verifikasi dokumen, persetujuan pendaftaran, simpan/finalisasi hasil, koreksi admin, unduh dokumen sensitif, kirim ulang SMS, akses lintas tenant oleh platform. |
| Konteks tenant     | Setiap entri log menyertakan `institution_id`; log tanpa tenant hanya untuk aksi platform. Alert bila ada query/akses lintas tenant di luar `/platform`.       |
| Format             | Log terstruktur (JSON) dengan waktu UTC, aktor, aksi, resource, IP, user agent, hasil. Jangan mencatat PII/rahasia.                                  |
| Log injection      | Sanitasi CR/LF pada nilai yang masuk log.                                                                                                             |
| Retensi            | Simpan minimal 90 hari (kebijakan dapat diperpanjang); log akses berkas sensitif terpisah.                                                            |
| Alerting           | Notifikasi untuk lonjakan login gagal, 403 berulang, 5xx, kegagalan queue, kegagalan backup, percobaan akses lintas peran.                           |
| Monitoring         | Health check, dashboard error, Laravel Pulse/Telescope hanya non-produksi atau terproteksi.                                                           |
| Bukti uji          | Test bahwa aksi kritis menulis activity log; test alert pada staging; pemeriksaan log bebas PII.                                                     |

### A10:2025 — Mishandling of Exceptional Conditions

| Aspek              | Ketentuan wajib                                                                                                                                          |
| ------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Fail closed        | Bila otorisasi/validasi/transaksi gagal, akses ditolak — bukan dilanjutkan dengan nilai default.                                                        |
| Pesan galat        | Produksi menampilkan pesan generik + ID korelasi; detail hanya di log. Jangan membocorkan stack trace, query, path server.                               |
| Transaksi          | Operasi multi-tabel wajib `DB::transaction`; rollback pada exception; verifikasi hasil setelah commit.                                                   |
| Eksepsi domain     | Gunakan exception domain + handler yang memetakan ke pesan ramah pengguna (422/409), bukan 500.                                                         |
| Resource limit     | Batas ukuran unggahan, jumlah baris impor, `per_page` maksimum, timeout HTTP gateway, batas percobaan job.                                               |
| Queue & integrasi  | Retry + backoff, dead-letter/failed_jobs, timeout, fallback Email bila SMS gagal, status `gagal` dapat dikirim ulang.                                    |
| Null/edge case     | Validasi data kosong, relasi terhapus, jadwal tanpa asesor, biaya nol (lewati pembayaran), kuota penuh, berkas hilang.                                  |
| Idempotensi        | Aksi yang dapat diulang (webhook, kirim ulang, approve ganda) harus idempoten.                                                                          |
| Bukti uji          | Test jadwal tanpa asesor, test biaya nol, test gateway timeout, test berkas hilang, test double approve.                                                |

---

## 4. Kontrol Baseline Wajib (Lintas Kategori)

| Kontrol                 | Implementasi                                                                                          |
| ----------------------- | ----------------------------------------------------------------------------------------------------- |
| CSRF                    | Token Laravel pada semua form Blade/Livewire; pengecualian hanya webhook terverifikasi signature.     |
| Validasi input          | FormRequest untuk semua input; whitelist kolom `$fillable`; cast tipe data; tolak field tak dikenal.   |
| Mass assignment         | Tidak memakai `$request->all()` untuk create/update; gunakan `validated()`.                            |
| Otorisasi               | Policy + `authorize()`; uji 403 pada setiap resource.                                                 |
| Unggah berkas           | Disk privat, nama acak, validasi MIME/ukuran, akses via signed URL/controller berpolicy.              |
| UUID                    | Semua PK UUID v7; route model binding; jangan pernah memakai ID berurutan di URL.                     |
| Isolasi tenant          | Model tenant-scoped memakai `BelongsToTenant`; `institution_id` auto-isi & immutable; policy + uji lintas tenant; job/command wajib context tenant. |
| Audit                   | `spatie/laravel-activitylog` untuk aksi kritis; causer/subject UUID.                                  |
| Header                  | Middleware header keamanan global; CSP ketat, longgarkan hanya bila terdokumentasi.                   |
| Rate limit              | Login, registrasi, impor, kirim ulang SMS, endpoint publik berat.                                     |
| Rahasia                 | Hanya dari `.env`/secret manager; `.env.example` tanpa nilai sensitif.                                 |
| Dependensi              | Audit berkala, lock file, peninjauan paket baru.                                                      |
| Error handling          | Handler global; `APP_DEBUG=false` produksi; laporan ke log/monitoring.                                |
| Backup & restore        | Harian terenkripsi, retensi ≥ 30 hari, uji restore berkala.                                           |

---

## 5. Checklist Keamanan per Fitur (Definition of Done)

Salin checklist ini ke deskripsi pekerjaan/PR dan isi hasilnya.

```markdown
### Checklist Keamanan (OWASP Top 10:2025)

- [ ] A01 Akses: Policy/authorize ada; uji 403 lintas peran, lintas pemilik, dan lintas tenant; tidak ada IDOR.
- [ ] A02 Konfigurasi: tidak ada debug/rahasia terbuka; header & cookie aman; storage privat.
- [ ] A03 Supply chain: tidak ada dependensi baru tanpa catatan; `composer audit` bersih.
- [ ] A04 Kriptografi: sandi/NIK/token aman; tidak ada PII di log/URL; berkas nama acak.
- [ ] A05 Injection: binding Eloquent; output di-escape; HTML CMS disanitasi; ekspor aman.
- [ ] A06 Desain: aturan bisnis di server; transaksi & lock; abuse case diuji.
- [ ] A07 Autentikasi: rate limit, sesi, blokir, reset sandi sesuai standar.
- [ ] A08 Integritas: validasi unggahan/impor; webhook terverifikasi; transaksi & unique constraint.
- [ ] A09 Logging: aksi kritis tercatat; tidak ada PII; alert relevan.
- [ ] A10 Eksepsi: fail closed; pesan generik; rollback; batas resource; retry.
- [ ] Test keamanan ditambahkan (feature/Dusk) dan lulus.
```

---

## 6. Pemetaan ke NFR BRD

| NFR    | Kategori NFR          | Kontrol OWASP terkait |
| ------ | --------------------- | --------------------- |
| NFR-04 | Keamanan transport    | A02, A04              |
| NFR-05 | Keamanan akun         | A01, A07, A10         |
| NFR-06 | Data pribadi (UU PDP) | A01, A04, A09         |
| NFR-07 | Unggah berkas         | A04, A05, A08         |
| NFR-08 | Kinerja               | A06, A10              |
| NFR-11 | Audit                 | A09                   |
| NFR-12 | Integritas            | A06, A08, A10         |
| NFR-13 | Keandalan notifikasi  | A08, A10              |
| NFR-14 | Pemeliharaan          | A03, A08              |
| NFR-15 | Aksesibilitas         | A02 (kontras & status) |

---

## 7. Tooling dan Gate Rilis

| Tahap       | Alat/Perintah                                                       | Kriteria lulus                       |
| ----------- | ------------------------------------------------------------------- | ------------------------------------ |
| Lokal       | `composer audit`, `npm audit`, `php artisan test`                   | Tidak ada High/Critical terbuka      |
| Gaya kode   | `vendor/bin/pint --test`                                            | Bersih                               |
| Analisis    | Larastan (level ≥ 5)                                                | Tidak ada error baru                 |
| Test fitur  | Pest/PHPUnit (otorisasi, validasi, transaksi)                       | Semua lulus                          |
| Test UI     | Laravel Dusk (smoke per portal + alur kritis)                       | Semua lulus                          |
| Konfigurasi | Pemeriksaan header, `APP_DEBUG`, cookie, storage                    | Sesuai bagian A02                    |
| Rilis       | Audit dependensi + SBOM + catatan perubahan keamanan                | Ditandatangani/dicatat               |

---

## 8. Penanganan Temuan

| Severity | Contoh                                                                 | SLA perbaikan   | Rilis              |
| -------- | ---------------------------------------------------------------------- | --------------- | ------------------ |
| Critical | Bypass otorisasi, kebocoran NIK massal, RCE, SQLi, kredensial bocor    | ≤ 24 jam        | Diblokir           |
| High     | IDOR terbatas, XSS tersimpan, rate limit login tidak ada, upload RCE   | ≤ 7 hari        | Diblokir           |
| Medium   | Header tidak lengkap, logging kurang, enumerasi akun ringan           | ≤ 30 hari       | Boleh dengan risiko dicatat |
| Low      | Informasi versi, pesan galat minor, konfigurasi kosmetik               | ≤ 90 hari       | Boleh              |

Setiap temuan wajib memiliki: deskripsi, langkah reproduksi, dampak, kategori OWASP, mitigasi, dan bukti uji setelah perbaikan.

---

## 9. Larangan Keras

1. Menyimpan sandi dengan hash lemah atau enkripsi dua arah.
2. Menaruh dokumen/NIK di disk publik atau URL tanpa otorisasi.
3. Menonaktifkan CSRF, Policy, atau validasi demi "mempercepat" pekerjaan.
4. Menampilkan stack trace/`APP_DEBUG=true` di produksi.
5. Menjalankan `DB::raw`, `unserialize`, atau `eval` dengan input pengguna tanpa pengamanan ketat.
6. Menulis kredensial, NIK, atau token ke kode, repo, log, atau pesan galat.
7. Menambah dependensi tanpa peninjauan dan tanpa audit.
8. Mengubah data lewat GET atau tanpa audit pada aksi kritis.
9. Menyimpan hasil asesmen final yang dapat diubah tanpa jejak.
10. Menganggap UUID sebagai pengganti otorisasi.
11. Membuat query tenant tanpa scope `institution_id`, menerima `institution_id` dari request, atau mencampur data antar LSP tanpa jalur platform beraudit.

---

## 10. Riwayat Revisi

| Versi | Tanggal     | Perubahan                                                          | Author                 |
| ----- | ----------- | ------------------------------------------------------------------ | ---------------------- |
| 1.0   | 30 Okt 2026 | Penetapan OWASP Top 10:2025 sebagai standar keamanan wajib proyek  | Muhamad Arya Kurniawan |
| 1.1   | 30 Okt 2026 | Menambahkan kontrol isolasi tenant (multitenancy) pada A01, A06, A09, baseline, checklist, dan larangan | Muhamad Arya Kurniawan |

---

*Dokumen ini wajib dibaca ulang setiap awal sesi kerja dan diperbarui bila ada kategori/kontrol baru yang relevan.*
