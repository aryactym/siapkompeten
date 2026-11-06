<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TinjauanProsesAsesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const STATUS_DRAF = 'draf';

    public const STATUS_FINAL = 'final';

    /**
     * Aspek prosedur asesmen FR.AK.06.
     *
     * @var array<string, string>
     */
    public const PROSES = [
        'rencana' => 'Rencana asesmen',
        'persiapan' => 'Persiapan asesmen',
        'implementasi' => 'Implementasi asesmen',
        'keputusan' => 'Keputusan asesmen',
        'umpan_balik' => 'Umpan balik asesmen',
    ];

    /**
     * Prinsip asesmen FR.AK.06.
     *
     * @var array<string, string>
     */
    public const PRINSIP = [
        'validitas' => 'Validitas',
        'reliabel' => 'Reliabel',
        'fleksibel' => 'Fleksibel',
        'adil' => 'Adil',
    ];

    /**
     * Dimensi kompetensi FR.AK.06.
     *
     * @var array<string, string>
     */
    public const DIMENSI = [
        'task_skills' => 'Task Skills',
        'task_management' => 'Task Management Skills',
        'contingency' => 'Contingency Management Skills',
        'job_role' => 'Job Role/Environment Skills',
        'transfer' => 'Transfer Skills',
    ];

    protected $fillable = [
        'institution_id',
        'jadwal_id',
        'asesor_id',
        'proses_json',
        'rekomendasi_proses',
        'dimensi_json',
        'rekomendasi_dimensi',
        'komentar',
        'status',
        'finalized_at',
        'finalized_by',
    ];

    protected function casts(): array
    {
        return [
            'proses_json' => 'array',
            'dimensi_json' => 'array',
            'finalized_at' => 'datetime',
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

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function nilaiProses(string $proses, string $prinsip): ?string
    {
        return $this->proses_json[$proses][$prinsip] ?? null;
    }

    public function nilaiDimensi(string $dimensi): ?string
    {
        return $this->dimensi_json[$dimensi] ?? null;
    }
}
