<?php

namespace App\Policies;

use App\Models\AsesorInvitation;
use App\Models\User;

class AsesorInvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function view(User $user, AsesorInvitation $invitation): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }

    public function delete(User $user, AsesorInvitation $invitation): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin'])
            && $invitation->status === AsesorInvitation::STATUS_MENUNGGU;
    }
}
