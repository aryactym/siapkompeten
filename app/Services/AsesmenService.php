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
     * Struktur unit -> elemen -> KUK milik skema untuk formulir APL-02 & FR.AK.02.
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
    public function verifikasiApl02(Apl02Penilaian $penilaian, array $verifikasi, string $asesorId, Pendaftaran $pendaftaran): void
    {
        DB::transaction(function () use ($penilaian, $verifikasi, $asesorId, $pendaftaran): void {
            // Map elemen-id → [KUK-id list] dari skema.
            $elemenToKuks = [];
            foreach ($pendaftaran->skema->unitKompetensis as $unit) {
                foreach ($unit->elemenKompetensis as $elemen) {
                    $elemenToKuks[$elemen->id] = $elemen->kriteriaUnjukKerjas->pluck('id')->all();
                }
            }

            // Iterasi verifikasi per KUK; cari elemen yg memiliki KUK, ambil bukti dari elemen tsb.
            foreach ($verifikasi as $kukId => $item) {
                // Cari elemen-id yg punya KUK-id ini.
                $elemenId = null;
                foreach ($elemenToKuks as $elemId => $kukIds) {
                    if (in_array($kukId, $kukIds)) {
                        $elemenId = $elemId;
                        break;
                    }
                }

                // Bukti form pakai key KUK-id (view fixed line 85).
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
     * Pastikan draf laporan FR.AK.02 tersedia.
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
     * Hitung saran keputusan per unit berdasarkan KUK tercapai.
     *
     * null = masih ada KUK belum dinilai
     * 'kompeten' = semua KUK tercapai
     * 'belum_kompeten' = ada KUK tidak tercapai
     *
     * @param  Collection<int, HasilAsesmenKuk>  $hasilKuksUnit  KUK milik unit ini saja
     * @return 'kompeten'|'belum_kompeten'|null
     */
    public function saranKeputusanUnit(Collection $hasilKuksUnit): ?string
    {
        if ($hasilKuksUnit->isEmpty()) {
            return null;
        }

        // Ada yang belum dinilai (tercapai === null)
        if ($hasilKuksUnit->contains(fn (HasilAsesmenKuk $k) => $k->tercapai === null)) {
            return null;
        }

        return $hasilKuksUnit->every(fn (HasilAsesmenKuk $k) => $k->tercapai === true)
            ? 'kompeten'
            : 'belum_kompeten';
    }

    /**
     * Simpan draf FR.AK.02: keputusan per unit, metode, dan penilaian per KUK.
     *
     * Skema field baru:
     *   units[id][keputusan]         — enum kompeten|belum_kompeten|asesmen_lanjut
     *   units[id][catatan]           — wajib jika keputusan ≠ saran (divalidasi di final, bukan draf)
     *   units[id][metode][x][digunakan]        — boolean
     *   units[id][metode][x][bukti_langsung]   — boolean, hanya disimpan jika digunakan
     *   units[id][metode][x][bukti_tidak_langsung]
     *   units[id][metode][x][bukti_tambahan]
     *   units[id][metode][x][keputusan]        — enum
     *   units[id][metode][x][catatan]          — text
     *   kuks[id][tercapai]           — 1|0|'' (1=ya, 0=tidak, ''=hapus/null)
     *   kuks[id][catatan]            — catatan asesor per KUK
     *
     * @param  array<string, mixed>  $units
     * @param  array<string, mixed>  $kuks
     */
    public function simpanMak02(HasilAsesmen $hasil, array $units, array $kuks): void
    {
        if ($hasil->isFinal()) {
            throw ValidationException::withMessages([
                'hasil' => 'Laporan FR.AK.02 sudah final dan tidak dapat diubah.',
            ]);
        }

        DB::transaction(function () use ($hasil, $units, $kuks): void {
            foreach ($units as $unitId => $unitData) {
                $unit = HasilAsesmenUnit::updateOrCreate(
                    ['hasil_id' => $hasil->id, 'unit_id' => $unitId],
                    [
                        'keputusan' => $unitData['keputusan'] ?? null,
                        'catatan' => $unitData['catatan'] ?? null,
                        // pencapaian: deprecated — tidak diupdate oleh UI baru
                    ]
                );

                foreach ($unitData['metode'] ?? [] as $metode => $metodeData) {
                    // Hanya simpan metode yang dikenal
                    if (! in_array($metode, HasilAsesmenMetode::METODES, true)) {
                        continue;
                    }

                    $digunakan = ! empty($metodeData['digunakan']);

                    HasilAsesmenMetode::updateOrCreate(
                        ['hasil_unit_id' => $unit->id, 'metode' => $metode],
                        [
                            'digunakan' => $digunakan,
                            // Bukti hanya bermakna jika digunakan; bila tidak digunakan → false
                            'bukti_langsung' => $digunakan && ! empty($metodeData['bukti_langsung']),
                            'bukti_tidak_langsung' => $digunakan && ! empty($metodeData['bukti_tidak_langsung']),
                            'bukti_tambahan' => $digunakan && ! empty($metodeData['bukti_tambahan']),
                            'keputusan' => $digunakan ? ($metodeData['keputusan'] ?? null) : null,
                            'catatan' => $digunakan ? ($metodeData['catatan'] ?? null) : null,
                            // pencapaian: deprecated — tidak diupdate
                        ]
                    );
                }
            }

            foreach ($kuks as $kukId => $kukData) {
                // tercapai: '' / null → null (belum dinilai), '1'/true → true, '0'/false → false
                $tercapai = isset($kukData['tercapai']) && $kukData['tercapai'] !== ''
                    ? (bool) $kukData['tercapai']
                    : null;

                HasilAsesmenKuk::updateOrCreate(
                    ['hasil_id' => $hasil->id, 'kuk_id' => $kukId],
                    [
                        'tercapai' => $tercapai,
                        'catatan' => $kukData['catatan'] ?? null,
                        // Field VATM (valid/asli/terkini/memadai) masih disimpan jika dikirim
                        'valid' => ! empty($kukData['valid']),
                        'asli' => ! empty($kukData['asli']),
                        'terkini' => ! empty($kukData['terkini']),
                        'memadai' => ! empty($kukData['memadai']),
                        // Deprecated fields — tidak diupdate lagi oleh UI baru
                        // 'pencapaian' → tidak diisi
                        // 'bukti_no'   → tidak diisi
                    ]
                );
            }
        });
    }

    /**
     * Finalkan FR.AK.02: kunci laporan, validasi kelengkapan, hitung persentase,
     * perbarui status kompetensi asesi dan status pendaftaran (FR-ASM-10/12).
     *
     * Aturan bisnis finalisasi:
     *   1. Semua unit harus punya keputusan.
     *   2. Semua KUK harus punya tercapai (tidak boleh null).
     *   3. Jika keputusan unit berbeda dari saran otomatis, catatan wajib ada.
     *   4. Rekomendasi wajib, umpan_balik wajib.
     *   5. Metode asesmen yang tidak digunakan wajib tanpa keputusan/catatan.
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
                'hasil' => 'Laporan FR.AK.02 sudah final.',
            ]);
        }

        // Muat semua relasi yang diperlukan untuk validasi
        $units = $hasil->units()->with('metodes')->get();
        $allKuks = $hasil->kuks()->get();

        $errors = [];

        // 1. Semua unit wajib punya keputusan
        $unitTanpaKeputusan = $units->filter(fn (HasilAsesmenUnit $u) => blank($u->keputusan));
        if ($unitTanpaKeputusan->isNotEmpty()) {
            $errors['hasil'] = 'Semua unit kompetensi harus memiliki keputusan sebelum difinalkan.';
        }

        // 2. Semua KUK wajib dinilai (tercapai tidak boleh null)
        $kukBelumDinilai = $allKuks->filter(fn (HasilAsesmenKuk $k) => $k->tercapai === null);
        if ($kukBelumDinilai->isNotEmpty()) {
            $errors['kuks'] = sprintf(
                '%d KUK belum dinilai. Semua KUK harus dinilai (Ya/Tidak) sebelum difinalkan.',
                $kukBelumDinilai->count()
            );
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        // 3. Validasi: keputusan unit berbeda dari saran → catatan wajib
        $unitCatatanErrors = [];
        foreach ($units as $hasilUnit) {
            // Kumpulkan KUK milik unit ini
            $kukUnit = $allKuks->filter(function (HasilAsesmenKuk $k): bool {
                // KUK terhubung ke unit via elemen kompetensi — query langsung lebih akurat
                return true; // akan divalidasi via query di bawah bila ada masalah
            });

            // Cari KUK yang terhubung ke unit ini via DB
            $kukUnitIds = $hasil->kuks()
                ->whereHas('kuk.elemenKompetensi', fn ($q) => $q->where('unit_kompetensi_id', $hasilUnit->unit_id))
                ->pluck('tercapai')
                ->all();

            if (empty($kukUnitIds)) {
                continue;
            }

            // Hitung saran
            $adaNull = in_array(null, $kukUnitIds, true);
            $saran = $adaNull
                ? null
                : (in_array(false, $kukUnitIds, true) ? 'belum_kompeten' : 'kompeten');

            // Jika saran berbeda dari keputusan dan catatan kosong → error
            if ($saran !== null && $hasilUnit->keputusan !== $saran && blank($hasilUnit->catatan)) {
                $unitCatatanErrors[] = sprintf(
                    'Unit "%s": keputusan berbeda dari saran otomatis (%s → %s), wajib mengisi catatan alasan.',
                    $hasilUnit->unit_id,
                    $saran,
                    $hasilUnit->keputusan
                );
            }
        }

        if ($unitCatatanErrors !== []) {
            throw ValidationException::withMessages([
                'units' => implode(' | ', $unitCatatanErrors),
            ]);
        }

        // 4. Metode asesmen yang tidak digunakan wajib tanpa keputusan/catatan.
        $metodeErrors = [];
        foreach ($units as $hasilUnit) {
            foreach ($hasilUnit->metodes as $metode) {
                if ($metode->digunakan) {
                    continue;
                }

                if ($metode->keputusan !== null || $metode->catatan !== null) {
                    $metodeErrors[] = sprintf(
                        'Unit "%s": metode "%s" tidak digunakan, keputusan/catatan harus kosong.',
                        $hasilUnit->unit_id,
                        $metode->metode
                    );
                }
            }
        }

        if ($metodeErrors !== []) {
            throw ValidationException::withMessages([
                'units' => implode(' | ', $metodeErrors),
            ]);
        }

        DB::transaction(function () use ($hasil, $pendaftaran, $asesorUser, $rekomendasi, $umpanBalik, $allKuks): void {
            $totalKuk = $allKuks->count();

            // Hitung persentase dari field `tercapai` (bukan `pencapaian` lama)
            $tercapaiCount = $allKuks->filter(fn (HasilAsesmenKuk $k) => $k->tercapai === true)->count();
            $persen = $totalKuk > 0 ? round(($tercapaiCount / $totalKuk) * 100, 2) : 0.0;

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
                ->log('Laporan FR.AK.02 difinalkan oleh asesor.');
        });
    }
}
