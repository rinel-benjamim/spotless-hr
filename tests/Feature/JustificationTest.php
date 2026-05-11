<?php

use App\Models\Employee;
use App\Models\User;
use App\UserRole;

beforeEach(function () {
    $this->manager = User::factory()->create(['role' => UserRole::Manager]);
    Employee::factory()->create(['user_id' => $this->manager->id]);

    $this->employeeUser = User::factory()->create(['role' => UserRole::Employee]);
    Employee::factory()->create(['user_id' => $this->employeeUser->id]);

    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

describe('Justification Creation', function () {
    it('manager can access create form', function () {
        $response = $this->actingAs($this->manager)
            ->get(route('justifications.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Justifications/Create')
        );
    });

    it('manager sees all employees in dropdown', function () {
        $response = $this->actingAs($this->manager)
            ->get(route('justifications.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('canSelectAllEmployees', true)
        );
    });

    it('manager can create justification for another employee', function () {
        $targetEmployee = Employee::factory()->create();

        $response = $this->actingAs($this->manager)->post(route('justifications.store'), [
            'employee_id' => $targetEmployee->id,
            'reason' => 'Médico',
        ]);

        $response->assertRedirect(route('justifications.index'));
        $this->assertDatabaseHas('justifications', [
            'employee_id' => $targetEmployee->id,
            'justified_by' => $this->manager->id,
            'status' => 'pending',
            'reason' => 'Médico',
        ]);
    });

    it('manager cannot create justification for non-existent employee', function () {
        $response = $this->actingAs($this->manager)->post(route('justifications.store'), [
            'employee_id' => 99999,
            'reason' => 'Teste',
        ]);

        $response->assertSessionHasErrors('employee_id');
    });

    it('admin cannot access create form', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('justifications.create'));

        $response->assertForbidden();
    });

    it('employee can only see themselves in create form', function () {
        $response = $this->actingAs($this->employeeUser)
            ->get(route('justifications.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('canSelectAllEmployees', false)
        );
    });

    it('employee cannot create justification for another employee', function () {
        $anotherEmployee = Employee::factory()->create();

        $response = $this->actingAs($this->employeeUser)->post(route('justifications.store'), [
            'employee_id' => $anotherEmployee->id,
            'reason' => 'Médico',
        ]);

        $response->assertForbidden();
    });

    it('employee can create justification for themselves', function () {
        $response = $this->actingAs($this->employeeUser)->post(route('justifications.store'), [
            'employee_id' => $this->employeeUser->employee->id,
            'reason' => 'Consulta médica',
        ]);

        $response->assertRedirect(route('justifications.index'));
        $this->assertDatabaseHas('justifications', [
            'employee_id' => $this->employeeUser->employee->id,
            'justified_by' => $this->employeeUser->id,
            'status' => 'pending',
            'reason' => 'Consulta médica',
        ]);
    });

    it('manager can create justification for themselves', function () {
        $response = $this->actingAs($this->manager)->post(route('justifications.store'), [
            'employee_id' => $this->manager->employee->id,
            'reason' => 'Consulta dentária',
        ]);

        $response->assertRedirect(route('justifications.index'));
        $this->assertDatabaseHas('justifications', [
            'employee_id' => $this->manager->employee->id,
            'justified_by' => $this->manager->id,
            'status' => 'pending',
        ]);
    });
});
