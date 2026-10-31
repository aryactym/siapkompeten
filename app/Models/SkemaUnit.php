<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SkemaUnit extends Pivot
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $table = 'skema_units';

    public $incrementing = false;

    protected $fillable = [
        'institution_id',
        'skema_id',
        'unit_kompetensi_id',
        'jenis',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Skema::class);
    }

    public function unitKompetensi(): BelongsTo
    {
        return $this->belongsTo(UnitKompetensi::class);
    }
}
