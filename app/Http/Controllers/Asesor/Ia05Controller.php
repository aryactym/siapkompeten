<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Ia05Soal;
use App\Models\Pendaftaran;
use App\Services\Ia05Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Ia05Controller extends Controller
{
    public function __construct(private readonly Ia05Service $ia05) {}

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        $ia05 = $this->ia05->ensure($pendaftaran);
        $ia05->load('soals');

        return view('asesor.ia05.show', ['pendaftaran' => $pendaftaran, 'ia05' => $ia05]);
    }

    public function tambah(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat diisi saat asesmen berjalan.');
        }

        $validated = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:2000'],
            'opsi_a' => ['required', 'string', 'max:1000'],
            'opsi_b' => ['required', 'string', 'max:1000'],
            'opsi_c' => ['nullable', 'string', 'max:1000'],
            'opsi_d' => ['nullable', 'string', 'max:1000'],
            'kunci' => ['required', Rule::in(array_keys(Ia05Soal::OPSI_LABELS))],
        ]);

        $ia05 = $this->ia05->ensure($pendaftaran);

        try {
            $this->ia05->tambahSoal($ia05, $validated, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Soal ditambahkan.');
    }

    public function hapus(Pendaftaran $pendaftaran, Ia05Soal $soal): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        abort_unless($soal->ia05->pendaftaran_id === $pendaftaran->id, 404);

        try {
            $this->ia05->hapusSoal($soal);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Soal dihapus.');
    }

    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat diisi saat asesmen berjalan.');
        }

        $validated = $request->validate([
            'judul' => ['nullable', 'string', 'max:200'],
            'petunjuk' => ['nullable', 'string', 'max:5000'],
            'umpan_balik' => ['nullable', 'string', 'max:2000'],
            'soals' => ['nullable', 'array'],
            'soals.*.id' => ['required', 'uuid'],
            'soals.*.pertanyaan' => ['nullable', 'string', 'max:2000'],
            'soals.*.opsi_a' => ['nullable', 'string', 'max:1000'],
            'soals.*.opsi_b' => ['nullable', 'string', 'max:1000'],
            'soals.*.opsi_c' => ['nullable', 'string', 'max:1000'],
            'soals.*.opsi_d' => ['nullable', 'string', 'max:1000'],
            'soals.*.kunci' => ['nullable', Rule::in(array_keys(Ia05Soal::OPSI_LABELS))],
            'soals.*.jawaban_asesi' => ['nullable', Rule::in(array_keys(Ia05Soal::OPSI_LABELS))],
        ]);

        $ia05 = $this->ia05->ensure($pendaftaran);

        try {
            $this->ia05->simpan($ia05, $validated, $validated['soals'] ?? [], $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'FR.IA.05 berhasil disimpan.');
    }

    public function final(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat difinalkan saat asesmen berjalan.');
        }

        $ia05 = $this->ia05->ensure($pendaftaran);

        try {
            $this->ia05->finalkan($ia05, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'FR.IA.05 difinalkan.');
    }

    public function pdf(Pendaftaran $pendaftaran)
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        $pendaftaran->load(['skema', 'asesi.user', 'jadwal.asesors.user', 'jadwal.tuk']);

        $ia05 = $this->ia05->ensure($pendaftaran);
        $ia05->load('soals');

        return Pdf::loadView('pdf.ia05', [
            'pendaftaran' => $pendaftaran,
            'skema' => $pendaftaran->skema,
            'ia05' => $ia05,
            'asesor' => $pendaftaran->jadwal?->asesors->first(),
        ])->setPaper('a4', 'portrait')->stream(sprintf('IA05-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
