<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstrumenAsesmenItem extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const HASIL_TERCAPAI = 'tercapai';

    public const HASIL_BELUM_TERCAPAI = 'belum_tercapai';

    public const HASIL_LABELS = [
        self::HASIL_TERCAPAI => 'Tercapai',
        self::HASIL_BELUM_TERCAPAI => 'Belum Tercapai',
    ];

    protected $fillable = [
        'institution_id',
        'instrumen_asesmen_id',
        'unit_kompetensi_id',
        'kuk_id',
        'urutan',
        'hasil',
        'catatan',
    ];

    public function instrumen(): BelongsTo
    {
        return $this->belongsTo(InstrumenAsesmen::class, 'instrumen_asesmen_id');
    }

    public function unitKompetensi(): BelongsTo
    {
        return $this->belongsTo(UnitKompetensi::class);
    }

    public function kuk(): BelongsTo
    {
        return $this->belongsTo(KriteriaUnjukKerja::class, 'kuk_id');
    }

    public function isTercapai(): bool
    {
        return $this->hasil === self::HASIL_TERCAPAI;
    }
}
