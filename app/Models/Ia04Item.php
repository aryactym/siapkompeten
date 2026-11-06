<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ia04Item extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'ia04_asesmen_id',
        'urutan',
        'lingkup',
        'pertanyaan',
        'tanggapan',
        'pencapaian',
    ];

    protected function casts(): array
    {
        return [
            'pencapaian' => 'boolean',
        ];
    }

    public function ia04(): BelongsTo
    {
        return $this->belongsTo(Ia04Asesmen::class, 'ia04_asesmen_id');
    }
}
