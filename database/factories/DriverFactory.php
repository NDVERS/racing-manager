<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => null,
            'name' => fake()->name(),
            'pace' => fake()->numberBetween(50, 85),
            'cornering' => fake()->numberBetween(50, 85),
            'consistency' => fake()->numberBetween(50, 85),
            'overtaking' => fake()->numberBetween(50, 85),
            'defensive' => fake()->numberBetween(50, 85),
            'racecraft' => fake()->numberBetween(50, 85),
            'experience' => fake()->numberBetween(20, 80),
            'salary' => fake()->numberBetween(800, 4000),
        ];
    }

    /**
     * Associate the driver with a team.
     */
    public function forTeam(?Team $team = null): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team?->id ?? Team::factory(),
        ]);
    }

    /**
     * Indicate that the driver is the designated lead driver.
     */
    public function lead(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_lead' => true,
        ]);
    }
}
