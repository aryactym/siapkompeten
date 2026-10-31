<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skkni extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'kode',
        'judul',
        'nomor_penetapan',
        'tahun',
        'file_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function unitKompetensis(): HasMany
    {
        return $this->hasMany(UnitKompetensi::class);
    }
}
