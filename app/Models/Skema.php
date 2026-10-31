<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skema extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'kode_skema',
        'nama_skema',
        'nomor_skema',
        'jenis',
        'level_kkni',
        'kategori',
        'deskripsi',
        'biaya',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level_kkni' => 'integer',
            'biaya' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function persyaratans(): HasMany
    {
        return $this->hasMany(SkemaPersyaratan::class)->orderBy('urutan');
    }

    public function skemaUnits(): HasMany
    {
        return $this->hasMany(SkemaUnit::class)->orderBy('urutan');
    }

    public function unitKompetensis(): BelongsToMany
    {
        return $this->belongsToMany(UnitKompetensi::class, 'skema_units')
            ->withPivot(['id', 'jenis', 'urutan'])
            ->withTimestamps()
            ->orderByPivot('urutan');
    }

    public function biayas(): HasMany
    {
        return $this->hasMany(Biaya::class)->orderBy('jenis_biaya');
    }

    /**
     * Rincian biaya yang ditanggung peserta (FR-PAY-03) untuk kebutuhan tagihan.
     *
     * @return array<int, array{jenis_biaya: string, nominal: int}>
     */
    public function biayaTagihan(): array
    {
        $items = $this->biayas()
            ->where('tanggung_jawab', Biaya::TANGGUNG_PESERTA)
            ->get()
            ->map(fn (Biaya $biaya): array => [
                'jenis_biaya' => $biaya->jenis_biaya,
                'nominal' => (int) $biaya->nominal,
            ])
            ->all();

        if ($items === [] && (int) $this->biaya > 0) {
            $items = [
                ['jenis_biaya' => 'Biaya Sertifikasi', 'nominal' => (int) $this->biaya],
            ];
        }

        return $items;
    }

    /**
     * Total tagihan yang dibebankan ke peserta (angka bulat, FR-PAY-06).
     */
    public function totalTagihanPeserta(): int
    {
        return array_sum(array_column($this->biayaTagihan(), 'nominal'));
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
