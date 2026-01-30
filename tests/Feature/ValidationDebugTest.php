<?php

use App\Models\Employee;
use App\Models\User;

test('investigate validation issue', function () {
    $user = User::factory()->create(['role' => 'admin']);
    Employee::factory()->create(['status' => 'active', 'base_salary' => 50000]);
    
    // Testar com generate_all como string 'true'
    $response = $this->actingAs($user)->post('/payrolls', [
        'year' => 2026,
        'month' => 1,
        'generate_all' => 'true',
    ]);
    
    // Verificar se há erros de validação
    if ($response->status() !== 302) {
        dump('Status:', $response->status());
        dump('Response:', $response->getContent());
    }
    
    expect($response->status())->toBe(302);
});