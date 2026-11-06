<?php

namespace App\Services;

use App\Models\Ia07Asesmen;
use App\Models\Ia07Item;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Ia07Service
{
    public function ensure(Pendaftaran $p): Ia07Asesmen
    {
        return Ia07Asesmen::firstOrCreate(['pendaftaran_id' => $p->id], ['institution_id' => $p->institution_id, 'status' => Ia07Asesmen::STATUS_DRAF]);
    }

    public function tambahItem(Ia07Asesmen $ia, string $pertanyaan, User $oleh): Ia07Item
    {
        if ($ia->isFinal()) {
            throw ValidationException::withMessages(['ia07' => 'Sudah final.']);
        }
        $urutan = (int) $ia->items()->max('urutan') + 1;

        return Ia07Item::create(['institution_id' => $ia->institution_id, 'ia07_asesmen_id' => $ia->id, 'urutan' => $urutan ?: 1, 'pertanyaan' => $pertanyaan]);
    }

    public function hapusItem(Ia07Item $item): void
    {
        if ($item->ia07->isFinal()) {
            throw ValidationException::withMessages(['ia07' => 'Sudah final.']);
        }
        $item->delete();
    }

    public function simpan(Ia07Asesmen $ia, array $items, ?string $umpanBalik, User $oleh): Ia07Asesmen
    {
        if ($ia->isFinal()) {
            throw ValidationException::withMessages(['ia07' => 'Sudah final.']);
        }
        DB::transaction(function () use ($ia, $items, $umpanBalik) {
            foreach ($items as $row) {
                $item = $ia->items()->whereKey($row['id'])->first();
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
            $ia->update(['umpan_balik' => $umpanBalik]);
        });

        return $ia->fresh();
    }

    public function finalkan(Ia07Asesmen $ia, User $oleh): Ia07Asesmen
    {
        if ($ia->isFinal()) {
            throw ValidationException::withMessages(['ia07' => 'Sudah final.']);
        }
        if ($ia->items()->count() === 0) {
            throw ValidationException::withMessages(['ia07' => 'Minimal satu pertanyaan.']);
        }
        if ($ia->items()->whereNull('pencapaian')->count() > 0) {
            throw ValidationException::withMessages(['ia07' => 'Semua pertanyaan wajib dinilai Ya/Tidak.']);
        }
        DB::transaction(function () use ($ia, $oleh) {
            $ia->update(['status' => Ia07Asesmen::STATUS_FINAL, 'finalized_at' => now(), 'finalized_by' => $oleh->id]);
            activity('asesmen')->causedBy($oleh)->performedOn($ia->pendaftaran)->log('Asesor memfinalkan FR.IA.07.');
        });

        return $ia->fresh();
    }
}
