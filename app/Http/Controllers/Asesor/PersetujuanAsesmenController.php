<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\PersetujuanAsesmen;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersetujuanAsesmenController extends Controller
{
    public function show(Request $request, Pendaftaran $pendaftaran): View
    {
        $this->authorize('view', $pendaftaran);

        if (! $request->user()->hasRole('asesor') || $pendaftaran->jadwal_id === null
            || ! $pendaftaran->jadwal->asesors()->where('asesors.user_id', $request->user()->id)->exists()) {
            if (! $request->user()->hasAnyRole(['super-admin', 'admin'])) {
                abort(403);
            }
        }

        foreach (PersetujuanAsesmen::requiredJenis() as $jenis) {
            PersetujuanAsesmen::firstOrCreate(
                ['pendaftaran_id' => $pendaftaran->id, 'jenis' => $jenis],
                ['institution_id' => $pendaftaran->institution_id, 'status' => PersetujuanAsesmen::STATUS_MENUNGGU],
            );
        }

        $pendaftaran->load(['skema', 'jadwal.tuk', 'asesi.user', 'persetujuanAsesmens']);

        return view('asesor.persetujuan-asesmen.show', [
            'pendaftaran' => $pendaftaran,
            'persetujuans' => $pendaftaran->persetujuanAsesmens->keyBy('jenis'),
            'allApproved' => PersetujuanAsesmen::allApprovedFor($pendaftaran),
        ]);
    }

    public function pdf(Request $request, Pendaftaran $pendaftaran, string $jenis)
    {
        $this->authorize('view', $pendaftaran);

        if (! in_array($jenis, PersetujuanAsesmen::requiredJenis(), true)) {
            abort(404);
        }

        if (! $request->user()->hasRole('asesor') || $pendaftaran->jadwal_id === null
            || ! $pendaftaran->jadwal->asesors()->where('asesors.user_id', $request->user()->id)->exists()) {
            if (! $request->user()->hasAnyRole(['super-admin', 'admin'])) {
                abort(403);
            }
        }

        $pendaftaran->load(['skema', 'jadwal.tuk', 'asesi.user', 'persetujuanAsesmens']);
        $persetujuan = $pendaftaran->persetujuanAsesmens->where('jenis', $jenis)->first();

        $data = $persetujuan?->data_json ?? [];

        return Pdf::loadView('pdf.frak'.$jenis, [
            'pendaftaran' => $pendaftaran,
            'data' => $data,
            'persetujuan' => $persetujuan,
            'skema' => $pendaftaran->skema,
            'jadwal' => $pendaftaran->jadwal,
            'asesiUser' => $pendaftaran->asesi->user,
        ])->setPaper('a4', 'portrait')
            ->stream(sprintf('FRAK%s-%s.pdf', strtoupper($jenis), $pendaftaran->no_pendaftaran));
    }

    public function pdfGabungan(Request $request, Pendaftaran $pendaftaran)
    {
        $this->authorize('view', $pendaftaran);

        if (! $request->user()->hasRole('asesor') || $pendaftaran->jadwal_id === null
            || ! $pendaftaran->jadwal->asesors()->where('asesors.user_id', $request->user()->id)->exists()) {
            if (! $request->user()->hasAnyRole(['super-admin', 'admin'])) {
                abort(403);
            }
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
