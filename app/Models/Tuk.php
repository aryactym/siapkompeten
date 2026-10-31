<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;

class Tuk extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'kode_tuk',
        'nama_tuk',
        'jenis',
        'penanggung_jawab',
        'telepon',
        'email',
        'alamat',
        'province_code',
        'city_code',
        'is_verified',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_code', 'code');
    }
}
