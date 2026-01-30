<?php

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;

test('can generate payrolls for all active employees', function () {
    $user = User::factory()->create(['role' => 'admin']);
    
    // Criar funcionários ativos com salário
    $employees = Employee::factory()->count(3)->create([
        'status' => 'active',
        'base_salary' => 50000.00,
    ]);
    
    // Criar um funcionário inativo (não deve gerar folha)
    Employee::factory()->create([
        'status' => 'inactive',
        'base_salary' => 50000.00,
    ]);
    
    // Criar um funcionário sem salário (não deve gerar folha)
    Employee::factory()->create([
        'status' => 'active',
        'base_salary' => null,
    ]);
    
    $response = $this->actingAs($user)->post('/payrolls', [
        'year' => 2024,
        'month' => 1,
        'generate_all' => true,
    ]);
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    // Verificar se foram geradas 3 folhas (apenas para funcionários ativos com salário)
    expect(Payroll::count())->toBe(3);
    
    // Verificar se todas as folhas são do mês correto
    $payrolls = Payroll::all();
    foreach ($payrolls as $payroll) {
        expect($payroll->reference_month->year)->toBe(2024);
        expect($payroll->reference_month->month)->toBe(1);
    }
});

test('can generate payroll for single employee', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $employee = Employee::factory()->create([
        'status' => 'active',
        'base_salary' => 50000.00,
    ]);
    
    $response = $this->actingAs($user)->post('/payrolls', [
        'year' => 2024,
        'month' => 1,
        'employee_id' => $employee->id,
        'generate_all' => false,
    ]);
    
    $response->assertRedirect();
    $response->assertSessionHas('success');
    
    expect(Payroll::count())->toBe(1);
    expect(Payroll::first()->employee_id)->toBe($employee->id);
});

test('generate_all false should not generate for all employees', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $employee = Employee::factory()->create([
        'status' => 'active',
        'base_salary' => 50000.00,
    ]);
    
    // Criar outros funcionários
    Employee::factory()->count(2)->create([
        'status' => 'active',
        'base_salary' => 50000.00,
    ]);
    
    $response = $this->actingAs($user)->post('/payrolls', [
        'year' => 2024,
        'month' => 1,
        'employee_id' => $employee->id,
        'generate_all' => false,
    ]);
    
    $response->assertRedirect();
    
    // Deve gerar apenas 1 folha, não 3
    expect(Payroll::count())->toBe(1);
});