<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ia05Soal extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const OPSI_LABELS = [
        'a' => 'A',
        'b' => 'B',
        'c' => 'C',
        'd' => 'D',
    ];

    protected $fillable = [
        'institution_id',
        'ia05_asesmen_id',
        'urutan',
        'pertanyaan',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'kunci',
        'jawaban_asesi',
    ];

    public function ia05(): BelongsTo
    {
        return $this->belongsTo(Ia05Asesmen::class, 'ia05_asesmen_id');
    }

    public function benar(): bool
    {
        return $this->jawaban_asesi !== null && $this->jawaban_asesi === $this->kunci;
    }

    /**
     * @return array<string, string|null>
     */
    public function opsi(): array
    {
        return [
            'a' => $this->opsi_a,
            'b' => $this->opsi_b,
            'c' => $this->opsi_c,
            'd' => $this->opsi_d,
        ];
    }
}
