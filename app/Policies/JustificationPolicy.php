<?php

namespace App\Policies;

use App\Models\Justification;
use App\Models\User;

class JustificationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Justification $justification): bool
    {
        return $user->isAdmin() || $user->employee?->isManager() || $user->employee?->id === $justification->employee_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Justification $justification): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }

    public function delete(User $user, Justification $justification): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }
}
