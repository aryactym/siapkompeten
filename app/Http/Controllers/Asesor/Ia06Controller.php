<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Ia06Soal;
use App\Models\Pendaftaran;
use App\Services\Ia06Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Ia06Controller extends Controller
{
    public function __construct(private readonly Ia06Service $ia06) {}

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        $ia06 = $this->ia06->ensure($pendaftaran);
        $ia06->load('soals');

        return view('asesor.ia06.show', ['pendaftaran' => $pendaftaran, 'ia06' => $ia06]);
    }

    public function tambah(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat diisi saat asesmen berjalan.');
        }

        $validated = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:2000'],
            'kunci' => ['nullable', 'string', 'max:5000'],
        ]);

        $ia06 = $this->ia06->ensure($pendaftaran);

        try {
            $this->ia06->tambahSoal($ia06, $validated, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Soal esai ditambahkan.');
    }

    public function hapus(Pendaftaran $pendaftaran, Ia06Soal $soal): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        abort_unless($soal->ia06->pendaftaran_id === $pendaftaran->id, 404);

        try {
            $this->ia06->hapusSoal($soal);
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
            'soals.*.kunci' => ['nullable', 'string', 'max:5000'],
            'soals.*.jawaban_asesi' => ['nullable', 'string', 'max:5000'],
            'soals.*.pencapaian' => ['nullable', 'in:1,0'],
        ]);

        $ia06 = $this->ia06->ensure($pendaftaran);

        try {
            $this->ia06->simpan($ia06, $validated, $validated['soals'] ?? [], $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'FR.IA.06 berhasil disimpan.');
    }

    public function final(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat difinalkan saat asesmen berjalan.');
        }

        $ia06 = $this->ia06->ensure($pendaftaran);

        try {
            $this->ia06->finalkan($ia06, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'FR.IA.06 difinalkan.');
    }

    public function pdf(Pendaftaran $pendaftaran)
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        $pendaftaran->load(['skema', 'asesi.user', 'jadwal.asesors.user', 'jadwal.tuk']);

        $ia06 = $this->ia06->ensure($pendaftaran);
        $ia06->load('soals');

        return Pdf::loadView('pdf.ia06', [
            'pendaftaran' => $pendaftaran,
            'skema' => $pendaftaran->skema,
            'ia06' => $ia06,
            'asesor' => $pendaftaran->jadwal?->asesors->first(),
        ])->setPaper('a4', 'portrait')->stream(sprintf('IA06-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
