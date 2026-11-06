<?php

namespace App\Services;

use App\Models\Ia06Asesmen;
use App\Models\Ia06Soal;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Ia06Service
{
    public function ensure(Pendaftaran $pendaftaran): Ia06Asesmen
    {
        return Ia06Asesmen::firstOrCreate(
            ['pendaftaran_id' => $pendaftaran->id],
            ['institution_id' => $pendaftaran->institution_id, 'status' => Ia06Asesmen::STATUS_DRAF]
        );
    }

    /**
     * @param  array{pertanyaan:string, kunci?:?string}  $data
     */
    public function tambahSoal(Ia06Asesmen $ia06, array $data, User $oleh): Ia06Soal
    {
        $this->pastikanBelumFinal($ia06);

        $urutan = ((int) $ia06->soals()->max('urutan')) + 1;

        return Ia06Soal::create([
            'institution_id' => $ia06->institution_id,
            'ia06_asesmen_id' => $ia06->id,
            'urutan' => $urutan,
            'pertanyaan' => $data['pertanyaan'],
            'kunci' => $data['kunci'] ?? null,
        ]);
    }

    public function hapusSoal(Ia06Soal $soal): void
    {
        $soal->loadMissing('ia06');
        $this->pastikanBelumFinal($soal->ia06);

        $soal->delete();
    }

    /**
     * @param  array<int, array{id:string, pertanyaan?:string, kunci?:?string, jawaban_asesi?:?string, pencapaian?:?string}>  $soals
     */
    public function simpan(Ia06Asesmen $ia06, array $data, array $soals, User $oleh): Ia06Asesmen
    {
        $this->pastikanBelumFinal($ia06);

        DB::transaction(function () use ($ia06, $data, $soals) {
            $ia06->update([
                'judul' => $data['judul'] ?? null,
                'petunjuk' => $data['petunjuk'] ?? null,
                'umpan_balik' => $data['umpan_balik'] ?? null,
            ]);

            foreach ($soals as $row) {
                $soal = $ia06->soals()->whereKey($row['id'])->first();
                if (! $soal) {
                    continue;
                }

                $pencapaian = $row['pencapaian'] ?? null;
                if ($pencapaian !== null && ! in_array($pencapaian, ['1', '0'], true)) {
                    throw ValidationException::withMessages(['pencapaian' => 'Nilai pencapaian tidak valid.']);
                }

                $soal->update([
                    'pertanyaan' => $row['pertanyaan'] ?? $soal->pertanyaan,
                    'kunci' => $row['kunci'] ?? $soal->kunci,
                    'jawaban_asesi' => $row['jawaban_asesi'] ?? null,
                    'pencapaian' => $pencapaian === null ? null : $pencapaian === '1',
                ]);
            }
        });

        return $ia06->fresh();
    }

    public function finalkan(Ia06Asesmen $ia06, User $oleh): Ia06Asesmen
    {
        $this->pastikanBelumFinal($ia06);

        if ($ia06->soals()->count() === 0) {
            throw ValidationException::withMessages(['ia06' => 'Minimal satu soal esai.']);
        }

        if ($ia06->soals()->whereNull('pencapaian')->count() > 0) {
            throw ValidationException::withMessages(['ia06' => 'Semua soal wajib dinilai Ya/Tidak.']);
        }

        DB::transaction(function () use ($ia06, $oleh) {
            $ia06->update([
                'status' => Ia06Asesmen::STATUS_FINAL,
                'finalized_at' => now(),
                'finalized_by' => $oleh->id,
            ]);

            activity('asesmen')->causedBy($oleh)->performedOn($ia06->pendaftaran)->log('Asesor memfinalkan FR.IA.06.');
        });

        return $ia06->fresh();
    }

    private function pastikanBelumFinal(Ia06Asesmen $ia06): void
    {
        if ($ia06->isFinal()) {
            throw ValidationException::withMessages(['ia06' => 'FR.IA.06 sudah final.']);
        }
    }
}
