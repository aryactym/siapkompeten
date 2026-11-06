<?php

namespace App\Policies;

use App\Models\RencanaAsesmen;
use App\Models\User;

class RencanaAsesmenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'asesor']);
    }

    public function view(User $user, RencanaAsesmen $rencana): bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        if ($user->hasRole('asesor')) {
            return $rencana->asesor()->where('asesors.user_id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin', 'asesor']);
    }

    public function update(User $user, RencanaAsesmen $rencana): bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        if ($user->hasRole('asesor') && ! $rencana->isFinal()) {
            return $rencana->asesor()->where('asesors.user_id', $user->id)->exists();
        }

        return false;
    }

    public function finalize(User $user, RencanaAsesmen $rencana): bool
    {
        return $this->update($user, $rencana);
    }
}
