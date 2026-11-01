<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilAsesmenKuk extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'hasil_id',
        'kuk_id',
        // === Kolom aktif (refactor baru) ===
        'tercapai',   // boolean nullable: null=belum dinilai, true=ya, false=tidak
        'catatan',    // text nullable: catatan asesor per KUK
        // === VATM (Valid/Asli/Terkini/Memadai) — masih aktif per KUK ===
        'valid',
        'asli',       // sebelumnya: autentik (rename via migration)
        'terkini',
        'memadai',
        // === Deprecated (dipertahankan untuk backward compat) ===
        'pencapaian', // → gunakan `tercapai`
        'bukti_no',   // → tidak diisi lagi oleh UI baru
    ];

    protected function casts(): array
    {
        return [
            'tercapai' => 'boolean',
            'pencapaian' => 'boolean',
            'valid' => 'boolean',
            'asli' => 'boolean',
            'terkini' => 'boolean',
            'memadai' => 'boolean',
        ];
    }

    public function hasil(): BelongsTo
    {
        return $this->belongsTo(HasilAsesmen::class, 'hasil_id');
    }

    public function kuk(): BelongsTo
    {
        return $this->belongsTo(KriteriaUnjukKerja::class, 'kuk_id');
    }
}
