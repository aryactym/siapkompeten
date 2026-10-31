<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilAsesmenUnit extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'hasil_id',
        'unit_id',
        'keputusan',
        'pencapaian',
    ];

    protected function casts(): array
    {
        return [
            'pencapaian' => 'boolean',
        ];
    }

    public function hasil(): BelongsTo
    {
        return $this->belongsTo(HasilAsesmen::class, 'hasil_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitKompetensi::class, 'unit_id');
    }

    public function metodes(): HasMany
    {
        return $this->hasMany(HasilAsesmenMetode::class, 'hasil_unit_id');
    }
}
