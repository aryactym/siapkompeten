<?php

namespace App\Http\Controllers;

use App\Models\InstitutionDocument;
use App\Services\InstitutionStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    public function store(Request $request, InstitutionStorage $storage): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $file = $validated['file'];
        $path = $storage->store($file);

        try {
            InstitutionDocument::create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'],
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        } catch (\Throwable $exception) {
            $storage->delete($path);
            throw $exception;
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Dokumen berhasil diunggah.');
    }

    public function show(InstitutionDocument $document): View
    {
        $this->authorize('view', $document);

        return view('documents.show', [
            'document' => $document,
        ]);
    }

    public function download(InstitutionDocument $document): BinaryFileResponse
    {
        $this->authorize('download', $document);

        abort_unless(
            Storage::disk('public')->exists($document->file_path),
            404,
            'Berkas tidak ditemukan.'
        );

        return Storage::disk('public')->download(
            $document->file_path,
            $document->original_name
        );
    }
}
