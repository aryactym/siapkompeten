<?php

namespace App\Services;

use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PendaftaranWorkflow
{
    /**
     * Asesi mengajukan dokumen persyaratan untuk diverifikasi admin (FR-REG-01/02).
     */
    public function submitDocuments(Pendaftaran $pendaftaran, User $asesiUser): Pendaftaran
    {
        if (! $pendaftaran->canUploadDocuments()) {
            throw ValidationException::withMessages([
                'dokumen' => 'Dokumen tidak dapat diajukan pada tahap ini.',
            ]);
        }

        $missing = $pendaftaran->missingRequiredDocuments();

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'dokumen' => 'Dokumen wajib belum lengkap: '.$missing->pluck('nama_dokumen')->join(', ').'.',
            ]);
        }

        return DB::transaction(function () use ($pendaftaran, $asesiUser): Pendaftaran {
            $pendaftaran->update(['status' => Pendaftaran::STATUS_DOKUMEN_DIAJUKAN]);

            activity('pendaftaran')
                ->causedBy($asesiUser)
                ->performedOn($pendaftaran)
                ->log('Asesi mengajukan dokumen pendaftaran.');

            return $pendaftaran->refresh();
        });
    }

    /**
     * Sinkronkan status pendaftaran setelah admin memverifikasi dokumen (FR-REG-03/04).
     */
    public function syncAfterVerification(Pendaftaran $pendaftaran, User $admin): Pendaftaran
    {
        $dokumens = $pendaftaran->dokumens()->get();

        if ($dokumens->contains('status', 'ditolak')) {
            $status = Pendaftaran::STATUS_DOKUMEN_DITOLAK;
        } elseif ($dokumens->isEmpty() || $dokumens->contains('status', 'menunggu')) {
            $status = Pendaftaran::STATUS_DOKUMEN_DIAJUKAN;
        } else {
            $status = $this->requiresPayment($pendaftaran)
                ? Pendaftaran::STATUS_MENUNGGU_PEMBAYARAN
                : Pendaftaran::STATUS_MENUNGGU_JADWAL;
        }

        if ($status !== $pendaftaran->status) {
            $pendaftaran->update(['status' => $status]);

            activity('pendaftaran')
                ->causedBy($admin)
                ->performedOn($pendaftaran)
                ->withProperties(['status' => $status])
                ->log('Status pendaftaran diperbarui setelah verifikasi dokumen.');
        }

        return $pendaftaran->refresh();
    }

    /**
     * Urutan bayar vs plotting dapat dikonfigurasi (design §16 no. 2).
     * Tagihan dihitung dari biaya skema yang ditanggung peserta (FR-PAY-03/04).
     */
    public function requiresPayment(Pendaftaran $pendaftaran): bool
    {
        if (! (bool) config('pendaftaran.payment_before_scheduling', true)) {
            return false;
        }

        return $pendaftaran->skema->totalTagihanPeserta() > 0;
    }
}
