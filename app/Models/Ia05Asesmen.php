<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ia05Asesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_DRAF = 'draf';

    public const STATUS_FINAL = 'final';

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'judul',
        'petunjuk',
        'umpan_balik',
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

    public function soals(): HasMany
    {
        return $this->hasMany(Ia05Soal::class, 'ia05_asesmen_id')->orderBy('urutan');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function jumlahBenar(): int
    {
        return $this->soals->filter(fn (Ia05Soal $soal) => $soal->benar())->count();
    }
}
