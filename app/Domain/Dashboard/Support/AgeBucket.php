<?php

namespace App\Domain\Dashboard\Support;

class AgeBucket
{
    /** @return array<string,int> */
    public static function buckets(): array
    {
        return [
            '≤25' => 0,
            '26–35' => 0,
            '36–45' => 0,
            '>45' => 0,
            'Tidak diketahui' => 0,
        ];
    }

    public static function bucket(?int $age): string
    {
        if ($age === null) {
            return 'Tidak diketahui';
        }
        if ($age <= 25) {
            return '≤25';
        }
        if ($age <= 35) {
            return '26–35';
        }
        if ($age <= 45) {
            return '36–45';
        }

        return '>45';
    }
}
