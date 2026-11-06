<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanAsesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_DRAF = 'draf';

    public const STATUS_FINAL = 'final';

    protected $fillable = [
        'institution_id',
        'jadwal_id',
        'asesor_id',
        'aspek_positif',
        'aspek_negatif',
        'pencatatan_penolakan',
        'saran_perbaikan',
        'catatan',
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

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalAsesmen::class, 'jadwal_id');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class);
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
