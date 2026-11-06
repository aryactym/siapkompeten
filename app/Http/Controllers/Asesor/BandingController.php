<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Banding;
use App\Models\Pendaftaran;
use App\Services\BandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BandingController extends Controller
{
    public function __construct(private readonly BandingService $banding) {}

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('view', $pendaftaran);

        $pendaftaran->load(['skema', 'jadwal.tuk', 'jadwal.asesors.user', 'asesi.user', 'banding.histories.pembuat']);

        return view('asesor.banding.show', [
            'pendaftaran' => $pendaftaran,
            'banding' => $pendaftaran->banding,
            'bandingStatuses' => Banding::STATUS_LABELS,
        ]);
    }

    public function review(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('view', $pendaftaran);

        $banding = $pendaftaran->banding;

        abort_if($banding === null, 404, 'Banding belum diajukan.');

        $validated = $request->validate([
            'status' => ['required', 'in:ditinjau,diterima,ditolak,selesai'],
            'keputusan' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->banding->tinjau($banding, $request->user(), $validated['status'], $validated['keputusan'] ?? null);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('success', 'Banding berhasil ditinjau.');
    }

    public function pdf(Pendaftaran $pendaftaran)
    {
        $this->authorize('view', $pendaftaran);

        $banding = $pendaftaran->banding;

        abort_if($banding === null, 404, 'Banding belum diajukan.');

        return $this->banding->pdf($banding);
    }
}
