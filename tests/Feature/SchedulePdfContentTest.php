<?php

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;

test('pdf content shows working days correctly', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $shift = Shift::factory()->create([
        'name' => 'Manhã',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00'
    ]);
    $employee = Employee::factory()->create([
        'status' => 'active',
        'shift_id' => $shift->id,
        'full_name' => 'João Silva'
    ]);
    
    // Criar escalas específicas para testar
    Schedule::create([
        'employee_id' => $employee->id,
        'date' => '2024-01-01', // Segunda - trabalho
        'shift_id' => $shift->id,
        'is_working_day' => true,
    ]);
    
    Schedule::create([
        'employee_id' => $employee->id,
        'date' => '2024-01-02', // Terça - trabalho
        'shift_id' => $shift->id,
        'is_working_day' => true,
    ]);
    
    Schedule::create([
        'employee_id' => $employee->id,
        'date' => '2024-01-06', // Sábado - folga
        'shift_id' => $shift->id,
        'is_working_day' => false,
    ]);
    
    // Capturar a view renderizada
    $schedules = Schedule::with(['employee', 'shift'])
        ->whereYear('date', 2024)
        ->whereMonth('date', 1)
        ->get()
        ->groupBy('employee_id');
    
    $viewData = [
        'schedules' => $schedules,
        'year' => 2024,
        'month' => 1,
        'employee' => null,
        'monthName' => 'Janeiro 2024'
    ];
    
    $html = view('pdf.schedules', $viewData)->render();
    
    // Verificar se o nome do funcionário aparece
    expect($html)->toContain('João Silva');
    
    // Verificar se o nome do turno aparece
    expect($html)->toContain('Manhã');
    
    // Verificar se FOLGA aparece para dias não úteis
    expect($html)->toContain('FOLGA');
    
    // Verificar se o horário do turno aparece
    expect($html)->toContain('08:00');
});