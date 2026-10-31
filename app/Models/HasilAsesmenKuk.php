<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilAsesmenKuk extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'hasil_id',
        'kuk_id',
        'pencapaian',
        'bukti_no',
        'valid',
        'autentik',
        'terkini',
        'memadai',
    ];

    protected function casts(): array
    {
        return [
            'pencapaian' => 'boolean',
            'valid' => 'boolean',
            'autentik' => 'boolean',
            'terkini' => 'boolean',
            'memadai' => 'boolean',
        ];
    }

    public function hasil(): BelongsTo
    {
        return $this->belongsTo(HasilAsesmen::class, 'hasil_id');
    }

    public function kuk(): BelongsTo
    {
        return $this->belongsTo(KriteriaUnjukKerja::class, 'kuk_id');
    }
}
