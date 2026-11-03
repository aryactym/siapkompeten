<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\Sertifikat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Manajemen Sertifikat Kompetensi BNSP (FR-RPT-04 / FR-CMS-06).
 */
class SertifikatController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Sertifikat::class);

        $sertifikats = Sertifikat::query()
            ->with(['asesi.user', 'skema', 'penerbit'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q')->trim().'%';
                $query->where(function ($query) use ($search): void {
                    $query->where('no_seri_blangko', 'like', $search)
                        ->orWhere('no_registrasi', 'like', $search)
                        ->orWhereHas('asesi.user', fn ($u) => $u->where('name', 'like', $search));
                });
            })
            ->latest('tanggal_terbit')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sertifikat.index', [
            'sertifikats' => $sertifikats,
            'search' => $request->string('q')->toString(),
        ]);
    }

    public function show(Sertifikat $sertifikat): View
    {
        $this->authorize('view', $sertifikat);

        $sertifikat->load(['asesi.user', 'skema', 'pendaftaran.jadwal.tuk', 'penerbit']);

        return view('admin.sertifikat.show', [
            'sertifikat' => $sertifikat,
        ]);
    }

    /**
     * Terbitkan sertifikat dari pendaftaran yang sudah selesai dan kompeten.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Sertifikat::class);

        $validated = $request->validate([
            'pendaftaran_id' => ['required', 'uuid', Rule::exists('pendaftarans', 'id')],
            'no_seri_blangko' => ['required', 'string', 'max:100', 'regex:/^[A-Z0-9\-.]+\.[A-Z0-9\-.]+\.\d{5,}\.\d{4}$/'],
        ]);

        $pendaftaran = Pendaftaran::with(['asesi.user', 'skema'])->findOrFail($validated['pendaftaran_id']);

        if ($pendaftaran->status !== Pendaftaran::STATUS_SELESAI) {
            throw ValidationException::withMessages([
                'pendaftaran_id' => 'Pendaftaran belum berstatus selesai.',
            ]);
        }

        if ($pendaftaran->asesi->status_kompetensi !== 'kompeten') {
            throw ValidationException::withMessages([
                'pendaftaran_id' => 'Asesi belum dinyatakan kompeten.',
            ]);
        }

        if ($pendaftaran->sertifikat()->exists()) {
            throw ValidationException::withMessages([
                'pendaftaran_id' => 'Sertifikat untuk pendaftaran ini sudah diterbitkan.',
            ]);
        }

        // Validasi format no_seri_blangko sesuai regex BNSP.
        if (! preg_match(Sertifikat::FORMAT_REGEX, $validated['no_seri_blangko'])) {
            throw ValidationException::withMessages([
                'no_seri_blangko' => 'Format nomor seri blangko tidak sesuai standar BNSP.',
            ]);
        }

        $noRegistrasi = $this->generateNoRegistrasi($pendaftaran);

        $sertifikat = DB::transaction(function () use ($validated, $pendaftaran, $noRegistrasi, $request): Sertifikat {
            return Sertifikat::create([
                'institution_id' => $pendaftaran->institution_id,
                'pendaftaran_id' => $pendaftaran->id,
                'asesi_id' => $pendaftaran->asesi_id,
                'skema_id' => $pendaftaran->skema_id,
                'no_seri_blangko' => $validated['no_seri_blangko'],
                'no_registrasi' => $noRegistrasi,
                'tanggal_terbit' => now(),
                'status' => Sertifikat::STATUS_AKTIF,
                'diterbitkan_oleh' => $request->user()->id,
            ]);
        });

        activity('sertifikat')
            ->causedBy($request->user())
            ->performedOn($sertifikat)
            ->withProperties(['no_registrasi' => $noRegistrasi])
            ->log('Sertifikat kompetensi diterbitkan.');

        return redirect()
            ->route('admin.sertifikat.show', $sertifikat)
            ->with('success', 'Sertifikat berhasil diterbitkan.');
    }

    public function pdf(Sertifikat $sertifikat): Response
    {
        $this->authorize('view', $sertifikat);

        $sertifikat->load([
            'asesi.user',
            'skema',
            'pendaftaran.jadwal.tuk',
            'pendaftaran.hasilAsesmens.asesor.user',
            'penerbit',
        ]);

        return Pdf::loadView('pdf.sertifikat', [
            'sertifikat' => $sertifikat,
            'institution' => $sertifikat->institution,
        ])
            ->setPaper('a4', 'landscape')
            ->stream(sprintf('sertifikat-%s.pdf', $sertifikat->no_registrasi));
    }

    /**
     * Bangkitkan nomor registrasi lisensi nasional sesuai format BNSP:
     * {LSP}.{SKEMA}.{NNNNN}.{TAHUN}
     */
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
