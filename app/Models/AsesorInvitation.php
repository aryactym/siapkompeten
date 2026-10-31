<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Undangan keanggotaan asesor (branch `single`).
 *
 * Catatan: System Design menetapkan `asesor_invitations` untuk undangan lintas-LSP
 * (berbasis NIK + profil global). Implementasi single-tenant ini membuat akun
 * User + profil Asesor langsung dari undangan, lalu mengirim tautan aktivasi
 * (set kata sandi) — pola yang sama dengan aktivasi asesi hasil impor.
 */
class AsesorInvitation extends Model
{
    use BelongsToTenant, HasUuids;

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUS_KEDALUWARSA = 'kedaluwarsa';

    protected $fillable = [
        'institution_id',
        'invited_by',
        'asesor_id',
        'nik_hash',
        'nama',
        'email',
        'no_register',
        'bidang_keahlian',
        'status',
        'expires_at',
        'responded_at',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class);
    }

    public function isExpired(?Carbon $at = null): bool
    {
        return $this->expires_at !== null && ($at ?? now())->gt($this->expires_at);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }
}
