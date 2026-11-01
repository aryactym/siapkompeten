<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilAsesmenUnit extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'hasil_id',
        'unit_id',
        'keputusan',
        'catatan',    // wajib bila keputusan berbeda dari saran otomatis
        // === Deprecated ===
        'pencapaian', // boolean — kini indikator hitung otomatis, bukan input
    ];

    protected function casts(): array
    {
        return [
            'pencapaian' => 'boolean',
        ];
    }

    /**
     * Hitung saran keputusan berdasarkan KUK yang dinilai.
     * Semua tercapai → kompeten; ada tidak → belum_kompeten; ada null → belum lengkap.
     */
    public function saranKeputusan(): ?string
    {
        $kuks = $this->hasil->kuks()->whereHas('kuk', fn ($q) => $q->whereHas(
            'elemenKompetensi', fn ($q2) => $q2->where('unit_kompetensi_id', $this->unit_id)
        ))->get();

        if ($kuks->isEmpty()) {
            return null;
        }

        if ($kuks->contains(fn ($k) => $k->tercapai === null)) {
            return null; // masih ada yang belum dinilai
        }

        return $kuks->every(fn ($k) => $k->tercapai === true)
            ? 'kompeten'
            : 'belum_kompeten';
    }

    public function hasil(): BelongsTo
    {
        return $this->belongsTo(HasilAsesmen::class, 'hasil_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitKompetensi::class, 'unit_id');
    }

    public function metodes(): HasMany
    {
        return $this->hasMany(HasilAsesmenMetode::class, 'hasil_unit_id');
    }
}
