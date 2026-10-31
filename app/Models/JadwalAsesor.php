<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalAsesor extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'jadwal_id',
        'asesor_id',
        'peran',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalAsesmen::class, 'jadwal_id');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class);
    }
}
