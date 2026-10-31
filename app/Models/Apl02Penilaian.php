<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apl02Penilaian extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_DRAF_ASESI = 'draf_asesi';

    public const STATUS_DISERAHKAN = 'diserahkan';

    public const STATUS_DIVERIFIKASI = 'diverifikasi';

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'asesor_id',
        'status',
        'diserahkan_pada',
        'diverifikasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'diserahkan_pada' => 'datetime',
            'diverifikasi_pada' => 'datetime',
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

    public function jawabans(): HasMany
    {
        return $this->hasMany(Apl02Jawaban::class, 'penilaian_id');
    }

    public function dapatDiisiAsesi(): bool
    {
        return $this->status === self::STATUS_DRAF_ASESI || $this->status === self::STATUS_DISERAHKAN;
    }

    public function isDiserahkan(): bool
    {
        return in_array($this->status, [self::STATUS_DISERAHKAN, self::STATUS_DIVERIFIKASI], true);
    }
}
