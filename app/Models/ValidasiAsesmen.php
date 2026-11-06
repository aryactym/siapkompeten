<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValidasiAsesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const KEPUTUSAN_DISETUJUI = 'disetujui';

    public const KEPUTUSAN_REVISI = 'revisi';

    public const KEPUTUSAN_DITOLAK = 'ditolak';

    public const KEPUTUSAN_LABELS = [
        self::KEPUTUSAN_DISETUJUI => 'Disetujui',
        self::KEPUTUSAN_REVISI => 'Revisi',
        self::KEPUTUSAN_DITOLAK => 'Ditolak',
    ];

    public const STATUS_DRAF = 'draf';

    public const STATUS_FINAL = 'final';

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'validator_id',
        'aspek_validitas',
        'aspek_reliabilitas',
        'aspek_keadilan',
        'aspek_kesesuaian',
        'keputusan',
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

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
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
