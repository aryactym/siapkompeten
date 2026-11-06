<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalAsesmen;
use App\Models\Pleno;
use App\Services\LaporanAsesmenService;
use App\Services\PlenoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PlenoController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.jadwal.index');
    }

    public function __construct(
        private readonly PlenoService $pleno,
        private readonly LaporanAsesmenService $laporan,
    ) {}

    public function show(JadwalAsesmen $jadwal): View
    {
        $this->authorize('view', $jadwal);

        $jadwal->load(['skema', 'tuk', 'event', 'asesors.user', 'pendaftarans.asesi.user', 'pendaftarans.hasilAsesmen']);

        $pleno = $this->pleno->ensure($jadwal);

        return view('admin.pleno.show', [
            'jadwal' => $jadwal,
            'pleno' => $pleno,
            'penghambat' => $this->laporan->penghambat($jadwal),
        ]);
    }

    public function store(Request $request, JadwalAsesmen $jadwal): RedirectResponse
    {
        $this->authorize('view', $jadwal);

        $validated = $request->validate([
            'nomor_ba' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['nullable', 'date'],
            'keputusan' => ['nullable', 'in:disetujui,ditolak'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'peserta' => ['nullable', 'array'],
            'peserta.*.pendaftaran_id' => ['required', 'uuid'],
            'peserta.*.keputusan' => ['required', 'in:kompeten,belum_kompeten'],
            'peserta.*.catatan' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->pleno->simpan(
                $jadwal,
                [
                    'nomor_ba' => $validated['nomor_ba'] ?? null,
                    'tanggal' => $validated['tanggal'] ?? null,
                    'keputusan' => $validated['keputusan'] ?? null,
                    'catatan' => $validated['catatan'] ?? null,
                ],
                $validated['peserta'] ?? [],
                $request->user(),
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('success', 'Pleno berhasil disimpan.');
    }

    public function final(Request $request, JadwalAsesmen $jadwal): RedirectResponse
    {
        $this->authorize('view', $jadwal);

        try {
            $this->pleno->finalkan($jadwal, $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Pleno difinalkan.');
    }

    public function pdf(JadwalAsesmen $jadwal)
    {
        $this->authorize('view', $jadwal);

        $jadwal->load(['skema', 'tuk', 'event', 'asesors.user']);

        $pleno = Pleno::where('jadwal_id', $jadwal->id)->first();

        abort_if($pleno === null, 404, 'Pleno belum dibuat.');

        $pleno->load(['pesertas.pendaftaran.asesi.user']);

        return Pdf::loadView('pdf.ba-pleno', [
            'jadwal' => $jadwal,
            'skema' => $jadwal->skema,
            'pleno' => $pleno,
            'asesor' => $jadwal->asesors->first(),
            'tuk' => $jadwal->tuk,
        ])->setPaper('a4', 'portrait')
            ->stream(sprintf('BA-PLENO-%s.pdf', $jadwal->id));
    }
}
