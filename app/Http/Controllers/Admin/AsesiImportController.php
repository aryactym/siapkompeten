<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\AsesiTemplateExport;
use App\Imports\ImportErrorReport;
use App\Models\ImportBatch;
use App\Services\AsesiImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AsesiImportController extends Controller
{
    public function create(): View
    {
        return view('admin.asesi.import', [
            'batches' => ImportBatch::with('user')->latest()->paginate(10),
        ]);
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new AsesiTemplateExport, 'template-impor-asesi.xlsx');
    }

    public function preview(Request $request, AsesiImportService $service): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,csv', 'max:5120'],
        ]);

        $batch = $service->createBatch($validated['file'], $request->user());

        return redirect()
            ->route('admin.asesi.import.show', $batch)
            ->with('success', 'Berkas berhasil dibaca. Periksa pratinjau sebelum mengimpor.');
    }

    public function show(ImportBatch $batch): View
    {
        $this->authorize('view', $batch);

        return view('admin.asesi.import-preview', [
            'batch' => $batch->load('user'),
            'rows' => $batch->rows()->paginate(50),
        ]);
    }

    public function commit(Request $request, ImportBatch $batch, AsesiImportService $service): RedirectResponse
    {
        $this->authorize('process', $batch);

        $service->commit($batch, $request->user());

        $batch->refresh();

        return redirect()
            ->route('admin.asesi.import.show', $batch)
            ->with(
                'success',
                sprintf(
                    'Impor selesai: %d asesi berhasil dibuat, %d baris gagal/ditolak.',
                    $batch->imported_rows,
                    $batch->invalid_rows,
                ),
            );
    }

    public function destroy(ImportBatch $batch, AsesiImportService $service): RedirectResponse
    {
        $this->authorize('delete', $batch);

        $service->deleteBatch($batch);

        return redirect()
            ->route('admin.asesi.import.create')
            ->with('success', 'Batch impor dibatalkan dan berkas dihapus.');
    }

    public function report(ImportBatch $batch): BinaryFileResponse
    {
        $this->authorize('view', $batch);

        return Excel::download(
            new ImportErrorReport($batch),
            'laporan-impor-asesi-'.now()->format('Ymd-His').'.xlsx',
        );
    }
}
