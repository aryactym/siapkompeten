<?php

namespace App\Imports;

use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class WilayahSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @return array<int, mixed>
     */
    public function headings(): array
    {
        return ['jenis', 'kode', 'nama', 'kode_induk'];
    }

    /**
     * @return array<int, array<array-key, mixed>>
     */
    public function array(): array
    {
        $provinces = Province::orderBy('code')->get(['code', 'name']);

        $rows = $provinces
            ->map(fn (Province $province): array => [
                'provinsi',
                $province->code,
                $province->name,
                null,
            ])
            ->all();

        $cities = City::orderBy('code')->get(['code', 'name', 'province_code']);

        foreach ($cities as $city) {
            $rows[] = [
                'kota',
                $city->code,
                $city->name,
                $city->province_code,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Kode Wilayah';
    }
}
