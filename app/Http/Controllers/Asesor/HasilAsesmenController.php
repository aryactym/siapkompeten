<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Apl02Penilaian;
use App\Models\HasilAsesmen;
use App\Models\HasilAsesmenMetode;
use App\Models\Pendaftaran;
use App\Services\AsesmenService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HasilAsesmenController extends Controller
{
    public function __construct(private readonly AsesmenService $asesmen) {}

    /**
     * Input hasil FR.AK.02 per unit, metode, dan KUK (FR-ASM-05..08).
     */
    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('inputMak02', $pendaftaran);

        $pendaftaran->load([
            'skema',
            'apl02.jawabans',
        ]);

        $hasil = $this->asesmen->ensureHasil($pendaftaran);

        $units = $this->asesmen->unitItems($pendaftaran->skema);
        $hasilKuksCollection = $hasil->kuks()->get()->keyBy('kuk_id');
        $hasilUnitsCollection = $hasil->units()->with('metodes')->get()->keyBy('unit_id');

        // Bukti dari APL-02 per KUK — untuk pemilih bukti di UI
        $apl02Jawabans = $pendaftaran->apl02?->jawabans
            ? $pendaftaran->apl02->jawabans->keyBy('kuk_id')
            : collect();

        // Saran keputusan per unit — dihitung di PHP (bukan query N+1)
        $saranPerUnit = [];
        foreach ($units as $unit) {
            $kukIds = $unit->elemenKompetensis
                ->flatMap(fn ($e) => $e->kriteriaUnjukKerjas)
                ->pluck('id');

            $kukUnit = $hasilKuksCollection->only($kukIds->all());

            if ($kukUnit->isEmpty()) {
                $saranPerUnit[$unit->id] = null;

                continue;
            }

            $adaNull = $kukUnit->contains(fn ($k) => $k->tercapai === null);
            $saranPerUnit[$unit->id] = $adaNull
                ? null
                : ($kukUnit->every(fn ($k) => $k->tercapai === true) ? 'kompeten' : 'belum_kompeten');
        }

        return view('asesor.mak02.show', [
            'pendaftaran' => $pendaftaran,
            'hasil' => $hasil,
            'units' => $units,
            'kuks' => $this->asesmen->kukItems($pendaftaran->skema),
            'hasilUnits' => $hasilUnitsCollection,
            'hasilKuks' => $hasilKuksCollection,
            'apl02Jawabans' => $apl02Jawabans,
            'saranPerUnit' => $saranPerUnit,
            'metodeLabels' => HasilAsesmenMetode::METODE_LABELS,
            'metodeDaftar' => HasilAsesmenMetode::METODES,
            'apl02Terverifikasi' => $pendaftaran->apl02?->status === Apl02Penilaian::STATUS_DIVERIFIKASI,
        ]);
    }

    /**
     * Simpan draf FR.AK.02 (belum terkunci).
     */
    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputMak02', $pendaftaran);

        $hasil = $this->asesmen->ensureHasil($pendaftaran);

        $validated = $request->validate([
            'metode_default' => ['nullable', 'array'],
            'metode_default.*' => ['string', 'in:observasi,demonstrasi,tes_lisan,tes_tertulis,wawancara,portofolio'],
            'units' => ['required', 'array'],
            'units.*.keputusan' => ['nullable', Rule::in(['kompeten', 'belum_kompeten', 'asesmen_lanjut'])],
            'units.*.catatan' => ['nullable', 'string', 'max:2000'],
            'units.*.metode' => ['nullable', 'array'],
            'units.*.metode.*.digunakan' => ['nullable', 'boolean'],
            'units.*.metode.*.bukti_langsung' => ['nullable', 'boolean'],
            'units.*.metode.*.bukti_tidak_langsung' => ['nullable', 'boolean'],
            'units.*.metode.*.bukti_tambahan' => ['nullable', 'boolean'],
            'units.*.metode.*.keputusan' => ['nullable', Rule::in(['kompeten', 'belum_kompeten', 'asesmen_lanjut'])],
            'units.*.metode.*.catatan' => ['nullable', 'string', 'max:1000'],
            'elemenTercapai' => ['required', 'array'],
            'elemenTercapai.*' => ['nullable', 'in:0,1,'],
            'elemenCatatan' => ['nullable', 'array'],
            'elemenCatatan.*' => ['nullable', 'string', 'max:1000'],
            'elemen4C' => ['nullable', 'array'],
            'elemen4C.*' => ['nullable', 'array'],
        ]);

        // Simpan default metode jika ada
        if (!empty($validated['metode_default'])) {
            $hasil->setDefaultMetodes($validated['metode_default']);
        }

        // Convert per elemen ke per KUK
        $kuks = $this->mapElemenToKuk($validated, $pendaftaran->skema);

        $this->asesmen->simpanMak02($hasil, $validated['units'], $kuks);

        activity('asesmen')
            ->causedBy($request->user())
            ->performedOn($pendaftaran)
            ->log('Asesor menyimpan draf FR.AK.02.');

        return redirect()
            ->route('asesor.mak02.show', $pendaftaran)
            ->with('success', 'Draf FR.AK.02 berhasil disimpan.');
    }

    /**
     * Finalkan laporan FR.AK.02: kunci, validasi kelengkapan, hitung persentase.
     */
    public function final(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputMak02', $pendaftaran);

        // Load relasi apl02 untuk pengecekan status
        $pendaftaran->loadMissing('apl02');

        abort_unless(
            $pendaftaran->apl02?->status === Apl02Penilaian::STATUS_DIVERIFIKASI,
            403,
            'APL-02 harus diverifikasi sebelum FR.AK.02 difinalkan.'
        );

        $validated = $request->validate([
            'rekomendasi' => ['required', Rule::in([
                HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN,
                HasilAsesmen::REKOMENDASI_TIDAK,
            ])],
            // umpan_balik wajib dicentang (server-side accepted)
            'umpan_balik' => ['accepted'],
            // konfirmasi: asesor menyatakan memahami penguncian laporan
            'konfirmasi' => ['accepted'],
        ]);

        $hasil = $this->asesmen->ensureHasil($pendaftaran);

        $this->asesmen->finalkan(
            $hasil,
            $pendaftaran,
            $request->user(),
            $validated['rekomendasi'],
            true, // umpan_balik sudah divalidasi accepted di atas
        );

        return redirect()
            ->route('asesor.mak02.show', $pendaftaran)
            ->with('success', sprintf(
                'FR.AK.02 difinalkan. Asesi dinyatakan %d%% %s.',
                (int) $hasil->fresh()->persen_kompeten,
                $validated['rekomendasi'] === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN
                    ? 'KOMPETEN'
                    : 'BELUM KOMPETEN'
            ));
    }

    /**
     * Map per elemen ke per KUK untuk simpan ke database.
     *
     * @return array<string, array{tercapai: bool|null, catatan: string|null, valid: bool, autentik: bool, terkini: bool, memadai: bool}>
     */
    private function mapElemenToKuk(array $validated, $skema): array
    {
        $result = [];
        
        $elemenIds = array_keys($validated['elemenTercapai'] ?? []);
        $elemenKompetensis = \App\Models\ElemenKompetensi::whereIn('id', $elemenIds)
            ->with('kriteriaUnjukKerjas')
            ->get();

        foreach ($elemenKompetensis as $elemen) {
            $tercapai = $validated['elemenTercapai'][$elemen->id] ?? '';
            $catatan = $validated['elemenCatatan'][$elemen->id] ?? null;
            $fourC = $validated['elemen4C'][$elemen->id] ?? [];

            foreach ($elemen->kriteriaUnjukKerjas as $kuk) {
                $result[$kuk->id] = [
                    'tercapai' => $tercapai === '' ? null : (bool) $tercapai,
                    'catatan' => $catatan,
                    'valid' => (bool) ($fourC['valid'] ?? false),
                    'autentik' => (bool) ($fourC['autentik'] ?? false),
                    'terkini' => (bool) ($fourC['terkini'] ?? false),
                    'memadai' => (bool) ($fourC['memadai'] ?? false),
                ];
            }
        }

        return $result;
    }

    /**
     * Unduh laporan FR.AK.02 dalam format PDF (FR-FRM-04).
     */
    public function pdf(Pendaftaran $pendaftaran): Response
    {
        $this->authorize('inputMak02', $pendaftaran);

        $hasil = $this->asesmen->ensureHasil($pendaftaran);
        $hasil->load(['asesor.user', 'units.metodes', 'kuks']);

        return Pdf::loadView('pdf.mak02', [
            'pendaftaran' => $pendaftaran,
            'hasil' => $hasil,
            'units' => $this->asesmen->unitItems($pendaftaran->skema),
            'kuks' => $this->asesmen->kukItems($pendaftaran->skema),
            'hasilUnits' => $hasil->units->keyBy('unit_id'),
            'hasilKuks' => $hasil->kuks->keyBy('kuk_id'),
            'metodeDefault' => $hasil->metode_default ?? [],
        ])->stream(sprintf('frak02-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
