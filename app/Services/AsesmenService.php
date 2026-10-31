<?php

namespace App\Services;

use App\Models\Apl02Jawaban;
use App\Models\Apl02Penilaian;
use App\Models\Asesor;
use App\Models\HasilAsesmen;
use App\Models\HasilAsesmenKuk;
use App\Models\HasilAsesmenMetode;
use App\Models\HasilAsesmenUnit;
use App\Models\KriteriaUnjukKerja;
use App\Models\Pendaftaran;
use App\Models\Skema;
use App\Models\UnitKompetensi;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsesmenService
{
    /**
     * Struktur unit -> elemen -> KUK milik skema untuk formulir APL-02 & MAK-02.
     *
     * @return Collection<int, UnitKompetensi>
     */
    public function unitItems(Skema $skema): Collection
    {
        return $skema->unitKompetensis()
            ->with(['elemenKompetensis.kriteriaUnjukKerjas'])
            ->get();
    }

    /**
     * Seluruh KUK pada skema (urutan unit, lalu elemen, lalu KUK).
     *
     * @return Collection<int, KriteriaUnjukKerja>
     */
    public function kukItems(Skema $skema): Collection
    {
        return $this->unitItems($skema)
            ->flatMap(fn ($unit) => $unit->elemenKompetensis)
            ->flatMap(fn ($elemen) => $elemen->kriteriaUnjukKerjas)
            ->values();
    }

    /**
     * Pastikan draf APL-02 tersedia untuk pendaftaran yang sudah terjadwal.
     */
    public function ensureApl02(Pendaftaran $pendaftaran): Apl02Penilaian
    {
        if ($pendaftaran->apl02()->exists()) {
            return $pendaftaran->apl02;
        }

        return Apl02Penilaian::create([
            'pendaftaran_id' => $pendaftaran->id,
            'status' => Apl02Penilaian::STATUS_DRAF_ASESI,
        ]);
    }

    /**
     * Simpan jawaban APL-02 asesi per KUK (FR-ASM-01).
     *
     * @param  array<string, array{jawaban: int|string, bukti: string|null}>  $jawabans
     */
    public function simpanJawabanAsesi(Apl02Penilaian $penilaian, array $jawabans): void
    {
        DB::transaction(function () use ($penilaian, $jawabans): void {
            foreach ($jawabans as $kukId => $jawaban) {
                Apl02Jawaban::updateOrCreate(
                    ['penilaian_id' => $penilaian->id, 'kuk_id' => $kukId],
                    [
                        'jawaban_asesi' => (bool) ($jawaban['jawaban'] ?? false),
                        'bukti_asesi' => $jawaban['bukti'] ?? null,
                    ]
                );
            }
        });
    }

    /**
     * Asesi menyerahkan APL-02 untuk diverifikasi asesor (FR-ASM-01).
     *
     * @param  Collection<int, KriteriaUnjukKerja>  $kuks
     */
    public function serahkanApl02(Apl02Penilaian $penilaian, Collection $kuks): void
    {
        $terjawab = $penilaian->jawabans()->pluck('kuk_id');
        $belum = $kuks->pluck('id')->reject(fn (string $id) => $terjawab->contains($id));

        if ($belum->isNotEmpty()) {
            throw ValidationException::withMessages([
                'apl02' => 'Seluruh KUK wajib dijawab sebelum APL-02 diserahkan.',
            ]);
        }

        $penilaian->update([
            'status' => Apl02Penilaian::STATUS_DISERAHKAN,
            'diserahkan_pada' => now(),
        ]);
    }

    /**
     * Asesor memverifikasi jawaban asesi dan menyetujui APL-02 (FR-ASM-02..04).
     *
     * @param  array<string, array{bukti: string|null, valid: bool, autentik: bool, terkini: bool, memadai: bool}>  $verifikasi
     */
    public function verifikasiApl02(Apl02Penilaian $penilaian, array $verifikasi, string $asesorId): void
    {
        DB::transaction(function () use ($penilaian, $verifikasi, $asesorId): void {
            foreach ($verifikasi as $kukId => $item) {
                Apl02Jawaban::updateOrCreate(
                    ['penilaian_id' => $penilaian->id, 'kuk_id' => $kukId],
                    [
                        'bukti_asesor' => $item['bukti'] ?? null,
                        'valid' => (bool) ($item['valid'] ?? false),
                        'autentik' => (bool) ($item['autentik'] ?? false),
                        'terkini' => (bool) ($item['terkini'] ?? false),
                        'memadai' => (bool) ($item['memadai'] ?? false),
                    ]
                );
            }

            $penilaian->update([
                'status' => Apl02Penilaian::STATUS_DIVERIFIKASI,
                'asesor_id' => $asesorId,
                'diverifikasi_pada' => now(),
            ]);
        });
    }

    /**
     * Pastikan draf laporan MAK-02 tersedia.
     */
    public function ensureHasil(Pendaftaran $pendaftaran): HasilAsesmen
    {
        if ($pendaftaran->hasilAsesmen()->exists()) {
            return $pendaftaran->hasilAsesmen;
        }

        return HasilAsesmen::create([
            'pendaftaran_id' => $pendaftaran->id,
            'status' => HasilAsesmen::STATUS_DRAF,
        ]);
    }

    /**
     * Simpan draf MAK-02: keputusan per unit, metode, dan capaian per KUK (FR-ASM-05..08).
     *
     * @param  array<string, mixed>  $units
     * @param  array<string, mixed>  $kuks
     */
    public function simpanMak02(HasilAsesmen $hasil, array $units, array $kuks): void
    {
        if ($hasil->isFinal()) {
            throw ValidationException::withMessages([
                'hasil' => 'Laporan MAK-02 sudah final dan tidak dapat diubah.',
            ]);
        }

        DB::transaction(function () use ($hasil, $units, $kuks): void {
            foreach ($units as $unitId => $unitData) {
                $unit = HasilAsesmenUnit::updateOrCreate(
                    ['hasil_id' => $hasil->id, 'unit_id' => $unitId],
                    [
                        'keputusan' => $unitData['keputusan'] ?? null,
                        'pencapaian' => ! empty($unitData['pencapaian']),
                    ]
                );

                foreach ($unitData['metode'] ?? [] as $metode => $metodeData) {
                    HasilAsesmenMetode::updateOrCreate(
                        ['hasil_unit_id' => $unit->id, 'metode' => $metode],
                        [
                            'digunakan' => ! empty($metodeData['digunakan']),
                            'bukti_langsung' => ! empty($metodeData['bukti_langsung']),
                            'bukti_tidak_langsung' => ! empty($metodeData['bukti_tidak_langsung']),
                            'bukti_tambahan' => ! empty($metodeData['bukti_tambahan']),
                            'pencapaian' => ! empty($metodeData['pencapaian']),
                            'keputusan' => $metodeData['keputusan'] ?? null,
                        ]
                    );
                }
            }

            foreach ($kuks as $kukId => $kukData) {
                HasilAsesmenKuk::updateOrCreate(
                    ['hasil_id' => $hasil->id, 'kuk_id' => $kukId],
                    [
                        'pencapaian' => ! empty($kukData['pencapaian']),
                        'bukti_no' => $kukData['bukti_no'] ?? null,
                        'valid' => ! empty($kukData['valid']),
                        'autentik' => ! empty($kukData['autentik']),
                        'terkini' => ! empty($kukData['terkini']),
                        'memadai' => ! empty($kukData['memadai']),
                    ]
                );
            }
        });
    }

    /**
     * Finalkan MAK-02: kunci laporan, hitung persentase, perbarui status kompetensi
     * asesi dan status pendaftaran (FR-ASM-10/12).
     */
    public function finalkan(
        HasilAsesmen $hasil,
        Pendaftaran $pendaftaran,
        User $asesorUser,
        string $rekomendasi,
        bool $umpanBalik,
    ): void {
        if ($hasil->isFinal()) {
            throw ValidationException::withMessages([
                'hasil' => 'Laporan MAK-02 sudah final.',
            ]);
        }

        DB::transaction(function () use ($hasil, $pendaftaran, $asesorUser, $rekomendasi, $umpanBalik): void {
            $totalKuk = $hasil->kuks()->count();

            if ($totalKuk === 0) {
                throw ValidationException::withMessages([
                    'hasil' => 'Belum ada capaian KUK yang disimpan untuk MAK-02.',
                ]);
            }

            $tercapai = $hasil->kuks()->where('pencapaian', true)->count();
            $persen = round(($tercapai / $totalKuk) * 100, 2);

            $asesor = Asesor::where('user_id', $asesorUser->id)->first();

            $hasil->update([
                'rekomendasi' => $rekomendasi,
                'persen_kompeten' => $persen,
                'umpan_balik_diberikan' => $umpanBalik,
                'status' => HasilAsesmen::STATUS_FINAL,
                'finalized_at' => now(),
                'finalized_by' => $asesorUser->id,
                'asesor_id' => $asesor?->id,
            ]);

            $pendaftaran->asesi->update([
                'status_kompetensi' => $rekomendasi === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN
                    ? 'kompeten'
                    : 'belum_kompeten',
            ]);

            $pendaftaran->update(['status' => Pendaftaran::STATUS_SELESAI]);

            activity('asesmen')
                ->causedBy($asesorUser)
                ->performedOn($pendaftaran)
                ->withProperties([
                    'rekomendasi' => $rekomendasi,
                    'persen_kompeten' => $persen,
                ])
                ->log('Laporan MAK-02 difinalkan oleh asesor.');
        });
    }
}
