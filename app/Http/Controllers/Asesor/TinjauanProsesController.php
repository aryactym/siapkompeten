<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\JadwalAsesmen;
use App\Models\TinjauanProsesAsesmen;
use App\Services\LaporanAsesmenService;
use App\Services\TinjauanProsesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TinjauanProsesController extends Controller
{
    public function __construct(
        private readonly TinjauanProsesService $tinjauan,
        private readonly LaporanAsesmenService $laporan,
    ) {}

    public function show(JadwalAsesmen $jadwal): View
    {
        $this->authorize('view', $jadwal);

        $jadwal->load(['skema', 'tuk', 'event', 'asesors.user']);

        return view('asesor.tinjauan-proses.show', [
            'jadwal' => $jadwal,
            'tinjauan' => TinjauanProsesAsesmen::firstOrNew(['jadwal_id' => $jadwal->id]),
            'penghambat' => $this->laporan->penghambat($jadwal),
            'asesor' => $jadwal->asesors->first(),
        ]);
    }

    public function store(Request $request, JadwalAsesmen $jadwal): RedirectResponse
    {
        $this->authorize('view', $jadwal);

        $validated = $request->validate([
            'proses' => ['nullable', 'array'],
            'proses.*.*' => ['nullable', 'string', 'max:255'],
            'rekomendasi_proses' => ['nullable', 'string', 'max:2000'],
            'dimensi' => ['nullable', 'array'],
            'dimensi.*' => ['nullable', 'string', 'max:255'],
            'rekomendasi_dimensi' => ['nullable', 'string', 'max:2000'],
            'komentar' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->tinjauan->simpan(
                $jadwal,
                $validated['proses'] ?? [],
                $validated['rekomendasi_proses'] ?? null,
                $validated['dimensi'] ?? [],
                $validated['rekomendasi_dimensi'] ?? null,
                $validated['komentar'] ?? null,
                $request->user(),
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('success', 'Tinjauan proses asesmen berhasil disimpan.');
    }

    public function final(Request $request, JadwalAsesmen $jadwal): RedirectResponse
    {
        $this->authorize('view', $jadwal);

        try {
            $this->tinjauan->finalkan($jadwal, $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Tinjauan proses asesmen difinalkan dan dikunci.');
    }

    public function pdf(JadwalAsesmen $jadwal)
    {
        $this->authorize('view', $jadwal);

        $jadwal->load(['skema', 'tuk', 'event', 'asesors.user']);

        $tinjauan = TinjauanProsesAsesmen::where('jadwal_id', $jadwal->id)->first();

        abort_if($tinjauan === null, 404, 'Tinjauan proses belum dibuat.');

        return Pdf::loadView('pdf.ak06', [
            'jadwal' => $jadwal,
            'skema' => $jadwal->skema,
            'tinjauan' => $tinjauan,
            'asesor' => $jadwal->asesors->first(),
            'tuk' => $jadwal->tuk,
        ])->setPaper('a4', 'landscape')
            ->stream(sprintf('AK06-%s.pdf', $jadwal->id));
    }
}
