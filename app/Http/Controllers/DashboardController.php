<?php

namespace App\Http\Controllers;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\InstitutionDocument;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\Skema;
use App\Models\Tuk;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $documents = InstitutionDocument::query()
            ->with('uploader')
            ->latest()
            ->paginate(10);

        $stats = [
            'total_skema' => Skema::count(),
            'total_asesi' => Asesi::count(),
            'total_jadwal' => JadwalAsesmen::count(),
            'total_asesor' => Asesor::aktif()->count(),
            'total_tuk' => Tuk::count(),
            'pending_apl02' => 0,
            'asesi_dinilai' => 0,
            'laporan_mak02' => 0,
            'perlu_verifikasi' => Pendaftaran::where('status', Pendaftaran::STATUS_DOKUMEN_DIAJUKAN)->count(),
            'siap_plotting' => Pendaftaran::where('status', Pendaftaran::STATUS_MENUNGGU_JADWAL)->count(),
        ];

        if ($user && $user->hasRole('asesor')) {
            $asesor = $user->asesor;

            $jadwals = $asesor === null
                ? collect()
                : JadwalAsesmen::query()
                    ->with(['event', 'skema', 'tuk'])
                    ->withCount('pendaftarans')
                    ->whereHas('asesors', fn ($query) => $query->whereKey($asesor->id))
                    ->orderByDesc('tanggal')
                    ->limit(5)
                    ->get();

            $stats['total_jadwal'] = $asesor === null
                ? 0
                : JadwalAsesmen::whereHas('asesors', fn ($query) => $query->whereKey($asesor->id))->count();

            $stats['asesi_dinilai'] = $asesor === null
                ? 0
                : Pendaftaran::whereHas('jadwal.asesors', fn ($query) => $query->whereKey($asesor->id))->count();

            return view('dashboard.asesor', [
                'user' => $user,
                'stats' => $stats,
                'jadwals' => $jadwals,
                'asesor' => $asesor,
                'institutionId' => TenantContext::id(),
            ]);
        }

        if ($user && $user->hasRole('asesi')) {
            $asesi = $user->asesi;

            $pendaftarans = $asesi === null
                ? collect()
                : $asesi->pendaftarans()
                    ->with(['skema.persyaratans', 'dokumens', 'jadwal.tuk'])
                    ->latest()
                    ->get();

            $activePendaftaran = $pendaftarans->first(
                fn (Pendaftaran $pendaftaran): bool => ! in_array(
                    $pendaftaran->status,
                    [Pendaftaran::STATUS_DITOLAK, Pendaftaran::STATUS_DIBATALKAN, Pendaftaran::STATUS_SELESAI],
                    true,
                )
            ) ?? $pendaftarans->first();

            return view('dashboard.asesi', [
                'user' => $user,
                'stats' => $stats,
                'asesi' => $asesi,
                'pendaftarans' => $pendaftarans,
                'activePendaftaran' => $activePendaftaran,
                'institutionId' => TenantContext::id(),
            ]);
        }

        return view('dashboard.admin', [
            'user' => $user,
            'documents' => $documents,
            'stats' => $stats,
            'institutionId' => TenantContext::id(),
        ]);
    }

    public function switchInstitution(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institution_id' => ['required', 'uuid'],
        ]);

        $institution = $request->user()
            ->accessibleInstitutions()
            ->firstWhere('id', $validated['institution_id']);

        abort_if($institution === null, 403, 'Anda tidak memiliki akses ke lembaga tersebut.');

        TenantContext::set($institution);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Lembaga aktif diubah ke '.$institution->name.'.');
    }
}
