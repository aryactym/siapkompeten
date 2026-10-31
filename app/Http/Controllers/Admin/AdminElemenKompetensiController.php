<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElemenKompetensi;
use App\Models\KriteriaUnjukKerja;
use App\Models\UnitKompetensi;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminElemenKompetensiController extends Controller
{
    public function index(Request $request): View
    {
        $unitId = $request->query('unit');

        $elemens = ElemenKompetensi::query()
            ->with(['unitKompetensi', 'kriteriaUnjukKerjas'])
            ->when($unitId, fn ($q) => $q->where('unit_kompetensi_id', $unitId))
            ->orderBy('urutan')
            ->get();

        return view('admin.elemen.index', [
            'elemens' => $elemens,
            'units' => UnitKompetensi::query()->where('is_active', true)->orderBy('kode_unit')->get(),
            'unitTerpilih' => $unitId ? UnitKompetensi::find($unitId) : null,
            'newElemen' => new ElemenKompetensi(['urutan' => 1]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateElemen($request);

        $elemen = ElemenKompetensi::create($validated);

        activity('elemen_kompetensi')
            ->causedBy($request->user())
            ->performedOn($elemen)
            ->log('Elemen kompetensi ditambahkan.');

        return redirect()
            ->route('admin.elemen.index', $request->filled('unit_kompetensi_id') ? ['unit' => $request->input('unit_kompetensi_id')] : [])
            ->with('success', 'Elemen kompetensi berhasil ditambahkan.');
    }

    public function update(Request $request, ElemenKompetensi $elemen): RedirectResponse
    {
        $elemen->update($this->validateElemen($request, $elemen));

        activity('elemen_kompetensi')
            ->causedBy($request->user())
            ->performedOn($elemen)
            ->log('Elemen kompetensi diperbarui.');

        return redirect()
            ->route('admin.elemen.index', ['unit' => $elemen->unit_kompetensi_id])
            ->with('success', 'Elemen kompetensi berhasil diperbarui.');
    }

    public function destroy(Request $request, ElemenKompetensi $elemen): RedirectResponse
    {
        $unitId = $elemen->unit_kompetensi_id;
        $elemen->delete();

        activity('elemen_kompetensi')
            ->causedBy($request->user())
            ->performedOn($elemen)
            ->log('Elemen kompetensi dihapus.');

        return redirect()
            ->route('admin.elemen.index', ['unit' => $unitId])
            ->with('success', 'Elemen kompetensi berhasil dihapus.');
    }

    public function storeKuk(Request $request, ElemenKompetensi $elemen): RedirectResponse
    {
        $validated = $request->validate([
            'urutan' => ['required', 'integer', 'min:1'],
            'pernyataan_kuk' => ['required', 'string'],
            'pertanyaan_observasi' => ['nullable', 'string'],
            'pertanyaan_lisan' => ['nullable', 'string'],
            'pertanyaan_tertulis' => ['nullable', 'string'],
        ]);

        $kuk = $elemen->kriteriaUnjukKerjas()->create($validated);

        activity('kuk')
            ->causedBy($request->user())
            ->performedOn($kuk)
            ->log('KUK ditambahkan.');

        return redirect()
            ->route('admin.elemen.index', ['unit' => $elemen->unit_kompetensi_id])
            ->with('success', 'Kriteria unjuk kerja berhasil ditambahkan.');
    }

    public function destroyKuk(Request $request, KriteriaUnjukKerja $kuk): RedirectResponse
    {
        $unitId = $kuk->elemenKompetensi?->unit_kompetensi_id;
        $kuk->delete();

        activity('kuk')
            ->causedBy($request->user())
            ->performedOn($kuk)
            ->log('KUK dihapus.');

        return redirect()
            ->route('admin.elemen.index', $unitId ? ['unit' => $unitId] : [])
            ->with('success', 'Kriteria unjuk kerja berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateElemen(Request $request, ?ElemenKompetensi $elemen = null): array
    {
        $validated = $request->validate([
            'unit_kompetensi_id' => [
                'required',
                'uuid',
                Rule::exists('unit_kompetensis', 'id')->where('institution_id', (string) (TenantContext::id() ?? '')),
            ],
            'urutan' => ['required', 'integer', 'min:1'],
            'nama_elemen' => ['required', 'string', 'max:255'],
        ]);

        return $validated;
    }
}
