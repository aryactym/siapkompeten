<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banding extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_DIAJUKAN = 'diajukan';

    public const STATUS_DITINJAU = 'ditinjau';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_DITOLAK = 'ditolak';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_LABELS = [
        self::STATUS_DIAJUKAN => 'Diajukan',
        self::STATUS_DITINJAU => 'Ditinjau',
        self::STATUS_DITERIMA => 'Diterima',
        self::STATUS_DITOLAK => 'Ditolak',
        self::STATUS_SELESAI => 'Selesai',
    ];

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'pengaju_id',
        'alasan',
        'status',
        'keputusan',
        'ditinjau_oleh',
        'ditinjau_pada',
    ];

    protected function casts(): array
    {
        return [
            'ditinjau_pada' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengaju_id');
    }

    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(BandingStatusHistory::class)->orderBy('created_at');
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_DITOLAK, self::STATUS_SELESAI], true);
    }
}
