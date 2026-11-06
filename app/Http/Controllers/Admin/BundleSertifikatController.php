<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\Sertifikat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Penerbitan sertifikat massal (bundle) per jadwal asesmen.
 *
 * Admin memilih jadwal, lalu memasukkan nomor seri blangko BNSP untuk setiap
 * peserta yang dinyatakan kompeten dan belum memiliki sertifikat.
 */
class BundleSertifikatController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('create', Sertifikat::class);

        $jadwals = JadwalAsesmen::query()
            ->with(['skema', 'event'])
            ->withCount([
                'pendaftarans as kompeten_count' => fn ($q) => $q
                    ->where('status', Pendaftaran::STATUS_SELESAI)
                    ->whereHas('asesi', fn ($a) => $a->where('status_kompetensi', 'kompeten'))
                    ->whereDoesntHave('sertifikat'),
                'pendaftarans as total_selesai_count' => fn ($q) => $q
                    ->where('status', Pendaftaran::STATUS_SELESAI),
            ])
            ->where('status', 'selesai')
            ->orderByDesc('tanggal')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sertifikat.bundle-index', [
            'jadwals' => $jadwals,
        ]);
    }

    /**
     * Tampilkan form penerbitan bundle untuk satu jadwal.
     */
    public function create(JadwalAsesmen $jadwal): View
    {
        $this->authorize('create', Sertifikat::class);

        $jadwal->load(['skema', 'event', 'tuk']);

        // Ambil peserta kompeten yang belum punya sertifikat
        $pesertaLayak = Pendaftaran::query()
            ->with(['asesi.user', 'sertifikat'])
            ->where('jadwal_id', $jadwal->id)
            ->where('status', Pendaftaran::STATUS_SELESAI)
            ->whereHas('asesi', fn ($q) => $q->where('status_kompetensi', 'kompeten'))
            ->whereDoesntHave('sertifikat')
            ->get();

        // Peserta yang sudah punya sertifikat (untuk referensi)
        $pesertaSudahTerbit = Pendaftaran::query()
            ->with(['asesi.user', 'sertifikat'])
            ->where('jadwal_id', $jadwal->id)
            ->whereHas('sertifikat')
            ->get();

        return view('admin.sertifikat.bundle-create', [
            'jadwal' => $jadwal,
            'pesertaLayak' => $pesertaLayak,
            'pesertaSudahTerbit' => $pesertaSudahTerbit,
        ]);
    }

    /**
     * Proses penerbitan massal sertifikat.
     */
    public function store(Request $request, JadwalAsesmen $jadwal): RedirectResponse
    {
        $this->authorize('create', Sertifikat::class);

        $validated = $request->validate([
            'sertifikat' => ['required', 'array', 'min:1'],
            'sertifikat.*.pendaftaran_id' => ['required', 'uuid'],
            'sertifikat.*.no_seri_blangko' => [
                'required',
                'string',
                'max:100',
                'distinct',
                'regex:/^[A-Z0-9\-.]+\.[A-Z0-9\-.]+\.\d{5,}\.\d{4}$/',
            ],
        ], [
            'sertifikat.*.no_seri_blangko.required' => 'Nomor seri blangko wajib diisi.',
            'sertifikat.*.no_seri_blangko.regex' => 'Format nomor seri tidak sesuai standar BNSP (contoh: LSP.SKM.00001.2026).',
            'sertifikat.*.no_seri_blangko.distinct' => 'Nomor seri blangko tidak boleh duplikat dalam satu bundle.',
        ]);

        $institution = $jadwal->institution;
        $diterbitkanOleh = $request->user()->id;
        $berhasil = 0;
        $errors = [];

        foreach ($validated['sertifikat'] as $index => $item) {
            $pendaftaran = Pendaftaran::with(['asesi.user', 'skema'])
                ->where('jadwal_id', $jadwal->id)
                ->where('status', Pendaftaran::STATUS_SELESAI)
                ->whereHas('asesi', fn ($q) => $q->where('status_kompetensi', 'kompeten'))
                ->whereHas('validasiAsesmen', fn ($q) => $q->where('status', 'final')->where('keputusan', 'disetujui'))
                ->whereHas('jadwal.pleno', fn ($q) => $q->where('status', 'final')->where('keputusan', 'disetujui'))
                ->whereDoesntHave('sertifikat')
                ->find($item['pendaftaran_id']);

            if ($pendaftaran === null) {
                $errors[] = "Pendaftaran #{$index} tidak valid atau tidak memenuhi syarat.";

                continue;
            }

            // Cek duplikat no_seri_blangko secara global
            if (Sertifikat::withoutTenantScope()->where('no_seri_blangko', $item['no_seri_blangko'])->exists()) {
                $errors[] = "No. seri blangko {$item['no_seri_blangko']} sudah digunakan.";

                continue;
            }

            try {
                DB::transaction(function () use ($pendaftaran, $item, $diterbitkanOleh, $institution): void {
                    $noRegistrasi = $this->generateNoRegistrasi($pendaftaran);

                    Sertifikat::create([
                        'institution_id' => $institution->id,
                        'pendaftaran_id' => $pendaftaran->id,
                        'asesi_id' => $pendaftaran->asesi_id,
                        'skema_id' => $pendaftaran->skema_id,
                        'no_seri_blangko' => $item['no_seri_blangko'],
                        'no_registrasi' => $noRegistrasi,
                        'tanggal_terbit' => now(),
                        'status' => Sertifikat::STATUS_AKTIF,
                        'diterbitkan_oleh' => $diterbitkanOleh,
                    ]);

                    activity('sertifikat')
                        ->causedBy(request()->user())
                        ->performedOn($pendaftaran)
                        ->withProperties([
                            'no_registrasi' => $noRegistrasi,
                            'bundle' => true,
                            'jadwal_id' => $pendaftaran->jadwal_id,
                        ])
                        ->log('Sertifikat diterbitkan via bundle jadwal.');
                });

                $berhasil++;
            } catch (\Throwable $e) {
                $errors[] = "Gagal menerbitkan untuk {$pendaftaran->asesi->user->name}: {$e->getMessage()}";
            }
        }

        if (! empty($errors)) {
            return redirect()
                ->route('admin.sertifikat.bundle.create', $jadwal)
                ->with('warning', "{$berhasil} sertifikat berhasil diterbitkan. Gagal: ".implode(' | ', $errors));
        }

        return redirect()
            ->route('admin.sertifikat.bundle.create', $jadwal)
            ->with('success', "{$berhasil} sertifikat berhasil diterbitkan untuk jadwal ini.");
    }

    private function generateNoRegistrasi(Pendaftaran $pendaftaran): string
    {
        $institution = $pendaftaran->institution;
        $part1 = $this->normalizeCode($institution?->nomor_lisensi ?? 'LSP');
        $part2 = $this->normalizeCode($pendaftaran->skema->kode_skema ?? 'SKEMA');
        $year = now()->format('Y');

        $sequence = Sertifikat::query()
            ->where('institution_id', $pendaftaran->institution_id)
            ->whereYear('tanggal_terbit', $year)
            ->count() + 1;

        do {
            $noRegistrasi = sprintf('%s.%s.%05d.%s', $part1, $part2, $sequence, $year);
            $sequence++;
        } while (Sertifikat::withoutTenantScope()->where('no_registrasi', $noRegistrasi)->exists());

        return $noRegistrasi;
    }

    private function normalizeCode(string $code): string
    {
        $normalized = strtoupper(preg_replace('/[^A-Z0-9.\-]/', '-', $code) ?? '');
        $normalized = preg_replace('/-+/', '-', $normalized) ?? $normalized;
        $normalized = trim($normalized, '-.');

        return $normalized !== '' ? $normalized : 'LSP';
    }
}
