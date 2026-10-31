<?php

namespace App\Imports;

use App\Models\ImportBatch;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ImportErrorReport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly ImportBatch $batch) {}

    /**
     * @return array<int, mixed>
     */
    public function headings(): array
    {
        return ['Baris', 'Status', 'Nama', 'NIK', 'Email', 'Keterangan'];
    }

    /**
     * @return array<int, array<array-key, mixed>>
     */
    public function array(): array
    {
        return $this->batch->rows()
            ->get()
            ->map(function ($row): array {
                $errors = collect($row->errors ?? [])->implode(' ');

                return [
                    $row->row_number,
                    $row->status,
                    $row->data['nama'] ?? '',
                    $row->data['nik'] ?? '',
                    $row->data['email'] ?? '',
                    $errors !== '' ? $errors : 'Berhasil diimpor',
                ];
            })
            ->all();
    }
}
