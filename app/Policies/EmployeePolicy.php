<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewAllData();
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->canViewAllData() || $user->employee?->id === $employee->id;
    }

    public function create(User $user): bool
    {
        return $user->canManageEmployees();
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->canManageEmployees();
    }

    public function delete(User $user, Employee $employee): bool
    {
        if ($employee->user) {
            return $user->canDelete($employee->user);
        }
        return $user->canManageEmployees();
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->canManageEmployees();
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->canManageEmployees();
    }
}
