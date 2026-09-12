<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'type' => fake()->randomElement(['race_prize', 'entry_fee', 'upgrade', 'repair']),
            'amount' => fake()->randomElement([15000, 10000, -1000, -5000, -2500]),
            'balance_after' => 50000,
            'description' => fake()->sentence(3),
            'reference_id' => null,
            'reference_type' => null,
        ];
    }
}
