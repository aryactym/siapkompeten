<?php

namespace App\Services;

use App\Models\HasilAsesmen;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\Pleno;
use App\Models\PlenoPeserta;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlenoService
{
    public function ensure(JadwalAsesmen $jadwal): Pleno
    {
        $pleno = Pleno::firstOrCreate(
            ['jadwal_id' => $jadwal->id],
            [
                'institution_id' => $jadwal->institution_id,
                'status' => Pleno::STATUS_DRAF,
                'tanggal' => $jadwal->tanggal,
            ],
        );

        if ($pleno->pesertas()->count() === 0) {
            foreach ($jadwal->pendaftarans()->with('hasilAsesmen')->get() as $pendaftaran) {
                if ($pendaftaran->status !== Pendaftaran::STATUS_SELESAI) {
                    continue;
                }

                $hasil = $pendaftaran->hasilAsesmen;

                PlenoPeserta::create([
                    'institution_id' => $jadwal->institution_id,
                    'pleno_id' => $pleno->id,
                    'pendaftaran_id' => $pendaftaran->id,
                    'keputusan' => $hasil?->rekomendasi === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN
                        ? PlenoPeserta::KEPUTUSAN_KOMPETEN
                        : PlenoPeserta::KEPUTUSAN_BELUM_KOMPETEN,
                ]);
            }
        }

        return $pleno;
    }

    /**
     * @param  array<int, array{pendaftaran_id: string, keputusan: string, catatan?: string|null}>  $peserta
     */
    public function simpan(JadwalAsesmen $jadwal, array $data, array $peserta, User $oleh): Pleno
    {
        $pleno = $this->ensure($jadwal);

        if ($pleno->isFinal()) {
            throw ValidationException::withMessages([
                'pleno' => 'Pleno sudah final dan tidak dapat diubah.',
            ]);
        }

        return DB::transaction(function () use ($pleno, $jadwal, $data, $peserta): Pleno {
            $pleno->fill([
                'institution_id' => $jadwal->institution_id,
                'nomor_ba' => $data['nomor_ba'] ?? null,
                'tanggal' => $data['tanggal'] ?? $pleno->tanggal,
                'keputusan' => $data['keputusan'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ]);
            $pleno->save();

            foreach ($peserta as $row) {
                $pesertaModel = $pleno->pesertas()->where('pendaftaran_id', $row['pendaftaran_id'])->first();

                if ($pesertaModel === null) {
                    continue;
                }

                $pesertaModel->update([
                    'keputusan' => $row['keputusan'],
                    'catatan' => $row['catatan'] ?? null,
                ]);
            }

            return $pleno;
        });
    }

    public function finalkan(JadwalAsesmen $jadwal, User $oleh): Pleno
    {
        $pleno = $this->ensure($jadwal);

        if ($pleno->isFinal()) {
            throw ValidationException::withMessages([
                'pleno' => 'Pleno sudah final.',
            ]);
        }

        if (blank($pleno->keputusan)) {
            throw ValidationException::withMessages([
                'keputusan' => 'Keputusan pleno wajib diisi sebelum finalisasi.',
            ]);
        }

        $belum = $pleno->pesertas()->whereNull('keputusan')->count();

        if ($belum > 0) {
            throw ValidationException::withMessages([
                'pleno' => sprintf('%d peserta belum mendapat keputusan.', $belum),
            ]);
        }

        return DB::transaction(function () use ($pleno, $oleh): Pleno {
            $pleno->update([
                'status' => Pleno::STATUS_FINAL,
                'finalized_at' => now(),
                'finalized_by' => $oleh->id,
            ]);

            activity('asesmen')
                ->causedBy($oleh)
                ->performedOn($pleno->jadwal)
                ->withProperties(['pleno_id' => $pleno->id, 'keputusan' => $pleno->keputusan])
                ->log('Pleno asesmen difinalkan.');

            return $pleno;
        });
    }
}
