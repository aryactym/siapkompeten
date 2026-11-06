<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetaInstrumen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'rencana_asesmen_unit_id',
        'kuk_id',
        'jenis_instrumen',
        'metode',
        'bukti',
        'urutan',
    ];

    public function rencanaAsesmenUnit(): BelongsTo
    {
        return $this->belongsTo(RencanaAsesmenUnit::class, 'rencana_asesmen_unit_id');
    }

    public function kuk(): BelongsTo
    {
        return $this->belongsTo(KriteriaUnjukKerja::class, 'kuk_id');
    }
}
