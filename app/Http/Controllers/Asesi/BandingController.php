<?php

namespace App\Http\Controllers\Asesi;

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

    public function show(Request $request, Pendaftaran $pendaftaran): View
    {
        $this->authorize('view', $pendaftaran);
        $this->ensureOwner($request, $pendaftaran);

        $pendaftaran->load(['skema', 'jadwal.tuk', 'jadwal.asesors.user', 'asesi.user', 'banding.histories.pembuat']);

        return view('asesi.banding.show', [
            'pendaftaran' => $pendaftaran,
            'banding' => $pendaftaran->banding,
            'bandingStatuses' => Banding::STATUS_LABELS,
        ]);
    }

    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('view', $pendaftaran);
        $this->ensureOwner($request, $pendaftaran);

        $validated = $request->validate([
            'alasan' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $this->banding->ajukan($pendaftaran, $request->user(), $validated['alasan']);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('asesi.banding.show', $pendaftaran)
            ->with('success', 'Banding berhasil diajukan.');
    }

    public function pdf(Request $request, Pendaftaran $pendaftaran)
    {
        $this->authorize('view', $pendaftaran);
        $this->ensureOwner($request, $pendaftaran);

        $banding = $pendaftaran->banding;

        abort_if($banding === null, 404, 'Banding belum diajukan.');

        return $this->banding->pdf($banding);
    }

    private function ensureOwner(Request $request, Pendaftaran $pendaftaran): void
    {
        if (! $pendaftaran->asesi()->where('user_id', $request->user()->id)->exists()) {
            abort(403);
        }
    }
}
