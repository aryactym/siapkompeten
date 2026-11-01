<?php

namespace Database\Seeders;

use App\Models\ElemenKompetensi;
use App\Models\Institution;
use App\Models\KriteriaUnjukKerja;
use App\Models\Skema;
use App\Models\SkemaPersyaratan;
use App\Models\SkemaUnit;
use App\Models\Skkni;
use App\Models\UnitKompetensi;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SkemaMenjahitPakaianWanitaSeeder extends Seeder
{
    /**
     * Data unit kompetensi dan elemennya (KUK 2-3 per elemen).
     *
     * @var array<int, array{kode: string, judul: string, elemen: array<int, string>}>
     */
    protected array $units = [
        [
            'kode' => 'GAR.CM01.003.01',
            'judul' => 'Mengikuti Prosedur Kesehatan, Keselamatan dan Keamanan Dalam Bekerja (K3)',
            'elemen' => [
                'Mengikuti prosedur K3 yang berlaku di tempat kerja',
                'Melaporkan bahaya di tempat kerja',
                'Mengikuti prosedur dalam keadaan darurat',
            ],
        ],
        [
            'kode' => 'GAR.CM01.004.01',
            'judul' => 'Memelihara Alat Jahit (Maintenance & Repair)',
            'elemen' => [
                'Melakukan pembersihan dan pelumasan mesin jahit',
                'Melakukan penyetelan dan perbaikan ringan mesin jahit',
                'Memelihara peralatan pendukung (gunting, setrika, dan alat jahit lainnya)',
            ],
        ],
        [
            'kode' => 'GAR.CM02.008.01',
            'judul' => 'Menjahit dengan Mesin (Sewing)',
            'elemen' => [
                'Menyiapkan mesin jahit, bahan, dan perlengkapan menjahit',
                'Menjahit komponen busana sesuai urutan dan prosedur kerja',
                'Menjahit dengan mesin sesuai standar mutu',
                'Memeriksa hasil jahitan',
            ],
        ],
        [
            'kode' => 'GAR.CM02.009.01',
            'judul' => 'Menyelesaikan Busana dengan Jahitan Tangan (Hand Sewing)',
            'elemen' => [
                'Menyiapkan alat dan bahan jahit tangan',
                'Menjahit tangan sesuai jenis dan fungsi jahitan',
                'Memeriksa hasil jahitan tangan',
            ],
        ],
        [
            'kode' => 'GAR.CM02.010.01',
            'judul' => 'Melakukan Pengepresan (Pressing)',
            'elemen' => [
                'Menyiapkan alat dan bahan pengepresan',
                'Melakukan pengepresan sesuai jenis bahan dan komponen busana',
                'Memeriksa hasil pengepresan',
            ],
        ],
    ];

    public function run(): void
    {
        $institutions = [
            'siap-kompeten' => null, // LSP default, profil sudah diisi MasterDataSeeder
        ];

        foreach ($institutions as $slug => $name) {
            DB::transaction(function () use ($slug, $name): void {
                $institution = Institution::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name ?? 'LSP Siap Kompeten', 'is_active' => true, 'is_setup_completed' => true]
                );

                if ($name !== null) {
                    $institution->update([
                        'jenis_lsp' => 'p3',
                        'is_setup_completed' => true,
                    ]);
                }

                TenantContext::set($institution);

                $this->seedInstitution($institution);
            });
        }

        TenantContext::clear();
    }

    protected function seedInstitution(Institution $institution): void
    {
        $upsert = function (string $model, array $match, array $values): object {
            $instance = $model::where($match)->first();

            if ($instance) {
                $instance->update($values);

                return $instance;
            }

            return $model::create(array_merge($match, $values, ['id' => (string) Str::uuid()]));
        };

        // 1. SKKNI
        $skkni = $upsert(Skkni::class, [
            'institution_id' => $institution->id,
            'kode' => 'SKKNI-GARMENT-2023',
        ], [
            'judul' => 'SKKNI Bidang Garment (Penjahitan Pakaian)',
            'nomor_penetapan' => 'KEP.0325/BNSP/II/2023',
            'tahun' => 2023,
            'is_active' => true,
        ]);

        // 2. Unit Kompetensi + Elemen + KUK
        $units = [];
        foreach ($this->units as $unitData) {
            $unit = $upsert(UnitKompetensi::class, [
                'institution_id' => $institution->id,
                'kode_unit' => $unitData['kode'],
            ], [
                'skkni_id' => $skkni->id,
                'judul_unit' => $unitData['judul'],
                'standar_kompetensi' => 'SKKNI',
                'deskripsi' => 'Unit kompetensi pada skema Menjahit Pakaian Wanita Dewasa.',
                'is_active' => true,
            ]);

            $units[] = $unit;

            foreach (array_values($unitData['elemen']) as $elemenIndex => $namaElemen) {
                $urutan = $elemenIndex + 1;

                $elemen = $upsert(ElemenKompetensi::class, [
                    'institution_id' => $institution->id,
                    'unit_kompetensi_id' => $unit->id,
                    'urutan' => $urutan,
                ], [
                    'nama_elemen' => $namaElemen,
                ]);

                $kukCount = $urutan % 2 === 0 ? 3 : 2;

                for ($k = 1; $k <= $kukCount; $k++) {
                    $upsert(KriteriaUnjukKerja::class, [
                        'institution_id' => $institution->id,
                        'elemen_kompetensi_id' => $elemen->id,
                        'urutan' => $urutan.'.'.$k,
                    ], [
                        // TODO: verifikasi dengan dokumen SKKNI — butir KUK resmi harus dicocokkan dengan dokumen skema BNSP.
                        'pernyataan_kuk' => sprintf(
                            'Butir unjuk kerja %d.%d (draft, perlu verifikasi SKKNI).',
                            $urutan,
                            $k
                        ),
                        'pertanyaan_observasi' => 'Peragakan pelaksanaan sesuai prosedur standar.',
                    ]);
                }
            }
        }

        // 3. Skema Sertifikasi
        $skema = $upsert(Skema::class, [
            'institution_id' => $institution->id,
            'kode_skema' => 'SKM-MPW-01',
        ], [
            'nama_skema' => 'Menjahit Pakaian Wanita Dewasa',
            'nomor_skema' => 'KEP.0325/BNSP/II/2023',
            'jenis' => 'okupasi',
            'level_kkni' => 2,
            'kategori' => 'Garment / Tata Busana',
            'deskripsi' => 'Skema sertifikasi okupasi nasional untuk kompetensi menjahit pakaian wanita dewasa, dinaungi SKKNI Bidang Garment.',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $persyaratan = [
            ['nama' => 'Pas Foto Formal (3x4)', 'ket' => 'Latar belakang merah', 'req' => true, 'urutan' => 1],
            ['nama' => 'KTP / Tanda Pengenal Resmi', 'ket' => 'NIK 16 digit terdaftar', 'req' => true, 'urutan' => 2],
            ['nama' => 'Ijazah Terakhir', 'ket' => 'Minimal SMP/sederajat', 'req' => true, 'urutan' => 3],
            ['nama' => 'Portofolio Hasil Jahitan', 'ket' => 'Contoh busana yang pernah dijahit', 'req' => false, 'urutan' => 4],
        ];

        foreach ($persyaratan as $p) {
            $upsert(SkemaPersyaratan::class, [
                'institution_id' => $institution->id,
                'skema_id' => $skema->id,
                'nama_dokumen' => $p['nama'],
            ], [
                'keterangan' => $p['ket'],
                'is_required' => $p['req'],
                'urutan' => $p['urutan'],
            ]);
        }

        foreach (array_values($units) as $i => $unit) {
            $upsert(SkemaUnit::class, [
                'institution_id' => $institution->id,
                'skema_id' => $skema->id,
                'unit_kompetensi_id' => $unit->id,
            ], [
                'jenis' => 'inti',
                'urutan' => $i + 1,
            ]);
        }
    }
}
