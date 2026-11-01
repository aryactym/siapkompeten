<?php

namespace Database\Seeders;

use App\Models\ElemenKompetensi;
use App\Models\Institution;
use App\Models\KriteriaUnjukKerja;
use App\Models\Skema;
use App\Models\SkemaPersyaratan;
use App\Models\SkemaUnit;
use App\Models\Skkni;
use App\Models\Tuk;
use App\Models\UnitKompetensi;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('slug', 'siap-kompeten')->first();

        if (! $institution) {
            return;
        }

        // Set context tenant untuk LSP Siap Kompeten
        TenantContext::set($institution);

        // Update profil lembaga
        $institution->update([
            'nomor_lisensi' => 'BNSP-LSP-1882-ID',
            'masa_berlaku_lisensi' => now()->addYears(3),
            'jenis_lsp' => 'p3',
            'email' => 'sekretariat@siapkompeten.id',
            'telepon' => '021-87654321',
            'alamat' => 'Gedung Menara Kompetensi Lt. 8, Jl. TB Simatupang, Jakarta Selatan',
            'website' => 'https://siapkompeten.id',
            'nama_ketua' => 'Dr. Ir. H. Bambang Soedibyo, M.T.',
            'is_setup_completed' => true,
        ]);

        // 1. TUK (Tempat Uji Kompetensi)
        Tuk::firstOrCreate(
            ['institution_id' => $institution->id, 'kode_tuk' => 'TUK-SK-01'],
            [
                'nama_tuk' => 'TUK Mandiri SiapKompeten Jakarta',
                'jenis' => 'mandiri',
                'penanggung_jawab' => 'Agus Santoso, S.Kom',
                'telepon' => '021-87654322',
                'email' => 'tuk-jakarta@siapkompeten.id',
                'alamat' => 'Jl. TB Simatupang No. 12, Jakarta Selatan',
                'province_code' => null,
                'city_code' => null,
                'is_verified' => true,
                'is_active' => true,
            ]
        );

        Tuk::firstOrCreate(
            ['institution_id' => $institution->id, 'kode_tuk' => 'TUK-SK-02'],
            [
                'nama_tuk' => 'TUK Sewaktu Lab Komputer Gedung B',
                'jenis' => 'sewaktu',
                'penanggung_jawab' => 'Siti Rahmawati, M.T.',
                'telepon' => '021-87654323',
                'email' => 'tuk-lab-b@siapkompeten.id',
                'alamat' => 'Kawasan Industri Sentul, Kab. Bogor',
                'province_code' => null,
                'city_code' => null,
                'is_verified' => true,
                'is_active' => true,
            ]
        );

        // 2. SKKNI
        $skkni = Skkni::firstOrCreate(
            ['institution_id' => $institution->id, 'kode' => 'SKKNI-TIK-2020-056'],
            [
                'judul' => 'SKKNI Bidang Rekayasa Perangkat Lunak (Software Engineering)',
                'nomor_penetapan' => 'Kepmenaker No. 56 Tahun 2020',
                'tahun' => 2020,
                'is_active' => true,
            ]
        );

        // 3. Unit Kompetensi (Independent under SKKNI)
        $unit1 = UnitKompetensi::firstOrCreate(
            ['institution_id' => $institution->id, 'kode_unit' => 'J.620100.004.01'],
            [
                'skkni_id' => $skkni->id,
                'judul_unit' => 'Menggunakan Struktur Data',
                'standar_kompetensi' => 'SKKNI',
                'deskripsi' => 'Unit ini menentukan kompetensi dalam menerapkan struktur data algoritma.',
                'is_active' => true,
            ]
        );

        $unit2 = UnitKompetensi::firstOrCreate(
            ['institution_id' => $institution->id, 'kode_unit' => 'J.620100.005.02'],
            [
                'skkni_id' => $skkni->id,
                'judul_unit' => 'Mengimplementasikan User Interface',
                'standar_kompetensi' => 'SKKNI',
                'deskripsi' => 'Unit ini menentukan kompetensi dalam membangun antarmuka pengguna interaktif.',
                'is_active' => true,
            ]
        );

        $unit3 = UnitKompetensi::firstOrCreate(
            ['institution_id' => $institution->id, 'kode_unit' => 'J.620100.009.01'],
            [
                'skkni_id' => $skkni->id,
                'judul_unit' => 'Menggunakan Spesifikasi Program',
                'standar_kompetensi' => 'SKKNI',
                'deskripsi' => 'Unit ini menentukan kompetensi dalam menganalisis dokumen spesifikasi teknis.',
                'is_active' => true,
            ]
        );

        $unit4 = UnitKompetensi::firstOrCreate(
            ['institution_id' => $institution->id, 'kode_unit' => 'J.620100.017.02'],
            [
                'skkni_id' => $skkni->id,
                'judul_unit' => 'Mengimplementasikan Rancangan Entitas dan Relasi Basis Data',
                'standar_kompetensi' => 'SKKNI',
                'deskripsi' => 'Unit ini menentukan kompetensi dalam pemodelan data relasional dan optimasi query.',
                'is_active' => true,
            ]
        );

        $unit5 = UnitKompetensi::firstOrCreate(
            ['institution_id' => $institution->id, 'kode_unit' => 'J.620100.023.02'],
            [
                'skkni_id' => $skkni->id,
                'judul_unit' => 'Membuat Dokumentasi Kode Program',
                'standar_kompetensi' => 'SKKNI',
                'deskripsi' => 'Unit ini menentukan kompetensi dalam menyusun technical manual dan dokumentasi API.',
                'is_active' => true,
            ]
        );

        // 4. Elemen & KUK untuk Unit J.620100.005.02 (User Interface)
        $elemen1 = ElemenKompetensi::firstOrCreate(
            ['institution_id' => $institution->id, 'unit_kompetensi_id' => $unit2->id, 'urutan' => 1],
            ['nama_elemen' => 'Mengidentifikasi rancangan user interface dan komponen interaktif']
        );

        KriteriaUnjukKerja::firstOrCreate(
            ['institution_id' => $institution->id, 'elemen_kompetensi_id' => $elemen1->id, 'urutan' => '1.1'],
            [
                'pernyataan_kuk' => 'Rancangan antarmuka pengguna diidentifikasi sesuai spesifikasi kebutuhan pengguna (user requirements).',
                'pertanyaan_observasi' => 'Bagaimana Anda memastikan palet warna dan tipografi memenuhi standar aksesibilitas?',
            ]
        );

        KriteriaUnjukKerja::firstOrCreate(
            ['institution_id' => $institution->id, 'elemen_kompetensi_id' => $elemen1->id, 'urutan' => '1.2'],
            [
                'pernyataan_kuk' => 'Komponen visual dan alur navigasi dikonfirmasi dengan pedoman desain sistem.',
                'pertanyaan_lisan' => 'Sebutkan prinsip utama dalam perancangan navigasi yang ergonomis!',
            ]
        );

        $elemen2 = ElemenKompetensi::firstOrCreate(
            ['institution_id' => $institution->id, 'unit_kompetensi_id' => $unit2->id, 'urutan' => 2],
            ['nama_elemen' => 'Mengimplementasikan rancangan antarmuka pengguna responsif']
        );

        KriteriaUnjukKerja::firstOrCreate(
            ['institution_id' => $institution->id, 'elemen_kompetensi_id' => $elemen2->id, 'urutan' => '2.1'],
            [
                'pernyataan_kuk' => 'Komponen antarmuka pengguna dibuat menggunakan bahasa mark-up dan styling standar.',
                'pertanyaan_observasi' => 'Peragakan pembuatan komponen input form dengan validasi feedback real-time.',
            ]
        );

        KriteriaUnjukKerja::firstOrCreate(
            ['institution_id' => $institution->id, 'elemen_kompetensi_id' => $elemen2->id, 'urutan' => '2.2'],
            [
                'pernyataan_kuk' => 'Responsivitas antarmuka diuji pada berbagai resolusi layar perangkat.',
                'pertanyaan_tertulis' => 'Jelaskan perbedaan responsive breakpoints pada viewport mobile dan desktop!',
            ]
        );

        // 5. Skema Sertifikasi
        $skema1 = Skema::firstOrCreate(
            ['institution_id' => $institution->id, 'kode_skema' => 'SKM-PROG-01'],
            [
                'nama_skema' => 'Pemrogram Madya (Junior Software Developer)',
                'nomor_skema' => 'SS-01/TIK-JSD/2026',
                'jenis' => 'okupasi',
                'level_kkni' => 4,
                'kategori' => 'Teknologi Informasi',
                'deskripsi' => 'Skema sertifikasi okupasi nasional bagi calon pengembang perangkat lunak profesional.',
                'biaya' => 750000.00,
                'is_active' => true,
            ]
        );

        // Persyaratan Skema 1
        $persyaratan1 = [
            ['nama' => 'Pas Foto Formal (3x4)', 'ket' => 'Latar belakang merah untuk BNSP', 'req' => true, 'urutan' => 1],
            ['nama' => 'KTP / Tanda Pengenal Resmi', 'ket' => 'NIK 16 digit terdaftar', 'req' => true, 'urutan' => 2],
            ['nama' => 'Ijazah Terakhir / Surat Keterangan Mahasiswa/Siswa', 'ket' => 'Minimal SMK RPL atau D3/S1 Informatika', 'req' => true, 'urutan' => 3],
            ['nama' => 'Portofolio Source Code / Proyek Aplikasi', 'ket' => 'Tautan GitHub atau berkas ZIP aplikasi yang pernah dibuat', 'req' => false, 'urutan' => 4],
        ];

        foreach ($persyaratan1 as $p) {
            SkemaPersyaratan::firstOrCreate(
                ['institution_id' => $institution->id, 'skema_id' => $skema1->id, 'nama_dokumen' => $p['nama']],
                ['keterangan' => $p['ket'], 'is_required' => $p['req'], 'urutan' => $p['urutan']]
            );
        }

        // Many-to-Many Mapping untuk Skema 1
        SkemaUnit::firstOrCreate(
            ['institution_id' => $institution->id, 'skema_id' => $skema1->id, 'unit_kompetensi_id' => $unit1->id],
            ['jenis' => 'inti', 'urutan' => 1]
        );
        SkemaUnit::firstOrCreate(
            ['institution_id' => $institution->id, 'skema_id' => $skema1->id, 'unit_kompetensi_id' => $unit2->id],
            ['jenis' => 'inti', 'urutan' => 2]
        );
        SkemaUnit::firstOrCreate(
            ['institution_id' => $institution->id, 'skema_id' => $skema1->id, 'unit_kompetensi_id' => $unit3->id],
            ['jenis' => 'inti', 'urutan' => 3]
        );

        // Skema 2: Fullstack Web Developer (Membuktikan N:M, Unit 2 dipakai bersama!)
        $skema2 = Skema::firstOrCreate(
            ['institution_id' => $institution->id, 'kode_skema' => 'SKM-WEB-02'],
            [
                'nama_skema' => 'Pengembang Web Fullstack (Fullstack Web Developer)',
                'nomor_skema' => 'SS-02/TIK-FWD/2026',
                'jenis' => 'okupasi',
                'level_kkni' => 5,
                'kategori' => 'Teknologi Informasi',
                'deskripsi' => 'Skema sertifikasi bagi profesional perancang dan pembangun aplikasi web frontend & backend.',
                'biaya' => 1200000.00,
                'is_active' => true,
            ]
        );

        // Many-to-Many Mapping untuk Skema 2 (Memakai Unit 2 & Unit 4 & Unit 5)
        SkemaUnit::firstOrCreate(
            ['institution_id' => $institution->id, 'skema_id' => $skema2->id, 'unit_kompetensi_id' => $unit2->id],
            ['jenis' => 'inti', 'urutan' => 1]
        );
        SkemaUnit::firstOrCreate(
            ['institution_id' => $institution->id, 'skema_id' => $skema2->id, 'unit_kompetensi_id' => $unit4->id],
            ['jenis' => 'inti', 'urutan' => 2]
        );
        SkemaUnit::firstOrCreate(
            ['institution_id' => $institution->id, 'skema_id' => $skema2->id, 'unit_kompetensi_id' => $unit5->id],
            ['jenis' => 'pilihan', 'urutan' => 3]
        );

        // =========================================================================
        // DATA UNTUK TENANT KEDUA: LSP MITRA NUSANTARA
        // =========================================================================
        $mitra = Institution::where('slug', 'mitra-nusantara')->first();

        if ($mitra) {
            TenantContext::set($mitra);

            $mitra->update([
                'nomor_lisensi' => 'BNSP-LSP-2041-ID',
                'masa_berlaku_lisensi' => now()->addYears(2),
                'jenis_lsp' => 'p3',
                'email' => 'kontak@mitranusantara.id',
                'telepon' => '031-76543210',
                'alamat' => 'Graha Mitra Lt. 4, Jl. Pemuda, Surabaya, Jawa Timur',
                'website' => 'https://mitranusantara.id',
                'nama_ketua' => 'Dra. Hj. Nurul Hidayati, M.M.',
                'is_setup_completed' => true,
            ]);

            Tuk::firstOrCreate(
                ['institution_id' => $mitra->id, 'kode_tuk' => 'TUK-MN-01'],
                [
                    'nama_tuk' => 'TUK Sewaktu Kampus Surabaya',
                    'jenis' => 'sewaktu',
                    'penanggung_jawab' => 'Rudi Hartono, S.T.',
                    'telepon' => '031-76543211',
                    'email' => 'tuk-surabaya@mitranusantara.id',
                    'alamat' => 'Jl. Pemuda No. 45, Kota Surabaya',
                    'province_code' => null,
                    'city_code' => null,
                    'is_verified' => true,
                    'is_active' => true,
                ]
            );

            $skkniMitra = Skkni::firstOrCreate(
                ['institution_id' => $mitra->id, 'kode' => 'SKKNI-K3-2022-108'],
                [
                    'judul' => 'SKKNI Keselamatan dan Kesehatan Kerja (K3)',
                    'nomor_penetapan' => 'Kepmenaker No. 108 Tahun 2022',
                    'tahun' => 2022,
                    'is_active' => true,
                ]
            );

            $unitK3 = UnitKompetensi::firstOrCreate(
                ['institution_id' => $mitra->id, 'kode_unit' => 'M.71KKK01.001.1'],
                [
                    'skkni_id' => $skkniMitra->id,
                    'judul_unit' => 'Menerapkan Prinsip Keselamatan dan Kesehatan Kerja',
                    'standar_kompetensi' => 'SKKNI',
                    'is_active' => true,
                ]
            );

            $skemaMitra = Skema::firstOrCreate(
                ['institution_id' => $mitra->id, 'kode_skema' => 'SKM-K3-01'],
                [
                    'nama_skema' => 'Petugas K3 Muda (Junior Safety Officer)',
                    'nomor_skema' => 'SS-01/K3-JSO/2026',
                    'jenis' => 'okupasi',
                    'level_kkni' => 4,
                    'kategori' => 'Keselamatan Kerja',
                    'biaya' => 850000.00,
                    'is_active' => true,
                ]
            );

            SkemaUnit::firstOrCreate(
                ['institution_id' => $mitra->id, 'skema_id' => $skemaMitra->id, 'unit_kompetensi_id' => $unitK3->id],
                ['jenis' => 'inti', 'urutan' => 1]
            );
        }

        TenantContext::clear();
    }
}
