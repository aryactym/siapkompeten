<?php

namespace App\Services;

use App\Models\Pendaftaran;
use App\Models\UmpanBalikAsesmen;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UmpanBalikService
{
    /**
     * Simpan dan kirim FR.AK.03 (umpan balik asesi).
     *
     * @param  array<string, string>  $komponen
     */
    public function submit(Pendaftaran $pendaftaran, array $komponen, ?string $catatan, User $oleh): UmpanBalikAsesmen
    {
        if ($pendaftaran->status !== Pendaftaran::STATUS_SELESAI) {
            throw ValidationException::withMessages([
                'umpan_balik' => 'Umpan balik hanya dapat diisi setelah keputusan asesmen ditetapkan.',
            ]);
        }

        $umpanBalik = UmpanBalikAsesmen::firstOrNew([
            'pendaftaran_id' => $pendaftaran->id,
        ]);

        if ($umpanBalik->exists && $umpanBalik->isSubmitted()) {
            throw ValidationException::withMessages([
                'umpan_balik' => 'Umpan balik sudah dikirim dan tidak dapat diubah.',
            ]);
        }

        $bersih = [];

        foreach (array_keys(UmpanBalikAsesmen::KOMPONEN) as $key) {
            $jawaban = $komponen[$key] ?? null;

            if (! is_string($jawaban) || ! array_key_exists($jawaban, UmpanBalikAsesmen::JAWABAN_LABELS)) {
                throw ValidationException::withMessages([
                    'komponen.'.$key => 'Seluruh komponen wajib dijawab Ya atau Tidak.',
                ]);
            }

            $bersih[$key] = $jawaban;
        }

        return DB::transaction(function () use ($umpanBalik, $pendaftaran, $bersih, $catatan, $oleh): UmpanBalikAsesmen {
            $umpanBalik->fill([
                'institution_id' => $pendaftaran->institution_id,
                'komponen_json' => $bersih,
                'catatan' => $catatan,
                'disubmit_pada' => now(),
                'disubmit_oleh' => $oleh->id,
            ]);
            $umpanBalik->save();

            activity('asesmen')
                ->causedBy($oleh)
                ->performedOn($pendaftaran)
                ->withProperties(['umpan_balik_id' => $umpanBalik->id])
                ->log('Asesi mengirim umpan balik FR.AK.03.');

            return $umpanBalik;
        });
    }

    /**
     * Rekap jawaban Ya/Tidak.
     *
     * @return array{ya: int, tidak: int, total: int}
     */
    public function rekap(UmpanBalikAsesmen $umpanBalik): array
    {
        $komponen = $umpanBalik->komponen();

        return [
            'ya' => count(array_filter($komponen, fn ($v) => $v === UmpanBalikAsesmen::JAWABAN_YA)),
            'tidak' => count(array_filter($komponen, fn ($v) => $v === UmpanBalikAsesmen::JAWABAN_TIDAK)),
            'total' => count(UmpanBalikAsesmen::KOMPONEN),
        ];
    }
}
