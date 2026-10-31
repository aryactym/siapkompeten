<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApprovePendaftaran;
use App\Http\Controllers\Controller;
use App\Models\HasilAsesmen;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\PendaftaranDokumen;
use App\Services\NotificationOutbox;
use App\Services\PendaftaranStorage;
use App\Services\PendaftaranWorkflow;
use App\Support\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PendaftaranController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->string('tab')->toString() ?: 'semua';

        $pendaftarans = Pendaftaran::query()
            ->with(['asesi.user', 'skema', 'jadwal.tuk'])
            ->withCount('dokumens')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q')->trim().'%';
                $query->where(function ($query) use ($search): void {
                    $query->where('no_pendaftaran', 'like', $search)
                        ->orWhereHas('asesi.user', fn ($userQuery) => $userQuery->where('name', 'like', $search));
                });
            })
            ->when($tab === 'perlu_verifikasi', fn ($query) => $query->where('status', Pendaftaran::STATUS_DOKUMEN_DIAJUKAN))
            ->when($tab === 'ditolak', fn ($query) => $query->whereIn('status', [Pendaftaran::STATUS_DOKUMEN_DITOLAK, Pendaftaran::STATUS_DITOLAK]))
            ->when($tab === 'siap_plotting', fn ($query) => $query->where('status', Pendaftaran::STATUS_MENUNGGU_JADWAL))
            ->when($tab === 'terjadwal', fn ($query) => $query->whereIn('status', [Pendaftaran::STATUS_TERJADWAL, Pendaftaran::STATUS_ASESMEN_BERJALAN]))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.pendaftaran.index', [
            'pendaftarans' => $pendaftarans,
            'tab' => $tab,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('view', $pendaftaran);

        $pendaftaran->load([
            'asesi.user',
            'asesi.province',
            'asesi.city',
            'skema.persyaratans',
            'dokumens.persyaratan',
            'dokumens.verifikator',
            'jadwal.tuk',
            'jadwal.asesors.user',
        ]);

        $jadwalOptions = JadwalAsesmen::query()
            ->with(['tuk', 'event'])
            ->withCount('pendaftarans')
            ->where('skema_id', $pendaftaran->skema_id)
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->whereHas('asesors')
            ->orderBy('tanggal')
            ->get()
            ->filter(fn (JadwalAsesmen $jadwal): bool => $jadwal->pendaftarans_count < $jadwal->kuota);

        return view('admin.pendaftaran.show', [
            'pendaftaran' => $pendaftaran,
            'jadwalOptions' => $jadwalOptions,
        ]);
    }

    public function verifyDokumen(
        Request $request,
        Pendaftaran $pendaftaran,
        PendaftaranDokumen $dokumen,
        PendaftaranWorkflow $workflow,
    ): RedirectResponse {
        $this->authorize('verify', $dokumen);
        abort_unless($dokumen->pendaftaran_id === $pendaftaran->id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'catatan' => ['nullable', 'string', 'max:1000', 'required_if:status,ditolak'],
        ]);

        DB::transaction(function () use ($request, $dokumen, $validated): void {
            $dokumen->update([
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
                'diverifikasi_oleh' => $request->user()->id,
                'diverifikasi_pada' => now(),
            ]);
        });

        $previousStatus = $pendaftaran->status;
        $pendaftaran = $workflow->syncAfterVerification($pendaftaran, $request->user());

        if ($pendaftaran->status !== $previousStatus) {
            $this->notifyStatusChange($pendaftaran, $previousStatus);
        }

        activity('pendaftaran')
            ->causedBy($request->user())
            ->performedOn($dokumen)
            ->withProperties(['status' => $validated['status']])
            ->log('Dokumen persyaratan diverifikasi.');

        return back()->with('success', 'Verifikasi dokumen berhasil disimpan.');
    }

    public function approve(
        Request $request,
        Pendaftaran $pendaftaran,
        ApprovePendaftaran $approvePendaftaran,
    ): RedirectResponse {
        $this->authorize('approve', $pendaftaran);

        $validated = $request->validate([
            'jadwal_id' => [
                'required',
                'uuid',
                Rule::exists('jadwal_asesmens', 'id')->where('institution_id', $pendaftaran->institution_id),
            ],
        ]);

        $jadwal = JadwalAsesmen::findOrFail($validated['jadwal_id']);

        try {
            $approvePendaftaran->execute($pendaftaran, $jadwal, $request->user());
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.pendaftaran.show', $pendaftaran)
            ->with('success', 'Pendaftaran disetujui dan asesi telah diplotting ke jadwal.');
    }

    public function reject(
        Request $request,
        Pendaftaran $pendaftaran,
        NotificationOutbox $outbox,
    ): RedirectResponse {
        $this->authorize('reject', $pendaftaran);

        $validated = $request->validate([
            'catatan_penolakan' => ['required', 'string', 'max:1000'],
        ]);

        $pendaftaran->update([
            'status' => Pendaftaran::STATUS_DITOLAK,
            'catatan_penolakan' => $validated['catatan_penolakan'],
        ]);

        activity('pendaftaran')
            ->causedBy($request->user())
            ->performedOn($pendaftaran)
            ->withProperties(['catatan' => $validated['catatan_penolakan']])
            ->log('Pendaftaran ditolak.');

        $outbox->queue(
            $pendaftaran->asesi->user,
            'pendaftaran_ditolak',
            'Pendaftaran Ditolak',
            sprintf(
                'Pendaftaran %s ditolak. Catatan admin: %s',
                $pendaftaran->no_pendaftaran,
                $validated['catatan_penolakan'],
            ),
            $pendaftaran,
        );

        return redirect()
            ->route('admin.pendaftaran.show', $pendaftaran)
            ->with('success', 'Pendaftaran berhasil ditolak.');
    }

    public function previewDokumen(PendaftaranDokumen $dokumen, PendaftaranStorage $storage): StreamedResponse
    {
        $this->authorize('view', $dokumen);

        abort_unless($storage->exists($dokumen->file_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk(PendaftaranStorage::DISK)->response(
            $dokumen->file_path,
            $dokumen->persyaratan->nama_dokumen,
            ['Content-Type' => $dokumen->file_mime],
        );
    }

    public function downloadDokumen(PendaftaranDokumen $dokumen, PendaftaranStorage $storage): StreamedResponse
    {
        $this->authorize('download', $dokumen);

        abort_unless($storage->exists($dokumen->file_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk(PendaftaranStorage::DISK)->download(
            $dokumen->file_path,
            $dokumen->persyaratan->nama_dokumen.'.'.pathinfo($dokumen->file_path, PATHINFO_EXTENSION),
        );
    }

    public function apl01(Pendaftaran $pendaftaran): Response
    {
        $this->authorize('view', $pendaftaran);

        $pendaftaran->load([
            'asesi.user',
            'asesi.province',
            'asesi.city',
            'skema',
            'jadwal.tuk',
        ]);

        activity('pendaftaran')
            ->causedBy(request()->user())
            ->performedOn($pendaftaran)
            ->log('APL-01 dicetak sebagai PDF.');

        return Pdf::loadView('pdf.apl01', [
            'pendaftaran' => $pendaftaran,
            'institution' => TenantContext::institution(),
        ])
            ->setPaper('a4', 'portrait')
            ->stream(sprintf('FORM-APL-01-%s-%s.pdf', str($pendaftaran->skema->kode_skema)->slug(), str($pendaftaran->asesi->user->name ?? 'asesi')->slug()));
    }

    public function mak01(Pendaftaran $pendaftaran): Response
    {
        $this->authorize('view', $pendaftaran);

        $pendaftaran->load([
            'asesi.user',
            'asesi.province',
            'asesi.city',
            'skema.persyaratans',
            'dokumens.persyaratan',
            'dokumens.verifikator',
        ]);

        activity('pendaftaran')
            ->causedBy(request()->user())
            ->performedOn($pendaftaran)
            ->log('MAK-01 dicetak sebagai PDF.');

        return Pdf::loadView('pdf.mak01', [
            'pendaftaran' => $pendaftaran,
            'institution' => TenantContext::institution(),
        ])
            ->setPaper('a4', 'portrait')
            ->stream(sprintf('FORM-MAK-01-%s-%s.pdf', str($pendaftaran->skema->kode_skema)->slug(), str($pendaftaran->asesi->user->name ?? 'asesi')->slug()));
    }

    public function mak03(Pendaftaran $pendaftaran): Response
    {
        $this->authorize('view', $pendaftaran);

        $pendaftaran->load([
            'asesi.user',
            'asesi.province',
            'asesi.city',
            'skema',
            'jadwal.tuk',
            'jadwal.asesors.asesor.user',
        ]);

        $hasil = HasilAsesmen::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->with(['asesor.user', 'units'])
            ->latest()
            ->first();

        activity('pendaftaran')
            ->causedBy(request()->user())
            ->performedOn($pendaftaran)
            ->log('MAK-03 dicetak sebagai PDF.');

        return Pdf::loadView('pdf.mak03', [
            'pendaftaran' => $pendaftaran,
            'hasil' => $hasil,
            'institution' => TenantContext::institution(),
        ])
            ->setPaper('a4', 'portrait')
            ->stream(sprintf('FORM-MAK-03-%s-%s.pdf', str($pendaftaran->skema->kode_skema)->slug(), str($pendaftaran->asesi->user->name ?? 'asesi')->slug()));
    }

    private function notifyStatusChange(Pendaftaran $pendaftaran, string $previousStatus): void
    {
        $outbox = app(NotificationOutbox::class);

        if ($pendaftaran->status === Pendaftaran::STATUS_DOKUMEN_DITOLAK) {
            $outbox->queue(
                $pendaftaran->asesi->user,
                'dokumen_ditolak',
                'Dokumen Pendaftaran Ditolak',
                sprintf('Dokumen pendaftaran %s ditolak. Silakan perbaiki dokumen yang ditandai.', $pendaftaran->no_pendaftaran),
                $pendaftaran,
            );

            return;
        }

        if ($pendaftaran->status === Pendaftaran::STATUS_MENUNGGU_JADWAL) {
            $outbox->queue(
                $pendaftaran->asesi->user,
                'dokumen_disetujui',
                'Dokumen Pendaftaran Disetujui',
                sprintf('Seluruh dokumen pendaftaran %s telah disetujui. Menunggu penjadwalan oleh admin.', $pendaftaran->no_pendaftaran),
                $pendaftaran,
            );
        }
    }
}
