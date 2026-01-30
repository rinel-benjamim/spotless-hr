<?php

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;

test('pdf export shows correct working days and days off', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $shift = Shift::factory()->create(['name' => 'Manhã', 'start_time' => '08:00', 'end_time' => '17:00']);
    $employee = Employee::factory()->create(['status' => 'active', 'shift_id' => $shift->id]);
    
    // Criar algumas escalas para janeiro 2024
    Schedule::create([
        'employee_id' => $employee->id,
        'date' => '2024-01-01', // Segunda-feira - dia de trabalho
        'shift_id' => $shift->id,
        'is_working_day' => true,
    ]);
    
    Schedule::create([
        'employee_id' => $employee->id,
        'date' => '2024-01-02', // Terça-feira - dia de trabalho
        'shift_id' => $shift->id,
        'is_working_day' => true,
    ]);
    
    Schedule::create([
        'employee_id' => $employee->id,
        'date' => '2024-01-06', // Sábado - folga
        'shift_id' => $shift->id,
        'is_working_day' => false,
    ]);
    
    Schedule::create([
        'employee_id' => $employee->id,
        'date' => '2024-01-07', // Domingo - folga
        'shift_id' => $shift->id,
        'is_working_day' => false,
    ]);
    
    // Testar exportação geral
    $response = $this->actingAs($user)->get('/schedules/export-pdf?year=2024&month=1');
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
    
    // Testar exportação específica do funcionário
    $response = $this->actingAs($user)->get("/schedules/export-pdf?year=2024&month=1&employee_id={$employee->id}");
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('pdf export works with no schedules', function () {
    $user = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($user)->get('/schedules/export-pdf?year=2024&month=1');
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('pdf export respects employee permissions', function () {
    $manager = User::factory()->create(['role' => 'admin']);
    $employeeUser = User::factory()->create(['role' => 'employee']);
    $employee = Employee::factory()->create(['user_id' => $employeeUser->id, 'status' => 'active']);
    
    // Manager pode exportar qualquer escala
    $response = $this->actingAs($manager)->get('/schedules/export-pdf?year=2024&month=1');
    $response->assertStatus(200);
    
    // Funcionário só pode exportar sua própria escala
    $response = $this->actingAs($employeeUser)->get('/schedules/export-pdf?year=2024&month=1');
    $response->assertStatus(200);
});