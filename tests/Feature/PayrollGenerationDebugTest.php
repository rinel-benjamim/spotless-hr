<?php

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;

test('can generate payrolls for january 2026 scenario', function () {
    $user = User::factory()->create(['role' => 'admin']);
    
    // Criar funcionários ativos com salário (simulando cenário real)
    $employees = Employee::factory()->count(5)->create([
        'status' => 'active',
        'base_salary' => 50000.00,
    ]);
    
    // Fazer a requisição exatamente como no frontend
    $response = $this->actingAs($user)->post('/payrolls', [
        'year' => 2026,
        'month' => 1,
        'generate_all' => true,
    ]);
    
    // Debug: verificar se há redirecionamento
    expect($response->status())->toBe(302);
    
    // Verificar se foram geradas folhas
    $payrollCount = Payroll::whereYear('reference_month', 2026)
        ->whereMonth('reference_month', 1)
        ->count();
        
    expect($payrollCount)->toBe(5);
    
    // Verificar se o redirecionamento está correto
    $response->assertRedirect('/payrolls?year=2026&month=1');
    $response->assertSessionHas('success');
});

test('debug generate_all parameter handling', function () {
    $user = User::factory()->create(['role' => 'admin']);
    
    // Testar diferentes formas de enviar generate_all
    $testCases = [
        ['generate_all' => true],
        ['generate_all' => 'true'],
        ['generate_all' => 1],
        ['generate_all' => '1'],
    ];
    
    foreach ($testCases as $index => $testData) {
        // Limpar folhas anteriores
        Payroll::truncate();
        
        // Criar funcionário para este teste
        Employee::factory()->create([
            'status' => 'active',
            'base_salary' => 50000.00,
        ]);
        
        $requestData = array_merge([
            'year' => 2026,
            'month' => $index + 1, // Mês diferente para cada teste
        ], $testData);
        
        $response = $this->actingAs($user)->post('/payrolls', $requestData);
        
        expect($response->status())->toBe(302, "Teste {$index} falhou");
        expect(Payroll::count())->toBe(1, "Teste {$index}: Folha não foi gerada");
    }
});