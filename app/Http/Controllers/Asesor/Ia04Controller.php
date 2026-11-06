<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Ia04Asesmen;
use App\Models\Ia04Item;
use App\Models\Pendaftaran;
use App\Services\Ia04Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Ia04Controller extends Controller
{
    public function __construct(private readonly Ia04Service $ia04) {}

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        $ia04 = $this->ia04->ensure($pendaftaran);
        $ia04->load('items');

        return view('asesor.ia04.show', [
            'pendaftaran' => $pendaftaran,
            'ia04' => $ia04,
            'units' => $pendaftaran->skema->skemaUnits()->with('unitKompetensi')->orderBy('urutan')->get(),
        ]);
    }

    public function tambah(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat diisi saat asesmen berjalan.');
        }

        $validated = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:2000'],
            'lingkup' => ['nullable', 'string', 'max:200'],
        ]);

        $ia04 = $this->ia04->ensure($pendaftaran);

        try {
            $this->ia04->tambahItem($ia04, $validated['pertanyaan'], $validated['lingkup'] ?? null, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Butir penilaian ditambahkan.');
    }

    public function hapus(Pendaftaran $pendaftaran, Ia04Item $item): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        abort_unless($item->ia04->pendaftaran_id === $pendaftaran->id, 404);

        try {
            $this->ia04->hapusItem($item);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Butir penilaian dihapus.');
    }

    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat diisi saat asesmen berjalan.');
        }

        $validated = $request->validate([
            'judul_kegiatan' => ['nullable', 'string', 'max:200'],
            'skenario' => ['nullable', 'string', 'max:5000'],
            'hal_dipersiapkan' => ['nullable', 'string', 'max:5000'],
            'waktu_persiapan' => ['nullable', 'string', 'max:100'],
            'hasil_dipresentasikan' => ['nullable', 'string', 'max:5000'],
            'waktu_presentasi' => ['nullable', 'string', 'max:100'],
            'umpan_balik' => ['nullable', 'string', 'max:2000'],
            'rekomendasi' => ['nullable', Rule::in(array_keys(Ia04Asesmen::REKOMENDASI_LABELS))],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.lingkup' => ['nullable', 'string', 'max:200'],
            'items.*.tanggapan' => ['nullable', 'string', 'max:2000'],
            'items.*.pencapaian' => ['nullable', 'in:1,0'],
        ]);

        $ia04 = $this->ia04->ensure($pendaftaran);

        try {
            $this->ia04->simpan($ia04, $validated, $validated['items'] ?? [], $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'FR.IA.04 berhasil disimpan.');
    }

    public function final(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        if ($pendaftaran->status !== Pendaftaran::STATUS_ASESMEN_BERJALAN) {
            return back()->with('error', 'Instrumen hanya dapat difinalkan saat asesmen berjalan.');
        }

        $ia04 = $this->ia04->ensure($pendaftaran);

        try {
            $this->ia04->finalkan($ia04, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'FR.IA.04 difinalkan.');
    }

    public function pdf(Pendaftaran $pendaftaran)
    {
        $this->authorize('inputInstrumen', $pendaftaran);

        $pendaftaran->load(['skema.skemaUnits.unitKompetensi', 'asesi.user', 'jadwal.asesors.user', 'jadwal.tuk']);

        $ia04 = $this->ia04->ensure($pendaftaran);
        $ia04->load('items');

        return Pdf::loadView('pdf.ia04', [
            'pendaftaran' => $pendaftaran,
            'skema' => $pendaftaran->skema,
            'ia04' => $ia04,
            'units' => $pendaftaran->skema->skemaUnits,
            'asesor' => $pendaftaran->jadwal?->asesors->first(),
        ])->setPaper('a4', 'portrait')->stream(sprintf('IA04-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
