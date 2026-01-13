<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('employees index returns an Inertia response', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('employees.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Employees/Index')
        );
});

test('employees create is forbidden for non-admins and accessible for admin', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('employees.create'))
        ->assertForbidden();

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('employees.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Employees/Create')
        );
});
