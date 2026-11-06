<?php

namespace App\Services;

use App\Models\InstrumenAsesmen;
use App\Models\InstrumenAsesmenItem;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstrumenAsesmenService
{
    /**
     * Ambil (atau buat) instrumen untuk pendaftaran + jenis, lengkap dengan
     * baris item per KUK skema.
     */
    public function ensure(Pendaftaran $pendaftaran, string $jenis): InstrumenAsesmen
    {
        if (! array_key_exists($jenis, InstrumenAsesmen::JENIS_LABELS)) {
            throw ValidationException::withMessages([
                'jenis' => 'Jenis instrumen tidak dikenal.',
            ]);
        }

        return DB::transaction(function () use ($pendaftaran, $jenis): InstrumenAsesmen {
            $instrumen = InstrumenAsesmen::firstOrCreate(
                [
                    'pendaftaran_id' => $pendaftaran->id,
                    'jenis' => $jenis,
                ],
                [
                    'institution_id' => $pendaftaran->institution_id,
                    'status' => InstrumenAsesmen::STATUS_DRAF,
                ],
            );

            $urutan = 1;
            foreach ($pendaftaran->skema->unitKompetensis as $unit) {
                foreach ($unit->elemenKompetensis as $elemen) {
                    foreach ($elemen->kriteriaUnjukKerjas as $kuk) {
                        InstrumenAsesmenItem::firstOrCreate(
                            [
                                'instrumen_asesmen_id' => $instrumen->id,
                                'kuk_id' => $kuk->id,
                            ],
                            [
                                'institution_id' => $pendaftaran->institution_id,
                                'unit_kompetensi_id' => $unit->id,
                                'urutan' => $urutan,
                            ],
                        );

                        $urutan++;
                    }
                }
            }

            return $instrumen;
        });
    }

    /**
     * Simpan hasil observasi/praktik per item.
     *
     * @param  array<string, array{hasil?: string|null, catatan?: string|null}>  $items
     */
    public function simpan(InstrumenAsesmen $instrumen, array $items): void
    {
        if ($instrumen->isFinal()) {
            throw ValidationException::withMessages([
                'instrumen' => 'Instrumen sudah final dan tidak dapat diubah.',
            ]);
        }

        DB::transaction(function () use ($instrumen, $items): void {
            foreach ($items as $itemId => $data) {
                $item = $instrumen->items()->whereKey($itemId)->first();

                if ($item === null) {
                    continue;
                }

                $hasil = $data['hasil'] ?? null;
                if ($hasil !== null && ! array_key_exists($hasil, InstrumenAsesmenItem::HASIL_LABELS)) {
                    throw ValidationException::withMessages([
                        'items' => 'Nilai hasil instrumen tidak dikenal.',
                    ]);
                }

                $item->update([
                    'hasil' => $hasil,
                    'catatan' => $data['catatan'] ?? null,
                ]);
            }
        });
    }

    public function finalkan(InstrumenAsesmen $instrumen, User $oleh): void
    {
        if ($instrumen->isFinal()) {
            throw ValidationException::withMessages([
                'instrumen' => 'Instrumen sudah final.',
            ]);
        }

        $belumDinilai = $instrumen->items()->whereNull('hasil')->count();

        if ($belumDinilai > 0) {
            throw ValidationException::withMessages([
                'instrumen' => sprintf('%d item belum dinilai. Seluruh item wajib dinilai sebelum finalisasi.', $belumDinilai),
            ]);
        }

        DB::transaction(function () use ($instrumen, $oleh): void {
            $instrumen->update([
                'status' => InstrumenAsesmen::STATUS_FINAL,
                'finalized_at' => now(),
                'finalized_by' => $oleh->id,
            ]);

            activity('asesmen')
                ->causedBy($oleh)
                ->performedOn($instrumen->pendaftaran)
                ->withProperties(['jenis' => $instrumen->jenis])
                ->log('Asesor memfinalkan instrumen asesmen.');
        });
    }

    /**
     * Rekap jumlah item per hasil.
     *
     * @return array{total: int, tercapai: int, belum_tercapai: int, belum_dinilai: int}
     */
    public function rekap(InstrumenAsesmen $instrumen): array
    {
        $items = $instrumen->items()->get();

        return [
            'total' => $items->count(),
            'tercapai' => $items->where('hasil', InstrumenAsesmenItem::HASIL_TERCAPAI)->count(),
            'belum_tercapai' => $items->where('hasil', InstrumenAsesmenItem::HASIL_BELUM_TERCAPAI)->count(),
            'belum_dinilai' => $items->whereNull('hasil')->count(),
        ];
    }
}
