<?php

return [
    'version' => 1,

    'cache_ttl' => 300,

    'roles' => [
        'admin' => [
            ['key' => 'stat_ringkas', 'width' => 12],
            ['key' => 'pendaftaran_tren', 'width' => 8],
            ['key' => 'asesi_gender', 'width' => 4],
            ['key' => 'pendaftaran_funnel', 'width' => 6],
            ['key' => 'hasil_per_skema', 'width' => 6],
            ['key' => 'jadwal_keterisian', 'width' => 6],
            ['key' => 'asesi_usia', 'width' => 6],
            ['key' => 'asesi_wilayah', 'width' => 6],
            ['key' => 'antrean_tindakan', 'width' => 6],
            ['key' => 'jadwal_7_hari', 'width' => 6],
        ],
        'asesor' => [
            ['key' => 'stat_asesor', 'width' => 12],
            ['key' => 'tugas_tertunda', 'width' => 6],
            ['key' => 'jadwal_mendatang', 'width' => 6],
            ['key' => 'hasil_saya', 'width' => 6],
            ['key' => 'peserta_usia', 'width' => 3],
            ['key' => 'peserta_gender', 'width' => 3],
        ],
        'pimpinan' => [
            ['key' => 'stat_ringkas', 'width' => 12],
            ['key' => 'pendaftaran_tren', 'width' => 8],
            ['key' => 'asesi_gender', 'width' => 4],
            ['key' => 'hasil_per_skema', 'width' => 6],
            ['key' => 'kompeten_rate_per_tuk', 'width' => 6],
            ['key' => 'pembayaran_valid_bulanan', 'width' => 12],
        ],
    ],
];
