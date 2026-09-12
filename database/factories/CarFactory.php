<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
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
            'name' => fake()->randomElement(['Apex Cyclone', 'Kuro GT', 'Vortex R1', 'Phantom GTS', 'Falcon RS']),
            'speed' => fake()->numberBetween(50, 85),
            'acceleration' => fake()->numberBetween(50, 85),
            'handling' => fake()->numberBetween(50, 85),
            'braking' => fake()->numberBetween(50, 85),
            'reliability' => fake()->numberBetween(55, 90),
            'level' => 1,
            'purchase_price' => fake()->numberBetween(15000, 50000),
        ];
    }

    /**
     * Associate the car with a team.
     */
    public function forTeam(?Team $team = null): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team?->id ?? Team::factory(),
        ]);
    }
}
