<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skema;
use App\Models\Skkni;
use App\Models\UnitKompetensi;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUnitKompetensiController extends Controller
{
    public function index(Request $request): View
    {
        $searchQuery = $request->string('q')->trim()->toString();
        $selectedKategori = $request->string('kategori')->trim()->toString();

        $units = UnitKompetensi::query()
            ->with('skkni')
            ->when($searchQuery !== '', function ($query) use ($searchQuery): void {
                $like = '%'.$searchQuery.'%';
                $query->where(function ($query) use ($like): void {
                    $query->where('kode_unit', 'like', $like)
                        ->orWhere('judul_unit', 'like', $like);
                });
            })
            ->when($selectedKategori !== '', function ($query) use ($selectedKategori): void {
                $query->whereHas('skemas', fn ($skemaQuery) => $skemaQuery->where('kategori', $selectedKategori));
            })
            ->orderBy('kode_unit')
            ->get();

        $kategoriSkemas = Skema::query()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('admin.unit.index', [
            'units' => $units,
            'skknis' => Skkni::query()->where('is_active', true)->orderBy('judul')->get(),
            'newUnit' => new UnitKompetensi(['is_active' => true]),
            'searchQuery' => $searchQuery,
            'selectedKategori' => $selectedKategori,
            'kategoriSkemas' => $kategoriSkemas,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUnit($request);

        $unit = UnitKompetensi::create($validated);

        activity('unit_kompetensi')
            ->causedBy($request->user())
            ->performedOn($unit)
            ->log('Unit kompetensi ditambahkan.');

        return redirect()
            ->route('admin.unit.index')
            ->with('success', 'Unit kompetensi berhasil ditambahkan.');
    }

    public function update(Request $request, UnitKompetensi $unit): RedirectResponse
    {
        $unit->update($this->validateUnit($request, $unit));

        activity('unit_kompetensi')
            ->causedBy($request->user())
            ->performedOn($unit)
            ->log('Unit kompetensi diperbarui.');

        return redirect()
            ->route('admin.unit.index')
            ->with('success', 'Unit kompetensi berhasil diperbarui.');
    }

    public function destroy(Request $request, UnitKompetensi $unit): RedirectResponse
    {
        $unit->delete();

        activity('unit_kompetensi')
            ->causedBy($request->user())
            ->performedOn($unit)
            ->log('Unit kompetensi dihapus.');

        return redirect()
            ->route('admin.unit.index')
            ->with('success', 'Unit kompetensi berhasil dihapus.');
    }

    public function show(UnitKompetensi $unit): View
    {
        $unit->load(['skkni', 'elemenKompetensis.kriteriaUnjukKerjas', 'skemas']);

        return view('admin.unit.show', ['unit' => $unit]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateUnit(Request $request, ?UnitKompetensi $unit = null): array
    {
        $validated = $request->validate([
            'kode_unit' => [
                'required',
                'string',
                'max:100',
                Rule::unique('unit_kompetensis', 'kode_unit')
                    ->where('institution_id', (string) (TenantContext::id() ?? ''))
                    ->ignore($unit?->id),
            ],
            'judul_unit' => ['required', 'string', 'max:255'],
            'standar_kompetensi' => ['nullable', 'string'],
            'deskripsi' => ['nullable', 'string'],
            'skkni_id' => ['nullable', 'uuid'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
