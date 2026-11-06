<?php

namespace App\Policies;

use App\Models\JadwalAsesmen;
use App\Models\User;

class JadwalAsesmenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'asesor']);
    }

    public function view(User $user, JadwalAsesmen $jadwal): bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin', 'komite-teknis', 'manajer-sertifikasi'])) {
            return true;
        }

        if ($user->hasRole('asesor')) {
            return $jadwal->asesors()->where('asesors.user_id', $user->id)->exists();
        }

        return false;
    }

    public function viewPeserta(User $user, JadwalAsesmen $jadwal): bool
    {
        return $this->view($user, $jadwal);
    }

    public function manage(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }
}
