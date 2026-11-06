<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\JadwalAsesmen;
use App\Models\LaporanAsesmen;
use App\Services\LaporanAsesmenService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LaporanAsesmenController extends Controller
{
    public function __construct(private readonly LaporanAsesmenService $laporan) {}

    public function show(JadwalAsesmen $jadwal): View
    {
        $this->authorize('view', $jadwal);

        $jadwal->load(['skema', 'tuk', 'event', 'asesors.user']);

        $laporan = LaporanAsesmen::firstOrNew(['jadwal_id' => $jadwal->id]);

        return view('asesor.laporan-asesmen.show', [
            'jadwal' => $jadwal,
            'laporan' => $laporan,
            'rekap' => $this->laporan->rekapAsesi($jadwal),
            'penghambat' => $this->laporan->penghambat($jadwal),
            'asesor' => $jadwal->asesors->first(),
        ]);
    }

    public function store(Request $request, JadwalAsesmen $jadwal): RedirectResponse
    {
        $this->authorize('view', $jadwal);

        $validated = $request->validate([
            'aspek_positif' => ['nullable', 'string', 'max:2000'],
            'aspek_negatif' => ['nullable', 'string', 'max:2000'],
            'pencatatan_penolakan' => ['nullable', 'string', 'max:2000'],
            'saran_perbaikan' => ['nullable', 'string', 'max:2000'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->laporan->simpan($jadwal, $validated, $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('success', 'Laporan asesmen berhasil disimpan.');
    }

    public function final(Request $request, JadwalAsesmen $jadwal): RedirectResponse
    {
        $this->authorize('view', $jadwal);

        try {
            $this->laporan->finalkan($jadwal, $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Laporan asesmen difinalkan dan dikunci.');
    }

    public function pdf(JadwalAsesmen $jadwal)
    {
        $this->authorize('view', $jadwal);

        $jadwal->load(['skema', 'tuk', 'event', 'asesors.user']);

        $laporan = LaporanAsesmen::where('jadwal_id', $jadwal->id)->first();

        abort_if($laporan === null, 404, 'Laporan asesmen belum dibuat.');

        return Pdf::loadView('pdf.ak05', [
            'jadwal' => $jadwal,
            'skema' => $jadwal->skema,
            'laporan' => $laporan,
            'rekap' => $this->laporan->rekapAsesi($jadwal),
            'asesor' => $jadwal->asesors->first(),
            'tuk' => $jadwal->tuk,
        ])->setPaper('a4', 'portrait')
            ->stream(sprintf('AK05-%s.pdf', $jadwal->id));
    }
}
