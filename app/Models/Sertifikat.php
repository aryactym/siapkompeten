<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Sertifikat Kompetensi BNSP (FR-RPT-04 / FR-CMS-06).
 *
 * Sistem bertindak sebagai pemicu cetak dan data otentikasi setelah hasil asesmen
 * "kompeten" disetujui. Dua nomor unik dipisahkan:
 *  - `no_seri_blangko`: nomor seri kertas hologram fisik BNSP (diisi admin).
 *  - `no_registrasi`: nomor registrasi lisensi nasional (dibangkitkan sistem).
 * Validasi format regex standar BNSP: ^[A-Z0-9\-\.]+\.[A-Z0-9\-\.]+\.\d{5,}\.\d{4}$
 */
class Sertifikat extends Model
{
    use BelongsToTenant, HasUuids;

    /** Format nomor seri/registrasi standar BNSP. */
    public const FORMAT_REGEX = '/^[A-Z0-9\-.]+\.[A-Z0-9\-.]+\.\d{5,}\.\d{4}$/';

    public const STATUS_AKTIF = 'aktif';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUS_KEDALUWARSA = 'kedaluwarsa';

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'asesi_id',
        'skema_id',
        'no_seri_blangko',
        'no_registrasi',
        'tanggal_terbit',
        'berlaku_hingga',
        'status',
        'diterbitkan_oleh',
        'qr_token',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'date',
            'berlaku_hingga' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $sertifikat): void {
            if (blank($sertifikat->qr_token)) {
                $sertifikat->qr_token = Str::random(32);
            }
            if (blank($sertifikat->tanggal_terbit)) {
                $sertifikat->tanggal_terbit = now();
            }
        });
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function asesi(): BelongsTo
    {
        return $this->belongsTo(Asesi::class);
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Skema::class);
    }

    public function penerbit(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterbitkan_oleh');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    /**
     * URL publik untuk verifikasi keaslian sertifikat (FR-CMS-06).
     */
    public function verifikasiUrl(): string
    {
        return route('sertifikat.verifikasi', $this->qr_token);
    }
}
