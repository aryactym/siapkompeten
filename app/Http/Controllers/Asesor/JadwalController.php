<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\JadwalAsesmen;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->input('view') === 'calendar') {
            return $this->calendar($request);
        }

        $asesor = $request->user()->asesor;

        $jadwals = JadwalAsesmen::query()
            ->with(['event', 'skema', 'tuk'])
            ->withCount('pendaftarans')
            ->when(
                $asesor === null,
                fn ($query) => $query->whereRaw('1 = 0'),
                fn ($query) => $query->whereHas('asesors', fn ($asesorQuery) => $asesorQuery->whereKey($asesor->id)),
            )
            ->orderByDesc('tanggal')
            ->orderBy('jam_mulai')
            ->paginate(15);

        return view('asesor.jadwal.index', [
            'jadwals' => $jadwals,
            'asesor' => $asesor,
        ]);
    }

    public function calendar(Request $request): View
    {
        $asesor = $request->user()->asesor;

        $monthParam = (string) $request->query('month', now()->format('Y-m'));

        try {
            $month = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } catch (\Throwable $exception) {
            $month = now()->startOfMonth();
        }

        $jadwals = JadwalAsesmen::query()
            ->with(['event', 'skema', 'tuk'])
            ->withCount('pendaftarans')
            ->when(
                $asesor === null,
                fn ($query) => $query->whereRaw('1 = 0'),
                fn ($query) => $query->whereHas('asesors', fn ($asesorQuery) => $asesorQuery->whereKey($asesor->id)),
            )
            ->whereBetween('tanggal', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        return view('asesor.jadwal.calendar', [
            'jadwals' => $jadwals,
            'asesor' => $asesor,
            'month' => $month,
        ]);
    }

    public function peserta(JadwalAsesmen $jadwal): View
    {
        $this->authorize('viewPeserta', $jadwal);

        $jadwal->load([
            'event',
            'skema.persyaratans',
            'tuk',
            'asesors.user',
            'pendaftarans' => fn ($query) => $query->orderBy('created_at'),
            'pendaftarans.asesi.user',
            'pendaftarans.asesi.province',
            'pendaftarans.dokumens',
            'pendaftarans.persetujuanAsesmens',
            'pendaftarans.presensi',
            'pendaftarans.rencanaAsesmen',
        ]);

        return view('asesor.jadwal.peserta', [
            'jadwal' => $jadwal,
        ]);
    }
}
