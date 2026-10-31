<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tuk;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;

class AdminTukController extends Controller
{
    public function index(): View
    {
        $tuks = Tuk::query()->with(['province', 'city'])->orderBy('nama_tuk')->get();

        return view('admin.tuk.index', [
            'tuks' => $tuks,
            'provinces' => Province::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
            'newTuk' => new Tuk(['jenis' => 'sewaktu', 'is_verified' => true, 'is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTuk($request);

        $tuk = Tuk::create($validated);

        activity('tuk')
            ->causedBy($request->user())
            ->performedOn($tuk)
            ->log('TUK ditambahkan.');

        return redirect()
            ->route('admin.tuk.index')
            ->with('success', 'TUK berhasil ditambahkan.');
    }

    public function update(Request $request, Tuk $tuk): RedirectResponse
    {
        $tuk->update($this->validateTuk($request, $tuk));

        activity('tuk')
            ->causedBy($request->user())
            ->performedOn($tuk)
            ->log('TUK diperbarui.');

        return redirect()
            ->route('admin.tuk.index')
            ->with('success', 'TUK berhasil diperbarui.');
    }

    public function destroy(Request $request, Tuk $tuk): RedirectResponse
    {
        $tuk->delete();

        activity('tuk')
            ->causedBy($request->user())
            ->performedOn($tuk)
            ->log('TUK dihapus.');

        return redirect()
            ->route('admin.tuk.index')
            ->with('success', 'TUK berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTuk(Request $request, ?Tuk $tuk = null): array
    {
        $validated = $request->validate([
            'kode_tuk' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tuks', 'kode_tuk')
                    ->where('institution_id', (string) (TenantContext::id() ?? ''))
                    ->ignore($tuk?->id),
            ],
            'nama_tuk' => ['required', 'string', 'max:200'],
            'jenis' => ['required', Rule::in(['sewaktu', 'mandiri', 'tempat_kerja'])],
            'penanggung_jawab' => ['required', 'string', 'max:150'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'alamat' => ['nullable', 'string'],
            'province_code' => ['nullable', 'string', 'size:2'],
            'city_code' => ['nullable', 'string', 'size:4'],
            'is_verified' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_verified'] = $request->boolean('is_verified');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
