<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekeningBank extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'nama_bank',
        'no_rekening',
        'atas_nama',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
        ];
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
