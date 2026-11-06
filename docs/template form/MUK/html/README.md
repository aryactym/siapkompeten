# Template HTML Formulir MUK

Hasil konversi seluruh `.docx` di folder induk menjadi template HTML A4 yang bisa
diisi dan dicetak (Cetak / Simpan PDF).

Buka [index.html](index.html) untuk daftar semua formulir.

## Isi

| Berkas | Keterangan |
| --- | --- |
| `*.html` | Satu template per dokumen, nama = `<KODE>_<Judul>.html` |
| `form.css` | Gaya bersama: halaman A4, tabel bergaris, isian, aturan cetak |
| `assets/` | Gambar/logo yang diambil dari dokumen asli |
| `index.html` | Daftar formulir + pencarian |
| `index.json` | Metadata hasil konversi (dipakai `index.html` dan generator) |

## Aturan cetak

- Ukuran A4, margin 15 mm, font Arial 11 pt.
- Tombol **Cetak / Simpan PDF** ada di kanan atas; otomatis hilang saat dicetak.
- Formulir panjang mengalir ke halaman berikutnya; header tabel diulang otomatis.

## Konvensi isian

- Sel kosong pada dokumen asli menjadi `<input type="text">`.
- Simbol ☐ menjadi `<input type="checkbox">`.
- `name` isian diambil dari label barisnya, mis. `nama_asesi`, `skema_judul`.

## Regenerasi

Dari folder induk (`template form/MUK`):

```bash
python docx2html.py
```

Skrip hanya menulis ulang berkas hasil konversi. Template yang ditulis tangan
(cover, FR.AK.01, FR.AK.04, FR.AK.07) disimpan di `../manual/` dan disalin apa
adanya ke folder ini.
