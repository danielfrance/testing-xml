<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Filing>
 */
class FilingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filing_type_id' => fake()->numberBetween(1, 4),
            'status' => fake()->randomElement(['Draft', 'Submitted', 'Approved', 'Rejected']),
        ];
    }
}
