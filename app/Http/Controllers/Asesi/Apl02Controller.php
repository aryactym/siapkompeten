<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Services\AsesmenService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class Apl02Controller extends Controller
{
    public function __construct(private readonly AsesmenService $asesmen) {}

    /**
     * Formulir APL-02: asesmen mandiri per KUK (FR-ASM-01).
     */
    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('fillApl02', $pendaftaran);

        $penilaian = $this->asesmen->ensureApl02($pendaftaran);
        $penilaian->load(['jawabans']);

        return view('asesi.apl02.show', [
            'pendaftaran' => $pendaftaran,
            'penilaian' => $penilaian,
            'units' => $this->asesmen->unitItems($pendaftaran->skema),
            'kuks' => $this->asesmen->kukItems($pendaftaran->skema),
            'jawabans' => $penilaian->jawabans->keyBy('kuk_id'),
            'dapatDiisi' => $penilaian->dapatDiisiAsesi(),
        ]);
    }

    /**
     * Simpan draf jawaban APL-02 asesi.
     *
     * @return array<int, mixed>
     */
    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('fillApl02', $pendaftaran);

        $penilaian = $this->asesmen->ensureApl02($pendaftaran);

        abort_unless($penilaian->dapatDiisiAsesi(), 403, 'APL-02 sudah diserahkan.');

        $validated = $request->validate([
            'elemen_approval' => ['required', 'array'],
            'elemen_approval.*' => ['nullable', 'boolean'],
            'bukti' => ['nullable', 'array'],
            'bukti.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->asesmen->simpanJawabanAsesi($penilaian, $this->mapPayloadPerElemen($validated, $pendaftaran->skema));

        activity('apl02')
            ->causedBy($request->user())
            ->performedOn($pendaftaran)
            ->log('Asesi menyimpan draf APL-02.');

        return redirect()
            ->route('asesi.apl02.show', $pendaftaran)
            ->with('success', 'Draf APL-02 berhasil disimpan.');
    }

    /**
     * Asesi menyerahkan APL-02 untuk diverifikasi asesor.
     */
    public function serah(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('fillApl02', $pendaftaran);

        $penilaian = $this->asesmen->ensureApl02($pendaftaran);

        abort_unless($penilaian->dapatDiisiAsesi(), 403, 'APL-02 sudah diserahkan.');

        $validated = $request->validate([
            'elemen_approval' => ['required', 'array'],
            'elemen_approval.*' => ['nullable', 'boolean'],
            'bukti' => ['nullable', 'array'],
            'bukti.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->asesmen->simpanJawabanAsesi($penilaian, $this->mapPayloadPerElemen($validated, $pendaftaran->skema));
        $this->asesmen->serahkanApl02($penilaian, $this->asesmen->kukItems($pendaftaran->skema));

        activity('apl02')
            ->causedBy($request->user())
            ->performedOn($pendaftaran)
            ->log('Asesi menyerahkan APL-02 untuk diverifikasi asesor.');

        return redirect()
            ->route('asesi.apl02.show', $pendaftaran)
            ->with('success', 'APL-02 berhasil diserahkan dan menunggu verifikasi asesor.');
    }

    /**
     * Unduh APL-02 terisi dalam format PDF (FR-FRM-02).
     */
    public function pdf(Pendaftaran $pendaftaran): Response
    {
        $this->authorize('fillApl02', $pendaftaran);

        $penilaian = $this->asesmen->ensureApl02($pendaftaran);
        $penilaian->load(['jawabans']);

        return Pdf::loadView('pdf.apl02', [
            'pendaftaran' => $pendaftaran,
            'penilaian' => $penilaian,
            'units' => $this->asesmen->unitItems($pendaftaran->skema),
            'jawabans' => $penilaian->jawabans->keyBy('kuk_id'),
        ])->stream(sprintf('apl02-%s.pdf', $pendaftaran->no_pendaftaran));
    }

    /**
     * Map payload per elemen ke per KUK.
     * Setiap KUK inherit approval dari elemen parent-nya.
     *
     * @return array<string, array{jawaban: bool, bukti: string|null}>
     */
    private function mapPayloadPerElemen(array $validated, $skema): array
    {
        $result = [];
        
        // Load semua elemen dengan KUK
        $elemenIds = array_keys($validated['elemen_approval'] ?? []);
        $elemenKompetensis = \App\Models\ElemenKompetensi::whereIn('id', $elemenIds)
            ->with('kriteriaUnjukKerjas')
            ->get();

        foreach ($elemenKompetensis as $elemen) {
            $elemenApproval = (bool) ($validated['elemen_approval'][$elemen->id] ?? false);
            $elemenBukti = $validated['bukti'][$elemen->id] ?? null;

            // Setiap KUK dalam elemen ini inherit approval elemen
            foreach ($elemen->kriteriaUnjukKerjas as $kuk) {
                $result[$kuk->id] = [
                    'jawaban' => $elemenApproval,
                    'bukti' => $elemenBukti,
                ];
            }
        }

        return $result;
    }
}
