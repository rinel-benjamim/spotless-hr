<?php

use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use App\UserRole;
use App\EmployeeRole;
use App\ContractType;
use App\Models\Schedule;
use App\Models\Payroll;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('automated user creation via employee store', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $shift = Shift::factory()->create();

    $response = $this->actingAs($admin)->post(route('employees.store'), [
        'full_name' => 'Automated User Test',
        'role' => EmployeeRole::Employee->value,
        'contract_type' => ContractType::FullTime->value,
        'shift_id' => $shift->id,
        'base_salary' => 1500,
        'email' => 'auto@example.com',
        'password' => 'password123',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('employees.index'));
    $this->assertDatabaseHas('users', ['email' => 'auto@example.com']);
    $user = User::where('email', 'auto@example.com')->first();
    $this->assertDatabaseHas('employees', ['full_name' => 'Automated User Test', 'user_id' => $user->id]);
});

test('bulk schedule creation via schedules store', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $employees = Employee::factory()->count(3)->create();
    $shift = Shift::factory()->create();
    $date = now()->addDays(2)->format('Y-m-d');

    $response = $this->actingAs($admin)->post(route('schedules.store'), [
        'employee_ids' => $employees->pluck('id')->map(fn($id) => (string)$id)->toArray(),
        'date' => $date,
        'shift_id' => $shift->id,
        'is_working_day' => true,
    ]);

    $response->assertRedirect();
    foreach ($employees as $employee) {
        $this->assertDatabaseHas('schedules', [
            'employee_id' => $employee->id,
            'date' => $date,
        ]);
    }
});

test('manager can access admin dashboard inertia component', function () {
    $managerUser = User::factory()->create(['role' => UserRole::Employee]);
    $managerEmployee = Employee::factory()->create([
        'user_id' => $managerUser->id,
        'role' => EmployeeRole::Manager
    ]);

    $this->actingAs($managerUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Admin')
            ->has('stats')
        );
});

test('generate payroll for all employees', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Employee::factory()->count(5)->create(['base_salary' => 2000, 'status' => 'active']);

    $response = $this->actingAs($admin)->post(route('payrolls.store'), [
        'year' => now()->year,
        'month' => now()->month,
        'generate_all' => true,
    ]);

    $response->assertRedirect();
    $this->assertEquals(5, Payroll::count());
});
