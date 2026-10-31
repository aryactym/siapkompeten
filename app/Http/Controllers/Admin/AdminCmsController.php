<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCmsController extends Controller
{
    public function index(Request $request): View
    {
        $kategori = $request->string('kategori')->toString();

        $contents = CmsContent::query()
            ->when($kategori !== '', fn ($q) => $q->where('kategori', $kategori))
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->orderByDesc('tanggal_terbit')
            ->get();

        return view('admin.cms.index', [
            'contents' => $contents,
            'kategori' => $kategori,
            'kategoris' => CmsContent::KATEGORI,
        ]);
    }

    public function create(): View
    {
        return view('admin.cms.form', [
            'content' => new CmsContent(['is_active' => true, 'urutan' => 1]),
            'kategoris' => CmsContent::KATEGORI,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateContent($request);

        $content = CmsContent::create($validated);

        activity('cms')
            ->causedBy($request->user())
            ->performedOn($content)
            ->log('Konten CMS ditambahkan.');

        return redirect()
            ->route('admin.cms.index')
            ->with('success', 'Konten CMS berhasil ditambahkan.');
    }

    public function edit(CmsContent $content): View
    {
        return view('admin.cms.form', [
            'content' => $content,
            'kategoris' => CmsContent::KATEGORI,
        ]);
    }

    public function update(Request $request, CmsContent $content): RedirectResponse
    {
        $content->update($this->validateContent($request, $content));

        activity('cms')
            ->causedBy($request->user())
            ->performedOn($content)
            ->log('Konten CMS diperbarui.');

        return redirect()
            ->route('admin.cms.index')
            ->with('success', 'Konten CMS berhasil diperbarui.');
    }

    public function destroy(Request $request, CmsContent $content): RedirectResponse
    {
        $content->delete();

        activity('cms')
            ->causedBy($request->user())
            ->performedOn($content)
            ->log('Konten CMS dihapus.');

        return redirect()
            ->route('admin.cms.index')
            ->with('success', 'Konten CMS berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateContent(Request $request, ?CmsContent $content = null): array
    {
        return $request->validate([
            'kategori' => ['required', Rule::in(array_keys(CmsContent::KATEGORI))],
            'judul' => ['required', 'string', 'max:255'],
            'sub_judul' => ['nullable', 'string', 'max:255'],
            'tanggal_terbit' => ['nullable', 'date'],
            'jam_terbit' => ['nullable', 'date_format:H:i'],
            'isi' => ['nullable', 'string'],
            'gambar_path' => ['nullable', 'string', 'max:500'],
            'urutan' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
