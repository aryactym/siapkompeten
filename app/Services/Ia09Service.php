<?php

namespace App\Services;

use App\Models\Ia09Asesmen;
use App\Models\Ia09Item;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Ia09Service
{
    public function ensure(Pendaftaran $p): Ia09Asesmen
    {
        return Ia09Asesmen::firstOrCreate(['pendaftaran_id' => $p->id], ['institution_id' => $p->institution_id, 'status' => Ia09Asesmen::STATUS_DRAF]);
    }

    public function tambahItem(Ia09Asesmen $ia, string $pertanyaan, User $oleh): Ia09Item
    {
        if ($ia->isFinal()) {
            throw ValidationException::withMessages(['Ia09' => 'Sudah final.']);
        }
        $urutan = (int) $ia->items()->max('urutan') + 1;

        return Ia09Item::create(['institution_id' => $ia->institution_id, 'Ia09_asesmen_id' => $ia->id, 'urutan' => $urutan ?: 1, 'pertanyaan' => $pertanyaan]);
    }

    public function hapusItem(Ia09Item $item): void
    {
        if ($item->Ia09->isFinal()) {
            throw ValidationException::withMessages(['Ia09' => 'Sudah final.']);
        }
        $item->delete();
    }

    public function simpan(Ia09Asesmen $ia, array $items, ?string $umpanBalik, User $oleh): Ia09Asesmen
    {
        if ($ia->isFinal()) {
            throw ValidationException::withMessages(['Ia09' => 'Sudah final.']);
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

    public function finalkan(Ia09Asesmen $ia, User $oleh): Ia09Asesmen
    {
        if ($ia->isFinal()) {
            throw ValidationException::withMessages(['Ia09' => 'Sudah final.']);
        }
        if ($ia->items()->count() === 0) {
            throw ValidationException::withMessages(['Ia09' => 'Minimal satu pertanyaan.']);
        }
        if ($ia->items()->whereNull('pencapaian')->count() > 0) {
            throw ValidationException::withMessages(['Ia09' => 'Semua pertanyaan wajib dinilai Ya/Tidak.']);
        }
        DB::transaction(function () use ($ia, $oleh) {
            $ia->update(['status' => Ia09Asesmen::STATUS_FINAL, 'finalized_at' => now(), 'finalized_by' => $oleh->id]);
            activity('asesmen')->causedBy($oleh)->performedOn($ia->pendaftaran)->log('Asesor memfinalkan FR.IA.07.');
        });

        return $ia->fresh();
    }
}
