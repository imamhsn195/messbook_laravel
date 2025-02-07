<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MessGroup>
 */
class MessGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::today()->subDays(rand(1, 30));
        $totalDays = rand(7, 30);
        $endDate = (clone $startDate)->addDays($totalDays);
        return [
            'name' => $this->faker->unique()->word(),
            'fixed_cost' => 70,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }
}
