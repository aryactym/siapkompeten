<?php

namespace App\Policies;

use App\Models\Pendaftaran;
use App\Models\User;

class PendaftaranPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function view(User $user, Pendaftaran $pendaftaran): bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        if ($user->hasRole('asesi')) {
            return $pendaftaran->asesi()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('asesor') && $pendaftaran->jadwal_id !== null) {
            return $pendaftaran->jadwal->asesors()->where('asesors.user_id', $user->id)->exists();
        }

        return false;
    }

    public function update(User $user, Pendaftaran $pendaftaran): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin'])
            || ($user->hasRole('asesi')
                && $pendaftaran->asesi()->where('user_id', $user->id)->exists()
                && $pendaftaran->canUploadDocuments());
    }

    public function verifyDocuments(User $user, Pendaftaran $pendaftaran): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function approve(User $user, Pendaftaran $pendaftaran): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function reject(User $user, Pendaftaran $pendaftaran): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * Asesi pemilik pendaftaran mengisi APL-02 setelah terjadwal (FR-ASM-01).
     */
    public function fillApl02(User $user, Pendaftaran $pendaftaran): bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        return $user->hasRole('asesi')
            && $pendaftaran->asesi()->where('user_id', $user->id)->exists()
            && in_array($pendaftaran->status, [
                Pendaftaran::STATUS_TERJADWAL,
                Pendaftaran::STATUS_ASESMEN_BERJALAN,
            ], true);
    }

    /**
     * Asesor yang ditugaskan pada jadwal pendaftaran (FR-ASM-02..08).
     */
    public function verifyApl02(User $user, Pendaftaran $pendaftaran): bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        if (! $user->hasRole('asesor') || $pendaftaran->jadwal_id === null) {
            return false;
        }

        return $pendaftaran->jadwal->asesors()->where('asesors.user_id', $user->id)->exists();
    }

    public function inputMak02(User $user, Pendaftaran $pendaftaran): bool
    {
        return $this->verifyApl02($user, $pendaftaran);
    }
}
