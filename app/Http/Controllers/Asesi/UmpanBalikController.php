<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\UmpanBalikAsesmen;
use App\Services\UmpanBalikService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UmpanBalikController extends Controller
{
    public function __construct(private readonly UmpanBalikService $umpanBalik) {}

    public function show(Request $request, Pendaftaran $pendaftaran): View
    {
        $this->authorize('view', $pendaftaran);
        $this->ensureOwner($request, $pendaftaran);

        $pendaftaran->load(['skema', 'jadwal.tuk', 'jadwal.asesors.user', 'asesi.user']);
        $umpanBalik = $pendaftaran->umpanBalik;

        return view('asesi.umpan-balik.show', [
            'pendaftaran' => $pendaftaran,
            'umpanBalik' => $umpanBalik,
            'komponen' => UmpanBalikAsesmen::KOMPONEN,
            'jawabanLabels' => UmpanBalikAsesmen::JAWABAN_LABELS,
            'rekap' => $umpanBalik?->isSubmitted() ? $this->umpanBalik->rekap($umpanBalik) : null,
            'asesor' => $pendaftaran->jadwal?->asesors->first(),
        ]);
    }

    public function store(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('view', $pendaftaran);
        $this->ensureOwner($request, $pendaftaran);

        $rules = [
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];

        foreach (array_keys(UmpanBalikAsesmen::KOMPONEN) as $key) {
            $rules['komponen.'.$key] = ['required', Rule::in(array_keys(UmpanBalikAsesmen::JAWABAN_LABELS))];
        }

        $validated = $request->validate($rules);

        try {
            $this->umpanBalik->submit(
                $pendaftaran,
                $validated['komponen'],
                $validated['catatan'] ?? null,
                $request->user(),
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('asesi.umpan-balik.show', $pendaftaran)
            ->with('success', 'Umpan balik berhasil dikirim. Terima kasih.');
    }

    public function pdf(Request $request, Pendaftaran $pendaftaran)
    {
        $this->authorize('view', $pendaftaran);

        $pendaftaran->load(['skema', 'jadwal.tuk', 'jadwal.asesors.user', 'asesi.user']);
        $umpanBalik = $pendaftaran->umpanBalik;

        abort_unless($umpanBalik?->isSubmitted(), 404, 'Umpan balik FR.AK.03 belum diisi.');

        return Pdf::loadView('pdf.ak03', [
            'pendaftaran' => $pendaftaran,
            'skema' => $pendaftaran->skema,
            'umpanBalik' => $umpanBalik,
            'komponen' => UmpanBalikAsesmen::KOMPONEN,
            'rekap' => $this->umpanBalik->rekap($umpanBalik),
            'asesor' => $pendaftaran->jadwal?->asesors->first(),
            'asesiUser' => $pendaftaran->asesi->user,
        ])->setPaper('a4', 'portrait')
            ->stream(sprintf('AK03-%s.pdf', $pendaftaran->no_pendaftaran));
    }

    private function ensureOwner(Request $request, Pendaftaran $pendaftaran): void
    {
        if (! $pendaftaran->asesi()->where('user_id', $request->user()->id)->exists()) {
            abort(403);
        }
    }
}
