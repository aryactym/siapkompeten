<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Apl02Penilaian;
use App\Models\HasilAsesmen;
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
     * Input hasil MAK-02 per unit, metode, dan KUK (FR-ASM-05..08).
     */
    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('inputMak02', $pendaftaran);

        $hasil = $this->asesmen->ensureHasil($pendaftaran);

        return view('asesor.mak02.show', [
            'pendaftaran' => $pendaftaran,
            'hasil' => $hasil,
            'units' => $this->asesmen->unitItems($pendaftaran->skema),
            'kuks' => $this->asesmen->kukItems($pendaftaran->skema),
            'hasilUnits' => $hasil->units()->with('metodes')->get()->keyBy('unit_id'),
            'hasilKuks' => $hasil->kuks()->get()->keyBy('kuk_id'),
            'apl02Terverifikasi' => $pendaftaran->apl02?->status === Apl02Penilaian::STATUS_DIVERIFIKASI,
        ]);
    }

    /**
     * Simpan draf MAK-02 (belum terkunci).
     *
     * @return array<string, mixed>
     */
    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputMak02', $pendaftaran);

        $hasil = $this->asesmen->ensureHasil($pendaftaran);

        $validated = $request->validate([
            'units' => ['required', 'array'],
            'units.*.keputusan' => ['nullable', Rule::in(['kompeten', 'belum_kompeten', 'asesmen_lanjut'])],
            'units.*.pencapaian' => ['nullable', 'boolean'],
            'units.*.metode' => ['nullable', 'array'],
            'units.*.metode.*.digunakan' => ['nullable', 'boolean'],
            'units.*.metode.*.bukti_langsung' => ['nullable', 'boolean'],
            'units.*.metode.*.bukti_tidak_langsung' => ['nullable', 'boolean'],
            'units.*.metode.*.bukti_tambahan' => ['nullable', 'boolean'],
            'units.*.metode.*.pencapaian' => ['nullable', 'boolean'],
            'units.*.metode.*.keputusan' => ['nullable', Rule::in(['kompeten', 'belum_kompeten', 'asesmen_lanjut'])],
            'kuks' => ['required', 'array'],
            'kuks.*.pencapaian' => ['nullable', 'boolean'],
            'kuks.*.bukti_no' => ['nullable', 'string', 'max:60'],
            'kuks.*.valid' => ['nullable', 'boolean'],
            'kuks.*.autentik' => ['nullable', 'boolean'],
            'kuks.*.terkini' => ['nullable', 'boolean'],
            'kuks.*.memadai' => ['nullable', 'boolean'],
        ]);

        $this->asesmen->simpanMak02($hasil, $validated['units'], $validated['kuks']);

        activity('asesmen')
            ->causedBy($request->user())
            ->performedOn($pendaftaran)->log('Asesor menyimpan draf MAK-02.');

        return redirect()
            ->route('asesor.mak02.show', $pendaftaran)
            ->with('success', 'Draf MAK-02 berhasil disimpan.');
    }

    /**
     * Finalkan laporan MAK-02: kunci, hitung persentase, perbarui status kompetensi.
     */
    public function final(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputMak02', $pendaftaran);

        $validated = $request->validate([
            'rekomendasi' => ['required', Rule::in([
                HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN,
                HasilAsesmen::REKOMENDASI_TIDAK,
            ])],
            'umpan_balik' => ['nullable', 'boolean'],
            'konfirmasi' => ['accepted'],
        ]);

        abort_unless(
            $pendaftaran->apl02?->status === Apl02Penilaian::STATUS_DIVERIFIKASI,
            403,
            'APL-02 harus diverifikasi sebelum MAK-02 difinalkan.'
        );

        $hasil = $this->asesmen->ensureHasil($pendaftaran);

        $this->asesmen->finalkan(
            $hasil,
            $pendaftaran,
            $request->user(),
            $validated['rekomendasi'],
            (bool) ($validated['umpan_balik'] ?? false),
        );

        return redirect()
            ->route('asesor.mak02.show', $pendaftaran)
            ->with('success', sprintf(
                'MAK-02 difinalkan. Asesi dinyatakan %d%% %s.',
                (int) $hasil->fresh()->persen_kompeten,
                $validated['rekomendasi'] === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN ? 'KOMPETEN' : 'BELUM KOMPETEN'
            ));
    }

    /**
     * Unduh laporan MAK-02 dalam format PDF (FR-FRM-04).
     */
    public function pdf(Pendaftaran $pendaftaran): Response
    {
        $this->authorize('inputMak02', $pendaftaran);

        $hasil = $this->asesmen->ensureHasil($pendaftaran);

        return Pdf::loadView('pdf.mak02', [
            'pendaftaran' => $pendaftaran,
            'hasil' => $hasil,
            'units' => $this->asesmen->unitItems($pendaftaran->skema),
            'kuks' => $this->asesmen->kukItems($pendaftaran->skema),
            'hasilUnits' => $hasil->units()->with('metodes')->get()->keyBy('unit_id'),
            'hasilKuks' => $hasil->kuks()->get()->keyBy('kuk_id'),
        ])->stream(sprintf('mak02-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
