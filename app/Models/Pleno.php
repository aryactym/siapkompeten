<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pleno extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const KEPUTUSAN_DISETUJUI = 'disetujui';

    public const KEPUTUSAN_DITOLAK = 'ditolak';

    public const STATUS_DRAF = 'draf';

    public const STATUS_FINAL = 'final';

    protected $fillable = [
        'institution_id',
        'jadwal_id',
        'nomor_ba',
        'tanggal',
        'keputusan',
        'catatan',
        'status',
        'finalized_at',
        'finalized_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'finalized_at' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalAsesmen::class, 'jadwal_id');
    }

    public function pesertas(): HasMany
    {
        return $this->hasMany(PlenoPeserta::class)->orderBy('created_at');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function isApproved(): bool
    {
        return $this->isFinal() && $this->keputusan === self::KEPUTUSAN_DISETUJUI;
    }
}
