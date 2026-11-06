<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ia04Asesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_DRAF = 'draf';

    public const STATUS_FINAL = 'final';

    public const REKOMENDASI_KOMPETEN = 'kompeten';

    public const REKOMENDASI_BELUM_KOMPETEN = 'belum_kompeten';

    public const REKOMENDASI_LABELS = [
        self::REKOMENDASI_KOMPETEN => 'Kompeten',
        self::REKOMENDASI_BELUM_KOMPETEN => 'Belum Kompeten',
    ];

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'judul_kegiatan',
        'skenario',
        'hal_dipersiapkan',
        'waktu_persiapan',
        'hasil_dipresentasikan',
        'waktu_presentasi',
        'umpan_balik',
        'rekomendasi',
        'status',
        'finalized_at',
        'finalized_by',
    ];

    protected function casts(): array
    {
        return [
            'finalized_at' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Ia04Item::class, 'ia04_asesmen_id')->orderBy('urutan');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }
}
