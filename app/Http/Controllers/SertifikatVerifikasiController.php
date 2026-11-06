<?php

namespace App\Http\Controllers;

use App\Models\Sertifikat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Verifikasi keaslian sertifikat publik via nomor/QR (FR-CMS-06).
 */
class SertifikatVerifikasiController extends Controller
{
    public function form(): View
    {
        return view('publik.verifikasi-form');
    }

    public function lookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nomor' => ['required', 'string', 'max:100'],
        ]);

        $nomor = trim($validated['nomor']);

        $sertifikat = Sertifikat::withoutTenantScope()
            ->where(function ($query) use ($nomor): void {
                $query->where('qr_token', $nomor)
                    ->orWhere('no_registrasi', $nomor)
                    ->orWhere('no_seri_blangko', $nomor);
            })
            ->first();

        if (! $sertifikat) {
            return back()
                ->withInput()
                ->with('error', 'Sertifikat dengan nomor tersebut tidak ditemukan.');
        }

        return redirect()->route('sertifikat.verifikasi', $sertifikat->qr_token);
    }

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
