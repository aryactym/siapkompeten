<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apl02Penilaian;
use App\Models\Asesi;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\Skema;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPelaporanController extends Controller
{
    public function index(Request $request): View
    {
        $dari = $request->string('dari')->toString() ?: now()->subYear()->toDateString();
        $sampai = $request->string('sampai')->toString() ?: now()->toDateString();

        $pendaftarans = Pendaftaran::query()
            ->with(['asesi.user', 'skema', 'jadwal.tuk'])
            ->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $rekapStatus = Pendaftaran::query()
            ->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.pelaporan.index', [
            'pendaftarans' => $pendaftarans,
            'dari' => $dari,
            'sampai' => $sampai,
            'rekap' => [
                'asesi' => Asesi::query()->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])->count(),
                'pendaftaran' => Pendaftaran::query()->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])->count(),
                'jadwal' => JadwalAsesmen::query()->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])->count(),
                'skema' => Skema::query()->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])->count(),
                'rekapStatus' => $rekapStatus,
            ],
        ]);
    }

    public function apl02(Request $request): View
    {
        $dari = $request->string('dari')->toString() ?: now()->subYear()->toDateString();
        $sampai = $request->string('sampai')->toString() ?: now()->toDateString();

        $penilaians = Apl02Penilaian::query()
            ->with(['pendaftaran.asesi.user', 'pendaftaran.skema', 'asesor.user'])
            ->whereHas('pendaftaran', fn ($q) => $q->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59']))
            ->latest('diserahkan_pada')
            ->paginate(20)
            ->withQueryString();

        $rekap = Apl02Penilaian::query()
            ->whereHas('pendaftaran', fn ($q) => $q->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59']))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.pelaporan.apl02', [
            'penilaians' => $penilaians,
            'dari' => $dari,
            'sampai' => $sampai,
            'rekap' => $rekap,
        ]);
    }
}
