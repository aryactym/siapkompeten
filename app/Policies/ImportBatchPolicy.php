<?php

namespace App\Policies;

use App\Models\ImportBatch;
use App\Models\User;

class ImportBatchPolicy
{
    public function view(User $user, ImportBatch $batch): bool
    {
        return $this->isAdmin($user);
    }

    public function process(User $user, ImportBatch $batch): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, ImportBatch $batch): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'admin']);
    }
}
