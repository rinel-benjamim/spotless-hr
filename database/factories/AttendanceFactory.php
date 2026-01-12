<?php

namespace Database\Factories;

use App\AttendanceType;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'type' => fake()->randomElement(AttendanceType::cases()),
            'recorded_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function checkIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AttendanceType::CheckIn,
        ]);
    }

    public function checkOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AttendanceType::CheckOut,
        ]);
    }
}
