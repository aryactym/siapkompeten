<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Services\Ia10Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Ia10Controller extends Controller
{
    public function __construct(private readonly Ia10Service $ia10) {}

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $ia10 = $this->ia10->ensure($pendaftaran);

        return view('asesor.ia10.show', ['pendaftaran' => $pendaftaran, 'ia10' => $ia10]);
    }

    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat diisi saat asesmen berjalan.');
        }
        $ia10 = $this->ia10->ensure($pendaftaran);
        $validated = $request->validate([
            'nama_pihak_ketiga' => ['nullable', 'string', 'max:200'],
            'jabatan' => ['nullable', 'string', 'max:200'],
            'instansi' => ['nullable', 'string', 'max:200'],
            'klarifikasi' => ['nullable', 'string', 'max:5000'],
        ]);
        try {
            $this->ia10->simpan($ia10, $validated, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'FR.IA.10 berhasil disimpan.');
    }

    public function final(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat difinalkan saat asesmen berjalan.');
        }
        $ia10 = $this->ia10->ensure($pendaftaran);
        try {
            $this->ia10->finalkan($ia10, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'FR.IA.10 difinalkan.');
    }

    public function pdf(Pendaftaran $pendaftaran)
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $pendaftaran->load(['skema', 'asesi.user', 'jadwal.asesors.user', 'jadwal.tuk']);
        $ia10 = $this->ia10->ensure($pendaftaran);

        return Pdf::loadView('pdf.ia10', ['pendaftaran' => $pendaftaran, 'skema' => $pendaftaran->skema, 'ia10' => $ia10, 'asesor' => $pendaftaran->jadwal?->asesors->first()])->setPaper('a4', 'portrait')->stream(sprintf('IA10-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
