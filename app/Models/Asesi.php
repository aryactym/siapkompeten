<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;

class Asesi extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'user_id',
        'no_registrasi',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'kebangsaan',
        'alamat_rumah',
        'kelurahan',
        'kecamatan_code',
        'kota_code',
        'provinsi_code',
        'kode_pos',
        'telp_rumah',
        'telp_kantor',
        'pendidikan_terakhir',
        'pekerjaan_instansi',
        'jabatan',
        'alamat_kantor',
        'foto_path',
        'status_verifikasi',
        'status_kompetensi',
        'sumber',
        'import_batch_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pendaftarans(): HasMany
    {
        return $this->hasMany(Pendaftaran::class);
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'provinsi_code', 'code');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'kota_code', 'code');
    }

    public function scopeTerverifikasi(Builder $query): Builder
    {
        return $query->where('status_verifikasi', 'terverifikasi');
    }

    public function scopeBelumTerverifikasi(Builder $query): Builder
    {
        return $query->where('status_verifikasi', 'belum_terverifikasi');
    }

    /**
     * Kelengkapan profil pokok untuk membuka pendaftaran skema (FR-ASI-02).
     */
    public function isProfileComplete(): bool
    {
        return filled($this->user?->nik)
            && filled($this->jenis_kelamin)
            && filled($this->tanggal_lahir)
            && filled($this->alamat_rumah)
            && filled($this->provinsi_code)
            && filled($this->kota_code)
            && filled($this->pendidikan_terakhir)
            && filled($this->user?->phone);
    }
}
