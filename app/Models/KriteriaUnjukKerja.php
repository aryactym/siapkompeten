<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KriteriaUnjukKerja extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'elemen_kompetensi_id',
        'urutan',
        'pernyataan_kuk',
        'pertanyaan_observasi',
        'pertanyaan_lisan',
        'pertanyaan_tertulis',
    ];

    public function elemenKompetensi(): BelongsTo
    {
        return $this->belongsTo(ElemenKompetensi::class);
    }
}
