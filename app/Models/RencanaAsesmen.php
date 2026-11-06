<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RencanaAsesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_DRAF = 'draf';

    public const STATUS_FINAL = 'final';

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'asesor_id',
        'pendekatan',
        'tujuan',
        'ruang_lingkup',
        'tanggal_asesmen',
        'status',
        'catatan',
        'instrumen_terpilih',
    ];

    protected function casts(): array
    {
        return [
            'instrumen_terpilih' => 'array',
            'tanggal_asesmen' => 'date',
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

    public function units(): HasMany
    {
        return $this->hasMany(RencanaAsesmenUnit::class, 'rencana_asesmen_id')->orderBy('urutan');
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }
}
