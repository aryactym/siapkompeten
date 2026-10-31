<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilAsesmenMetode extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const METODES = ['observasi', 'tes_lisan', 'tes_tertulis'];

    protected $fillable = [
        'institution_id',
        'hasil_unit_id',
        'metode',
        'digunakan',
        'bukti_langsung',
        'bukti_tidak_langsung',
        'bukti_tambahan',
        'pencapaian',
        'keputusan',
    ];

    protected function casts(): array
    {
        return [
            'digunakan' => 'boolean',
            'bukti_langsung' => 'boolean',
            'bukti_tidak_langsung' => 'boolean',
            'bukti_tambahan' => 'boolean',
            'pencapaian' => 'boolean',
        ];
    }

    public function hasilUnit(): BelongsTo
    {
        return $this->belongsTo(HasilAsesmenUnit::class, 'hasil_unit_id');
    }
}
