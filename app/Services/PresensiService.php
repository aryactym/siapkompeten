<?php

namespace App\Services;

use App\Models\Pendaftaran;
use App\Models\PersetujuanAsesmen;
use App\Models\PresensiAsesi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PresensiService
{
    /**
     * Catat atau koreksi presensi asesi pada satu pendaftaran.
     */
    public function catat(Pendaftaran $pendaftaran, string $status, ?string $catatan, User $oleh): PresensiAsesi
    {
        if (! array_key_exists($status, PresensiAsesi::STATUS_LABELS)) {
            throw ValidationException::withMessages([
                'status' => 'Status presensi tidak dikenal.',
            ]);
        }

        return DB::transaction(function () use ($pendaftaran, $status, $catatan, $oleh): PresensiAsesi {
            $presensi = PresensiAsesi::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id],
                [
                    'institution_id' => $pendaftaran->institution_id,
                    'jadwal_id' => $pendaftaran->jadwal_id,
                    'status' => $status,
                    'catatan' => $catatan,
                    'dicatat_oleh' => $oleh->id,
                    'dicatat_pada' => now(),
                    'terkunci_pada' => null,
                ],
            );

            activity('asesmen')
                ->causedBy($oleh)
                ->performedOn($pendaftaran)
                ->withProperties(['status' => $status, 'presensi_id' => $presensi->id])
                ->log('Presensi asesi dicatat.');

            return $presensi;
        });
    }

    /**
     * Mulai asesmen: transisi `terjadwal` → `asesmen_berjalan`.
     *
     * Gerbang pre-asesmen (FR.AK.01/04/07) wajib sudah disetujui asesi.
     */
    public function mulaiAsesmen(Pendaftaran $pendaftaran, User $oleh): void
    {
        if ($pendaftaran->status !== Pendaftaran::STATUS_TERJADWAL) {
            throw ValidationException::withMessages([
                'status' => 'Asesmen hanya dapat dimulai dari status terjadwal.',
            ]);
        }

        if (! PersetujuanAsesmen::allApprovedFor($pendaftaran)) {
            throw ValidationException::withMessages([
                'persetujuan' => 'Seluruh dokumen pre-asesmen (FR.AK.01, FR.AK.04, FR.AK.07) wajib disetujui sebelum asesmen dimulai.',
            ]);
        }

        DB::transaction(function () use ($pendaftaran, $oleh): void {
            $pendaftaran->update(['status' => Pendaftaran::STATUS_ASESMEN_BERJALAN]);

            activity('asesmen')
                ->causedBy($oleh)
                ->performedOn($pendaftaran)
                ->log('Asesmen dimulai.');
        });
    }

    /**
     * Kunci seluruh presensi milik pendaftaran (dipanggil saat asesmen selesai).
     */
    public function kunci(Pendaftaran $pendaftaran): void
    {
        PresensiAsesi::where('pendaftaran_id', $pendaftaran->id)
            ->whereNull('terkunci_pada')
            ->update(['terkunci_pada' => now()]);
    }
}
