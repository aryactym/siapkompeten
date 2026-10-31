<?php

namespace App\Policies;

use App\Models\InstitutionDocument;
use App\Models\User;

class InstitutionDocumentPolicy
{
    public function view(User $user, InstitutionDocument $document): bool
    {
        return $this->belongsToAccessibleInstitution($user, $document);
    }

    public function download(User $user, InstitutionDocument $document): bool
    {
        return $this->belongsToAccessibleInstitution($user, $document);
    }

    private function belongsToAccessibleInstitution(User $user, InstitutionDocument $document): bool
    {
        return $user->institutions()
            ->whereKey($document->institution_id)
            ->where('is_active', true)
            ->exists();
    }
}
