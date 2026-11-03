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

    public function show(Institution $institution): View
    {
        return view('admin.institution.index', [
            'institution' => $institution,
        ]);
    }

    public function update(Request $request, Institution $institution): RedirectResponse
    {
        // Validate non-file fields only
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

    public function uploadLogo(Request $request, Institution $institution): RedirectResponse
    {
        $validated = $request->validate([
            'logo_type' => ['required', Rule::in(['logo_bnsp_path', 'logo_lsp_path'])],
            'logo_file' => ['required', 'file', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ]);

        $field = $validated['logo_type'];
        $file = $request->file('logo_file');

        // Hapus file lama
        $oldPath = $institution->getAttribute($field);
        if ($oldPath && \Storage::disk('public')->exists($oldPath)) {
            \Storage::disk('public')->delete($oldPath);
        }

        // Simpan file baru — pola stream seperti PendaftaranStorage (reliable di Windows)
        $tempPath = $file->getPathname();

        if (blank($tempPath) || ! is_readable($tempPath)) {
            return back()
                ->withErrors(['logo_file' => 'Berkas sementara tidak dapat dibaca. Coba unggah ulang.']);
        }

        $stream = fopen($tempPath, 'rb');

        if ($stream === false) {
            return back()
                ->withErrors(['logo_file' => 'Berkas sementara tidak dapat dibuka. Coba unggah ulang.']);
        }

        $filename = time().'_'.$file->getClientOriginalName();
        $path = 'institution/logos/'.$filename;

        try {
            $stored = \Storage::disk('public')->put($path, $stream);
        } catch (\Exception $e) {
            $stored = false;
        } finally {
            fclose($stream);
        }

        if (! $stored) {
            return back()
                ->withErrors(['logo_file' => 'Berkas gagal disimpan.']);
        }

        $institution->update([$field => $path]);

        return back()
            ->with('success', 'Logo berhasil diperbarui.');
    }
}
