<?php

namespace App\Policies;

use App\Models\AsesorKompetensi;
use App\Models\User;

class AsesorKompetensiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function update(User $user, AsesorKompetensi $kompetensi): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function delete(User $user, AsesorKompetensi $kompetensi): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }
}
