<?php

use App\Models\Employee;
use App\Models\User;

test('payroll navigation handles date parameters correctly', function () {
    $user = User::factory()->create(['role' => 'admin']);
    
    // Teste com parâmetros válidos
    $response = $this->actingAs($user)->get('/payrolls?year=2024&month=1');
    $response->assertStatus(200);
    
    // Teste com parâmetros inválidos (devem ser corrigidos)
    $response = $this->actingAs($user)->get('/payrolls?year=abc&month=xyz');
    $response->assertStatus(200);
    
    // Teste com valores extremos
    $response = $this->actingAs($user)->get('/payrolls?year=1900&month=15');
    $response->assertStatus(200);
});

test('calendar navigation handles date parameters correctly', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $employee = Employee::factory()->create();
    
    // Teste com parâmetros válidos
    $response = $this->actingAs($user)->get("/reports/calendar/{$employee->id}?year=2024&month=1");
    $response->assertStatus(200);
    
    // Teste com parâmetros inválidos
    $response = $this->actingAs($user)->get("/reports/calendar/{$employee->id}?year=abc&month=xyz");
    $response->assertStatus(200);
    
    // Teste com valores extremos
    $response = $this->actingAs($user)->get("/reports/calendar/{$employee->id}?year=1900&month=15");
    $response->assertStatus(200);
});

test('employee report navigation handles date parameters correctly', function () {
    $user = User::factory()->create(['role' => 'admin']);
    $employee = Employee::factory()->create();
    
    // Teste com parâmetros válidos
    $response = $this->actingAs($user)->get("/reports/employee/{$employee->id}?year=2024&month=1");
    $response->assertStatus(200);
    
    // Teste com parâmetros inválidos
    $response = $this->actingAs($user)->get("/reports/employee/{$employee->id}?year=abc&month=xyz");
    $response->assertStatus(200);
    
    // Teste com valores extremos
    $response = $this->actingAs($user)->get("/reports/employee/{$employee->id}?year=1900&month=15");
    $response->assertStatus(200);
});