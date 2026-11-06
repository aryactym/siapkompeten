<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalAsesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'event_id',
        'skema_id',
        'tuk_id',
        'gelombang',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'kuota',
        'lingkup_asesor',
        'no_surat_tugas',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'kuota' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(EventAsesmen::class, 'event_id');
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Skema::class);
    }

    public function tuk(): BelongsTo
    {
        return $this->belongsTo(Tuk::class);
    }

    public function penugasan(): HasMany
    {
        return $this->hasMany(JadwalAsesor::class, 'jadwal_id');
    }

    public function asesors(): BelongsToMany
    {
        return $this->belongsToMany(Asesor::class, 'jadwal_asesors', 'jadwal_id', 'asesor_id')
            ->withPivot(['id', 'peran', 'notified_at'])
            ->withTimestamps();
    }

    public function pendaftarans(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'jadwal_id');
    }

    public function pleno(): HasOne
    {
        return $this->hasOne(Pleno::class, 'jadwal_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereIn('status', ['terbuka', 'berjalan']);
    }

    public function isFull(): bool
    {
        return $this->pendaftarans()->count() >= $this->kuota;
    }

    public function sisaKuota(): int
    {
        return max(0, $this->kuota - $this->pendaftarans()->count());
    }

    /**
     * Deteksi bentrok jadwal asesor pada tanggal dengan rentang jam yang beririsan.
     */
    public static function hasAsesorConflict(
        string $asesorId,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        ?string $ignoreJadwalId = null,
    ): bool {
        $jamMulai = strlen($jamMulai) === 5 ? $jamMulai.':00' : $jamMulai;
        $jamSelesai = strlen($jamSelesai) === 5 ? $jamSelesai.':00' : $jamSelesai;

        return static::query()
            ->whereDate('tanggal', $tanggal)
            ->where('status', '!=', 'dibatalkan')
            ->when($ignoreJadwalId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreJadwalId))
            ->whereHas('asesors', fn (Builder $query): Builder => $query->whereKey($asesorId))
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->exists();
    }
}
