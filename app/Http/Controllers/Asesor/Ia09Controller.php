<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Ia09Item;
use App\Models\Pendaftaran;
use App\Services\Ia09Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Ia09Controller extends Controller
{
    public function __construct(private readonly Ia09Service $Ia09) {}

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN && ! $pendaftaran->Ia09Asesmen?->isFinal()) {
            // allow view
        }
        $Ia09 = $this->Ia09->ensure($pendaftaran);
        $Ia09->load('items');

        return view('asesor.ia09.show', ['pendaftaran' => $pendaftaran, 'Ia09' => $Ia09]);
    }

    public function tambah(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $validated = $request->validate(['pertanyaan' => ['required', 'string', 'max:1000']]);
        $Ia09 = $this->Ia09->ensure($pendaftaran);
        try {
            $this->Ia09->tambahItem($Ia09, $validated['pertanyaan'], $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Pertanyaan ditambahkan.');
    }

    public function hapus(Pendaftaran $pendaftaran, Ia09Item $item): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        try {
            $this->Ia09->hapusItem($item);
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
        $Ia09 = $this->Ia09->ensure($pendaftaran);
        $validated = $request->validate([
            'umpan_balik' => ['nullable', 'string', 'max:2000'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.tanggapan' => ['nullable', 'string', 'max:2000'],
            'items.*.pencapaian' => ['nullable', 'in:1,0'],
            'items.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);
        try {
            $this->Ia09->simpan($Ia09, $validated['items'] ?? [], $validated['umpan_balik'] ?? null, $request->user());
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
        $Ia09 = $this->Ia09->ensure($pendaftaran);
        try {
            $this->Ia09->finalkan($Ia09, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'FR.IA.03 difinalkan.');
    }

    public function pdf(Pendaftaran $pendaftaran)
    {
        $this->authorize('inputInstrumen', $pendaftaran);
        $pendaftaran->load(['skema', 'asesi.user', 'jadwal.asesors.user', 'jadwal.tuk']);
        $Ia09 = $this->Ia09->ensure($pendaftaran);
        $Ia09->load('items');

        return Pdf::loadView('pdf.ia09', ['pendaftaran' => $pendaftaran, 'skema' => $pendaftaran->skema, 'Ia09' => $Ia09, 'asesor' => $pendaftaran->jadwal?->asesors->first()])->setPaper('a4', 'portrait')->stream(sprintf('ia09-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
