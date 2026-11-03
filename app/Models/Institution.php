<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'nomor_lisensi',
        'masa_berlaku_lisensi',
        'jenis_lsp',
        'email',
        'telepon',
        'alamat',
        'website',
        'nama_ketua',
        'logo_path',
        'logo_bnsp_path',
        'logo_lsp_path',
        'stempel_path',
        'tanda_tangan_path',
        'is_active',
        'is_setup_completed',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_setup_completed' => 'boolean',
            'masa_berlaku_lisensi' => 'date',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(InstitutionDocument::class);
    }

    public function tuks(): HasMany
    {
        return $this->hasMany(Tuk::class);
    }

    public function skknis(): HasMany
    {
        return $this->hasMany(Skkni::class);
    }

    public function skemas(): HasMany
    {
        return $this->hasMany(Skema::class);
    }

    public function unitKompetensis(): HasMany
    {
        return $this->hasMany(UnitKompetensi::class);
    }
}
