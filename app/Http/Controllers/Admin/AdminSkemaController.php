<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skema;
use App\Models\SkemaPersyaratan;
use App\Models\Skkni;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSkemaController extends Controller
{
    public function index(Request $request): View
    {
        $searchQuery = $request->string('q')->trim()->toString();
        $selectedJenis = $request->string('jenis')->trim()->toString();
        $selectedKategori = $request->string('kategori')->trim()->toString();
        $selectedStatus = $request->string('status')->trim()->toString();

        $skemas = Skema::query()
            ->when($searchQuery !== '', function ($query) use ($searchQuery): void {
                $like = '%'.$searchQuery.'%';
                $query->where(function ($query) use ($like): void {
                    $query->where('kode_skema', 'like', $like)
                        ->orWhere('nama_skema', 'like', $like);
                });
            })
            ->when($selectedJenis !== '', fn ($query) => $query->where('jenis', $selectedJenis))
            ->when($selectedKategori !== '', fn ($query) => $query->where('kategori', $selectedKategori))
            ->when($selectedStatus === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($selectedStatus === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderBy('nama_skema')
            ->get();

        $kategoriSkemas = Skema::query()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('admin.skema.index', [
            'skemas' => $skemas,
            'skknis' => Skkni::query()->where('is_active', true)->orderBy('judul')->get(),
            'newSkema' => new Skema(['jenis' => 'okupasi', 'is_active' => true]),
            'searchQuery' => $searchQuery,
            'selectedJenis' => $selectedJenis,
            'selectedKategori' => $selectedKategori,
            'selectedStatus' => $selectedStatus,
            'kategoriSkemas' => $kategoriSkemas,
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

    public function editPersyaratan(Skema $skema, SkemaPersyaratan $persyaratan): View
    {
        $this->authorize('update', $skema);

        return view('admin.skema.persyaratan-edit', [
            'skema' => $skema,
            'persyaratan' => $persyaratan,
        ]);
    }

    public function storePersyaratan(Request $request, Skema $skema): RedirectResponse
    {
        $this->authorize('update', $skema);

        $validated = $request->validate([
            'nama_dokumen' => ['required', 'string', 'max:200'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $skema->persyaratans()->create([
            'institution_id' => TenantContext::requireId(),
            'nama_dokumen' => $validated['nama_dokumen'],
            'keterangan' => $validated['keterangan'],
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'urutan' => $skema->persyaratans()->max('urutan') + 1,
        ]);

        return redirect()
            ->route('admin.skema.persyaratan', $skema)
            ->with('success', 'Persyaratan dokumen berhasil ditambahkan.');
    }

    public function updatePersyaratan(Request $request, Skema $skema, SkemaPersyaratan $persyaratan): RedirectResponse
    {
        $this->authorize('update', $skema);

        $validated = $request->validate([
            'nama_dokumen' => ['required', 'string', 'max:200'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        $persyaratan->update([
            'nama_dokumen' => $validated['nama_dokumen'],
            'keterangan' => $validated['keterangan'],
            'is_required' => (bool) ($validated['is_required'] ?? false),
        ]);

        return redirect()
            ->route('admin.skema.persyaratan', $skema)
            ->with('success', 'Persyaratan dokumen berhasil diperbarui.');
    }

    public function destroyPersyaratan(Request $request, Skema $skema, SkemaPersyaratan $persyaratan): RedirectResponse
    {
        $this->authorize('update', $skema);

        $persyaratan->delete();

        return redirect()
            ->route('admin.skema.persyaratan', $skema)
            ->with('success', 'Persyaratan dokumen berhasil dihapus.');
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
