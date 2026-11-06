<?php

namespace App\Services;

use App\Models\Asesor;
use App\Models\JadwalAsesmen;
use App\Models\TinjauanProsesAsesmen;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TinjauanProsesService
{
    public function __construct(private readonly LaporanAsesmenService $laporan) {}

    /**
     * @param  array<string, array<string, string|null>>  $proses
     * @param  array<string, string|null>  $dimensi
     */
    public function simpan(
        JadwalAsesmen $jadwal,
        array $proses,
        ?string $rekomendasiProses,
        array $dimensi,
        ?string $rekomendasiDimensi,
        ?string $komentar,
        User $oleh,
    ): TinjauanProsesAsesmen {
        $tinjauan = TinjauanProsesAsesmen::firstOrNew(['jadwal_id' => $jadwal->id]);

        if ($tinjauan->exists && $tinjauan->isFinal()) {
            throw ValidationException::withMessages([
                'tinjauan' => 'Tinjauan proses sudah final dan tidak dapat diubah.',
            ]);
        }

        $asesor = Asesor::where('user_id', $oleh->id)->first();

        $tinjauan->fill([
            'institution_id' => $jadwal->institution_id,
            'asesor_id' => $asesor?->id,
            'proses_json' => $this->bersihkanProses($proses),
            'rekomendasi_proses' => $rekomendasiProses,
            'dimensi_json' => $this->bersihkanDimensi($dimensi),
            'rekomendasi_dimensi' => $rekomendasiDimensi,
            'komentar' => $komentar,
        ]);
        $tinjauan->save();

        return $tinjauan;
    }

    public function finalkan(JadwalAsesmen $jadwal, User $oleh): TinjauanProsesAsesmen
    {
        $tinjauan = TinjauanProsesAsesmen::firstOrNew(['jadwal_id' => $jadwal->id]);

        if ($tinjauan->exists && $tinjauan->isFinal()) {
            throw ValidationException::withMessages([
                'tinjauan' => 'Tinjauan proses sudah final.',
            ]);
        }

        $penghambat = $this->laporan->penghambat($jadwal);

        if ($penghambat->isNotEmpty()) {
            $nama = $penghambat
                ->map(fn ($pendaftaran) => $pendaftaran->asesi?->user?->name ?? $pendaftaran->no_pendaftaran)
                ->implode(', ');

            throw ValidationException::withMessages([
                'tinjauan' => sprintf('Tinjauan belum dapat difinalkan; asesmen belum selesai untuk: %s.', $nama),
            ]);
        }

        $asesor = Asesor::where('user_id', $oleh->id)->first();

        return DB::transaction(function () use ($tinjauan, $jadwal, $asesor, $oleh): TinjauanProsesAsesmen {
            $tinjauan->fill([
                'institution_id' => $jadwal->institution_id,
                'asesor_id' => $tinjauan->asesor_id ?? $asesor?->id,
                'status' => TinjauanProsesAsesmen::STATUS_FINAL,
                'finalized_at' => now(),
                'finalized_by' => $oleh->id,
            ]);
            $tinjauan->save();

            activity('asesmen')
                ->causedBy($oleh)
                ->performedOn($jadwal)
                ->withProperties(['tinjauan_id' => $tinjauan->id])
                ->log('Asesor memfinalkan tinjauan proses asesmen (FR.AK.06).');

            return $tinjauan;
        });
    }

    /**
     * @param  array<string, array<string, string|null>>  $proses
     * @return array<string, array<string, string>>
     */
    private function bersihkanProses(array $proses): array
    {
        $bersih = [];

        foreach (array_keys(TinjauanProsesAsesmen::PROSES) as $aspek) {
            foreach (array_keys(TinjauanProsesAsesmen::PRINSIP) as $prinsip) {
                $nilai = trim((string) ($proses[$aspek][$prinsip] ?? ''));

                if ($nilai !== '') {
                    $bersih[$aspek][$prinsip] = $nilai;
                }
            }
        }

        return $bersih;
    }

    /**
     * @param  array<string, string|null>  $dimensi
     * @return array<string, string>
     */
    private function bersihkanDimensi(array $dimensi): array
    {
        $bersih = [];

        foreach (array_keys(TinjauanProsesAsesmen::DIMENSI) as $key) {
            $nilai = trim((string) ($dimensi[$key] ?? ''));

            if ($nilai !== '') {
                $bersih[$key] = $nilai;
            }
        }

        return $bersih;
    }
}
