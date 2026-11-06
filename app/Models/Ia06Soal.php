<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ia06Soal extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    protected $fillable = [
        'institution_id',
        'ia06_asesmen_id',
        'urutan',
        'pertanyaan',
        'kunci',
        'jawaban_asesi',
        'pencapaian',
    ];

    protected function casts(): array
    {
        return [
            'pencapaian' => 'boolean',
        ];
    }

    public function ia06(): BelongsTo
    {
        return $this->belongsTo(Ia06Asesmen::class, 'ia06_asesmen_id');
    }
}
