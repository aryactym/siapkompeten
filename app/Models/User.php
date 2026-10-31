<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'institution_id',
    'name',
    'username',
    'nik',
    'nik_hash',
    'email',
    'phone',
    'password',
    'status',
    'blocked_reason',
    'is_locked',
    'last_login_at',
    'last_login_ip',
])]
#[Hidden(['password', 'remember_token', 'nik_hash'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUuids, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'nik' => 'encrypted',
            'is_locked' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Calculate HMAC-SHA256 hash for NIK to enable blind indexing.
     */
    public static function hashNik(?string $nik): ?string
    {
        if (blank($nik)) {
            return null;
        }

        return hash_hmac('sha256', trim($nik), (string) config('app.key'));
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->isDirty('nik')) {
                $user->attributes['nik_hash'] = static::hashNik($user->nik);
            }
        });
    }

    /**
     * Return masked NIK for safe display (e.g. 3201********0001).
     */
    public function getMaskedNikAttribute(): ?string
    {
        if (blank($this->nik)) {
            return null;
        }

        $nik = (string) $this->nik;
        $len = strlen($nik);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return substr($nik, 0, 4).str_repeat('*', max(0, $len - 8)).substr($nik, -4);
    }

    public function isBlocked(): bool
    {
        return $this->status === 'diblokir';
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function asesor(): HasOne
    {
        return $this->hasOne(Asesor::class);
    }

    public function asesi(): HasOne
    {
        return $this->hasOne(Asesi::class);
    }

    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Institutions the user may switch into. Inactive institutions are excluded.
     *
     * @return Collection<int, Institution>
     */
    public function accessibleInstitutions(): Collection
    {
        return $this->institutions()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
