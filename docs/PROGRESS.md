# PROGRESS — Perbaikan Dashboard Admin & Registrasi Asesi

> Dokumen ini mencatat progres sesi perbaikan sesuai tugas.
> Dibuat: 31 Oktober 2026. Diperbarui: 31 Oktober 2026.

---

## Sesi 2 — Perbaikan Registrasi Asesi `/daftar`

### Masalah yang diperbaiki

| # | Masalah | Status |
|---|---------|--------|
| 1 | Semua ~500+ kota di-render ke HTML sekaligus, difilter JS client-side | ✅ Diperbaiki |
| 2 | `@stack('scripts')` tidak ada di layout auth → JS filter kota tidak jalan | ✅ Diperbaiki |
| 3 | Kota tidak dinonaktifkan sebelum provinsi dipilih (UX buruk) | ✅ Diperbaiki |
| 4 | Tidak ada validasi server bahwa `kota_code` milik `provinsi_code` | ✅ Diperbaiki |
| 5 | `bg-light` hardcode di body layout auth (merusak dark mode) | ✅ Diperbaiki |
| 6 | Duplikasi `use DocumentController` di routes/web.php | ✅ Diperbaiki |

### File yang dibuat/diubah

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/WilayahController.php` | **Baru** — endpoint `GET /wilayah/provinsi/{code}/kota`, normalisasi nama wilayah (Title Case, "KAB." → "Kabupaten"), validasi kode numerik 2 digit, 404 untuk kode tidak valid |
| `routes/web.php` | Tambah route `wilayah.kota` (throttle 60/mnt), tambah import `WilayahController`, hapus duplikasi import `DocumentController` |
| `resources/views/layouts/auth.blade.php` | Tambah `@stack('scripts')` sebelum `</body>`, hapus `bg-light` dari `<body>` |
| `resources/views/auth/register.blade.php` | Hapus loop $cities (500+ option), ganti select kota jadi disabled dengan placeholder, JS `@push('scripts')` diganti AJAX fetch ke endpoint baru, preselect `old('kota_code')` dipertahankan |
| `app/Http/Controllers/Auth/RegisterController.php` | Hapus `$cities` dari `create()`, tambah validasi server `kota_code` milik `provinsi_code` |

### Cara kerja sekarang

1. Halaman `/daftar` hanya memuat daftar provinsi (~35 item)
2. Select kota **disabled** dengan teks "Pilih provinsi terlebih dahulu"
3. Saat provinsi dipilih → AJAX `GET /wilayah/provinsi/{code}/kota` → select kota diisi, diaktifkan
4. Nama kota sudah dinormalisasi: "KAB BANDUNG" → "Kabupaten Bandung"
5. State loading & error ditampilkan ke pengguna
6. Saat form gagal validasi server dan `old('provinsi_code')` ada → kota langsung dimuat ulang via AJAX + preselect `old('kota_code')`
7. Server memvalidasi bahwa `kota_code` benar-benar milik `provinsi_code` yang dipilih

---

> Dokumen ini mencatat progres sesi perbaikan dashboard admin sesuai tugas di PROGRESS_TRACKER.md Fase 6.
> Dibuat: 31 Oktober 2026.

---

## Temuan Inventaris Awal (langkah 1 selesai)

### Temuan yang dikonfirmasi dari kode nyata

| # | Temuan | Lokasi | Status |
|---|--------|--------|--------|
| 1 | Kartu statistik tanpa pembungkus `.row` — `@section('content')` langsung diisi empat `col-sm-6` tanpa `<div class="row row-deck row-cards">`, ditambah satu `</div>` berlebih mengakhiri blok yang tidak ada | `resources/views/dashboard/admin.blade.php` | 🔧 Dikerjakan |
| 2 | Kelas Bootstrap 4: `font-weight-medium`, `font-weight-bold` di `x-tabler.stat`; ikon `ti ti` (sudah benar sebenarnya — prefix manual sudah ada) | `components/tabler/stat.blade.php` | 🔧 Dikerjakan |
| 3 | Kartu statistik tidak bisa diklik, tidak ada hint kontekstual | `components/tabler/stat.blade.php` | 🔧 Dikerjakan |
| 4 | `DashboardController` memakai query langsung bukan melalui `StatRingkas` widget; data tidak dari registry | `DashboardController.php` | 🔧 Dikerjakan |
| 5 | Panel "Status Keamanan & Kepatuhan" statis: klaim OWASP, AES-256, "Terisolasi", "Resmi BNSP" hardcode | `dashboard/admin.blade.php` | 🔧 Dikerjakan |
| 6 | Navigasi: item tanpa BRD (Bundle TUG, Sertifikat & TUG, Template Presensi, SIUP User, Lampiran, Kategori Prestasi) sudah ada di sidebar tetapi route belum ada → tampil "Segera" | `layouts/admin.blade.php` | 🔧 Menu perlu dirapikan |
| 7 | Route SKKNI: sidebar memakai `admin.stkkni.*` — route sudah benar di web.php | `layouts/admin.blade.php` | ✅ OK |
| 8 | Header duplikat: `<x-tabler.header>` dan `<x-component.header>` — layout admin hanya memakai `x-tabler.header`, tidak ada duplikasi aktif. Tapi `x-tabler.header` punya brand ganda dengan sidebar | `components/tabler/header.blade.php` | 🔧 Dikerjakan |
| 9 | `<h2 class="page-title text-dark">` di layout — seharusnya tidak ada `text-dark` hardcode; layout memakai `<h2>` bukan `<h1>` untuk judul halaman, sementara sidebar brand memakai `<h1>` | `layouts/admin.blade.php` | 🔧 Dikerjakan |
| 10 | `<body class="bg-light">`, `<header ... bg-white>`, sidebar `navbar-dark bg-dark` — kelas hardcode | `layouts/admin.blade.php`, `header.blade.php` | 🔧 Dikerjakan |
| 11 | `<style>` inline di `<head>` (font-feature-settings) | `layouts/admin.blade.php` | 🔧 Dikerjakan |
| 12 | Footer berisi "Standar Keamanan: OWASP Top 10:2025" dan "Multi-LSP Ready" hardcode | `layouts/admin.blade.php` | 🔧 Dikerjakan |
| 13 | Modal unggah dokumen: route `documents.store` di luar prefix `/admin` | `dashboard/admin.blade.php` | 🔧 Dikerjakan |
| 14 | `Profil Saya` → `#profil` fallback masih ada di `x-tabler.header` | `components/tabler/header.blade.php` | 🔧 Dikerjakan |
| 15 | "Kunci Layar" memakai GET `route('lock')` — GET tidak mengubah state | `components/tabler/header.blade.php` | 🔧 Dikerjakan |
| 16 | Aksi header (`d-none d-sm-inline-block`) — tersembunyi total di mobile | `dashboard/admin.blade.php` | 🔧 Dikerjakan |
| 17 | "Tambah Skema" menuju `admin.skema.index` bukan form create | `dashboard/admin.blade.php` | 🔧 Dikerjakan |
| 18 | Widget arsitektur sudah ada (`DashboardRegistry`, `Widget` contract, `WidgetController`, `config/dashboard.php`) — tinggal dihubungkan ke view | domain/dashboard/* | 🔧 Dikerjakan |
| 19 | Kartu onboarding checklist belum ada | — | 🔧 Dikerjakan |
| 20 | Antrean tindakan di `AntreanTindakan` widget memakai URL literal (`/admin/pendaftaran?tab=...`) | `Widgets/AntreanTindakan.php` | 🔧 Dikerjakan |
| 21 | Komponen `x-component.header` (non-Tabler) masih ada tapi tidak dipakai di layout admin | `components/header.blade.php` | Biarkan (dipakai portal lain?) |

### Route yang belum ada (item menu "planned")

- `admin.asesor.skema.index` — Lingkup Kompetensi
- `admin.asesor.invite.index` — Undang Keanggotaan
- `admin.jadwal.bundle.*` — Bundle Sertifikat
- `admin.tuk.bundle.*` — Bundle TUG
- `admin.sertifikat.*` — Sertifikat
- `admin.template_presensi.*` — Template Presensi
- `admin.siup_user.*` — SIUP User
- `admin.lampiran.*` — Lampiran
- `admin.prestasi.kategori.*` — Kategori Prestasi
- `admin.pengaturan.group.*` — Tim & Persetujuan
- `admin.pelaporan.*` — Data Laporan (sudah ada `admin.pelaporan.index`)
- `profile.show` — Profil Saya

### Keputusan yang menunggu konfirmasi (default dipakai)

1. **Item menu di luar BRD** (Bundle TUG/Sertifikat, Template Presensi, SIUP User, Lampiran, Kategori Prestasi): **default = disembunyikan di produksi** (flag `planned`).
2. **"Dokumen & Portofolio Lembaga"**: **default = ringkasan di dashboard, pengelolaan penuh di Profil Lembaga**.
3. **Nama merek tampilan**: **default = `config('app.name')`**.
4. **Tema default**: **default = mengikuti sistem, tanpa tombol ganti tema di fase ini**.

---

## Urutan Kerja

### Langkah 1 — Inventaris ✅ SELESAI

### Langkah 2 — Perbaikan struktur HTML, komponen kartu + ikon, tes penjaga pola

- [ ] Perbaiki `x-tabler.stat`: props baru (`label`, `value`, `icon`, `color`, `href`, `hint`, `trend`), hapus `font-weight-*`, gunakan `fw-*`, `h1`/`fs-1 fw-bold`, `stretched-link`
- [ ] Perbaiki `dashboard/admin.blade.php`: bungkus kartu dengan `<div class="row row-deck row-cards">`, hapus `</div>` berlebih
- [ ] Tambah tes penjaga pola (pola terlarang di views)

### Langkah 3 — Menu terpusat

- [ ] Pisahkan menu admin ke `config/navigation.php` (tidak wajib per tugas — sudah di layout, cukup rapikan)
- [ ] Tambah flag `planned` untuk item luar BRD
- [ ] Perbaiki seluruh URL literal → `route()`
- [ ] Item `planned` tidak render di produksi

### Langkah 4 — Header dan semantik

- [ ] Hapus brand dari `x-tabler.header` (brand hanya di sidebar)
- [ ] Hapus hamburger yang menarget `#navbar-menu` (tidak ada) dari header
- [ ] Satu `<h1>` per halaman (sidebar brand = `<div>`, bukan `<h1>`; page-title = `<h1>`)
- [ ] Switcher menampilkan LSP aktif; tersembunyi jika hanya 1

### Langkah 5 — Tema dan gaya

- [ ] Hapus `bg-light` pada `<body>`, `bg-white` pada header, `text-dark` pada brand/judul
- [ ] Sidebar: ganti `navbar-dark bg-dark` → `data-bs-theme="dark"` pada `<aside>`
- [ ] Pindahkan `<style>` inline ke CSS
- [ ] Footer: hapus klaim statis, ambil nama dari `config('app.name')`

### Langkah 6 — Widget dashboard

- [ ] Onboarding checklist
- [ ] Antrean tindakan (dari widget yang sudah ada)
- [ ] Kesehatan sistem (ganti panel statis)
- [ ] Jadwal 7 hari (dari widget)
- [ ] Dokumen ringkas

### Langkah 7 — Keamanan

- [ ] Hapus Boost script dari produksi
- [ ] Tenant switch: session regenerate
- [ ] Lock via POST + CSRF
- [ ] Modal dokumen → route `/admin`

### Langkah 8 — Tes dan verifikasi

- [ ] Semua tes lulus
- [ ] `npm run build` sukses
- [ ] Perbarui `PROGRESS_TRACKER.md`
