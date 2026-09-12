<?php

namespace Database\Seeders;

use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class SponsorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sponsors = [
            // Primary Sponsors (Title Partners)
            [
                'name' => 'Apex Energy & Oil',
                'description' => 'Global synthetic fuel supplier providing entry-level funding and race-completion incentives.',
                'tier' => 'primary',
                'min_reputation' => 0,
                'signing_bonus' => 15000,
                'target_objective' => 'finish_race',
                'bonus_per_race' => 2000,
                'duration_races' => 4,
            ],
            [
                'name' => 'Quantum Telematics',
                'description' => 'High-bandwidth telemetry network offering competitive bonuses for top-half finishes.',
                'tier' => 'primary',
                'min_reputation' => 30,
                'signing_bonus' => 35000,
                'target_objective' => 'finish_top_5',
                'bonus_per_race' => 4500,
                'duration_races' => 5,
            ],
            [
                'name' => 'Hyperion Cloud Systems',
                'description' => 'Elite computing conglomerate demanding podium prestige and offering heavyweight capital.',
                'tier' => 'primary',
                'min_reputation' => 70,
                'signing_bonus' => 60000,
                'target_objective' => 'finish_top_3',
                'bonus_per_race' => 8500,
                'duration_races' => 5,
            ],

            // Secondary / Technical Sponsors
            [
                'name' => 'K-Battery Tech',
                'description' => 'Hybrid powertrain innovators paying performance incentives for strong midfield and top 5 finishes.',
                'tier' => 'secondary',
                'min_reputation' => 15,
                'signing_bonus' => 10000,
                'target_objective' => 'finish_top_5',
                'bonus_per_race' => 2500,
                'duration_races' => 3,
            ],
            [
                'name' => 'Horizon Global Logistics',
                'description' => 'International freight leader rewarding championship podiums across endurance rounds.',
                'tier' => 'secondary',
                'min_reputation' => 45,
                'signing_bonus' => 20000,
                'target_objective' => 'finish_top_3',
                'bonus_per_race' => 4000,
                'duration_races' => 4,
            ],
            [
                'name' => 'Vertex Precision Aero',
                'description' => 'Aerospace engineering firm offering massive speed bonuses for setting the official fastest lap.',
                'tier' => 'secondary',
                'min_reputation' => 60,
                'signing_bonus' => 25000,
                'target_objective' => 'score_fastest_lap',
                'bonus_per_race' => 6000,
                'duration_races' => 3,
            ],
        ];

        foreach ($sponsors as $sponsor) {
            Sponsor::updateOrCreate(
                ['name' => $sponsor['name']],
                $sponsor
            );
        }
    }
}
