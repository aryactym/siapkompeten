<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitKompetensi extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'skkni_id',
        'kode_unit',
        'judul_unit',
        'standar_kompetensi',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function skkni(): BelongsTo
    {
        return $this->belongsTo(Skkni::class);
    }

    public function skemas(): BelongsToMany
    {
        return $this->belongsToMany(Skema::class, 'skema_units')
            ->withPivot(['id', 'jenis', 'urutan'])
            ->withTimestamps()
            ->orderByPivot('urutan');
    }

    public function elemenKompetensis(): HasMany
    {
        return $this->hasMany(ElemenKompetensi::class)->orderBy('urutan');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
