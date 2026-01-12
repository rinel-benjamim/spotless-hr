<?php

namespace Database\Factories;

use App\ContractType;
use App\EmployeeRole;
use App\EmployeeStatus;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'employee_code' => 'EMP'.fake()->unique()->numberBetween(1000, 9999),
            'full_name' => fake()->name(),
            'role' => fake()->randomElement(EmployeeRole::cases()),
            'contract_type' => fake()->randomElement(ContractType::cases()),
            'shift_id' => Shift::factory(),
            'status' => EmployeeStatus::Active,
        ];
    }

    public function withUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatus::Inactive,
        ]);
    }
}
