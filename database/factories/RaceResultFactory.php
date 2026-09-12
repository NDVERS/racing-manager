<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RaceResult>
 */
class RaceResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'race_id' => Race::factory(),
            'team_id' => Team::factory(),
            'car_id' => Car::factory(),
            'driver_id' => Driver::factory(),
            'position' => fake()->numberBetween(1, 10),
            'race_time' => sprintf('%02d:%02d.%03d', fake()->numberBetween(15, 25), fake()->numberBetween(0, 59), fake()->numberBetween(0, 999)),
            'prize_money' => fake()->numberBetween(0, 15000),
            'reputation_earned' => fake()->numberBetween(0, 10),
            'strategy' => fake()->randomElement(['balanced', 'push', 'conserve']),
            'status' => 'finished',
            'simulation_log' => ['total_laps' => 12],
        ];
    }
}
