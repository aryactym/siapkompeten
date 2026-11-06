<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiAsesi extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_HADIR = 'hadir';

    public const STATUS_IZIN = 'izin';

    public const STATUS_TIDAK_HADIR = 'tidak_hadir';

    public const STATUS_LABELS = [
        self::STATUS_HADIR => 'Hadir',
        self::STATUS_IZIN => 'Izin',
        self::STATUS_TIDAK_HADIR => 'Tidak Hadir',
    ];

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'jadwal_id',
        'status',
        'catatan',
        'dicatat_oleh',
        'dicatat_pada',
        'terkunci_pada',
    ];

    protected function casts(): array
    {
        return [
            'dicatat_pada' => 'datetime',
            'terkunci_pada' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalAsesmen::class, 'jadwal_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function isLocked(): bool
    {
        return $this->terkunci_pada !== null;
    }

    public function isHadir(): bool
    {
        return $this->status === self::STATUS_HADIR;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
