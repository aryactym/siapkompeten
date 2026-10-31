<?php

namespace App\Policies;

use App\Models\Sertifikat;
use App\Models\User;

class SertifikatPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function view(User $user, Sertifikat $sertifikat): bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        return $user->hasRole('asesi')
            && $sertifikat->asesi()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Sertifikat $sertifikat): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function delete(User $user, Sertifikat $sertifikat): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }
}
