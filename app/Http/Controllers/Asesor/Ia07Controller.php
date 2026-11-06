<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Ia07Item;
use App\Models\Pendaftaran;
use App\Services\Ia07Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Ia07Controller extends Controller
{
    public function __construct(private readonly Ia07Service $Ia07) {}

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN && ! $pendaftaran->Ia07Asesmen?->isFinal()) {
            // allow view
        }
        $Ia07 = $this->Ia07->ensure($pendaftaran);
        $Ia07->load('items');

        return view('asesor.ia07.show', ['pendaftaran' => $pendaftaran, 'Ia07' => $Ia07]);
    }

    public function tambah(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $validated = $request->validate(['pertanyaan' => ['required', 'string', 'max:1000']]);
        $Ia07 = $this->Ia07->ensure($pendaftaran);
        try {
            $this->Ia07->tambahItem($Ia07, $validated['pertanyaan'], $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Pertanyaan ditambahkan.');
    }

    public function hapus(Pendaftaran $pendaftaran, Ia07Item $item): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        try {
            $this->Ia07->hapusItem($item);
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
        $Ia07 = $this->Ia07->ensure($pendaftaran);
        $validated = $request->validate([
            'umpan_balik' => ['nullable', 'string', 'max:2000'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.tanggapan' => ['nullable', 'string', 'max:2000'],
            'items.*.pencapaian' => ['nullable', 'in:1,0'],
            'items.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);
        try {
            $this->Ia07->simpan($Ia07, $validated['items'] ?? [], $validated['umpan_balik'] ?? null, $request->user());
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
        $Ia07 = $this->Ia07->ensure($pendaftaran);
        try {
            $this->Ia07->finalkan($Ia07, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'FR.IA.03 difinalkan.');
    }

    public function pdf(Pendaftaran $pendaftaran)
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $pendaftaran->load(['skema', 'asesi.user', 'jadwal.asesors.user', 'jadwal.tuk']);
        $Ia07 = $this->Ia07->ensure($pendaftaran);
        $Ia07->load('items');

        return Pdf::loadView('pdf.ia07', ['pendaftaran' => $pendaftaran, 'skema' => $pendaftaran->skema, 'Ia07' => $Ia07, 'asesor' => $pendaftaran->jadwal?->asesors->first()])->setPaper('a4', 'portrait')->stream(sprintf('ia07-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
