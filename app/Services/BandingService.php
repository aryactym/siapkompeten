<?php

namespace App\Services;

use App\Models\Banding;
use App\Models\BandingStatusHistory;
use App\Models\HasilAsesmen;
use App\Models\Pendaftaran;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BandingService
{
    public function __construct(private readonly NotificationOutbox $outbox) {}

    public function ajukan(Pendaftaran $pendaftaran, User $pengaju, string $alasan): Banding
    {
        if ($pendaftaran->status !== Pendaftaran::STATUS_SELESAI) {
            throw ValidationException::withMessages([
                'banding' => 'Banding hanya dapat diajukan setelah keputusan asesmen ditetapkan.',
            ]);
        }

        $existing = Banding::where('pendaftaran_id', $pendaftaran->id)->first();

        if ($existing !== null && ! $existing->isClosed()) {
            throw ValidationException::withMessages([
                'banding' => 'Banding untuk pendaftaran ini masih berjalan.',
            ]);
        }

        return DB::transaction(function () use ($pendaftaran, $pengaju, $alasan): Banding {
            $banding = Banding::create([
                'institution_id' => $pendaftaran->institution_id,
                'pendaftaran_id' => $pendaftaran->id,
                'pengaju_id' => $pengaju->id,
                'alasan' => $alasan,
                'status' => Banding::STATUS_DIAJUKAN,
            ]);

            BandingStatusHistory::create([
                'institution_id' => $pendaftaran->institution_id,
                'banding_id' => $banding->id,
                'status' => Banding::STATUS_DIAJUKAN,
                'catatan' => $alasan,
                'created_by' => $pengaju->id,
            ]);

            $this->notifikasiPeninjau($pendaftaran, 'Banding asesmen diajukan', sprintf(
                'Asesi mengajukan banding untuk pendaftaran %s. Alasan: %s',
                $pendaftaran->no_pendaftaran,
                $alasan,
            ), $banding);

            return $banding;
        });
    }

    public function tinjau(Banding $banding, User $peninjau, string $status, ?string $keputusan): Banding
    {
        if (! in_array($status, [Banding::STATUS_DITINJAU, Banding::STATUS_DITERIMA, Banding::STATUS_DITOLAK, Banding::STATUS_SELESAI], true)) {
            throw ValidationException::withMessages([
                'status' => 'Status banding tidak dikenal.',
            ]);
        }

        if (in_array($status, [Banding::STATUS_DITERIMA, Banding::STATUS_DITOLAK], true) && blank($keputusan)) {
            throw ValidationException::withMessages([
                'keputusan' => 'Keputusan wajib diisi untuk status diterima/ditolak.',
            ]);
        }

        return DB::transaction(function () use ($banding, $peninjau, $status, $keputusan): Banding {
            $banding->update([
                'status' => $status,
                'keputusan' => $keputusan,
                'ditinjau_oleh' => $peninjau->id,
                'ditinjau_pada' => now(),
            ]);

            BandingStatusHistory::create([
                'institution_id' => $banding->institution_id,
                'banding_id' => $banding->id,
                'status' => $status,
                'catatan' => $keputusan,
                'created_by' => $peninjau->id,
            ]);

            if ($status === Banding::STATUS_DITERIMA) {
                $banding->pendaftaran->update(['status' => Pendaftaran::STATUS_ASESMEN_BERJALAN]);

                HasilAsesmen::where('pendaftaran_id', $banding->pendaftaran_id)
                    ->update(['status' => HasilAsesmen::STATUS_DRAF]);
            }

            $this->outbox->queue(
                $banding->pengaju,
                'banding_status',
                'Status Banding Diperbarui',
                sprintf('Banding untuk pendaftaran %s kini berstatus "%s". %s', $banding->pendaftaran->no_pendaftaran, Banding::STATUS_LABELS[$status] ?? $status, $keputusan ?? ''),
                $banding,
            );

            return $banding;
        });
    }

    public function pdf(Banding $banding)
    {
        $banding->load(['pendaftaran.skema', 'pendaftaran.asesi.user', 'pendaftaran.jadwal.tuk', 'pengaju', 'peninjau', 'histories.pembuat']);

        return Pdf::loadView('pdf.ak04', [
            'banding' => $banding,
            'pendaftaran' => $banding->pendaftaran,
            'skema' => $banding->pendaftaran->skema,
            'jadwal' => $banding->pendaftaran->jadwal,
            'asesiUser' => $banding->pendaftaran->asesi->user,
        ])->setPaper('a4', 'portrait')
            ->stream(sprintf('AK04-%s.pdf', $banding->pendaftaran->no_pendaftaran));
    }

    private function notifikasiPeninjau(Pendaftaran $pendaftaran, string $judul, string $pesan, Banding $banding): void
    {
        $jadwal = $pendaftaran->jadwal;

        if ($jadwal !== null) {
            foreach ($jadwal->asesors as $asesor) {
                if ($asesor->user !== null) {
                    $this->outbox->queue($asesor->user, 'banding_baru', $judul, $pesan, $banding);
                }
            }
        }

        $admins = User::role('admin')
            ->where('institution_id', $pendaftaran->institution_id)
            ->get();

        foreach ($admins as $admin) {
            $this->outbox->queue($admin, 'banding_baru', $judul, $pesan, $banding);
        }
    }
}
