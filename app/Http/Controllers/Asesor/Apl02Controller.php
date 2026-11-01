<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\Apl02Penilaian;
use App\Models\Pendaftaran;
use App\Services\AsesmenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class Apl02Controller extends Controller
{
    public function __construct(private readonly AsesmenService $asesmen) {}

    /**
     * Verifikasi APL-02 oleh asesor (FR-ASM-02..04).
     */
    public function show(Pendaftaran $pendaftaran): View
    {
        $this->authorize('verifyApl02', $pendaftaran);

        $penilaian = $this->asesmen->ensureApl02($pendaftaran);
        $penilaian->load(['jawabans', 'asesor.user']);

        return view('asesor.apl02.show', [
            'pendaftaran' => $pendaftaran,
            'penilaian' => $penilaian,
            'units' => $this->asesmen->unitItems($pendaftaran->skema),
            'jawabans' => $penilaian->jawabans->keyBy('kuk_id'),
            'dapatDiverifikasi' => $penilaian->status === Apl02Penilaian::STATUS_DISERAHKAN,
        ]);
    }

    /**
     * Simpan hasil verifikasi asesor dan setujui APL-02.
     */
    public function verifikasi(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $this->authorize('verifyApl02', $pendaftaran);

        $penilaian = $this->asesmen->ensureApl02($pendaftaran);

        abort_unless($penilaian->status === Apl02Penilaian::STATUS_DISERAHKAN, 403, 'APL-02 belum diserahkan asesi.');

        $validated = $request->validate([
            'bukti' => ['nullable', 'array'],
            'bukti.*' => ['nullable', 'string', 'max:1000'],
            'verifikasi' => ['required', 'array'],
            'verifikasi.*' => ['array'],
            'verifikasi.*.valid' => ['nullable', 'boolean'],
            'verifikasi.*.autentik' => ['nullable', 'boolean'],
            'verifikasi.*.terkini' => ['nullable', 'boolean'],
            'verifikasi.*.memadai' => ['nullable', 'boolean'],
        ]);

        $asesor = $request->user()->asesor ?? null;
        abort_if($asesor === null, 403, 'Akun tidak terdaftar sebagai asesor.');

        $verifikasi = [];

        foreach ($validated['verifikasi'] as $kukId => $flags) {
            $verifikasi[$kukId] = [
                'bukti' => $validated['bukti'][$kukId] ?? null,
                'valid' => ! empty($flags['valid']),
                'autentik' => ! empty($flags['autentik']),
                'terkini' => ! empty($flags['terkini']),
                'memadai' => ! empty($flags['memadai']),
            ];
        }

        $this->asesmen->verifikasiApl02($penilaian, $verifikasi, $asesor->id, $pendaftaran);

        activity('apl02')
            ->causedBy($request->user())
            ->performedOn($pendaftaran)
            ->log('Asesor memverifikasi APL-02 asesi.');

        return redirect()
            ->route('asesor.apl02.show', $pendaftaran)
            ->with('success', 'APL-02 berhasil diverifikasi.');
    }
}
