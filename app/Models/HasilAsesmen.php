<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilAsesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_DRAF = 'draf';

    public const STATUS_FINAL = 'final';

    public const REKOMENDASI_DIREKOMENDASIKAN = 'direkomendasikan';

    public const REKOMENDASI_TIDAK = 'tidak_direkomendasikan';

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'asesor_id',
        'rekomendasi',
        'persen_kompeten',
        'umpan_balik_diberikan',
        'status',
        'finalized_at',
        'finalized_by',
    ];

    protected function casts(): array
    {
        return [
            'persen_kompeten' => 'float',
            'umpan_balik_diberikan' => 'boolean',
            'finalized_at' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class);
    }

    public function kuks(): HasMany
    {
        return $this->hasMany(HasilAsesmenKuk::class, 'hasil_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(HasilAsesmenUnit::class, 'hasil_id');
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }
}
