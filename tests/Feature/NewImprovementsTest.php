<?php

use App\Models\Employee;
use App\Models\User;
use App\Models\Shift;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\CompanySetting;
use App\Models\Justification;
use App\AttendanceType;
use App\UserRole;
use App\Services\PayrollService;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a user when an employee is created', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $shift = Shift::factory()->create();

    $response = $this->actingAs($admin)->post(route('employees.store'), [
        'full_name' => 'John Doe',
        'role' => \App\EmployeeRole::Employee->value,
        'contract_type' => \App\ContractType::FullTime->value,
        'shift_id' => $shift->id,
        'base_salary' => 1000,
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('employees.index'));
    
    $employee = Employee::where('full_name', 'John Doe')->first();
    expect($employee)->not->toBeNull();
    expect($employee->user_id)->not->toBeNull();
    
    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect($employee->user_id)->toBe($user->id);
});

it('allows bulk schedule creation', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $employees = Employee::factory()->count(3)->create();
    $shift = Shift::factory()->create();
    $date = now()->addDay()->format('Y-m-d');

    $response = $this->actingAs($admin)->post(route('schedules.store'), [
        'employee_ids' => $employees->pluck('id')->toArray(),
        'date' => $date,
        'shift_id' => $shift->id,
        'is_working_day' => true,
    ]);

    $response->assertRedirect();
    
    foreach ($employees as $employee) {
        $this->assertDatabaseHas('schedules', [
            'employee_id' => $employee->id,
            'date' => Carbon::parse($date)->format('Y-m-d'),
            'shift_id' => $shift->id,
        ]);
    }
});

it('calculates deductions for late entry and early exit without justification', function () {
    $shift = Shift::factory()->create([
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
        'tolerance_minutes' => 0,
    ]);
    
    $employee = Employee::factory()->create([
        'shift_id' => $shift->id,
        'base_salary' => 1000,
        'deduction_per_absence' => 50,
    ]);
    
    CompanySetting::create([
        'company_name' => 'Test Co',
        'late_deduction_amount' => 10,
        'early_exit_deduction_amount' => 15,
    ]);

    $date = now()->subMonth()->startOfMonth()->setDay(15);
    $year = $date->year;
    $month = $date->month;

    // Late entry
    Attendance::create([
        'employee_id' => $employee->id,
        'type' => AttendanceType::CheckIn,
        'recorded_at' => $date->copy()->setTime(8, 30),
    ]);
    
    // Early exit
    Attendance::create([
        'employee_id' => $employee->id,
        'type' => AttendanceType::CheckOut,
        'recorded_at' => $date->copy()->setTime(16, 30),
    ]);

    $attendanceService = app(AttendanceService::class);
    $payrollService = new PayrollService($attendanceService);
    
    $payroll = $payrollService->generatePayroll($employee, $year, $month);
    
    // 1 day worked, 1 late (10), 1 early exit (15)
    // base 1000 - 10 - 15 = 975
    // Other days are absences! To avoid absences, we should set recorded_at for those days too or just expect them.
    // Better: let's expect the absences too if we want, or use a date where there are no other past work days in the month.
    // If we use subMonth()->startOfMonth(), and today is Jan 23, subMonth is Dec. 
    // Dec has many work days.
    
    // Let's mock today to be the same as the recorded_at day + 1.
    Carbon::setTestNow($date->copy()->addDay());
    
    $payroll = $payrollService->generatePayroll($employee, $year, $month);
    
    // Absences from start of month to $date.
    // If $date is 15th (Thursday), absences are 1st, 2nd, 5th, 6th, 7th, 8th, 9th, 12th, 13th, 14th. (10 days)
    // 10 absences * 50 = 500.
    // Total deductions = 500 + 10 + 15 = 525.
    
    expect((float)$payroll->total_deductions)->toBe(525.0);
    expect((float)$payroll->net_salary)->toBe(475.0);
    
    Carbon::setTestNow();
});

it('does not deduct for late/early exit with approved justification', function () {
    $shift = Shift::factory()->create([
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
        'tolerance_minutes' => 0,
    ]);
    
    $employee = Employee::factory()->create([
        'shift_id' => $shift->id,
        'base_salary' => 1000,
        'deduction_per_absence' => 50,
    ]);
    
    CompanySetting::create([
        'company_name' => 'Test Co',
        'late_deduction_amount' => 10,
        'early_exit_deduction_amount' => 15,
    ]);

    $date = now()->subMonth()->startOfMonth()->setDay(15);
    $year = $date->year;
    $month = $date->month;

    Carbon::setTestNow($date->copy()->addDay());

    // Late entry: 08:30
    $checkIn = Attendance::create([
        'employee_id' => $employee->id,
        'type' => AttendanceType::CheckIn,
        'recorded_at' => $date->copy()->setTime(8, 30),
    ]);
    
    // Approved Justification
    Justification::create([
        'employee_id' => $employee->id,
        'attendance_id' => $checkIn->id,
        'absence_date' => $date->toDateString(),
        'reason' => 'Traffic',
        'status' => 'approved',
        'justified_by' => User::factory()->create()->id,
    ]);

    $attendanceService = app(AttendanceService::class);
    $payrollService = new PayrollService($attendanceService);
    
    $payroll = $payrollService->generatePayroll($employee, $year, $month);
    
    // Late is justified, so no late deduction.
    // Still has 10 absences (500).
    expect((float)$payroll->total_deductions)->toBe(500.0);
    expect((float)$payroll->net_salary)->toBe(500.0);
    
    Carbon::setTestNow();
});

it('allows managers to see all payrolls and attendances', function () {
    $managerUser = User::factory()->create(['role' => UserRole::Manager]);
    $managerEmployee = Employee::factory()->create([
        'user_id' => $managerUser->id,
        'role' => \App\EmployeeRole::Manager
    ]);
    
    $otherEmployee = Employee::factory()->create();
    
    // Create payroll for other employee
    Payroll::create([
        'employee_id' => $otherEmployee->id,
        'reference_month' => now()->startOfMonth(),
        'base_salary' => 1000,
        'total_days_worked' => 20,
        'absences_count' => 0,
        'late_count' => 0,
        'early_exit_count' => 0,
        'total_deductions' => 0,
        'total_bonus' => 0,
        'net_salary' => 1000,
    ]);

    Attendance::create([
        'employee_id' => $otherEmployee->id,
        'type' => AttendanceType::CheckIn,
        'recorded_at' => now()
    ]);

    // Test Payroll Index
    $response = $this->actingAs($managerUser)->get(route('payrolls.index', [
        'year' => now()->year,
        'month' => now()->month
    ]));
    $response->assertStatus(200);
    
    // Test Attendance Index
    $response = $this->actingAs($managerUser)->get(route('attendances.index'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('employees', 2)
    );
});

