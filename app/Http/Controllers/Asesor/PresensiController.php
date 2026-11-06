<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\PresensiAsesi;
use App\Services\PresensiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PresensiController extends Controller
{
    public function catat(Request $request, Pendaftaran $pendaftaran, PresensiService $presensi): RedirectResponse
    {
        $this->authorize('catatPresensi', $pendaftaran);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(PresensiAsesi::STATUS_LABELS))],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        if ($pendaftaran->presensi?->isLocked()) {
            return back()->with('error', 'Presensi sudah terkunci karena asesmen telah selesai.');
        }

        if ($pendaftaran->status === Pendaftaran::STATUS_SELESAI) {
            return back()->with('error', 'Presensi tidak dapat diubah karena pendaftaran sudah selesai.');
        }

        $presensi->catat($pendaftaran, $validated['status'], $validated['catatan'] ?? null, $request->user());

        return back()->with('success', 'Presensi asesi disimpan.');
    }

    public function mulai(Request $request, Pendaftaran $pendaftaran, PresensiService $presensi): RedirectResponse
    {
        $this->authorize('mulaiAsesmen', $pendaftaran);

        try {
            $presensi->mulaiAsesmen($pendaftaran, $request->user());
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Asesmen dimulai.');
    }
}
