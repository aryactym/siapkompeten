<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkemaPersyaratan extends Model
{
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'skema_id',
        'nama_dokumen',
        'keterangan',
        'is_required',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(Skema::class);
    }
}
