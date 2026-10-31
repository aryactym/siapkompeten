<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventAsesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'nama',
        'tahun',
        'periode',
        'tempat_ringkas',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'periode' => 'integer',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(JadwalAsesmen::class, 'event_id');
    }

    public function getPeriodeLabelAttribute(): ?string
    {
        if ($this->periode === null) {
            return null;
        }

        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ][$this->periode] ?? null;
    }
}
