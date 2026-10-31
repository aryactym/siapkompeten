<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesor;
use App\Models\AsesorKompetensi;
use App\Models\Skema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Lingkup Kompetensi asesor (FR-ASR-10): pemetaan asesor ke skema yang dapat
 * diasesinya. Berbasis tabel `asesor_kompetensis` (lihat catatan di model).
 */
class AsesorLingkupController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AsesorKompetensi::class);

        $asesors = Asesor::query()
            ->with(['user', 'kompetensis.skema'])
            ->withCount('kompetensis')
            ->orderBy('no_register')
            ->paginate(15)
            ->withQueryString();

        return view('admin.asesor.lingkup.index', [
            'asesors' => $asesors,
        ]);
    }

    public function show(Asesor $asesor): View
    {
        $this->authorize('viewAny', AsesorKompetensi::class);

        $asesor->load(['user', 'kompetensis.skema']);

        $assignedSkemaIds = $asesor->kompetensis->pluck('skema_id')->filter()->all();

        $availableSkemas = Skema::query()
            ->aktif()
            ->when($assignedSkemaIds !== [], fn ($q) => $q->whereNotIn('id', $assignedSkemaIds))
            ->orderBy('nama_skema')
            ->get();

        return view('admin.asesor.lingkup.show', [
            'asesor' => $asesor,
            'availableSkemas' => $availableSkemas,
        ]);
    }

    public function store(Request $request, Asesor $asesor): RedirectResponse
    {
        $this->authorize('create', AsesorKompetensi::class);

        $validated = $request->validate([
            'skema_id' => [
                'required',
                'uuid',
                Rule::exists('skemas', 'id')->where('institution_id', $asesor->institution_id),
                Rule::unique('asesor_kompetensis', 'skema_id')
                    ->where('institution_id', $asesor->institution_id)
                    ->where('asesor_id', $asesor->id),
            ],
            'berlaku_mulai' => ['nullable', 'date'],
            'berlaku_hingga' => ['nullable', 'date', 'after_or_equal:berlaku_mulai'],
        ]);

        AsesorKompetensi::create([
            'institution_id' => $asesor->institution_id,
            'asesor_id' => $asesor->id,
            'skema_id' => $validated['skema_id'],
            'berlaku_mulai' => $validated['berlaku_mulai'] ?? null,
            'berlaku_hingga' => $validated['berlaku_hingga'] ?? null,
        ]);

        activity('asesor')
            ->causedBy($request->user())
            ->performedOn($asesor)
            ->log('Lingkup kompetensi asesor ditambahkan.');

        return redirect()
            ->route('admin.asesor.skema.show', $asesor)
            ->with('success', 'Lingkup kompetensi ditambahkan.');
    }

    public function destroy(Request $request, Asesor $asesor, AsesorKompetensi $kompetensi): RedirectResponse
    {
        $this->authorize('delete', $kompetensi);
        abort_unless($kompetensi->asesor_id === $asesor->id, 404);

        $kompetensi->delete();

        activity('asesor')
            ->causedBy($request->user())
            ->performedOn($asesor)
            ->log('Lingkup kompetensi asesor dihapus.');

        return redirect()
            ->route('admin.asesor.skema.show', $asesor)
            ->with('success', 'Lingkup kompetensi dihapus.');
    }
}
