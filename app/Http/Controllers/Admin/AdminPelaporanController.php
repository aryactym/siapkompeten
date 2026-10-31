<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PendaftaranExport;
use App\Http\Controllers\Controller;
use App\Models\Apl02Penilaian;
use App\Models\Asesi;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\Skema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminPelaporanController extends Controller
{
    /** Kunci filter yang diizinkan dikirim dari form. */
    private const FILTER_KEYS = [
        'dari', 'sampai', 'no_pendaftaran',
        'nama_asesi', 'nama_asesor', 'skema_id', 'status',
    ];

    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);

        $pendaftarans = $this->buildQuery($filters)
            ->paginate(20)
            ->withQueryString();

        [$dari, $sampai] = [$filters['dari'], $filters['sampai']];

        $rekapStatus = Pendaftaran::query()
            ->when($dari !== '', fn ($q) => $q->where('created_at', '>=', $dari.' 00:00:00'))
            ->when($sampai !== '', fn ($q) => $q->where('created_at', '<=', $sampai.' 23:59:59'))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $skemas = Skema::query()->aktif()->orderBy('nama_skema')->get(['id', 'nama_skema', 'kode_skema']);

        $statuses = [
            Pendaftaran::STATUS_DRAF,
            Pendaftaran::STATUS_DOKUMEN_DIAJUKAN,
            Pendaftaran::STATUS_DOKUMEN_DITOLAK,
            Pendaftaran::STATUS_MENUNGGU_PEMBAYARAN,
            Pendaftaran::STATUS_PEMBAYARAN_DIAJUKAN,
            Pendaftaran::STATUS_MENUNGGU_JADWAL,
            Pendaftaran::STATUS_TERJADWAL,
            Pendaftaran::STATUS_ASESMEN_BERJALAN,
            Pendaftaran::STATUS_SELESAI,
            Pendaftaran::STATUS_DITOLAK,
            Pendaftaran::STATUS_DIBATALKAN,
        ];

        return view('admin.pelaporan.index', [
            'pendaftarans' => $pendaftarans,
            'filters' => $filters,
            'skemas' => $skemas,
            'statuses' => $statuses,
            // Backward compat untuk stat cards
            'dari' => $dari,
            'sampai' => $sampai,
            'rekap' => [
                'asesi' => Asesi::query()
                    ->when($dari !== '', fn ($q) => $q->where('created_at', '>=', $dari.' 00:00:00'))
                    ->when($sampai !== '', fn ($q) => $q->where('created_at', '<=', $sampai.' 23:59:59'))
                    ->count(),
                'pendaftaran' => Pendaftaran::query()
                    ->when($dari !== '', fn ($q) => $q->where('created_at', '>=', $dari.' 00:00:00'))
                    ->when($sampai !== '', fn ($q) => $q->where('created_at', '<=', $sampai.' 23:59:59'))
                    ->count(),
                'jadwal' => JadwalAsesmen::query()
                    ->when($dari !== '', fn ($q) => $q->where('created_at', '>=', $dari.' 00:00:00'))
                    ->when($sampai !== '', fn ($q) => $q->where('created_at', '<=', $sampai.' 23:59:59'))
                    ->count(),
                'skema' => Skema::query()
                    ->when($dari !== '', fn ($q) => $q->where('created_at', '>=', $dari.' 00:00:00'))
                    ->when($sampai !== '', fn ($q) => $q->where('created_at', '<=', $sampai.' 23:59:59'))
                    ->count(),
                'rekapStatus' => $rekapStatus,
            ],
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', Pendaftaran::class);

        $filters = $this->extractFilters($request);

        $filename = sprintf(
            'pelaporan-pendaftaran-%s.xlsx',
            now()->format('Ymd-His'),
        );

        return Excel::download(new PendaftaranExport($filters), $filename);
    }

    public function apl02(Request $request): View
    {
        $dari = $request->string('dari')->toString() ?: now()->subYear()->toDateString();
        $sampai = $request->string('sampai')->toString() ?: now()->toDateString();

        $penilaians = Apl02Penilaian::query()
            ->with(['pendaftaran.asesi.user', 'pendaftaran.skema', 'asesor.user'])
            ->whereHas('pendaftaran', fn ($q) => $q
                ->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59']))
            ->latest('diserahkan_pada')
            ->paginate(20)
            ->withQueryString();

        $rekap = Apl02Penilaian::query()
            ->whereHas('pendaftaran', fn ($q) => $q
                ->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59']))
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

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Ekstrak dan normalisasi semua filter dari request.
     *
     * @return array{dari:string, sampai:string, no_pendaftaran:string, nama_asesi:string, nama_asesor:string, skema_id:string, status:string}
     */
    private function extractFilters(Request $request): array
    {
        return [
            'dari' => $request->string('dari')->toString() ?: now()->subYear()->toDateString(),
            'sampai' => $request->string('sampai')->toString() ?: now()->toDateString(),
            'no_pendaftaran' => $request->string('no_pendaftaran')->toString(),
            'nama_asesi' => $request->string('nama_asesi')->toString(),
            'nama_asesor' => $request->string('nama_asesor')->toString(),
            'skema_id' => $request->string('skema_id')->toString(),
            'status' => $request->string('status')->toString(),
        ];
    }

    /**
     * Bangun query Pendaftaran dengan eager-load dan semua filter yang aktif.
     */
    private function buildQuery(array $filters): Builder
    {
        $q = Pendaftaran::query()
            ->with(['asesi.user', 'skema', 'jadwal.tuk', 'jadwal.asesors.user'])
            ->withCount('dokumens');

        if ($filters['dari'] !== '') {
            $q->where('created_at', '>=', $filters['dari'].' 00:00:00');
        }

        if ($filters['sampai'] !== '') {
            $q->where('created_at', '<=', $filters['sampai'].' 23:59:59');
        }

        if (filled($filters['no_pendaftaran'])) {
            $q->where('no_pendaftaran', 'like', '%'.trim($filters['no_pendaftaran']).'%');
        }

        if (filled($filters['nama_asesi'])) {
            $q->whereHas('asesi.user', fn ($u) => $u->where('name', 'like', '%'.trim($filters['nama_asesi']).'%'));
        }

        if (filled($filters['nama_asesor'])) {
            $q->whereHas('jadwal.asesors.user', fn ($u) => $u->where('name', 'like', '%'.trim($filters['nama_asesor']).'%'));
        }

        if (filled($filters['skema_id'])) {
            $q->where('skema_id', $filters['skema_id']);
        }

        if (filled($filters['status'])) {
            $q->where('status', $filters['status']);
        }

        return $q->latest();
    }
}
