<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Apl02Jawaban extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'penilaian_id',
        'kuk_id',
        'jawaban_asesi',
        'bukti_asesi',
        'bukti_asesor',
        'valid',
        'autentik',
        'terkini',
        'memadai',
    ];

    protected function casts(): array
    {
        return [
            'jawaban_asesi' => 'boolean',
            'valid' => 'boolean',
            'autentik' => 'boolean',
            'terkini' => 'boolean',
            'memadai' => 'boolean',
        ];
    }

    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(Apl02Penilaian::class, 'penilaian_id');
    }

    public function kuk(): BelongsTo
    {
        return $this->belongsTo(KriteriaUnjukKerja::class, 'kuk_id');
    }

    /**
     * Empat bukti kompetensi (4C) telah dinyatakan asesor.
     */
    public function semuaBuktiAsesor(): bool
    {
        return $this->valid && $this->autentik && $this->terkini && $this->memadai;
    }
}
