<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AsesiTemplateSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @return array<int, mixed>
     */
    public function headings(): array
    {
        return [
            'nama',
            'nik',
            'email',
            'telepon',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'kode_provinsi',
            'kode_kota',
            'kode_kecamatan',
            'pendidikan_terakhir',
            'pekerjaan',
            'jabatan',
        ];
    }

    /**
     * @return array<int, array<array-key, mixed>>
     */
    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Data Asesi';
    }
}
