# Template Formulir Asesmen BNSP (MAK-01, MAK-02, MAK-03 / FR.AK)

Dokumen ini merangkum struktur standar formulir BNSP yang diekstrak dari `docs/template/Template_Formulir_MAK_BNSP.docx` sebagai acuan pembuatan modul asesmen (Fase 5) dan generator PDF (`resources/views/pdf/*`).

---

## 1. MAK-01 / FR.AK.01: Formulir Persetujuan Asesmen dan Kerahasiaan

Digunakan sebelum uji kompetensi dimulai. Memastikan asesi memahami proses asesmen dan asesor berkomitmen menjaga kerahasiaan.

### Kolom Identitas:
- **Judul Skema Sertifikasi**
- **Nomor Skema Sertifikasi**
- **Nama Asesor**
- **Nama Asesi / Peserta**

### Klausul & Persetujuan:
1. **Komitmen dan Persetujuan Asesi**:
   > *"Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya digunakan untuk pengembangan profesional dan hanya dapat diakses oleh pihak yang berwenang."*
   - Tanda tangan Asesi + Tanggal
2. **Komitmen Kerahasiaan Asesor**:
   > *"Saya menyatakan tidak akan membuka hasil pekerjaan asesi kepada pihak mana pun selain pihak yang berwenang."*
   - Tanda tangan Asesor + Tanggal

---

## 2. MAK-02 / FR.AK.05: Formulir Hasil / Laporan Penilaian Asesmen

Digunakan oleh asesor untuk mencatat penilaian per unit kompetensi dan memberikan rekomendasi akhir.

### Kolom Identitas:
- **Nama Asesi**
- **Nama Asesor**
- **Tanggal Asesmen**

### Rekomendasi Hasil Akhir:
- `[ ]` **KOMPETEN (K)**
- `[ ]` **BELUM KOMPETEN (BK)**

### Tabel Capaian Unit Kompetensi:
| No | Kode Unit | Judul Unit Kompetensi | Capaian K | Capaian BK |
|:---|:---|:---|:---:|:---:|
| 1  | LOG.OO01.001.01 | Menerapkan Prosedur Keselamatan Kerja | [ ] | [ ] |
| ...| ... | ... | [ ] | [ ] |

### Umpan Balik dan Catatan Asesmen:
- **Aspek Positif / Kekuatan Asesi**: (Teks catatan kekuatan)
- **Aspek Negatif / Area Perbaikan**: (Teks catatan perbaikan)
- **Saran Tindak Lanjut**: (Teks rekomendasi asesmen ulang/pelatihan)

---

## 3. MAK-03 / FR.AK.04: Formulir Banding Asesmen

Digunakan apabila asesi merasa keputusan rekomendasi asesor tidak adil atau tidak sesuai bukti.

### Pertanyaan Klarifikasi (Ya / Tidak):
1. Apakah proses banding telah dijelaskan kepada Anda? (`Ya` / `Tidak`)
2. Apakah Anda telah mendiskusikan banding dengan Asesor? (`Ya` / `Tidak`)
3. Apakah Anda mau melibatkan orang lain untuk membantu Anda? (`Ya` / `Tidak`)

### Form Pengajuan:
- **Alasan Pengajuan Banding oleh Asesi**: (Textarea penjelasan keberatan)
- **Tanda Tangan Pemohon (Asesi)** + Tanggal
- **Tanda Tangan Penerima (LSP)** + Tanggal
