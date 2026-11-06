<?php

namespace App\Services;

use App\Models\Ia03Asesmen;
use App\Models\Ia03Item;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Ia03Service
{
    public function ensure(Pendaftaran $p): Ia03Asesmen
    {
        return Ia03Asesmen::firstOrCreate(
            ['pendaftaran_id' => $p->id],
            ['institution_id' => $p->institution_id, 'status' => Ia03Asesmen::STATUS_DRAF],
        );
    }

    public function tambahItem(Ia03Asesmen $ia03, string $pertanyaan, User $oleh): Ia03Item
    {
        if ($ia03->isFinal()) {
            throw ValidationException::withMessages(['ia03' => 'Sudah final.']);
        }
        $urutan = (int) $ia03->items()->max('urutan') + 1;

        return Ia03Item::create([
            'institution_id' => $ia03->institution_id,
            'ia03_asesmen_id' => $ia03->id,
            'urutan' => $urutan ?: 1,
            'pertanyaan' => $pertanyaan,
        ]);
    }

    public function hapusItem(Ia03Item $item): void
    {
        if ($item->ia03->isFinal()) {
            throw ValidationException::withMessages(['ia03' => 'Sudah final.']);
        }
        $item->delete();
    }

    /**
     * @param  array<int, array{id:string, tanggapan?:string|null, pencapaian?:string|null, catatan?:string|null}>  $items
     */
    public function simpan(Ia03Asesmen $ia03, array $items, ?string $umpanBalik, User $oleh): Ia03Asesmen
    {
        if ($ia03->isFinal()) {
            throw ValidationException::withMessages(['ia03' => 'Sudah final.']);
        }
        DB::transaction(function () use ($ia03, $items, $umpanBalik) {
            foreach ($items as $row) {
                $item = $ia03->items()->whereKey($row['id'])->first();
                if (! $item) {
                    continue;
                }
                $pencapaian = $row['pencapaian'] ?? null;
                if ($pencapaian !== null && ! in_array($pencapaian, ['1', '0'], true)) {
                    throw ValidationException::withMessages(['pencapaian' => 'Nilai pencapaian tidak valid.']);
                }
                $item->update([
                    'tanggapan' => $row['tanggapan'] ?? null,
                    'pencapaian' => $pencapaian === null ? null : ($pencapaian === '1'),
                    'catatan' => $row['catatan'] ?? null,
                ]);
            }
            $ia03->update(['umpan_balik' => $umpanBalik]);
        });

        return $ia03->fresh();
    }

    public function finalkan(Ia03Asesmen $ia03, User $oleh): Ia03Asesmen
    {
        if ($ia03->isFinal()) {
            throw ValidationException::withMessages(['ia03' => 'Sudah final.']);
        }
        if ($ia03->items()->count() === 0) {
            throw ValidationException::withMessages(['ia03' => 'Minimal satu pertanyaan.']);
        }
        if ($ia03->items()->whereNull('pencapaian')->count() > 0) {
            throw ValidationException::withMessages(['ia03' => 'Semua pertanyaan wajib dinilai Ya/Tidak.']);
        }
        DB::transaction(function () use ($ia03, $oleh) {
            $ia03->update(['status' => Ia03Asesmen::STATUS_FINAL, 'finalized_at' => now(), 'finalized_by' => $oleh->id]);
            activity('asesmen')->causedBy($oleh)->performedOn($ia03->pendaftaran)->log('Asesor memfinalkan FR.IA.03.');
        });

        return $ia03->fresh();
    }
}
