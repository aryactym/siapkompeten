<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilAsesmenMetode extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    /**
     * Daftar metode yang didukung.
     * Urutan tampilan mengikuti urutan array ini.
     * Update nilai ini HARUS disertai migrasi ENUM di database.
     *
     * @var list<string>
     */
    public const METODES = [
        'observasi',
        'demonstrasi',
        'tes_lisan',
        'tes_tertulis',
        'wawancara',
        'portofolio',
    ];

    /**
     * Label Indonesia untuk setiap metode.
     *
     * @var array<string,string>
     */
    public const METODE_LABELS = [
        'observasi' => 'Observasi',
        'demonstrasi' => 'Demonstrasi / Simulasi',
        'tes_lisan' => 'Tes Lisan',
        'tes_tertulis' => 'Tes Tertulis',
        'wawancara' => 'Wawancara',
        'portofolio' => 'Portofolio',
    ];

    protected $fillable = [
        'institution_id',
        'hasil_unit_id',
        'metode',
        'digunakan',
        'bukti_langsung',
        'bukti_tidak_langsung',
        'bukti_tambahan',
        'keputusan',
        'catatan',
        // === Deprecated ===
        'pencapaian', // boolean — digantikan oleh `keputusan` enum
    ];

    protected function casts(): array
    {
        return [
            'digunakan' => 'boolean',
            'bukti_langsung' => 'boolean',
            'bukti_tidak_langsung' => 'boolean',
            'bukti_tambahan' => 'boolean',
            'pencapaian' => 'boolean',
        ];
    }

    public function hasilUnit(): BelongsTo
    {
        return $this->belongsTo(HasilAsesmenUnit::class, 'hasil_unit_id');
    }
}
