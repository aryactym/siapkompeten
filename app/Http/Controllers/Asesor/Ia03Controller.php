<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Ia03Item;
use App\Models\Pendaftaran;
use App\Services\Ia03Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Ia03Controller extends Controller
{
    public function __construct(private readonly Ia03Service $ia03) {}

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN && ! $pendaftaran->ia03Asesmen?->isFinal()) {
            // allow view
        }
        $ia03 = $this->ia03->ensure($pendaftaran);
        $ia03->load('items');

        return view('asesor.ia03.show', ['pendaftaran' => $pendaftaran, 'ia03' => $ia03]);
    }

    public function tambah(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $validated = $request->validate(['pertanyaan' => ['required', 'string', 'max:1000']]);
        $ia03 = $this->ia03->ensure($pendaftaran);
        try {
            $this->ia03->tambahItem($ia03, $validated['pertanyaan'], $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Pertanyaan ditambahkan.');
    }

    public function hapus(Pendaftaran $pendaftaran, Ia03Item $item): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        try {
            $this->ia03->hapusItem($item);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Pertanyaan dihapus.');
    }

    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat diisi saat asesmen berjalan.');
        }
        $ia03 = $this->ia03->ensure($pendaftaran);
        $validated = $request->validate([
            'umpan_balik' => ['nullable', 'string', 'max:2000'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.tanggapan' => ['nullable', 'string', 'max:2000'],
            'items.*.pencapaian' => ['nullable', 'in:1,0'],
            'items.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);
        try {
            $this->ia03->simpan($ia03, $validated['items'] ?? [], $validated['umpan_balik'] ?? null, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'FR.IA.03 berhasil disimpan.');
    }

    public function final(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat difinalkan saat asesmen berjalan.');
        }
        $ia03 = $this->ia03->ensure($pendaftaran);
        try {
            $this->ia03->finalkan($ia03, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'FR.IA.03 difinalkan.');
    }

    public function pdf(Pendaftaran $pendaftaran)
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $pendaftaran->load(['skema', 'asesi.user', 'jadwal.asesors.user', 'jadwal.tuk']);
        $ia03 = $this->ia03->ensure($pendaftaran);
        $ia03->load('items');

        return Pdf::loadView('pdf.ia03', ['pendaftaran' => $pendaftaran, 'skema' => $pendaftaran->skema, 'ia03' => $ia03, 'asesor' => $pendaftaran->jadwal?->asesors->first()])->setPaper('a4', 'portrait')->stream(sprintf('IA03-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
