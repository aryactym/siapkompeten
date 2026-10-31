<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Biaya extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const TANGGUNG_PESERTA = 'peserta';

    public const TANGGUNG_FASILITATOR = 'fasilitator';

    protected $fillable = [
        'institution_id',
        'skkni_id',
        'skema_id',
        'jenis_biaya',
        'nominal',
        'tanggung_jawab',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'integer',
        ];
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Skema::class);
    }

    public function skkni(): BelongsTo
    {
        return $this->belongsTo(Skkni::class);
    }

    public function ditanggungPeserta(): bool
    {
        return $this->tanggung_jawab === self::TANGGUNG_PESERTA;
    }
}
