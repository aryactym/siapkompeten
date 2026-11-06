<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UmpanBalikAsesmen extends Model
{
    use BelongsToTenant, HasFactory, HasUuids;

    public const JAWABAN_YA = 'ya';

    public const JAWABAN_TIDAK = 'tidak';

    public const JAWABAN_LABELS = [
        self::JAWABAN_YA => 'Ya',
        self::JAWABAN_TIDAK => 'Tidak',
    ];

    /**
     * Komponen FR.AK.03 sesuai template MUK.
     *
     * @var array<string, string>
     */
    public const KOMPONEN = [
        'p1' => 'Saya mendapatkan penjelasan yang cukup memadai mengenai proses asesmen/uji kompetensi',
        'p2' => 'Saya diberikan kesempatan untuk mempelajari standar kompetensi yang akan diujikan dan menilai diri sendiri terhadap pencapaiannya',
        'p3' => 'Asesor memberikan kesempatan untuk mendiskusikan/menegosiasikan metoda, instrumen dan sumber asesmen serta jadwal asesmen',
        'p4' => 'Asesor berusaha menggali seluruh bukti pendukung yang sesuai dengan latar belakang pelatihan dan pengalaman yang saya miliki',
        'p5' => 'Saya sepenuhnya diberikan kesempatan untuk mendemonstrasikan kompetensi yang saya miliki selama asesmen',
        'p6' => 'Saya mendapatkan penjelasan yang memadai mengenai keputusan asesmen',
        'p7' => 'Asesor memberikan umpan balik yang mendukung setelah asesmen serta tindak lanjutnya',
        'p8' => 'Asesor bersama saya mempelajari semua dokumen asesmen serta menandatanganinya',
        'p9' => 'Saya mendapatkan jaminan kerahasiaan hasil asesmen serta penjelasan penanganan dokumen asesmen',
        'p10' => 'Asesor menggunakan keterampilan komunikasi yang efektif selama asesmen',
    ];

    protected $fillable = [
        'institution_id',
        'pendaftaran_id',
        'komponen_json',
        'catatan',
        'disubmit_pada',
        'disubmit_oleh',
    ];

    protected function casts(): array
    {
        return [
            'komponen_json' => 'array',
            'disubmit_pada' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function pengisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disubmit_oleh');
    }

    public function isSubmitted(): bool
    {
        return $this->disubmit_pada !== null;
    }

    /**
     * @return array<string, string>
     */
    public function komponen(): array
    {
        return $this->komponen_json ?? [];
    }

    public function jawaban(string $key): ?string
    {
        return $this->komponen()[$key] ?? null;
    }
}
