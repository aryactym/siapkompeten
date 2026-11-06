<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\PersetujuanAsesmen;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PersetujuanAsesmenController extends Controller
{
    public function show(Request $request, Pendaftaran $pendaftaran): View|RedirectResponse
    {
        $this->authorize('view', $pendaftaran);

        if (! $request->user()->hasRole('asesi') || ! $pendaftaran->asesi()->where('user_id', $request->user()->id)->exists()) {
            abort(403);
        }

        $this->ensureRecords($pendaftaran);

        $pendaftaran->load(['skema', 'jadwal.tuk', 'persetujuanAsesmens']);

        $data = [];
        foreach (PersetujuanAsesmen::requiredJenis() as $jenis) {
            $p = $pendaftaran->persetujuanAsesmens->where('jenis', $jenis)->first();
            $data[$jenis] = $p?->data_json ?? [];
        }

        // Prefill skema/jadwal info from pendaftaran
        $skema = $pendaftaran->skema;
        $jadwal = $pendaftaran->jadwal;
        $asesiUser = $request->user();

        return view('asesi.persetujuan-asesmen.show', [
            'pendaftaran' => $pendaftaran,
            'persetujuans' => $pendaftaran->persetujuanAsesmens->keyBy('jenis'),
            'data' => $data,
            'skema' => $skema,
            'jadwal' => $jadwal,
            'asesiUser' => $asesiUser,
        ]);
    }

    public function approve(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('view', $pendaftaran);

        if (! $request->user()->hasRole('asesi') || ! $pendaftaran->asesi()->where('user_id', $request->user()->id)->exists()) {
            abort(403);
        }

        if (! in_array($pendaftaran->status, [Pendaftaran::STATUS_TERJADWAL, Pendaftaran::STATUS_ASESMEN_BERJALAN], true)) {
            return redirect()
                ->route('asesi.pre-asesmen.show', $pendaftaran)
                ->with('error', 'Dokumen pre-asesmen hanya dapat disetujui setelah pendaftaran terjadwal.');
        }

        $this->ensureRecords($pendaftaran);

        $validated = $request->validate([
            'tanggal' => ['nullable', 'date'],
            'setuju_ak01' => ['sometimes', 'accepted'],
            'setuju_ak04' => ['sometimes', 'accepted'],
            'setuju_ak07' => ['sometimes', 'accepted'],
        ]);

        $approved = 0;
        foreach (PersetujuanAsesmen::requiredJenis() as $jenis) {
            $field = 'setuju_'.$jenis;
            if (! ($validated[$field] ?? false)) {
                continue;
            }

            $persetujuan = $pendaftaran->persetujuanAsesmens()->where('jenis', $jenis)->first();

            $persetujuan->update([
                'data_json' => array_merge(
                    $persetujuan->data_json ?? [],
                    $request->except(['_token', 'setuju_ak01', 'setuju_ak04', 'setuju_ak07', 'tanggal']),
                    ['disetujui_tanggal' => $validated['tanggal']],
                ),
                'status' => PersetujuanAsesmen::STATUS_DISETUJUI,
                'disetujui_pada' => now(),
                'disetujui_oleh' => $request->user()->id,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000),
                'dokumen_hash' => hash('sha256', $pendaftaran->id.'|'.$jenis.'|'.now()->timestamp),
            ]);

            $approved++;
        }

        if ($approved === 0) {
            return redirect()
                ->route('asesi.pre-asesmen.show', $pendaftaran)
                ->with('error', 'Pilih minimal satu formulir yang akan disetujui.');
        }

        activity('pendaftaran')
            ->causedBy($request->user())
            ->performedOn($pendaftaran)
            ->log("Asesi menyetujui {$approved} dokumen pre-asesmen.");

        return redirect()
            ->route('asesi.pre-asesmen.show', $pendaftaran)
            ->with('success', "$approved dokumen pre-asesmen disetujui.");
    }

    private function ensureRecords(Pendaftaran $pendaftaran): void
    {
        foreach (PersetujuanAsesmen::requiredJenis() as $jenis) {
            PersetujuanAsesmen::firstOrCreate(
                [
                    'pendaftaran_id' => $pendaftaran->id,
                    'jenis' => $jenis,
                ],
                [
                    'institution_id' => $pendaftaran->institution_id,
                    'status' => PersetujuanAsesmen::STATUS_MENUNGGU,
                ],
            );
        }
    }

    public function pdfGabungan(Request $request, Pendaftaran $pendaftaran)
    {
        $this->authorize('view', $pendaftaran);

        if (! $request->user()->hasRole('asesi') || ! $pendaftaran->asesi()->where('user_id', $request->user()->id)->exists()) {
            abort(403);
        }

        $pendaftaran->load(['skema', 'jadwal.tuk', 'asesi.user', 'persetujuanAsesmens']);

        $data = [];
        foreach (PersetujuanAsesmen::requiredJenis() as $jenis) {
            $p = $pendaftaran->persetujuanAsesmens->where('jenis', $jenis)->first();
            $data[$jenis] = $p?->data_json ?? [];
        }

        $persetujuan = $pendaftaran->persetujuanAsesmens->first();

        return Pdf::loadView('pdf.pre-asesmen-gabungan', [
            'pendaftaran' => $pendaftaran,
            'data' => $data['ak07'] ?? [],
            'persetujuan' => $persetujuan,
            'skema' => $pendaftaran->skema,
            'jadwal' => $pendaftaran->jadwal,
            'asesiUser' => $pendaftaran->asesi->user,
        ])->setPaper('a4', 'portrait')
            ->stream(sprintf('PRE-ASESMEN-%s.pdf', $pendaftaran->no_pendaftaran));
    }
}
