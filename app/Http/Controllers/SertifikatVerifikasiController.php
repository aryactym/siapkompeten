<?php

namespace App\Http\Controllers;

use App\Models\Sertifikat;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Verifikasi keaslian sertifikat publik via nomor/QR (FR-CMS-06).
 */
class SertifikatVerifikasiController extends Controller
{
    public function verify(Request $request, string $token): View
    {
        $sertifikat = Sertifikat::withoutTenantScope()
            ->where('qr_token', $token)
            ->with(['asesi.user', 'skema', 'institution'])
            ->first();

        return view('publik.verifikasi-sertifikat', [
            'sertifikat' => $sertifikat,
            'token' => $token,
        ]);
    }
}
