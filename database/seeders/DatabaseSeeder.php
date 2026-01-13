<?php

namespace Database\Seeders;

use App\AttendanceType;
use App\EmployeeRole;
use App\Models\Attendance;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::create([
            'company_name' => 'Lavandaria Spotless',
            'business_hours_start' => '08:00:00',
            'business_hours_end' => '20:00:00',
            'timezone' => 'Europe/Lisbon',
            'currency' => 'AKZ',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@lavandaria.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Admin,
        ]);

        $morningShift = Shift::create([
            'name' => 'Turno da Manhã',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'tolerance_minutes' => 15,
            'description' => 'Turno diurno padrão',
        ]);

        $afternoonShift = Shift::create([
            'name' => 'Turno da Tarde',
            'start_time' => '14:00:00',
            'end_time' => '22:00:00',
            'tolerance_minutes' => 15,
            'description' => 'Turno vespertino',
        ]);

        $nightShift = Shift::create([
            'name' => 'Turno da Noite',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'tolerance_minutes' => 15,
            'description' => 'Turno noturno',
        ]);

        $employeeUser = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@lavandaria.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Employee,
        ]);

        $employee1 = Employee::create([
            'user_id' => $employeeUser->id,
            'employee_code' => 'EMP1001',
            'full_name' => 'João Silva',
            'role' => EmployeeRole::Operator,
            'contract_type' => 'full_time',
            'shift_id' => $morningShift->id,
            'hire_date' => now()->subYears(2),
        ]);

        $employee2 = Employee::create([
            'user_id' => null,
            'employee_code' => 'EMP1002',
            'full_name' => 'Maria Santos',
            'role' => EmployeeRole::Washer,
            'contract_type' => 'full_time',
            'shift_id' => $morningShift->id,
            'hire_date' => now()->subYears(3),
        ]);

        $employee3 = Employee::create([
            'user_id' => null,
            'employee_code' => 'EMP1003',
            'full_name' => 'Pedro Costa',
            'role' => EmployeeRole::Ironer,
            'contract_type' => 'part_time',
            'shift_id' => $afternoonShift->id,
            'hire_date' => now()->subMonths(6),
        ]);

        $employee4 = Employee::create([
            'user_id' => null,
            'employee_code' => 'EMP1004',
            'full_name' => 'Ana Rodrigues',
            'role' => EmployeeRole::Supervisor,
            'contract_type' => 'full_time',
            'shift_id' => $morningShift->id,
            'hire_date' => now()->subYears(4),
        ]);

        $today = now();
        Attendance::create([
            'employee_id' => $employee1->id,
            'type' => AttendanceType::CheckIn,
            'recorded_at' => $today->copy()->setTime(8, 5),
        ]);

        Attendance::create([
            'employee_id' => $employee2->id,
            'type' => AttendanceType::CheckIn,
            'recorded_at' => $today->copy()->setTime(7, 55),
        ]);

        Attendance::create([
            'employee_id' => $employee4->id,
            'type' => AttendanceType::CheckIn,
            'recorded_at' => $today->copy()->setTime(8, 0),
        ]);

        $yesterday = $today->copy()->subDay();
        Attendance::create([
            'employee_id' => $employee1->id,
            'type' => AttendanceType::CheckIn,
            'recorded_at' => $yesterday->copy()->setTime(8, 10),
        ]);

        Attendance::create([
            'employee_id' => $employee1->id,
            'type' => AttendanceType::CheckOut,
            'recorded_at' => $yesterday->copy()->setTime(16, 5),
        ]);

        Attendance::create([
            'employee_id' => $employee2->id,
            'type' => AttendanceType::CheckIn,
            'recorded_at' => $yesterday->copy()->setTime(8, 0),
        ]);

        Attendance::create([
            'employee_id' => $employee2->id,
            'type' => AttendanceType::CheckOut,
            'recorded_at' => $yesterday->copy()->setTime(16, 0),
        ]);
    }
}
