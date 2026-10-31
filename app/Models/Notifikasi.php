<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notifikasi extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'channel',
        'tujuan',
        'user_id',
        'judul',
        'pesan',
        'tipe',
        'related_type',
        'related_id',
        'status',
        'attempts',
        'error',
        'sent_at',
        'provider_ref',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeGagal(Builder $query): Builder
    {
        return $query->where('status', 'gagal');
    }

    public function markSent(?string $providerRef = null): void
    {
        $this->update([
            'status' => 'terkirim',
            'sent_at' => now(),
            'provider_ref' => $providerRef,
            'error' => null,
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->update([
            'status' => 'gagal',
            'attempts' => $this->attempts + 1,
            'error' => $error,
        ]);
    }
}
