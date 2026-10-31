<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skkni;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminStkkniController extends Controller
{
    public function index(): View
    {
        $skknis = Skkni::query()->orderBy('kode')->get();

        return view('admin.skkni.index', [
            'skknis' => $skknis,
            'newSkkni' => new Skkni(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSkkni($request);

        $skkni = Skkni::create($validated);

        activity('skkni')
            ->causedBy($request->user())
            ->performedOn($skkni)
            ->log('SKKNI ditambahkan.');

        return redirect()
            ->route('admin.stkkni.index')
            ->with('success', 'SKKNI berhasil ditambahkan.');
    }

    public function update(Request $request, Skkni $skkni): RedirectResponse
    {
        $skkni->update($this->validateSkkni($request, $skkni));

        activity('skkni')
            ->causedBy($request->user())
            ->performedOn($skkni)
            ->log('SKKNI diperbarui.');

        return redirect()
            ->route('admin.stkkni.index')
            ->with('success', 'SKKNI berhasil diperbarui.');
    }

    public function destroy(Request $request, Skkni $skkni): RedirectResponse
    {
        $skkni->delete();

        activity('skkni')
            ->causedBy($request->user())
            ->performedOn($skkni)
            ->log('SKKNI dihapus.');

        return redirect()
            ->route('admin.stkkni.index')
            ->with('success', 'SKKNI berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSkkni(Request $request, ?Skkni $skkni = null): array
    {
        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:100',
                Rule::unique('skknis', 'kode')
                    ->where('institution_id', (string) (TenantContext::id() ?? ''))
                    ->ignore($skkni?->id),
            ],
            'judul' => ['required', 'string', 'max:255'],
            'nomor_penetapan' => ['nullable', 'string', 'max:150'],
            'tahun' => ['required', 'integer', 'min:1900', 'max:2100'],
            'file_path' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    public function show(Skkni $stkkni): View
    {
        $stkkni->load(['unitKompetensis.elemenKompetensis.kriteriaUnjukKerjas']);

        return view('admin.skkni.show', ['skkni' => $stkkni]);
    }
}
