<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\Asesi;
use App\Models\Pendaftaran;
use App\Models\PendaftaranDokumen;
use App\Models\Skema;
use App\Services\PendaftaranStorage;
use App\Services\PendaftaranWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PendaftaranController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $asesi = $this->asesi($request);

        if ($asesi === null) {
            return redirect()->route('dashboard')->with('error', 'Profil asesi tidak ditemukan.');
        }

        $pendaftarans = Pendaftaran::query()
            ->with(['skema', 'jadwal.tuk'])
            ->where('asesi_id', $asesi->id)
            ->latest()
            ->paginate(10);

        return view('asesi.pendaftaran.index', [
            'pendaftarans' => $pendaftarans,
            'asesi' => $asesi,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $asesi = $this->asesi($request);

        if ($asesi === null) {
            return redirect()->route('dashboard')->with('error', 'Profil asesi tidak ditemukan.');
        }

        if (! $asesi->isProfileComplete()) {
            return redirect()
                ->route('asesi.profil.edit')
                ->with('error', 'Lengkapi profil pokok terlebih dahulu sebelum mendaftar skema.');
        }

        return view('asesi.pendaftaran.create', [
            'asesi' => $asesi,
            'skemas' => Skema::aktif()->orderBy('nama_skema')->get(),
            'activeSkemaIds' => $asesi->pendaftarans()->aktif()->pluck('skema_id')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $asesi = $this->asesi($request);

        if ($asesi === null) {
            return redirect()->route('dashboard')->with('error', 'Profil asesi tidak ditemukan.');
        }

        if (! $asesi->isProfileComplete()) {
            return redirect()
                ->route('asesi.profil.edit')
                ->with('error', 'Lengkapi profil pokok terlebih dahulu sebelum mendaftar skema.');
        }

        $validated = $request->validate([
            'skema_id' => [
                'required',
                'uuid',
                Rule::exists('skemas', 'id')
                    ->where('institution_id', $asesi->institution_id)
                    ->where('is_active', true),
            ],
            'tujuan_asesmen' => ['required', Rule::in(array_keys(Pendaftaran::TUJUAN_LABELS))],
        ]);

        $existing = $asesi->pendaftarans()
            ->aktif()
            ->where('skema_id', $validated['skema_id'])
            ->exists();

        if ($existing) {
            return back()->with('error', 'Anda masih memiliki pendaftaran aktif pada skema tersebut.');
        }

        $pendaftaran = DB::transaction(function () use ($asesi, $validated, $request): Pendaftaran {
            $pendaftaran = Pendaftaran::create([
                'no_pendaftaran' => $this->generateNoPendaftaran($asesi->institution_id),
                'asesi_id' => $asesi->id,
                'skema_id' => $validated['skema_id'],
                'tujuan_asesmen' => $validated['tujuan_asesmen'],
                'status' => Pendaftaran::STATUS_DRAF,
            ]);

            activity('pendaftaran')
                ->causedBy($request->user())
                ->performedOn($pendaftaran)
                ->log('Asesi mendaftar skema sertifikasi.');

            return $pendaftaran;
        });

        return redirect()
            ->route('asesi.pendaftaran.show', $pendaftaran)
            ->with('success', 'Pendaftaran skema berhasil dibuat. Silakan unggah dokumen persyaratan.');
    }

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('view', $pendaftaran);

        $pendaftaran->load([
            'skema.persyaratans',
            'dokumens.persyaratan',
            'jadwal.tuk',
            'jadwal.asesors.user',
        ]);

        return view('asesi.pendaftaran.show', [
            'pendaftaran' => $pendaftaran,
        ]);
    }

    public function uploadDokumen(
        Request $request,
        Pendaftaran $pendaftaran,
        PendaftaranStorage $storage,
    ): RedirectResponse {
        $this->authorize('update', $pendaftaran);

        $validated = $request->validate([
            'skema_persyaratan_id' => [
                'required',
                'uuid',
                Rule::exists('skema_persyaratans', 'id')
                    ->where('institution_id', $pendaftaran->institution_id)
                    ->where('skema_id', $pendaftaran->skema_id),
            ],
            'file' => [
                'required',
                'file',
                'mimes:'.implode(',', config('pendaftaran.document_mimes')),
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:'.config('pendaftaran.document_max_size_kb'),
            ],
        ]);

        $file = $validated['file'];
        $oldPath = null;

        try {
            $path = $storage->store($pendaftaran, $file);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        try {
            DB::transaction(function () use ($pendaftaran, $validated, $file, $path, &$oldPath): void {
                $dokumen = PendaftaranDokumen::where('pendaftaran_id', $pendaftaran->id)
                    ->where('skema_persyaratan_id', $validated['skema_persyaratan_id'])
                    ->first();

                if ($dokumen !== null) {
                    $oldPath = $dokumen->file_path;
                    $dokumen->update([
                        'file_path' => $path,
                        'file_mime' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'status' => 'menunggu',
                        'catatan' => null,
                        'diverifikasi_oleh' => null,
                        'diverifikasi_pada' => null,
                    ]);

                    return;
                }

                PendaftaranDokumen::create([
                    'pendaftaran_id' => $pendaftaran->id,
                    'skema_persyaratan_id' => $validated['skema_persyaratan_id'],
                    'file_path' => $path,
                    'file_mime' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => 'menunggu',
                ]);
            });
        } catch (\Throwable $exception) {
            $storage->delete($path);
            throw $exception;
        }

        if (filled($oldPath)) {
            $storage->delete($oldPath);
        }

        activity('pendaftaran')
            ->causedBy($request->user())
            ->performedOn($pendaftaran)
            ->log('Asesi mengunggah dokumen persyaratan.');

        return back()->with('success', 'Dokumen persyaratan berhasil diunggah.');
    }

    public function deleteDokumen(
        Request $request,
        Pendaftaran $pendaftaran,
        PendaftaranDokumen $dokumen,
        PendaftaranStorage $storage,
    ): RedirectResponse {
        $this->authorize('update', $pendaftaran);
        abort_unless($dokumen->pendaftaran_id === $pendaftaran->id, 404);

        $storage->delete($dokumen->file_path);
        $dokumen->delete();

        activity('pendaftaran')
            ->causedBy($request->user())
            ->performedOn($pendaftaran)
            ->log('Asesi menghapus dokumen persyaratan.');

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function submitDokumen(
        Request $request,
        Pendaftaran $pendaftaran,
        PendaftaranWorkflow $workflow,
    ): RedirectResponse {
        $this->authorize('update', $pendaftaran);

        $workflow->submitDocuments($pendaftaran, $request->user());

        return redirect()
            ->route('asesi.pendaftaran.show', $pendaftaran)
            ->with('success', 'Dokumen berhasil diajukan. Menunggu verifikasi admin.');
    }

    public function downloadDokumen(
        PendaftaranDokumen $dokumen,
        PendaftaranStorage $storage,
    ): StreamedResponse {
        $this->authorize('download', $dokumen);

        abort_unless($storage->exists($dokumen->file_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk(PendaftaranStorage::DISK)->download(
            $dokumen->file_path,
            $dokumen->persyaratan->nama_dokumen.'.'.pathinfo($dokumen->file_path, PATHINFO_EXTENSION),
        );
    }

    private function asesi(Request $request): ?Asesi
    {
        return Asesi::with('user')->where('user_id', $request->user()->id)->first();
    }

    private function generateNoPendaftaran(string $institutionId): string
    {
        $prefix = now()->format('Ymd');
        $sequence = Pendaftaran::withoutTenantScope()
            ->where('institution_id', $institutionId)
            ->where('no_pendaftaran', 'like', $prefix.'%')
            ->count() + 1;

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (
            Pendaftaran::withoutTenantScope()
                ->where('institution_id', $institutionId)
                ->where('no_pendaftaran', $number)
                ->exists()
        );

        return $number;
    }
}
