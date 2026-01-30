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
        return $user->canViewAllData() || $user->employee?->id === $payroll->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->canManageEmployees();
    }

    public function update(User $user, Payroll $payroll): bool
    {
        return $user->canManageEmployees();
    }

    public function delete(User $user, Payroll $payroll): bool
    {
        return $user->canManageEmployees();
    }
}
