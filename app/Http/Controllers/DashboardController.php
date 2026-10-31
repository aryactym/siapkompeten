<?php

namespace App\Http\Controllers;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\InstitutionDocument;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\Skema;
use App\Models\Skkni;
use App\Models\Tuk;
use App\Services\SystemHealthService;
use App\Support\ActivePortal;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, SystemHealthService $healthService): View
    {
        $user = $request->user();

        $portal = $user !== null
            ? ActivePortal::resolve($request, $user)
            : ActivePortal::PORTAL_ASESI;

        // Pastikan session sinkron dengan portal yang dipilih.
        if ($user !== null) {
            ActivePortal::set($request, $portal);
        }

        if ($portal === ActivePortal::PORTAL_ASESOR && $user?->hasRole('asesor')) {
            return $this->asesorDashboard($user);
        }

        if ($portal === ActivePortal::PORTAL_ASESI && $user?->hasRole('asesi')) {
            return $this->asesiDashboard($user);
        }

        return $this->adminDashboard($user, $healthService);
    }

    /**
     * Dashboard admin/pimpinan.
     */
    private function adminDashboard(mixed $user, SystemHealthService $healthService): View
    {
        // --- Statistik kartu ---
        $totalSkema = Skema::count();
        $skemaNonaktif = Skema::where('is_active', false)->count();
        $totalAsesi = Asesi::count();
        $asesBelumVerif = Asesi::where('status_verifikasi', 'belum_terverifikasi')->count();
        $jadwalMendatang = JadwalAsesmen::where('tanggal', '>=', now()->toDateString())->count();
        $jadwalMendatang7Hari = JadwalAsesmen::whereBetween('tanggal', [
            now()->toDateString(),
            now()->addDays(7)->toDateString(),
        ])->count();
        $totalAsesor = Asesor::count();
        $asesorAktif = Asesor::where('status', 'aktif')->count();

        $stats = [
            'total_skema' => $totalSkema,
            'skema_nonaktif' => $skemaNonaktif,
            'total_asesi' => $totalAsesi,
            'asesi_belum_verif' => $asesBelumVerif,
            'jadwal_mendatang' => $jadwalMendatang,
            'jadwal_mendatang_7hari' => $jadwalMendatang7Hari,
            'total_asesor' => $totalAsesor,
            'asesor_aktif' => $asesorAktif,
        ];

        // --- Antrean tindakan ---
        $actionQueue = [
            [
                'label' => 'Dokumen menunggu verifikasi',
                'count' => Pendaftaran::where('status', Pendaftaran::STATUS_DOKUMEN_DIAJUKAN)->count(),
                'href' => route('admin.pendaftaran.index', ['tab' => 'perlu_verifikasi']),
                'color' => 'orange',
            ],
            [
                'label' => 'Pembayaran menunggu validasi',
                'count' => Pendaftaran::where('status', Pendaftaran::STATUS_PEMBAYARAN_DIAJUKAN)->count(),
                'href' => route('admin.pembayaran.index'),
                'color' => 'yellow',
            ],
            [
                'label' => 'Siap plotting jadwal',
                'count' => Pendaftaran::where('status', Pendaftaran::STATUS_MENUNGGU_JADWAL)->count(),
                'href' => route('admin.pendaftaran.index', ['tab' => 'siap_plotting']),
                'color' => 'blue',
            ],
        ];

        // --- Jadwal 7 hari ke depan ---
        $jadwal7HariRaw = JadwalAsesmen::query()
            ->with(['skema', 'tuk'])
            ->whereBetween('tanggal', [
                now()->toDateString(),
                now()->addDays(7)->toDateString(),
            ])
            ->orderBy('tanggal')
            ->limit(8)
            ->get();

        $jadwal7Hari = $jadwal7HariRaw->map(fn (JadwalAsesmen $j) => [
            'label' => ($j->skema?->nama ?? $j->skema?->kode ?? '—').' · '.($j->tuk?->nama ?? '—'),
            'meta' => $j->tanggal instanceof Carbon
                ? $j->tanggal->translatedFormat('d M Y')
                : $j->tanggal,
            'href' => route('admin.jadwal.show', $j),
        ])->all();

        // --- Kesehatan sistem ---
        $systemHealth = $healthService->checks();

        // --- Dokumen lembaga (ringkas) ---
        $documents = InstitutionDocument::query()
            ->with('uploader')
            ->latest()
            ->get();

        // --- Onboarding checklist ---
        $onboardingSteps = $this->buildOnboardingSteps();

        return view('dashboard.admin', [
            'user' => $user,
            'stats' => $stats,
            'actionQueue' => $actionQueue,
            'jadwal7Hari' => $jadwal7Hari,
            'systemHealth' => $systemHealth,
            'documents' => $documents,
            'onboardingSteps' => $onboardingSteps,
            'institutionId' => TenantContext::id(),
        ]);
    }

    /**
     * Langkah onboarding dihitung dari database, bukan flag manual.
     *
     * @return array<int, array{label: string, done: bool, href: string, description: string}>
     */
    private function buildOnboardingSteps(): array
    {
        return [
            [
                'label' => 'Profil Lembaga',
                'done' => TenantContext::institution()?->name !== null
                    && TenantContext::institution()?->nomor_lisensi !== null,
                'href' => route('admin.institution.index'),
                'description' => 'Lengkapi nama, nomor lisensi BNSP, dan kontak lembaga.',
            ],
            [
                'label' => 'SKKNI',
                'done' => Skkni::exists(),
                'href' => route('admin.stkkni.index'),
                'description' => 'Tambahkan minimal satu Standar Kompetensi Kerja.',
            ],
            [
                'label' => 'Skema Sertifikasi',
                'done' => Skema::where('is_active', true)->exists(),
                'href' => route('admin.skema.index'),
                'description' => 'Buat skema aktif lengkap dengan persyaratan dan unit kompetensi.',
            ],
            [
                'label' => 'Tempat Uji Kompetensi (TUK)',
                'done' => Tuk::exists(),
                'href' => route('admin.tuk.index'),
                'description' => 'Daftarkan minimal satu TUK.',
            ],
            [
                'label' => 'Asesor',
                'done' => Asesor::where('status', 'aktif')->exists(),
                'href' => route('admin.asesor.index'),
                'description' => 'Tambahkan asesor aktif yang siap ditugaskan.',
            ],
            [
                'label' => 'Jadwal Uji',
                'done' => JadwalAsesmen::exists(),
                'href' => route('admin.jadwal.create'),
                'description' => 'Buat jadwal uji kompetensi pertama.',
            ],
        ];
    }

    /**
     * Dashboard asesor.
     */
    private function asesorDashboard(mixed $user): View
    {
        $asesor = $user->asesor;

        $jadwals = $asesor === null
            ? collect()
            : JadwalAsesmen::query()
                ->with(['event', 'skema', 'tuk'])
                ->withCount('pendaftarans')
                ->whereHas('asesors', fn ($q) => $q->whereKey($asesor->id))
                ->orderByDesc('tanggal')
                ->limit(5)
                ->get();

        $stats = [
            'total_jadwal' => $asesor === null
                ? 0
                : JadwalAsesmen::whereHas('asesors', fn ($q) => $q->whereKey($asesor->id))->count(),
            'asesi_dinilai' => $asesor === null
                ? 0
                : Pendaftaran::whereHas('jadwal.asesors', fn ($q) => $q->whereKey($asesor->id))->count(),
        ];

        return view('dashboard.asesor', [
            'user' => $user,
            'stats' => $stats,
            'jadwals' => $jadwals,
            'asesor' => $asesor,
            'institutionId' => TenantContext::id(),
        ]);
    }

    /**
     * Dashboard asesi.
     */
    private function asesiDashboard(mixed $user): View
    {
        $asesi = $user->asesi;

        $pendaftarans = $asesi === null
            ? collect()
            : $asesi->pendaftarans()
                ->with(['skema.persyaratans', 'dokumens', 'jadwal.tuk'])
                ->latest()
                ->get();

        $activePendaftaran = $pendaftarans->first(
            fn (Pendaftaran $p): bool => ! in_array(
                $p->status,
                [Pendaftaran::STATUS_DITOLAK, Pendaftaran::STATUS_DIBATALKAN, Pendaftaran::STATUS_SELESAI],
                true,
            )
        ) ?? $pendaftarans->first();

        return view('dashboard.asesi', [
            'user' => $user,
            'stats' => [],
            'asesi' => $asesi,
            'pendaftarans' => $pendaftarans,
            'activePendaftaran' => $activePendaftaran,
            'institutionId' => TenantContext::id(),
        ]);
    }

    /**
     * Pindah lembaga aktif (tenant switcher).
     */
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

        // Regenerate session state setelah pindah tenant
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Lembaga aktif diubah ke '.$institution->name.'.');
    }
}
