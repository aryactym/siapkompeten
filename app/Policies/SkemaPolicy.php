<?php

namespace App\Policies;

use App\Models\Skema;
use App\Models\User;

class SkemaPolicy
{
    public function update(User $user, Skema $skema): bool
    {
        // Super admin platform — bypass
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin/pengguna tenant — harus milik institution yang sama
        return $user->institution_id === $skema->institution_id;
    }

    public function delete(User $user, Skema $skema): bool
    {
        return $this->update($user, $skema);
    }
}
