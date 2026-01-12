<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->isAdmin() || $user->employee?->id === $attendance->employee_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return false;
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return false;
    }

    public function restore(User $user, Attendance $attendance): bool
    {
        return false;
    }

    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return false;
    }
}
