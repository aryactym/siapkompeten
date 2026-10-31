<?php

namespace App\Policies;

use App\Models\Asesi;
use App\Models\User;

class AsesiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function view(User $user, Asesi $asesi): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin'])
            || ($user->hasRole('asesi') && $asesi->user_id === $user->id);
    }

    public function update(User $user, Asesi $asesi): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin'])
            || ($user->hasRole('asesi') && $asesi->user_id === $user->id);
    }

    public function verify(User $user, Asesi $asesi): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function resetPassword(User $user, Asesi $asesi): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }
}
