<?php

namespace App\Domain\Dashboard\Support;

use Carbon\Carbon;

class TimeSeries
{
    /**
     * @param array<string, int|float> $values keyed by "Y-m"
     * @return array{categories: array<int,string>, data: array<int,int|float>}
     */
    public static function fillMonths(int $tahun, array $values, array $labels = []): array
    {
        $categories = [];
        $data = [];

        for ($m = 1; $m <= 12; $m++) {
            $key = sprintf('%04d-%02d', $tahun, $m);
            $categories[] = $labels[$m - 1] ?? Carbon::create($tahun, $m, 1)->translatedFormat('M');
            $data[] = $values[$key] ?? 0;
        }

        return ['categories' => $categories, 'data' => $data];
    }
}
