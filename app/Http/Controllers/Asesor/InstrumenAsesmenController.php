<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\InstrumenAsesmen;
use App\Models\InstrumenAsesmenItem;
use App\Models\Pendaftaran;
use App\Services\InstrumenAsesmenService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InstrumenAsesmenController extends Controller
{
    public function __construct(private readonly InstrumenAsesmenService $instrumen) {}

    public function show(Request $request, Pendaftaran $pendaftaran, string $jenis): View
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $this->ensureJenis($jenis);

        $instrumen = $this->instrumen->ensure($pendaftaran, $jenis);
        $instrumen->load(['items.kuk', 'items.unitKompetensi', 'finalizer']);

        return view('asesor.instrumen.show', [
            'pendaftaran' => $pendaftaran,
            'instrumen' => $instrumen,
            'jenis' => $jenis,
            'rekap' => $this->instrumen->rekap($instrumen),
        ]);
    }

    public function store(Request $request, Pendaftaran $pendaftaran, string $jenis): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $this->ensureJenis($jenis);

        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat diisi saat asesmen berjalan.');
        }

        $instrumen = $this->instrumen->ensure($pendaftaran, $jenis);

        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:2000'],
            'items' => ['nullable', 'array'],
            'items.*.hasil' => ['nullable', Rule::in(array_keys(InstrumenAsesmenItem::HASIL_LABELS))],
            'items.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->instrumen->simpan($instrumen, $validated['items'] ?? []);
            $instrumen->update(['catatan' => $validated['catatan'] ?? null]);
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Instrumen berhasil disimpan.');
    }

    public function final(Request $request, Pendaftaran $pendaftaran, string $jenis): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $this->ensureJenis($jenis);

        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat difinalkan saat asesmen berjalan.');
        }

        $instrumen = $this->instrumen->ensure($pendaftaran, $jenis);

        try {
            $this->instrumen->finalkan($instrumen, $request->user());
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Instrumen difinalkan dan dikunci.');
    }

    public function pdf(Pendaftaran $pendaftaran, string $jenis)
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $this->ensureJenis($jenis);

        $pendaftaran->load(['skema', 'asesi.user', 'jadwal.asesors.user', 'jadwal.tuk']);
        $instrumen = $this->instrumen->ensure($pendaftaran, $jenis);
        $instrumen->load(['items.kuk', 'items.unitKompetensi']);

        return Pdf::loadView('pdf.instrumen-asesmen', [
            'pendaftaran' => $pendaftaran,
            'skema' => $pendaftaran->skema,
            'instrumen' => $instrumen,
            'asesor' => $pendaftaran->jadwal?->asesors->first(),
            'rekap' => $this->instrumen->rekap($instrumen),
        ])->setPaper('a4', 'portrait')
            ->stream(sprintf('%s-%s.pdf', strtoupper($jenis), $pendaftaran->no_pendaftaran));
    }

    private function ensureJenis(string $jenis): void
    {
        abort_unless(array_key_exists($jenis, InstrumenAsesmen::JENIS_LABELS), 404);
    }
}
