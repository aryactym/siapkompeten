<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AsesiTemplateExport implements Export, WithMultipleSheets
{
    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new AsesiTemplateSheet,
            new WilayahSheet,
        ];
    }
}
