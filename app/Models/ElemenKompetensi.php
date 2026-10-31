<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ElemenKompetensi extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'unit_kompetensi_id',
        'urutan',
        'nama_elemen',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function unitKompetensi(): BelongsTo
    {
        return $this->belongsTo(UnitKompetensi::class);
    }

    public function kriteriaUnjukKerjas(): HasMany
    {
        return $this->hasMany(KriteriaUnjukKerja::class)->orderBy('urutan');
    }
}
