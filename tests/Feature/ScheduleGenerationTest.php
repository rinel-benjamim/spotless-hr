<?php

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;

test('can generate monthly schedules for all employees', function () {
    $user = User::factory()->create(['role' => 'admin']);
    
    // Criar turnos
    $shift1 = Shift::factory()->create(['name' => 'Manhã']);
    $shift2 = Shift::factory()->create(['name' => 'Tarde']);
    
    // Criar funcionários com turnos
    $employee1 = Employee::factory()->create([
        'status' => 'active',
        'shift_id' => $shift1->id,
    ]);
    
    $employee2 = Employee::factory()->create([
        'status' => 'active', 
        'shift_id' => $shift2->id,
    ]);
    
    $response = $this->actingAs($user)->post('/schedules', [
        'employee_ids' => [$employee1->id, $employee2->id],
        'year' => 2024,
        'month' => 1,
        'generate_month' => true,
        // shift_id não enviado - deve usar turno padrão de cada funcionário
    ]);
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    // Verificar se foram criadas escalas para janeiro 2024 (31 dias)
    $schedulesEmployee1 = Schedule::where('employee_id', $employee1->id)
        ->whereYear('date', 2024)
        ->whereMonth('date', 1)
        ->count();
        
    $schedulesEmployee2 = Schedule::where('employee_id', $employee2->id)
        ->whereYear('date', 2024)
        ->whereMonth('date', 1)
        ->count();
    
    expect($schedulesEmployee1)->toBe(31);
    expect($schedulesEmployee2)->toBe(31);
    
    // Verificar se os turnos padrão foram usados
    $schedule1 = Schedule::where('employee_id', $employee1->id)->first();
    $schedule2 = Schedule::where('employee_id', $employee2->id)->first();
    
    expect($schedule1->shift_id)->toBe($shift1->id);
    expect($schedule2->shift_id)->toBe($shift2->id);
});

test('can generate monthly schedules with specific shift', function () {
    $user = User::factory()->create(['role' => 'admin']);
    
    $defaultShift = Shift::factory()->create(['name' => 'Padrão']);
    $specificShift = Shift::factory()->create(['name' => 'Específico']);
    
    $employee = Employee::factory()->create([
        'status' => 'active',
        'shift_id' => $defaultShift->id,
    ]);
    
    $response = $this->actingAs($user)->post('/schedules', [
        'employee_ids' => [$employee->id],
        'year' => 2024,
        'month' => 2,
        'generate_month' => true,
        'shift_id' => $specificShift->id, // Turno específico
    ]);
    
    $response->assertRedirect();
    
    // Verificar se foi usado o turno específico, não o padrão
    $schedule = Schedule::where('employee_id', $employee->id)->first();
    expect($schedule->shift_id)->toBe($specificShift->id);
});

test('working days are set correctly for weekdays', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $shift = Shift::factory()->create();
    $employee = Employee::factory()->create([
        'status' => 'active',
        'shift_id' => $shift->id,
    ]);
    
    $response = $this->actingAs($user)->post('/schedules', [
        'employee_ids' => [$employee->id],
        'year' => 2024,
        'month' => 1, // Janeiro 2024
        'generate_month' => true,
    ]);
    
    $response->assertRedirect();
    
    // Verificar se foram criadas 31 escalas para janeiro
    $totalSchedules = Schedule::where('employee_id', $employee->id)
        ->whereYear('date', 2024)
        ->whereMonth('date', 1)
        ->count();
    expect($totalSchedules)->toBe(31);
    
    // Verificar alguns dias específicos de janeiro 2024
    // 1 de janeiro de 2024 é segunda-feira (dia útil)
    $monday = Schedule::where('employee_id', $employee->id)
        ->where('date', '2024-01-01')
        ->first();
    expect($monday)->not->toBeNull();
    expect($monday->is_working_day)->toBe(true);
    
    // 6 de janeiro de 2024 é sábado (não é dia útil)
    $saturday = Schedule::where('employee_id', $employee->id)
        ->where('date', '2024-01-06')
        ->first();
    expect($saturday)->not->toBeNull();
    expect($saturday->is_working_day)->toBe(false);
});

test('generate_month false should create single day schedule', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $shift = Shift::factory()->create();
    $employee = Employee::factory()->create([
        'status' => 'active',
        'shift_id' => $shift->id,
    ]);
    
    $response = $this->actingAs($user)->post('/schedules', [
        'employee_ids' => [$employee->id],
        'date' => '2024-01-15',
        'shift_id' => $shift->id,
        'is_working_day' => true,
        'generate_month' => false,
    ]);
    
    $response->assertRedirect();
    
    // Deve criar apenas 1 escala
    expect(Schedule::count())->toBe(1);
    
    $schedule = Schedule::first();
    expect($schedule->date->format('Y-m-d'))->toBe('2024-01-15');
    expect($schedule->is_working_day)->toBe(true);
});