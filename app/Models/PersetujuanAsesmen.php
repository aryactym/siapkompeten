<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersetujuanAsesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const JENIS_AK01 = 'ak01';

    public const JENIS_AK04 = 'ak04';

    public const JENIS_AK07 = 'ak07';

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DISETUJUI = 'disetujui';

    public const JENIS_LABELS = [
        self::JENIS_AK01 => 'FR.AK.01 — Persetujuan Asesmen dan Kerahasiaan',
        self::JENIS_AK04 => 'FR.AK.04 — Banding Asesmen',
        self::JENIS_AK07 => 'FR.AK.07 — Penyesuaian yang Wajar dan Beralasan',
    ];

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'jenis',
        'status',
        'disetujui_pada',
        'disetujui_oleh',
        'ip_address',
        'user_agent',
        'dokumen_hash',
        'catatan',
        'data_json',
    ];

    protected function casts(): array
    {
        return [
            'disetujui_pada' => 'datetime',
            'data_json' => 'array',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_DISETUJUI && $this->disetujui_pada !== null;
    }

    public static function requiredJenis(): array
    {
        return [self::JENIS_AK01, self::JENIS_AK04, self::JENIS_AK07];
    }

    public static function allApprovedFor(Pendaftaran $pendaftaran): bool
    {
        $approved = $pendaftaran->persetujuanAsesmens()
            ->where('status', self::STATUS_DISETUJUI)
            ->pluck('jenis')
            ->all();

        return count(array_intersect(self::requiredJenis(), $approved)) === count(self::requiredJenis());
    }
}
