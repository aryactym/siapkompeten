<?php

namespace App\Actions;

use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\User;
use App\Services\NotificationOutbox;
use DomainException;
use Illuminate\Support\Facades\DB;

class ApprovePendaftaran
{
    public function __construct(private readonly NotificationOutbox $outbox) {}

    /**
     * Setujui pendaftaran dan plotting ke jadwal uji (FR-REG-05/06/07, FR-SCH-08/09).
     */
    public function execute(Pendaftaran $pendaftaran, JadwalAsesmen $jadwal, User $admin): Pendaftaran
    {
        $pendaftaran = DB::transaction(function () use ($pendaftaran, $jadwal, $admin): Pendaftaran {
            $jadwal = JadwalAsesmen::withoutTenantScope()
                ->whereKey($jadwal->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $pendaftaran = Pendaftaran::withoutTenantScope()
                ->whereKey($pendaftaran->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($jadwal->institution_id !== $pendaftaran->institution_id) {
                throw new DomainException('Tenant pendaftaran dan jadwal tidak sama.');
            }

            if ($pendaftaran->status !== Pendaftaran::STATUS_MENUNGGU_JADWAL) {
                throw new DomainException('Pendaftaran belum siap diplotting ke jadwal.');
            }

            if ($jadwal->skema_id !== $pendaftaran->skema_id) {
                throw new DomainException('Skema jadwal tidak sesuai dengan skema pendaftaran.');
            }

            if (in_array($jadwal->status, ['selesai', 'dibatalkan'], true)) {
                throw new DomainException('Jadwal tidak dapat menerima peserta baru.');
            }

            if ($jadwal->asesors()->doesntExist()) {
                throw new DomainException('Jadwal belum memiliki asesor.');
            }

            if ($jadwal->pendaftarans()->count() >= $jadwal->kuota) {
                throw new DomainException('Kuota jadwal penuh.');
            }

            $pendaftaran->update([
                'jadwal_id' => $jadwal->id,
                'status' => Pendaftaran::STATUS_TERJADWAL,
                'disetujui_oleh' => $admin->id,
                'disetujui_pada' => now(),
                'catatan_penolakan' => null,
            ]);

            activity('pendaftaran')
                ->causedBy($admin)
                ->performedOn($pendaftaran)
                ->withProperties(['jadwal_id' => $jadwal->id])
                ->log('Pendaftaran disetujui dan diplotting ke jadwal.');

            return $pendaftaran->refresh();
        });

        $this->outbox->queue(
            $pendaftaran->asesi->user,
            'pendaftaran_disetujui',
            'Pendaftaran Disetujui',
            sprintf(
                'Pendaftaran %s untuk skema %s telah disetujui. Jadwal uji: %s pukul %s di %s.',
                $pendaftaran->no_pendaftaran,
                $pendaftaran->skema->nama_skema,
                $pendaftaran->jadwal->tanggal->translatedFormat('d F Y'),
                substr($pendaftaran->jadwal->jam_mulai, 0, 5),
                $pendaftaran->jadwal->tuk->nama_tuk,
            ),
            $pendaftaran,
        );

        return $pendaftaran;
    }
}
