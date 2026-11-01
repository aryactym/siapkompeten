<?php

namespace Database\Seeders;

use App\Models\ElemenKompetensi;
use App\Models\Institution;
use App\Models\KriteriaUnjukKerja;
use App\Models\Skema;
use App\Models\SkemaUnit;
use App\Models\Skkni;
use App\Models\UnitKompetensi;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;

class KualifikasiProduktivitasSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('slug', 'siap-kompeten')->first();
        if (!$institution) return;

        TenantContext::set($institution);

        // SKKNI
        $skkni = Skkni::firstOrCreate(
            ['institution_id' => $institution->id, 'kode' => 'SKKNI-PRO-2024-001'],
            [
                'judul' => 'SKKNI Bidang Produktivitas',
                'nomor_penetapan' => 'Kepmenaker Tahun 2024',
                'tahun' => 2024,
                'is_active' => true,
            ]
        );

        // Skema
        $skema = Skema::firstOrCreate(
            ['institution_id' => $institution->id, 'kode_skema' => 'SKM-PROD-001'],
            [
                'nama_skema' => 'Kualifikasi 5 Bidang Produktivitas',
                'jenis' => 'okupasi',
                'biaya' => 0,
                'is_active' => true,
            ]
        );

        // Unit Kompetensi dan Elemen/KUK
        $units = [
            [
                'kode' => 'M.70PRO00.001.3',
                'judul' => 'Menjelaskan Konsep-Konsep dan Mindset (Pola Pikir) Produktivitas',
                'elemen' => [
                    [
                        'nama' => 'Menjelaskan konsep Produktivitas',
                        'kuks' => [
                            'Definisi Produktivitas dijelaskan sesuai dengan literatur dan ketentuan yang berlaku.',
                            'Tujuan peningkatan Produktivitas dijelaskan sesuai konteks dan strategi organisasi.',
                            'Faktor-faktor yang mempengaruhi Produktivitas diuraikan secara sistematis.',
                            'Pengertian continuous improvement (peningkatan berkelanjutan) dijelaskan sesuai standar manajemen mutu dan Produktivitas serta dapat memberikan contoh penerapannya.',
                        ]
                    ],
                    [
                        'nama' => 'Menjelaskan mindset (pola pikir) Produktivitas',
                        'kuks' => [
                            'Mindset (pola pikir) Produktivitas didefinisikan sesuai dengan prinsip-prinsip budaya kerja organisasi.',
                            'Peran mindset (pola pikir) dalam peningkatan Produktivitas diuraikan disertai dengan contoh kasus atau studi kasus yang relevan.',
                            'Strategi pembentukan dan pengembangan mindset (pola pikir) positif untuk Produktivitas yang dapat diterapkan, dirumuskan kebutuhan organisasi.',
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'M.70PRO00.003.3',
                'judul' => 'Melakukan Pengukuran Produktivitas',
                'elemen' => [
                    [
                        'nama' => 'Mengumpulkan data dan informasi Produktivitas',
                        'kuks' => [
                            'Sumber data dan informasi Produktivitas mikro dan makro dikumpulkan sesuai dengan kebutuhan organisasi.',
                            'Sumber data dan informasi diidentifikasi secara akurat dan relevansinya dengan pengukuran Produktivitas mikro dan makro.',
                            'Data dan informasi yang dikumpulkan diverifikasi untuk memastikan keakuratan dan kelengkapannya.',
                        ]
                    ],
                    [
                        'nama' => 'Menetapkan metode pengukuran Produktivitas',
                        'kuks' => [
                            'Jenis-jenis metode pengukuran Produktivitas mikro (misalnya, Produktivitas tenaga kerja) dan makro (misalnya, Total Factor Productivity pada sektor industri) diidentifikasi sesuai kebutuhan.',
                            'Rasio atau indikator Produktivitas ditetapkan sesuai dengan metode yang dipilih untuk setiap skala pengukuran.',
                        ]
                    ],
                    [
                        'nama' => 'Mengukur Produktivitas',
                        'kuks' => [
                            'Data terkait pengukuran Produktivitas mikro dan makro diolah sesuai dengan metode yang telah ditetapkan.',
                            'Hasil pengukuran dianalisis dan diinterpretasi, serta mengidentifikasi faktor-faktor yang mempengaruhinya pada tingkat mikro dan makro.',
                            'Rasio-rasio Produktivitas diukur sesuai kebutuhan.',
                            'Laporan hasil pengukuran disusun, memuat analisis dan interpretasi untuk tingkat mikro dan makro, serta dan disampaikan kepada pihak terkait sesuai dengan prosedur.',
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'M.70PRO00.005.3',
                'judul' => 'Menerapkan Alat, Teknik, dan Metode Peningkatan Produktivitas',
                'elemen' => [
                    [
                        'nama' => 'Merencanakan penerapan alat, teknik, dan metode peningkatan Produktivitas',
                        'kuks' => [
                            'Tujuan dan sasaran penerapan alat, teknik, dan metode peningkatan Produktivitas ditetapkan sesuai dengan kebutuhan organisasi.',
                            'Rencana penerapan alat, teknik, dan metode peningkatan Produktivitas disusun dengan mempertimbangkan sumber daya yang tersedia dan target yang ingin dicapai.',
                            'Alat, teknik, dan metode yang akan diterapkan dipilih berdasarkan kesesuaian dengan kondisi dan kebutuhan organisasi.',
                        ]
                    ],
                    [
                        'nama' => 'Melaksanakan penerapan alat, teknik, dan metode peningkatan Produktivitas',
                        'kuks' => [
                            'Alat, teknik, dan metode yang akan diterapkan pada masing-masing fokus area disiapkan secara lengkap.',
                            'Indikator keberhasilan penerapan alat, teknik, dan metode peningkatan Produktivitas ditetapkan.',
                            'Penerapan alat, teknik, dan metode peningkatan Produktivitas dilakukan sesuai dengan prosedur dan standar yang telah ditetapkan.',
                        ]
                    ],
                    [
                        'nama' => 'Mengevaluasi hasil penerapan alat, teknik, dan metode peningkatan Produktivitas',
                        'kuks' => [
                            'Hasil penerapan alat, teknik, dan metode peningkatan Produktivitas dievaluasi berdasarkan indikator kinerja yang telah ditetapkan.',
                            'Rekomendasi perbaikan yang diperlukan disusun secara tepat berdasarkan hasil evaluasi.',
                            'Hasil penerapan alat, teknik, dan metode peningkatan Produktivitas didokumentasikan.',
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'M.70PRO00.002.3',
                'judul' => 'Merencanakan Program Peningkatan Produktivitas',
                'elemen' => [
                    [
                        'nama' => 'Mengumpulkan data dan informasi peningkatan Produktivitas',
                        'kuks' => [
                            'Data dan informasi terkait dengan rencana program peningkatan Produktivitas diidentifikasi',
                            'Data dan informasi rencana program peningkatan Produktivitas yang diperlukan dikumpulkan menggunakan metode yang tepat.',
                        ]
                    ],
                    [
                        'nama' => 'Menganalisis data dan informasi peningkatan Produktivitas',
                        'kuks' => [
                            'Data dan informasi diolah secara komprehensif dengan menggunakan metode yang tepat',
                            'Kekuatan, kelemahan, peluang, dan tantangan diidentifikasi secara tepat beserta rekomendasi penanggulangan dan pemanfaatannya',
                            'Laporan hasil analisis data dan informasi disusun sesuai dengan format yang berlaku di organisasi',
                        ]
                    ],
                    [
                        'nama' => 'Menyusun rencana program peningkatan Produktivitas',
                        'kuks' => [
                            'Rencana program peningkatan Produktivitas disusun secara komprehensif berdasarkan hasil analisis data dan informasi dan dengan mempertimbangkan ketersediaan sumber daya, dan prioritas',
                            'Organisasi dan sumber daya untuk pelaksanaan rencana program peningkatan Produktivitas disiapkan secara tepat dan lengkap, termasuk jadwal pelaksanaannya',
                            'Indikator keberhasilan rencana program ditetapkan.',
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'M.70PRO00.004.3',
                'judul' => 'Menganalisis dan Melaporkan Hasil Pengukuran Produktivitas',
                'elemen' => [
                    [
                        'nama' => 'Mengumpulkan data hasil pengukuran Produktivitas',
                        'kuks' => [
                            'Data hasil pengukuran Produktivitas dikumpulkan',
                            'Keakuratan data diverifikasi untuk memastikan validitas analisis.',
                            'Data hasil verifikasi disajikan dalam format yang sesuai untuk memudahkan proses analisis.',
                        ]
                    ],
                    [
                        'nama' => 'Menganalisis tren dan pola Produktivitas',
                        'kuks' => [
                            'Data hasil pengukuran Produktivitas dianalisis untuk mengidentifikasi perubahan tingkat Produktivitas.',
                            'Faktor-faktor yang mempengaruhi tingkat Produktivitas dianalisis berdasarkan hubungan sebab akibat.',
                            'Hasil pengukuran Produktivitas diperbandingkan dengan target dan/atau benchmark.',
                        ]
                    ],
                    [
                        'nama' => 'Mengidentifikasi penyebab ketidaksesuaian Produktivitas',
                        'kuks' => [
                            'Area dengan kinerja Produktivitas yang tidak sesuai dengan target diidentifikasi.',
                            'Penyebab utama ketidaksesuaian Produktivitas dianalisis menggunakan metode yang sesuai.',
                            'Faktor internal dan eksternal yang mempengaruhi Produktivitas dikaji untuk memahami dampaknya secara menyeluruh.',
                            'Hasil analisis penyebab ketidaksesuaian didokumentasikan untuk referensi dalam perbaikan Produktivitas.',
                        ]
                    ],
                    [
                        'nama' => 'Menyusun laporan dan rekomendasi peningkatan Produktivitas',
                        'kuks' => [
                            'Solusi dan rekomendasi perbaikan Produktivitas disusun berdasarkan hasil analisis.',
                            'Alternatif solusi dievaluasi untuk memilih strategi peningkatan Produktivitas yang paling efektif.',
                            'Rekomendasi peningkatan Produktivitas dikomunikasikan kepada pihak terkait.',
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'M.70PRO00.006.3',
                'judul' => 'Mengimplementasikan Rencana Peningkatan Produktivitas',
                'elemen' => [
                    [
                        'nama' => 'Menetapkan sasaran implementasi rencana peningkatan Produktivitas',
                        'kuks' => [
                            'Sasaran implementasi Peningkatan Produktivitas ditetapkan berdasarkan analisis data dan kebutuhan organisasi.',
                            'Sasaran peningkatan Produktivitas disusun secara Specific, Measurable, Achievable, Relevant, Time-bound (SMART).',
                        ]
                    ],
                    [
                        'nama' => 'Melaksanakan rencana peningkatan Produktivitas',
                        'kuks' => [
                            'Langkah-langkah implementasi dirancang secara sistematis.',
                            'Indikator keberhasilan capaian rencana Peningkatan Produktivitas ditetapkan sesuai dengan sasaran Peningkatan Produktivitas.',
                            'Rencana implementasi Peningkatan Produktivitas dilaksanakan sesuai sasaran Peningkatan Produktivitas.',
                        ]
                    ],
                    [
                        'nama' => 'Menyusun pengendalian implementasi rencana peningkatan Produktivitas',
                        'kuks' => [
                            'Sasaran pengendalian ditetapkan sesuai dengan rencana implementasi Peningkatan Produktivitas.',
                            'Metode pengendalian ditetapkan sesuai dengan prosedur.',
                            'Rencana pelaksanaan pengendalian disusun dengan indikator keberhasilan.',
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'M.70PRO00.007.3',
                'judul' => 'Melakukan Monitoring dan Evaluasi Pelaksanaan Peningkatan Produktivitas',
                'elemen' => [
                    [
                        'nama' => 'Merencanakan monitoring dan evaluasi peningkatan Produktivitas',
                        'kuks' => [
                            'Tujuan dan sasaran monitoring dan evaluasi ditetapkan sesuai dengan target peningkatan Produktivitas.',
                            'Pihak terkait dalam monitoring ditetapkan.',
                            'Metode dan alat monitoring serta evaluasi dipilih sesuai dengan kebutuhan dan karakteristik pekerjaan.',
                            'Jadwal dan sumber daya yang diperlukan untuk monitoring dan evaluasi disusun secara terstruktur.',
                        ]
                    ],
                    [
                        'nama' => 'Melaksanakan monitoring peningkatan Produktivitas',
                        'kuks' => [
                            'Pelaksanaan peningkatan Produktivitas dimonitor sesuai dengan rencana yang telah disusun.',
                            'Data dan informasi terkait dengan proses dan hasil peningkatan Produktivitas dikumpulkan secara lengkap, akurat, dan sistematis.',
                            'Indikator kinerja Produktivitas dipantau secara berkala sesuai dengan standar yang ditetapkan.',
                        ]
                    ],
                    [
                        'nama' => 'Melaksanakan evaluasi peningkatan Produktivitas',
                        'kuks' => [
                            'Data hasil monitoring dianalisis untuk menilai tingkat pencapaian target Produktivitas.',
                            'Faktor-faktor yang mempengaruhi Produktivitas diidentifikasi dan dievaluasi.',
                            'Rekomendasi perbaikan disusun berdasarkan hasil evaluasi.',
                            'Hasil monitoring dan evaluasi didokumentasikan.',
                        ]
                    ],
                    [
                        'nama' => 'Melaporkan hasil monitoring dan evaluasi peningkatan Produktivitas',
                        'kuks' => [
                            'Laporan hasil monitoring dan evaluasi disusun.',
                            'Rekomendasi hasil monitoring dan evaluasi disampaikan kepada pihak terkait.',
                        ]
                    ],
                ]
            ],
            [
                'kode' => 'N.78SPS02.061.1',
                'judul' => 'Melakukan Komunikasi di Tempat Kerja',
                'elemen' => [
                    [
                        'nama' => 'Mengkomunikasikan tentang nilai-nilai perusahaan/tempat pelaksanaan pelatihan',
                        'kuks' => [
                            'Nilai-nilai perusahan yang dianut serta peraturan perusahaan diidentifikasi sesuai jenjang pelatihan dan tanggung jawabnya.',
                            'Nilai-nilai dan peraturan dikomunikasi kan secara terus menerus dan bertahap untuk dijadikan budaya.',
                            'Cara untuk berkomunikasi dengan atasan dan bawahan bila ada kondisi abnormal ditentukan sesuai situasi dan kondisi.',
                        ]
                    ],
                    [
                        'nama' => 'Mengkomunikasikan informasi tentang tugas, proses, peristiwa atau kejadian',
                        'kuks' => [
                            'Teknik komunikasi yang akan digunakan misalnya telepon, secara langsung, laporan tertulis, sketsa-sketsa dan sebagainya dipilih secara tepat.',
                            'Pengoperasian ganda yang melibatkan beberapa topik/ area dikomunikasikan pada pihak terkait.',
                            'Simak aktif dilakukan tanpa menginterupsi pembicara yang sedang berbicara.',
                            'Sumber-sumber informasi yang benar dikenali.',
                            'Informasi dipilih dan diurutkan dengan tepat, dan dilaporkan secara lisan dan tertulis bila perlu.',
                        ]
                    ],
                    [
                        'nama' => 'Melakukan partisipasi dalam diskusi kelompok untuk mencapai hasil kerja yang tepat',
                        'kuks' => [
                            'Tanggapan-tanggapan didiskusikan dengan orang – orang dalam kelompok.',
                            'Kontribusi yang membangun dibuat berkenaan dengan proses produksi terkait.',
                            'Cita-cita dan tujuan dikomunikasikan dengan anggota kelompok.',
                        ]
                    ],
                    [
                        'nama' => 'Menyampaikan pandangan kelompok terhadap orang lain',
                        'kuks' => [
                            'Pandangan, pendapat orang lain dimengerti dan digambarkan dengan akurat.',
                            'Orang yang sedang menyampaikan pendapatnya didengar dengan baik.',
                        ]
                    ],
                    [
                        'nama' => 'Memberi tanggapan terhadap keluhan pelanggan',
                        'kuks' => [
                            'Keluhan-keluhan ditangani secara bijaksana, sopan dan ramah.',
                            'Tanggung jawab diambil untuk memecahkan masalah keluahan tersebut.',
                            'Sifat dan rincian keluhan dibuktikan dan disetujui oleh pelanggan.',
                            'Tindakan yang tepat diambil untuk memecahkan keluhan demi kepuasan pelanggan.',
                            'Teknik-teknik digunakan untuk mengubah keluhan menjadi kesempatan untuk mendemonstrasikan pelayanan yang berkualitas tinggi.',
                        ]
                    ],
                ]
            ],
        ];

        // Seed units & elements & KUKs
        foreach ($units as $unitIdx => $unitData) {
            $unit = UnitKompetensi::firstOrCreate(
                ['institution_id' => $institution->id, 'kode_unit' => $unitData['kode']],
                [
                    'skkni_id' => $skkni->id,
                    'judul_unit' => $unitData['judul'],
                    'standar_kompetensi' => 'SKKNI',
                    'deskripsi' => $unitData['judul'],
                    'is_active' => true,
                ]
            );

            // Link ke skema
            SkemaUnit::firstOrCreate(
                ['skema_id' => $skema->id, 'unit_kompetensi_id' => $unit->id],
                [
                    'jenis' => 'inti',
                    'urutan' => $unitIdx + 1,
                ]
            );

            // Elemen & KUKs
            foreach ($unitData['elemen'] as $elemenIdx => $elemenData) {
                $elemen = ElemenKompetensi::firstOrCreate(
                    ['unit_kompetensi_id' => $unit->id, 'nama_elemen' => $elemenData['nama']],
                    [
                        'urutan' => $elemenIdx + 1,
                    ]
                );

                foreach ($elemenData['kuks'] as $kukIdx => $kukPernyataan) {
                    KriteriaUnjukKerja::firstOrCreate(
                        ['elemen_kompetensi_id' => $elemen->id, 'pernyataan_kuk' => $kukPernyataan],
                        [
                            'urutan' => $kukIdx + 1,
                        ]
                    );
                }
            }
        }
    }
}
