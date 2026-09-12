<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarUpgrade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarUpgrade>
 */
class CarUpgradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $part = fake()->randomElement(['engine', 'suspension', 'brakes', 'reliability']);
        $increases = match ($part) {
            'engine' => ['speed' => fake()->numberBetween(2, 5), 'acceleration' => fake()->numberBetween(2, 4)],
            'suspension' => ['handling' => fake()->numberBetween(3, 6)],
            'brakes' => ['braking' => fake()->numberBetween(3, 6)],
            'reliability' => ['reliability' => fake()->numberBetween(4, 8)],
        };

        return [
            'car_id' => Car::factory(),
            'part_type' => $part,
            'level' => fake()->numberBetween(1, 3),
            'cost' => fake()->numberBetween(2000, 10000),
            'stat_increases' => $increases,
        ];
    }
}
