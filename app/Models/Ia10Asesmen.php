<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ia10Asesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_DRAF = 'draf';

    public const STATUS_FINAL = 'final';

    protected $fillable = ['institution_id', 'pendaftaran_id', 'nama_pihak_ketiga', 'jabatan', 'instansi', 'klarifikasi', 'status', 'finalized_at', 'finalized_by'];

    protected function casts(): array
    {
        return ['finalized_at' => 'datetime'];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
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
