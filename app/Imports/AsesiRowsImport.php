<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AsesiRowsImport implements SkipsEmptyRows, ToArray, WithHeadingRow
{
    /**
     * @param  array<array-key, mixed>  $array
     */
    public function array(array $array): void
    {
        // Data dibaca langsung melalui Excel::toArray(); tidak ada persistensi di sini.
    }
}
