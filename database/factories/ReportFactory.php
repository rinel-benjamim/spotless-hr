<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['attendance', 'payroll', 'schedule', 'general']),
            'data' => ['month' => $this->faker->date('Y-m')],
            'status' => 'completed',
            'generated_at' => now(),
        ];
    }
}
