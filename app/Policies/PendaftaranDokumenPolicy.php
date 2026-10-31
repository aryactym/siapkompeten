<?php

namespace App\Policies;

use App\Models\PendaftaranDokumen;
use App\Models\User;

class PendaftaranDokumenPolicy
{
    public function view(User $user, PendaftaranDokumen $dokumen): bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        if ($user->hasRole('asesi')) {
            return $dokumen->pendaftaran->asesi()->where('user_id', $user->id)->exists();
        }

        if ($user->hasRole('asesor') && $dokumen->pendaftaran->jadwal_id !== null) {
            return $dokumen->pendaftaran->jadwal->asesors()->where('asesors.user_id', $user->id)->exists();
        }

        return false;
    }

    public function download(User $user, PendaftaranDokumen $dokumen): bool
    {
        return $this->view($user, $dokumen);
    }

    public function upload(User $user, PendaftaranDokumen $dokumen): bool
    {
        return $user->hasRole('asesi')
            && $dokumen->pendaftaran->asesi()->where('user_id', $user->id)->exists()
            && $dokumen->pendaftaran->canUploadDocuments();
    }

    public function verify(User $user, PendaftaranDokumen $dokumen): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }
}
