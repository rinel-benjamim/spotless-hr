<?php

namespace App\Observers;

use App\EmployeeRole;
use App\Models\Employee;
use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeObserver
{
    public function creating(Employee $employee): void
    {
        if (empty($employee->employee_code)) {
            $employee->employee_code = $this->generateEmployeeCode();
        }
    }

    public function created(Employee $employee): void
    {
        if ($employee->user_id === null && ! empty($employee->temp_password)) {
            $user = User::create([
                'name' => $employee->full_name,
                'email' => $this->generateEmail($employee),
                'password' => Hash::make($employee->temp_password),
                'role' => $employee->role === EmployeeRole::Manager ? UserRole::Admin : UserRole::Employee,
            ]);

            $employee->update(['user_id' => $user->id]);
        }
    }

    protected function generateEmployeeCode(): string
    {
        $lastEmployee = Employee::withTrashed()
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastEmployee ? ((int) substr($lastEmployee->employee_code, 3) + 1) : 1;

        return 'EMP'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    protected function generateEmail(Employee $employee): string
    {
        $baseEmail = Str::slug($employee->full_name).'@lavandaria.com';
        $email = $baseEmail;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = Str::slug($employee->full_name).$counter.'@lavandaria.com';
            $counter++;
        }

        return $email;
    }
}
