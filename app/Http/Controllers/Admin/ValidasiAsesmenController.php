<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\ValidasiAsesmen;
use App\Services\ValidasiAsesmenService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ValidasiAsesmenController extends Controller
{
    public function __construct(private readonly ValidasiAsesmenService $validasi) {}

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('view', $pendaftaran);

        $pendaftaran->load(['skema', 'jadwal.tuk', 'jadwal.asesors.user', 'asesi.user']);

        return view('admin.validasi-asesmen.show', [
            'pendaftaran' => $pendaftaran,
            'validasi' => ValidasiAsesmen::firstOrNew(['pendaftaran_id' => $pendaftaran->id]),
        ]);
    }

    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('view', $pendaftaran);

        $validated = $request->validate([
            'aspek_validitas' => ['nullable', 'string', 'max:2000'],
            'aspek_reliabilitas' => ['nullable', 'string', 'max:2000'],
            'aspek_keadilan' => ['nullable', 'string', 'max:2000'],
            'aspek_kesesuaian' => ['nullable', 'string', 'max:2000'],
            'keputusan' => ['nullable', 'in:disetujui,revisi,ditolak'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->validasi->simpan($pendaftaran, $validated, $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('success', 'Validasi asesmen berhasil disimpan.');
    }

    public function final(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('view', $pendaftaran);

        try {
            $this->validasi->finalkan($pendaftaran, $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('success', 'Validasi asesmen difinalkan.');
    }

    public function pdf(Pendaftaran $pendaftaran)
    {
        $this->authorize('view', $pendaftaran);

        $pendaftaran->load(['skema', 'jadwal.tuk', 'jadwal.asesors.user', 'asesi.user']);

        $validasi = ValidasiAsesmen::where('pendaftaran_id', $pendaftaran->id)->first();

        abort_if($validasi === null, 404, 'Validasi asesmen belum dibuat.');

        return Pdf::loadView('pdf.va', [
            'pendaftaran' => $pendaftaran,
            'skema' => $pendaftaran->skema,
            'validasi' => $validasi,
            'asesor' => $pendaftaran->jadwal?->asesors->first(),
            'asesiUser' => $pendaftaran->asesi->user,
        ])->setPaper('a4', 'portrait')
            ->stream(sprintf('VA-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
