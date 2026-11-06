<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumenAsesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const JENIS_IA01 = 'ia01';

    public const JENIS_IA02 = 'ia02';

    public const JENIS_IA08 = 'ia08';

    public const JENIS_IA11 = 'ia11';

    public const JENIS_LABELS = [
        self::JENIS_IA01 => 'FR.IA.01 — Ceklis Observasi Aktivitas di Tempat Kerja',
        self::JENIS_IA02 => 'FR.IA.02 — Tugas Praktik Demonstrasi',
        self::JENIS_IA08 => 'FR.IA.08 — Ceklis Verifikasi Portofolio',
        self::JENIS_IA11 => 'FR.IA.11 — Ceklis Reviu Produk',
    ];

    public const STATUS_DRAF = 'draf';

    public const STATUS_FINAL = 'final';

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'jenis',
        'status',
        'catatan',
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
        return $this->hasMany(InstrumenAsesmenItem::class)->orderBy('urutan');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function label(): string
    {
        return self::JENIS_LABELS[$this->jenis] ?? $this->jenis;
    }
}
