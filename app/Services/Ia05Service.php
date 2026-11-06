<?php

namespace App\Services;

use App\Models\Ia05Asesmen;
use App\Models\Ia05Soal;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Ia05Service
{
    public function ensure(Pendaftaran $pendaftaran): Ia05Asesmen
    {
        return Ia05Asesmen::firstOrCreate(
            ['pendaftaran_id' => $pendaftaran->id],
            ['institution_id' => $pendaftaran->institution_id, 'status' => Ia05Asesmen::STATUS_DRAF]
        );
    }

    /**
     * @param  array{pertanyaan:string, opsi_a:string, opsi_b:string, opsi_c?:?string, opsi_d?:?string, kunci:string}  $data
     */
    public function tambahSoal(Ia05Asesmen $ia05, array $data, User $oleh): Ia05Soal
    {
        $this->pastikanBelumFinal($ia05);
        $this->pastikanKunciTersedia($data);

        $urutan = ((int) $ia05->soals()->max('urutan')) + 1;

        return Ia05Soal::create([
            'institution_id' => $ia05->institution_id,
            'ia05_asesmen_id' => $ia05->id,
            'urutan' => $urutan,
            'pertanyaan' => $data['pertanyaan'],
            'opsi_a' => $data['opsi_a'],
            'opsi_b' => $data['opsi_b'],
            'opsi_c' => $data['opsi_c'] ?? null,
            'opsi_d' => $data['opsi_d'] ?? null,
            'kunci' => $data['kunci'],
        ]);
    }

    public function hapusSoal(Ia05Soal $soal): void
    {
        $soal->loadMissing('ia05');
        $this->pastikanBelumFinal($soal->ia05);

        $soal->delete();
    }

    /**
     * @param  array<int, array{id:string, pertanyaan?:string, opsi_a?:string, opsi_b?:string, opsi_c?:?string, opsi_d?:?string, kunci?:string, jawaban_asesi?:?string}>  $soals
     */
    public function simpan(Ia05Asesmen $ia05, array $data, array $soals, User $oleh): Ia05Asesmen
    {
        $this->pastikanBelumFinal($ia05);

        DB::transaction(function () use ($ia05, $data, $soals) {
            $ia05->update([
                'judul' => $data['judul'] ?? null,
                'petunjuk' => $data['petunjuk'] ?? null,
                'umpan_balik' => $data['umpan_balik'] ?? null,
            ]);

            foreach ($soals as $row) {
                $soal = $ia05->soals()->whereKey($row['id'])->first();
                if (! $soal) {
                    continue;
                }

                $kunci = $row['kunci'] ?? $soal->kunci;
                $opsiC = array_key_exists('opsi_c', $row) ? $row['opsi_c'] : $soal->opsi_c;
                $opsiD = array_key_exists('opsi_d', $row) ? $row['opsi_d'] : $soal->opsi_d;

                if (in_array($kunci, ['c', 'd'], true) && blank($kunci === 'c' ? $opsiC : $opsiD)) {
                    throw ValidationException::withMessages(['kunci' => 'Kunci jawaban menunjuk opsi yang kosong.']);
                }

                $jawaban = $row['jawaban_asesi'] ?? null;
                if ($jawaban !== null && ! in_array($jawaban, ['a', 'b', 'c', 'd'], true)) {
                    throw ValidationException::withMessages(['jawaban_asesi' => 'Jawaban asesi tidak valid.']);
                }

                $soal->update([
                    'pertanyaan' => $row['pertanyaan'] ?? $soal->pertanyaan,
                    'opsi_a' => $row['opsi_a'] ?? $soal->opsi_a,
                    'opsi_b' => $row['opsi_b'] ?? $soal->opsi_b,
                    'opsi_c' => $opsiC,
                    'opsi_d' => $opsiD,
                    'kunci' => $kunci,
                    'jawaban_asesi' => $jawaban,
                ]);
            }
        });

        return $ia05->fresh();
    }

    public function finalkan(Ia05Asesmen $ia05, User $oleh): Ia05Asesmen
    {
        $this->pastikanBelumFinal($ia05);

        if ($ia05->soals()->count() === 0) {
            throw ValidationException::withMessages(['ia05' => 'Minimal satu soal pilihan ganda.']);
        }

        DB::transaction(function () use ($ia05, $oleh) {
            $ia05->update([
                'status' => Ia05Asesmen::STATUS_FINAL,
                'finalized_at' => now(),
                'finalized_by' => $oleh->id,
            ]);

            activity('asesmen')->causedBy($oleh)->performedOn($ia05->pendaftaran)->log('Asesor memfinalkan FR.IA.05.');
        });

        return $ia05->fresh();
    }

    /**
     * @param  array{kunci:string, opsi_c?:?string, opsi_d?:?string}  $data
     */
    private function pastikanKunciTersedia(array $data): void
    {
        $kunci = $data['kunci'];

        if ($kunci === 'c' && blank($data['opsi_c'] ?? null)) {
            throw ValidationException::withMessages(['opsi_c' => 'Opsi C kosong tetapi dipilih sebagai kunci.']);
        }

        if ($kunci === 'd' && blank($data['opsi_d'] ?? null)) {
            throw ValidationException::withMessages(['opsi_d' => 'Opsi D kosong tetapi dipilih sebagai kunci.']);
        }
    }

    private function pastikanBelumFinal(Ia05Asesmen $ia05): void
    {
        if ($ia05->isFinal()) {
            throw ValidationException::withMessages(['ia05' => 'FR.IA.05 sudah final.']);
        }
    }
}
