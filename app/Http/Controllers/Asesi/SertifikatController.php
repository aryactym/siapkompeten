<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\Asesi;
use App\Models\Sertifikat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Portal asesi: hasil & sertifikat (FR-RPT-04).
 */
class SertifikatController extends Controller
{
    public function index(Request $request): View
    {
        $asesi = Asesi::with('user')->where('user_id', $request->user()->id)->firstOrFail();

        $sertifikats = Sertifikat::query()
            ->with(['skema', 'asesi.user'])
            ->where('asesi_id', $asesi->id)
            ->where('status', Sertifikat::STATUS_AKTIF)
            ->latest('tanggal_terbit')
            ->get();

        return view('asesi.sertifikat.index', [
            'sertifikats' => $sertifikats,
        ]);
    }

    public function show(Request $request, Sertifikat $sertifikat): View
    {
        $this->authorize('view', $sertifikat);

        $sertifikat->load(['skema', 'pendaftaran.jadwal.tuk']);

        return view('asesi.sertifikat.show', [
            'sertifikat' => $sertifikat,
        ]);
    }

    public function pdf(Request $request, Sertifikat $sertifikat): Response
    {
        $this->authorize('view', $sertifikat);

        $sertifikat->load([
            'asesi.user',
            'skema',
            'pendaftaran.jadwal.tuk',
            'pendaftaran.hasilAsesmen.asesor.user',
            'penerbit',
        ]);

        return Pdf::loadView('pdf.sertifikat', [
            'sertifikat' => $sertifikat,
            'institution' => $sertifikat->institution,
        ])
            ->setPaper('a4', 'landscape')
            ->stream(sprintf('sertifikat-%s.pdf', $sertifikat->no_registrasi));
    }
}
