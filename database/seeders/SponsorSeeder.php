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
            // ==========================================
            // 1. Primary Sponsors (Title Partners)
            // ==========================================
            [
                'name' => 'Apex Energy & Synthetic Fuels',
                'description' => 'Global synthetic fuel and lubricant supplier providing essential baseline funding and race-completion incentives.',
                'tier' => 'primary',
                'min_reputation' => 0,
                'signing_bonus' => 15000,
                'target_objective' => 'finish_race',
                'bonus_per_race' => 2000,
                'duration_races' => 4,
            ],
            [
                'name' => 'NovaTech Semiconductors',
                'description' => 'Pioneering automotive microprocessor manufacturer offering generous milestone grants for top 5 finishes.',
                'tier' => 'primary',
                'min_reputation' => 15,
                'signing_bonus' => 25000,
                'target_objective' => 'finish_top_5',
                'bonus_per_race' => 3500,
                'duration_races' => 4,
            ],
            [
                'name' => 'Quantum Telematics Global',
                'description' => 'High-bandwidth telemetry network offering competitive bonuses for top-half finishes.',
                'tier' => 'primary',
                'min_reputation' => 30,
                'signing_bonus' => 35000,
                'target_objective' => 'finish_top_5',
                'bonus_per_race' => 4500,
                'duration_races' => 5,
            ],
            [
                'name' => 'Vanguard Private Equity',
                'description' => 'Global institutional capital fund investing heavily into ambitious racing constructors aiming for podium positions.',
                'tier' => 'primary',
                'min_reputation' => 50,
                'signing_bonus' => 48000,
                'target_objective' => 'finish_top_3',
                'bonus_per_race' => 6500,
                'duration_races' => 4,
            ],
            [
                'name' => 'Hyperion Cloud Systems',
                'description' => 'Elite enterprise cloud conglomerate demanding podium prestige and offering heavyweight championship capital.',
                'tier' => 'primary',
                'min_reputation' => 70,
                'signing_bonus' => 60000,
                'target_objective' => 'finish_top_3',
                'bonus_per_race' => 8500,
                'duration_races' => 5,
            ],
            [
                'name' => 'Aetherius Quantum Power',
                'description' => 'Next-generation zero-emission fusion consortium providing enormous financial backing to world-class teams.',
                'tier' => 'primary',
                'min_reputation' => 90,
                'signing_bonus' => 80000,
                'target_objective' => 'finish_top_3',
                'bonus_per_race' => 12000,
                'duration_races' => 5,
            ],

            // ==========================================
            // 2. Secondary Sponsors (Technical Partners)
            // ==========================================
            [
                'name' => 'VoltCore Hybrid Dynamics',
                'description' => 'Starter electric-hybrid component developer supporting emerging constructors with steady race-finish payouts.',
                'tier' => 'secondary',
                'min_reputation' => 0,
                'signing_bonus' => 8000,
                'target_objective' => 'finish_race',
                'bonus_per_race' => 1500,
                'duration_races' => 3,
            ],
            [
                'name' => 'K-Battery Tech',
                'description' => 'Hybrid powertrain innovators paying performance incentives for strong midfield and top 5 finishes.',
                'tier' => 'secondary',
                'min_reputation' => 15,
                'signing_bonus' => 12000,
                'target_objective' => 'finish_top_5',
                'bonus_per_race' => 2500,
                'duration_races' => 3,
            ],
            [
                'name' => 'CyberKinetics AI Systems',
                'description' => 'Predictive racecraft telemetry laboratory incentivizing consistent top 5 scoring performances.',
                'tier' => 'secondary',
                'min_reputation' => 25,
                'signing_bonus' => 16000,
                'target_objective' => 'finish_top_5',
                'bonus_per_race' => 3000,
                'duration_races' => 4,
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
            [
                'name' => 'Titanium Composites Lab',
                'description' => 'Ultra-lightweight chassis metallurgy supplier rewarding frequent podium celebrations.',
                'tier' => 'secondary',
                'min_reputation' => 75,
                'signing_bonus' => 32000,
                'target_objective' => 'finish_top_3',
                'bonus_per_race' => 5500,
                'duration_races' => 4,
            ],

            // ==========================================
            // 3. Associate Suppliers & Component Sponsors
            // ==========================================
            [
                'name' => 'BremboTech Braking Solutions',
                'description' => 'Precision brake caliper supplier providing easy starting grants for reliable race finishers.',
                'tier' => 'secondary',
                'min_reputation' => 5,
                'signing_bonus' => 6000,
                'target_objective' => 'finish_race',
                'bonus_per_race' => 1200,
                'duration_races' => 3,
            ],
            [
                'name' => 'PirelliCore Motorsport Rubber',
                'description' => 'High-degradation racing compound manufacturer rewarding strategic top 5 grid execution.',
                'tier' => 'secondary',
                'min_reputation' => 20,
                'signing_bonus' => 14000,
                'target_objective' => 'finish_top_5',
                'bonus_per_race' => 2800,
                'duration_races' => 3,
            ],
            [
                'name' => 'Spectre Lubricants & Additives',
                'description' => 'High-temperature thermal powertrain additive specialists rewarding competitive top 5 placings.',
                'tier' => 'secondary',
                'min_reputation' => 35,
                'signing_bonus' => 18000,
                'target_objective' => 'finish_top_5',
                'bonus_per_race' => 3200,
                'duration_races' => 4,
            ],
            [
                'name' => 'AeroShield Nanocoatings',
                'description' => 'Hydrophobic friction-reducing surface coating technology firm paying premier rewards for fastest laps.',
                'tier' => 'secondary',
                'min_reputation' => 55,
                'signing_bonus' => 22000,
                'target_objective' => 'score_fastest_lap',
                'bonus_per_race' => 5000,
                'duration_races' => 3,
            ],
            [
                'name' => 'Chronos Swiss Chronographs',
                'description' => 'Luxury official timekeeper brand rewarding podium excellence with high-value commercial bonuses.',
                'tier' => 'secondary',
                'min_reputation' => 65,
                'signing_bonus' => 28000,
                'target_objective' => 'finish_top_3',
                'bonus_per_race' => 5000,
                'duration_races' => 4,
            ],
            [
                'name' => 'Galactic Orbit Communications',
                'description' => 'Satellite uplink provider delivering premium signing bonuses for lightning-quick fastest lap heroics.',
                'tier' => 'secondary',
                'min_reputation' => 85,
                'signing_bonus' => 38000,
                'target_objective' => 'score_fastest_lap',
                'bonus_per_race' => 8000,
                'duration_races' => 4,
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
