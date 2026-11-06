<?php

namespace App\Services;

use App\Models\Asesor;
use App\Models\HasilAsesmen;
use App\Models\HasilAsesmenUnit;
use App\Models\JadwalAsesmen;
use App\Models\LaporanAsesmen;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LaporanAsesmenService
{
    /**
     * Pendaftaran pada jadwal yang belum selesai / hasilnya belum final.
     *
     * @return Collection<int, Pendaftaran>
     */
    public function penghambat(JadwalAsesmen $jadwal): Collection
    {
        return $jadwal->pendaftarans()
            ->with('hasilAsesmen')
            ->get()
            ->filter(fn (Pendaftaran $pendaftaran) => $pendaftaran->status !== Pendaftaran::STATUS_SELESAI
                || $pendaftaran->hasilAsesmen?->isFinal() !== true)
            ->values();
    }

    /**
     * Baris rekap per asesi untuk FR.AK.05.
     *
     * @return Collection<int, array{pendaftaran: Pendaftaran, nama: string, rekomendasi: string, keterangan: string}>
     */
    public function rekapAsesi(JadwalAsesmen $jadwal): Collection
    {
        return $jadwal->pendaftarans()
            ->with(['asesi.user', 'hasilAsesmen.units.unit'])
            ->orderBy('created_at')
            ->get()
            ->map(function (Pendaftaran $pendaftaran): array {
                $hasil = $pendaftaran->hasilAsesmen;

                return [
                    'pendaftaran' => $pendaftaran,
                    'nama' => $pendaftaran->asesi?->user?->name ?? '—',
                    'rekomendasi' => match ($hasil?->rekomendasi) {
                        HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN => 'K',
                        HasilAsesmen::REKOMENDASI_TIDAK => 'BK',
                        default => '—',
                    },
                    'keterangan' => $this->keteranganBelumKompeten($hasil),
                ];
            });
    }

    /**
     * Kode + judul unit yang dinyatakan belum kompeten.
     */
    private function keteranganBelumKompeten(?HasilAsesmen $hasil): string
    {
        if ($hasil === null) {
            return '—';
        }

        return $hasil->units
            ->filter(fn (HasilAsesmenUnit $unit) => $unit->keputusan === 'belum_kompeten')
            ->map(fn (HasilAsesmenUnit $unit) => trim(sprintf(
                '%s %s',
                $unit->unit?->kode_unit ?? '',
                $unit->unit?->judul_unit ?? '',
            )))
            ->filter()
            ->implode('; ') ?: '—';
    }

    /**
     * @param  array{aspek_positif?: string|null, aspek_negatif?: string|null, pencatatan_penolakan?: string|null, saran_perbaikan?: string|null, catatan?: string|null}  $data
     */
    public function simpan(JadwalAsesmen $jadwal, array $data, User $oleh): LaporanAsesmen
    {
        $laporan = LaporanAsesmen::firstOrNew(['jadwal_id' => $jadwal->id]);

        if ($laporan->exists && $laporan->isFinal()) {
            throw ValidationException::withMessages([
                'laporan' => 'Laporan asesmen sudah final dan tidak dapat diubah.',
            ]);
        }

        $asesor = Asesor::where('user_id', $oleh->id)->first();

        $laporan->fill([
            'institution_id' => $jadwal->institution_id,
            'asesor_id' => $asesor?->id,
            'aspek_positif' => $data['aspek_positif'] ?? null,
            'aspek_negatif' => $data['aspek_negatif'] ?? null,
            'pencatatan_penolakan' => $data['pencatatan_penolakan'] ?? null,
            'saran_perbaikan' => $data['saran_perbaikan'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);
        $laporan->save();

        return $laporan;
    }

    public function finalkan(JadwalAsesmen $jadwal, User $oleh): LaporanAsesmen
    {
        $laporan = LaporanAsesmen::firstOrNew(['jadwal_id' => $jadwal->id]);

        if ($laporan->exists && $laporan->isFinal()) {
            throw ValidationException::withMessages([
                'laporan' => 'Laporan asesmen sudah final.',
            ]);
        }

        $penghambat = $this->penghambat($jadwal);

        if ($penghambat->isNotEmpty()) {
            $nama = $penghambat
                ->map(fn (Pendaftaran $pendaftaran) => $pendaftaran->asesi?->user?->name ?? $pendaftaran->no_pendaftaran)
                ->implode(', ');

            throw ValidationException::withMessages([
                'laporan' => sprintf('Laporan belum dapat difinalkan; asesmen belum selesai untuk: %s.', $nama),
            ]);
        }

        $asesor = Asesor::where('user_id', $oleh->id)->first();

        return DB::transaction(function () use ($laporan, $jadwal, $asesor, $oleh): LaporanAsesmen {
            $laporan->fill([
                'institution_id' => $jadwal->institution_id,
                'asesor_id' => $laporan->asesor_id ?? $asesor?->id,
                'status' => LaporanAsesmen::STATUS_FINAL,
                'finalized_at' => now(),
                'finalized_by' => $oleh->id,
            ]);
            $laporan->save();

            activity('asesmen')
                ->causedBy($oleh)
                ->performedOn($jadwal)
                ->withProperties(['laporan_id' => $laporan->id])
                ->log('Asesor memfinalkan laporan asesmen (FR.AK.05).');

            return $laporan;
        });
    }
}
