<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\Skema;
use App\Models\Tuk;
use App\Services\NotificationOutbox;
use App\Support\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        $jadwals = JadwalAsesmen::query()
            ->with(['event', 'skema', 'tuk'])
            ->withCount(['asesors', 'pendaftarans'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('tanggal')
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        return view('admin.jadwal.index', [
            'jadwals' => $jadwals,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.jadwal.form', [
            'jadwal' => new JadwalAsesmen(['tanggal' => now()->toDateString(), 'kuota' => 20]),
            'events' => EventAsesmen::orderByDesc('tahun')->get(),
            'skemas' => Skema::aktif()->orderBy('nama_skema')->get(),
            'tuks' => Tuk::where('is_active', true)->orderBy('nama_tuk')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateJadwal($request);

        $jadwal = DB::transaction(fn (): JadwalAsesmen => JadwalAsesmen::create($validated));

        activity('jadwal')
            ->causedBy($request->user())
            ->performedOn($jadwal)
            ->log('Jadwal uji kompetensi dibuat.');

        return redirect()
            ->route('admin.jadwal.show', $jadwal)
            ->with('success', 'Jadwal uji berhasil dibuat. Silakan tugaskan asesor.');
    }

    public function show(JadwalAsesmen $jadwal): View
    {
        $jadwal->load([
            'event',
            'skema.persyaratans',
            'tuk',
            'penugasan.asesor.user',
            'pendaftarans.asesi.user',
        ]);

        return view('admin.jadwal.show', [
            'jadwal' => $jadwal,
            'asesors' => Asesor::aktif()
                ->with('user')
                ->whereNotIn('id', $jadwal->penugasan->pluck('asesor_id'))
                ->orderBy('no_register')
                ->get(),
        ]);
    }

    public function edit(JadwalAsesmen $jadwal): View
    {
        return view('admin.jadwal.form', [
            'jadwal' => $jadwal,
            'events' => EventAsesmen::orderByDesc('tahun')->get(),
            'skemas' => Skema::orderBy('nama_skema')->get(),
            'tuks' => Tuk::orderBy('nama_tuk')->get(),
        ]);
    }

    public function update(Request $request, JadwalAsesmen $jadwal): RedirectResponse
    {
        $validated = $this->validateJadwal($request);

        $this->ensureNoAsesorConflict(
            $jadwal->asesors()->get(),
            $validated['tanggal'],
            $validated['jam_mulai'],
            $validated['jam_selesai'],
            $jadwal->id,
        );

        $jadwal->update($validated);

        activity('jadwal')
            ->causedBy($request->user())
            ->performedOn($jadwal)
            ->log('Jadwal uji kompetensi diperbarui.');

        return redirect()
            ->route('admin.jadwal.show', $jadwal)
            ->with('success', 'Jadwal uji kompetensi berhasil diperbarui.');
    }

    public function destroy(Request $request, JadwalAsesmen $jadwal): RedirectResponse
    {
        if ($jadwal->pendaftarans()->exists()) {
            return back()->with('error', 'Jadwal sudah memiliki peserta dan tidak dapat dihapus.');
        }

        activity('jadwal')
            ->causedBy($request->user())
            ->performedOn($jadwal)
            ->log('Jadwal uji kompetensi dihapus.');

        $jadwal->delete();

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', 'Jadwal uji kompetensi berhasil dihapus.');
    }

    public function assignAsesor(Request $request, JadwalAsesmen $jadwal, NotificationOutbox $outbox): RedirectResponse
    {
        $validated = $request->validate([
            'asesor_id' => [
                'required',
                'uuid',
                Rule::exists('asesors', 'id')
                    ->where('institution_id', $jadwal->institution_id)
                    ->where('status', 'aktif'),
            ],
            'peran' => ['required', 'in:utama,pendamping,observer'],
        ]);

        $asesor = Asesor::with('user')->findOrFail($validated['asesor_id']);

        if ($jadwal->penugasan()->where('asesor_id', $asesor->id)->exists()) {
            return back()->with('error', 'Asesor tersebut sudah ditugaskan pada jadwal ini.');
        }

        if (JadwalAsesmen::hasAsesorConflict(
            $asesor->id,
            $jadwal->tanggal->toDateString(),
            $jadwal->jam_mulai,
            $jadwal->jam_selesai,
            $jadwal->id,
        )) {
            return back()->with('error', 'Asesor '.$asesor->nama_lengkap.' memiliki jadwal lain yang bentrok.');
        }

        $penugasan = $jadwal->penugasan()->create([
            'asesor_id' => $asesor->id,
            'peran' => $validated['peran'],
        ]);

        if ($asesor->user) {
            $notifikasi = $outbox->queue(
                $asesor->user,
                'jadwal_asesor',
                'Penugasan Asesor',
                sprintf(
                    'Anda ditugaskan sebagai asesor %s pada %s, %s pukul %s-%s di %s.',
                    $validated['peran'],
                    $jadwal->event->nama,
                    $jadwal->tanggal->translatedFormat('d F Y'),
                    substr($jadwal->jam_mulai, 0, 5),
                    substr($jadwal->jam_selesai, 0, 5),
                    $jadwal->tuk->nama_tuk,
                ),
                $jadwal,
            );

            if ($notifikasi !== null) {
                $penugasan->update(['notified_at' => now()]);
            }
        }

        activity('jadwal')
            ->causedBy($request->user())
            ->performedOn($jadwal)
            ->withProperties(['asesor_id' => $asesor->id, 'peran' => $validated['peran']])
            ->log('Asesor ditugaskan pada jadwal.');

        return back()->with('success', 'Asesor berhasil ditugaskan pada jadwal.');
    }

    public function removeAsesor(Request $request, JadwalAsesmen $jadwal, JadwalAsesor $jadwalAsesor): RedirectResponse
    {
        abort_unless($jadwalAsesor->jadwal_id === $jadwal->id, 404);

        if ($jadwal->pendaftarans()->exists() && $jadwal->asesors()->count() <= 1) {
            return back()->with('error', 'Jadwal sudah memiliki peserta; minimal satu asesor harus tetap ditugaskan.');
        }

        activity('jadwal')
            ->causedBy($request->user())
            ->performedOn($jadwal)
            ->withProperties(['asesor_id' => $jadwalAsesor->asesor_id])
            ->log('Penugasan asesor dilepas dari jadwal.');

        $jadwalAsesor->delete();

        return back()->with('success', 'Penugasan asesor berhasil dilepas.');
    }

    public function presensi(JadwalAsesmen $jadwal): Response
    {
        $this->authorize('viewPeserta', $jadwal);

        $jadwal->load([
            'event',
            'skema',
            'tuk',
            'asesors.user',
            'pendaftarans.asesi.user',
        ]);

        activity('jadwal')
            ->causedBy(request()->user())
            ->performedOn($jadwal)
            ->log('Presensi peserta dicetak sebagai PDF.');

        return Pdf::loadView('pdf.presensi', [
            'jadwal' => $jadwal,
        ])
            ->setPaper('a4', 'portrait')
            ->stream($this->pdfFilename('presensi', $jadwal));
    }

    public function suratTugas(JadwalAsesmen $jadwal, JadwalAsesor $jadwalAsesor): Response
    {
        $this->authorize('view', $jadwal);

        abort_unless($jadwalAsesor->jadwal_id === $jadwal->id, 404);

        $jadwal->load(['event', 'skema', 'tuk']);
        $jadwalAsesor->load('asesor.user');

        activity('jadwal')
            ->causedBy(request()->user())
            ->performedOn($jadwal)
            ->log('Surat tugas asesor dicetak sebagai PDF.');

        return Pdf::loadView('pdf.surat-tugas', [
            'jadwal' => $jadwal,
            'penugasan' => $jadwalAsesor,
        ])
            ->setPaper('a4', 'portrait')
            ->stream($this->pdfFilename('surat-tugas', $jadwal));
    }

    private function pdfFilename(string $prefix, JadwalAsesmen $jadwal): string
    {
        $slug = str($jadwal->skema->nama_skema)->slug()->limit(50, '')->toString();

        return sprintf(
            '%s-%s-%s.pdf',
            $prefix,
            $slug !== '' ? $slug : 'jadwal',
            $jadwal->tanggal->format('Ymd'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validateJadwal(Request $request): array
    {
        $institutionId = $request->user()->institution_id ?? session(TenantContext::SESSION_ID);

        return $request->validate([
            'event_id' => [
                'required',
                'uuid',
                Rule::exists('event_asesmens', 'id')->where('institution_id', $institutionId),
            ],
            'skema_id' => [
                'required',
                'uuid',
                Rule::exists('skemas', 'id')->where('institution_id', $institutionId),
            ],
            'tuk_id' => [
                'required',
                'uuid',
                Rule::exists('tuks', 'id')->where('institution_id', $institutionId),
            ],
            'gelombang' => ['nullable', 'string', 'max:50'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'kuota' => ['required', 'integer', 'between:1,1000'],
            'lingkup_asesor' => ['required', 'in:wilayah,nonwilayah'],
            'no_surat_tugas' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:draf,terbuka,berjalan,selesai,dibatalkan'],
        ]);
    }

    /**
     * @param  iterable<int, Asesor>  $asesors
     */
    private function ensureNoAsesorConflict(
        iterable $asesors,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        string $jadwalId,
    ): void {
        foreach ($asesors as $asesor) {
            if (JadwalAsesmen::hasAsesorConflict($asesor->id, $tanggal, $jamMulai, $jamSelesai, $jadwalId)) {
                throw ValidationException::withMessages([
                    'tanggal' => 'Jadwal bentrok dengan penugasan asesor '.$asesor->nama_lengkap.'.',
                ]);
            }
        }
    }
}
