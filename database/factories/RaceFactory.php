<?php

namespace Database\Factories;

use App\Models\Race;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Race>
 */
class RaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city().' GP',
            'location' => fake()->city(),
            'laps' => fake()->randomElement([10, 12, 15, 20]),
            'track_type' => fake()->randomElement(['high_speed', 'technical', 'balanced']),
            'weather' => fake()->randomElement(['dry', 'wet']),
            'entry_fee' => fake()->randomElement([500, 1000, 1500, 2000]),
            'prize_pool' => fake()->randomElement([10000, 15000, 25000, 40000]),
        ];
    }
}
