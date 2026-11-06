<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranDokumen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'skema_persyaratan_id',
        'file_path',
        'file_mime',
        'file_size',
        'nilai_teks',
        'status',
        'catatan',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function persyaratan(): BelongsTo
    {
        return $this->belongsTo(SkemaPersyaratan::class, 'skema_persyaratan_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function isPreviewable(): bool
    {
        return in_array($this->file_mime, ['application/pdf', 'image/jpeg', 'image/png'], true);
    }
}
