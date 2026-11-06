<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RencanaAsesmenUnit extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'rencana_asesmen_id',
        'unit_kompetensi_id',
        'urutan',
        'catatan',
    ];

    public function rencanaAsesmen(): BelongsTo
    {
        return $this->belongsTo(RencanaAsesmen::class, 'rencana_asesmen_id');
    }

    public function unitKompetensi(): BelongsTo
    {
        return $this->belongsTo(UnitKompetensi::class);
    }

    public function petaInstrumens(): HasMany
    {
        return $this->hasMany(PetaInstrumen::class, 'rencana_asesmen_unit_id')->orderBy('urutan');
    }
}
