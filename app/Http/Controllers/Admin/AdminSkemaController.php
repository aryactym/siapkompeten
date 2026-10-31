<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skema;
use App\Models\Skkni;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSkemaController extends Controller
{
    public function index(): View
    {
        $skemas = Skema::query()->orderBy('nama_skema')->get();

        return view('admin.skema.index', [
            'skemas' => $skemas,
            'skknis' => Skkni::query()->where('is_active', true)->orderBy('judul')->get(),
            'newSkema' => new Skema(['jenis' => 'okupasi', 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSkema($request);

        $skema = Skema::create($validated);

        activity('skema')
            ->causedBy($request->user())
            ->performedOn($skema)
            ->log('Skema sertifikasi ditambahkan.');

        return redirect()
            ->route('admin.skema.index')
            ->with('success', 'Skema sertifikasi berhasil ditambahkan.');
    }

    public function update(Request $request, Skema $skema): RedirectResponse
    {
        $skema->update($this->validateSkema($request, $skema));

        activity('skema')
            ->causedBy($request->user())
            ->performedOn($skema)
            ->log('Skema sertifikasi diperbarui.');

        return redirect()
            ->route('admin.skema.index')
            ->with('success', 'Skema sertifikasi berhasil diperbarui.');
    }

    public function destroy(Request $request, Skema $skema): RedirectResponse
    {
        $skema->delete();

        activity('skema')
            ->causedBy($request->user())
            ->performedOn($skema)
            ->log('Skema sertifikasi dihapus.');

        return redirect()
            ->route('admin.skema.index')
            ->with('success', 'Skema sertifikasi berhasil dihapus.');
    }

    public function show(Skema $skema): View
    {
        $skema->load([
            'persyaratans',
            'skemaUnits.unitKompetensi.elemenKompetensis.kriteriaUnjukKerjas',
        ]);

        return view('admin.skema.show', ['skema' => $skema]);
    }

    public function persyaratan(Skema $skema): View
    {
        return view('admin.skema.persyaratan', [
            'skema' => $skema->load('persyaratans'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSkema(Request $request, ?Skema $skema = null): array
    {
        $validated = $request->validate([
            'kode_skema' => [
                'required',
                'string',
                'max:100',
                Rule::unique('skemas', 'kode_skema')
                    ->where('institution_id', (string) (TenantContext::id() ?? ''))
                    ->ignore($skema?->id),
            ],
            'nama_skema' => ['required', 'string', 'max:255'],
            'nomor_skema' => ['nullable', 'string', 'max:150'],
            'jenis' => ['required', Rule::in(['kkni', 'okupasi', 'klaster'])],
            'level_kkni' => ['nullable', 'integer', 'min:1', 'max:9'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'biaya' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
