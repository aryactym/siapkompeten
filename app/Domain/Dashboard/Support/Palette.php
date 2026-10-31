<?php

namespace App\Domain\Dashboard\Support;

class Palette
{
    public const SEMANTIC = [
        'kompeten' => 'chart-1',
        'belum_kompeten' => 'chart-2',
        'asesmen_lanjut' => 'chart-3',
        'menunggu' => 'chart-4',
        'disetujui' => 'chart-1',
        'ditolak' => 'chart-5',
    ];

    public static function color(string $key): string
    {
        return self::SEMANTIC[$key] ?? 'chart-1';
    }
}
