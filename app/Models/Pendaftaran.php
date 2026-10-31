<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Pendaftaran extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    public const STATUS_DRAF = 'draf';

    public const STATUS_DOKUMEN_DIAJUKAN = 'dokumen_diajukan';

    public const STATUS_DOKUMEN_DITOLAK = 'dokumen_ditolak';

    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';

    public const STATUS_PEMBAYARAN_DIAJUKAN = 'pembayaran_diajukan';

    public const STATUS_MENUNGGU_JADWAL = 'menunggu_jadwal';

    public const STATUS_TERJADWAL = 'terjadwal';

    public const STATUS_ASESMEN_BERJALAN = 'asesmen_berjalan';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_DITOLAK = 'ditolak';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $fillable = [
        'institution_id',
        'no_pendaftaran',
        'asesi_id',
        'skema_id',
        'jadwal_id',
        'status',
        'catatan_penolakan',
        'disetujui_oleh',
        'disetujui_pada',
    ];

    protected function casts(): array
    {
        return [
            'disetujui_pada' => 'datetime',
        ];
    }

    public function asesi(): BelongsTo
    {
        return $this->belongsTo(Asesi::class);
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Skema::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalAsesmen::class, 'jadwal_id');
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(PendaftaranDokumen::class);
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class);
    }

    public function apl02(): HasOne
    {
        return $this->hasOne(Apl02Penilaian::class);
    }

    public function hasilAsesmen(): HasOne
    {
        return $this->hasOne(HasilAsesmen::class);
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Status yang masih dapat diedit/diunggah dokumen oleh asesi.
     *
     * @return array<int, string>
     */
    public static function editableStatuses(): array
    {
        return [self::STATUS_DRAF, self::STATUS_DOKUMEN_DITOLAK];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            self::STATUS_DITOLAK,
            self::STATUS_DIBATALKAN,
            self::STATUS_SELESAI,
        ]);
    }

    public function canUploadDocuments(): bool
    {
        return in_array($this->status, self::editableStatuses(), true);
    }

    public function isWaitingDocumentVerification(): bool
    {
        return $this->status === self::STATUS_DOKUMEN_DIAJUKAN;
    }

    public function isWaitingSchedule(): bool
    {
        return $this->status === self::STATUS_MENUNGGU_JADWAL;
    }

    /**
     * Tahap pembayaran: asesi boleh mengunggah bukti bayar (FR-PAY-04).
     */
    public function isWaitingPayment(): bool
    {
        return in_array($this->status, [
            self::STATUS_MENUNGGU_PEMBAYARAN,
            self::STATUS_PEMBAYARAN_DIAJUKAN,
        ], true);
    }

    /**
     * Total tagihan peserta untuk pendaftaran ini (FR-PAY-04).
     */
    public function totalTagihan(): int
    {
        return $this->skema->totalTagihanPeserta();
    }

    /**
     * Persyaratan wajib skema yang belum memiliki berkas.
     *
     * @return Collection<int, SkemaPersyaratan>
     */
    public function missingRequiredDocuments(): Collection
    {
        $uploaded = $this->dokumens
            ->whereNotNull('file_path')
            ->pluck('skema_persyaratan_id')
            ->all();

        return $this->skema->persyaratans()
            ->where('is_required', true)
            ->get()
            ->reject(fn (SkemaPersyaratan $persyaratan): bool => in_array($persyaratan->id, $uploaded, true))
            ->values();
    }

    public function allRequiredDocumentsUploaded(): bool
    {
        return $this->missingRequiredDocuments()->isEmpty();
    }

    public function hasRejectedDocuments(): bool
    {
        return $this->dokumens()->where('status', 'ditolak')->exists();
    }

    /**
     * Tahapan aktif asesi untuk strict phase gating (FR-ASI-12).
     *
     * @return array{key: string, label: string, step: int}
     */
    public function currentStage(): array
    {
        if (! $this->asesi->isProfileComplete()) {
            return ['key' => 'profil', 'label' => 'Kelengkapan Profil', 'step' => 1];
        }

        return match ($this->status) {
            self::STATUS_DRAF, self::STATUS_DOKUMEN_DITOLAK => ['key' => 'dokumen', 'label' => 'Unggah Dokumen Persyaratan', 'step' => 1],
            self::STATUS_DOKUMEN_DIAJUKAN => ['key' => 'verifikasi', 'label' => 'Verifikasi Dokumen oleh Admin', 'step' => 2],
            self::STATUS_MENUNGGU_PEMBAYARAN, self::STATUS_PEMBAYARAN_DIAJUKAN => ['key' => 'pembayaran', 'label' => 'Pembayaran Biaya Sertifikasi', 'step' => 3],
            self::STATUS_MENUNGGU_JADWAL => ['key' => 'plotting', 'label' => 'Penjadwalan oleh Admin', 'step' => 4],
            self::STATUS_TERJADWAL => ['key' => 'apl02', 'label' => 'Asesmen Mandiri (APL-02)', 'step' => 5],
            self::STATUS_ASESMEN_BERJALAN => ['key' => 'asesmen', 'label' => 'Pelaksanaan Asesmen', 'step' => 6],
            self::STATUS_SELESAI => ['key' => 'selesai', 'label' => 'Asesmen Selesai', 'step' => 7],
            self::STATUS_DITOLAK => ['key' => 'ditolak', 'label' => 'Pendaftaran Ditolak', 'step' => 0],
            self::STATUS_DIBATALKAN => ['key' => 'dibatalkan', 'label' => 'Pendaftaran Dibatalkan', 'step' => 0],
            default => ['key' => 'dokumen', 'label' => 'Pendaftaran', 'step' => 1],
        };
    }
}
