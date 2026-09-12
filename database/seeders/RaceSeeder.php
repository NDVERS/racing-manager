<?php

namespace Database\Seeders;

use App\Models\Race;
use Illuminate\Database\Seeder;

class RaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $races = [
            [
                'name' => 'Cimahi GP',
                'location' => 'Cimahi',
                'laps' => 12,
                'track_type' => 'technical',
                'weather' => 'dry',
                'entry_fee' => 1000,
                'prize_pool' => 15000,
            ],
            [
                'name' => 'Sentul Speed Circuit',
                'location' => 'Sentul',
                'laps' => 15,
                'track_type' => 'high_speed',
                'weather' => 'dry',
                'entry_fee' => 1500,
                'prize_pool' => 22000,
            ],
            [
                'name' => 'Mandalika Coastal Challenge',
                'location' => 'Mandalika',
                'laps' => 14,
                'track_type' => 'balanced',
                'weather' => 'wet',
                'entry_fee' => 2000,
                'prize_pool' => 30000,
            ],
            [
                'name' => 'Bandung Hill Climb GP',
                'location' => 'Bandung',
                'laps' => 10,
                'track_type' => 'technical',
                'weather' => 'wet',
                'entry_fee' => 1200,
                'prize_pool' => 18000,
            ],
            [
                'name' => 'Jakarta Night Prix',
                'location' => 'Jakarta',
                'laps' => 16,
                'track_type' => 'high_speed',
                'weather' => 'dry',
                'entry_fee' => 2500,
                'prize_pool' => 40000,
            ],
        ];

        foreach ($races as $raceData) {
            Race::firstOrCreate(
                ['name' => $raceData['name']],
                $raceData
            );
        }
    }
}
