<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->isAdmin() || $user->employee?->isManager() || $user->employee?->id === $employee->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }
}
