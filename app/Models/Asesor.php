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
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;

class Asesor extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'user_id',
        'no_register',
        'no_lisensi',
        'lisensi_berlaku_hingga',
        'gelar_depan',
        'gelar_belakang',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'kebangsaan',
        'alamat',
        'province_code',
        'city_code',
        'kode_pos',
        'pendidikan_terakhir',
        'bidang_keahlian',
        'institusi_asal',
        'telepon',
        'email',
        'foto_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'lisensi_berlaku_hingga' => 'date',
            'tanggal_lahir' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function penugasan(): HasMany
    {
        return $this->hasMany(JadwalAsesor::class);
    }

    public function jadwals(): BelongsToMany
    {
        return $this->belongsToMany(JadwalAsesmen::class, 'jadwal_asesors')
            ->withPivot(['id', 'peran', 'notified_at'])
            ->withTimestamps();
    }

    /**
     * Lingkup kompetensi asesor: pemetaan ke skema yang dapat diasesinya.
     */
    public function kompetensis(): HasMany
    {
        return $this->hasMany(AsesorKompetensi::class);
    }

    /**
     * Skema yang menjadi lingkup asesor (melalui asesor_kompetensis).
     */
    public function skemaKompetensis(): BelongsToMany
    {
        return $this->belongsToMany(Skema::class, 'asesor_kompetensis')
            ->withPivot(['id', 'berlaku_mulai', 'berlaku_hingga'])
            ->withTimestamps();
    }

    /**
     * Apakah asesor kompeten untuk skema tertentu.
     * Tanpa lingkup sama sekali → asesor dianggap kompeten pada seluruh skema.
     */
    public function isKompetenForSkema(Skema $skema): bool
    {
        if ($this->kompetensis()->doesntExist()) {
            return true;
        }

        return $this->kompetensis()->where('skema_id', $skema->id)->exists();
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_code', 'code');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function getNamaLengkapAttribute(): string
    {
        $nama = trim(implode(' ', array_filter([
            $this->gelar_depan,
            $this->user?->name,
            $this->gelar_belakang,
        ])));

        return $nama !== '' ? $nama : ($this->user?->name ?? '-');
    }
}
