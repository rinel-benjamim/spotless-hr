<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    public function definition(): array
    {
        $startHour = fake()->numberBetween(6, 14);
        $endHour = $startHour + 8;

        return [
            'name' => fake()->randomElement(['Manhã', 'Tarde', 'Noite']),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $endHour),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
