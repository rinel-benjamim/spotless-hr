<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;

class PayrollPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payroll $payroll): bool
    {
        return $user->isAdmin() || $user->employee?->isManager() || $user->employee?->id === $payroll->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }

    public function update(User $user, Payroll $payroll): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }

    public function delete(User $user, Payroll $payroll): bool
    {
        return $user->isAdmin() || $user->employee?->isManager();
    }
}
