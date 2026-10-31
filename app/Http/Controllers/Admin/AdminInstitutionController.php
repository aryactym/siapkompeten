<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminInstitutionController extends Controller
{
    public function index(): View
    {
        $institution = TenantContext::institution() ?? Institution::query()->firstOrFail();

        return view('admin.institution.index', [
            'institution' => $institution,
        ]);
    }

    public function update(Request $request, Institution $institution): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nomor_lisensi' => ['nullable', 'string', 'max:100'],
            'masa_berlaku_lisensi' => ['nullable', 'date'],
            'jenis_lsp' => ['required', Rule::in(['p1', 'p2', 'p3'])],
            'email' => ['nullable', 'email', 'max:150'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:200'],
            'nama_ketua' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', $institution->is_active);
        $validated['is_setup_completed'] = true;

        $institution->update($validated);

        activity('institution')
            ->causedBy($request->user())
            ->performedOn($institution)
            ->log('Profil lembaga diperbarui.');

        return redirect()
            ->route('admin.institution.index')
            ->with('success', 'Profil lembaga berhasil diperbarui.');
    }
}
