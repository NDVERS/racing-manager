<?php

namespace Database\Factories;

use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sponsor>
 */
class SponsorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Motorsport',
            'description' => fake()->sentence(),
            'tier' => 'secondary',
            'min_reputation' => fake()->numberBetween(0, 50),
            'signing_bonus' => fake()->numberBetween(10000, 30000),
            'target_objective' => fake()->randomElement(['finish_race', 'finish_top_5', 'finish_top_3', 'score_fastest_lap']),
            'bonus_per_race' => fake()->numberBetween(2000, 5000),
            'duration_races' => fake()->numberBetween(3, 5),
        ];
    }
}
