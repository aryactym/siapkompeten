<?php

namespace App\Services;

use App\Models\Ia04Asesmen;
use App\Models\Ia04Item;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Ia04Service
{
    public function ensure(Pendaftaran $pendaftaran): Ia04Asesmen
    {
        return Ia04Asesmen::firstOrCreate(
            ['pendaftaran_id' => $pendaftaran->id],
            ['institution_id' => $pendaftaran->institution_id, 'status' => Ia04Asesmen::STATUS_DRAF]
        );
    }

    public function tambahItem(Ia04Asesmen $ia04, string $pertanyaan, ?string $lingkup, User $oleh): Ia04Item
    {
        $this->pastikanBelumFinal($ia04);

        $urutan = ((int) $ia04->items()->max('urutan')) + 1;

        return Ia04Item::create([
            'institution_id' => $ia04->institution_id,
            'ia04_asesmen_id' => $ia04->id,
            'urutan' => $urutan,
            'lingkup' => $lingkup,
            'pertanyaan' => $pertanyaan,
        ]);
    }

    public function hapusItem(Ia04Item $item): void
    {
        $item->loadMissing('ia04');
        $this->pastikanBelumFinal($item->ia04);

        $item->delete();
    }

    /**
     * @param  array<int, array{id:string, lingkup?:?string, tanggapan?:?string, pencapaian?:?string}>  $items
     */
    public function simpan(Ia04Asesmen $ia04, array $data, array $items, User $oleh): Ia04Asesmen
    {
        $this->pastikanBelumFinal($ia04);

        DB::transaction(function () use ($ia04, $data, $items) {
            $ia04->update([
                'judul_kegiatan' => $data['judul_kegiatan'] ?? null,
                'skenario' => $data['skenario'] ?? null,
                'hal_dipersiapkan' => $data['hal_dipersiapkan'] ?? null,
                'waktu_persiapan' => $data['waktu_persiapan'] ?? null,
                'hasil_dipresentasikan' => $data['hasil_dipresentasikan'] ?? null,
                'waktu_presentasi' => $data['waktu_presentasi'] ?? null,
                'umpan_balik' => $data['umpan_balik'] ?? null,
                'rekomendasi' => $data['rekomendasi'] ?? null,
            ]);

            foreach ($items as $row) {
                $item = $ia04->items()->whereKey($row['id'])->first();
                if (! $item) {
                    continue;
                }

                $pencapaian = $row['pencapaian'] ?? null;
                if ($pencapaian !== null && ! in_array($pencapaian, ['1', '0'], true)) {
                    throw ValidationException::withMessages(['pencapaian' => 'Nilai pencapaian tidak valid.']);
                }

                $item->update([
                    'lingkup' => $row['lingkup'] ?? $item->lingkup,
                    'tanggapan' => $row['tanggapan'] ?? null,
                    'pencapaian' => $pencapaian === null ? null : $pencapaian === '1',
                ]);
            }
        });

        return $ia04->fresh();
    }

    public function finalkan(Ia04Asesmen $ia04, User $oleh): Ia04Asesmen
    {
        $this->pastikanBelumFinal($ia04);

        if ($ia04->items()->count() === 0) {
            throw ValidationException::withMessages(['ia04' => 'Minimal satu butir pertanyaan penilaian.']);
        }

        if ($ia04->items()->whereNull('pencapaian')->count() > 0) {
            throw ValidationException::withMessages(['ia04' => 'Semua butir wajib dinilai Ya/Tidak.']);
        }

        if (blank($ia04->rekomendasi)) {
            throw ValidationException::withMessages(['rekomendasi' => 'Rekomendasi asesor wajib dipilih.']);
        }

        DB::transaction(function () use ($ia04, $oleh) {
            $ia04->update([
                'status' => Ia04Asesmen::STATUS_FINAL,
                'finalized_at' => now(),
                'finalized_by' => $oleh->id,
            ]);

            activity('asesmen')->causedBy($oleh)->performedOn($ia04->pendaftaran)->log('Asesor memfinalkan FR.IA.04.');
        });

        return $ia04->fresh();
    }

    private function pastikanBelumFinal(Ia04Asesmen $ia04): void
    {
        if ($ia04->isFinal()) {
            throw ValidationException::withMessages(['ia04' => 'FR.IA.04 sudah final.']);
        }
    }
}
