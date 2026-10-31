<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_VALID = 'valid';

    public const STATUS_TIDAK_VALID = 'tidak_valid';

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'total',
        'rekening_bank_id',
        'bukti_path',
        'bukti_original_name',
        'bukti_mime',
        'bukti_size',
        'tanggal_bayar',
        'status',
        'catatan',
        'divalidasi_oleh',
        'divalidasi_pada',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'bukti_size' => 'integer',
            'tanggal_bayar' => 'date',
            'divalidasi_pada' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function rekeningBank(): BelongsTo
    {
        return $this->belongsTo(RekeningBank::class);
    }

    public function divalidasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh');
    }

    public function isMenunggu(): bool
    {
        return $this->status === self::STATUS_MENUNGGU;
    }

    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }

    public function hasBukti(): bool
    {
        return filled($this->bukti_path);
    }
}
