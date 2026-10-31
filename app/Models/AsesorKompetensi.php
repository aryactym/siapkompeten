<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Lingkup kompetensi asesor: pemetaan asesor ke skema yang dapat diasesinya.
 *
 * Catatan: System Design menetapkan tabel `asesor_kompetensis` sebagai bagian
 * revisi multi-LSP (merujuk `asesor_institution_id`). Implementasi branch `single`
 * (1 LSP per instalasi) tetap memakai `asesor_id` langsung ke tabel `asesors`
 * agar sederhana tanpa mesin keanggotaan lintas-LSP.
 */
class AsesorKompetensi extends Model
{
    use BelongsToTenant, HasUuids;

    protected $fillable = [
        'institution_id',
        'asesor_id',
        'skema_id',
        'berlaku_mulai',
        'berlaku_hingga',
    ];

    protected function casts(): array
    {
        return [
            'berlaku_mulai' => 'date',
            'berlaku_hingga' => 'date',
        ];
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class);
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Skema::class);
    }

    /**
     * Apakah lingkup ini masih berlaku pada tanggal acuan.
     */
    public function isBerlaku(?Carbon $at = null): bool
    {
        $at ??= now();

        if ($this->berlaku_mulai !== null && $at->lt($this->berlaku_mulai)) {
            return false;
        }

        if ($this->berlaku_hingga !== null && $at->gt($this->berlaku_hingga)) {
            return false;
        }

        return true;
    }
}
