<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'Alex Carter',
                'pace' => 65,
                'cornering' => 64,
                'consistency' => 70,
                'overtaking' => 62,
                'defensive' => 63,
                'racecraft' => 66,
                'experience' => 40,
                'salary' => 1200,
            ],
            [
                'name' => 'Liam Vance',
                'pace' => 74,
                'cornering' => 60,
                'consistency' => 62,
                'overtaking' => 75,
                'defensive' => 58,
                'racecraft' => 65,
                'experience' => 45,
                'salary' => 1800,
            ],
            [
                'name' => 'Elena Rostova',
                'pace' => 68,
                'cornering' => 76,
                'consistency' => 80,
                'overtaking' => 65,
                'defensive' => 70,
                'racecraft' => 72,
                'experience' => 55,
                'salary' => 2200,
            ],
            [
                'name' => 'Marcus Chen',
                'pace' => 70,
                'cornering' => 72,
                'consistency' => 75,
                'overtaking' => 70,
                'defensive' => 76,
                'racecraft' => 82,
                'experience' => 75,
                'salary' => 2800,
            ],
            [
                'name' => 'Sofia Bianchi',
                'pace' => 72,
                'cornering' => 68,
                'consistency' => 60,
                'overtaking' => 80,
                'defensive' => 62,
                'racecraft' => 68,
                'experience' => 35,
                'salary' => 1900,
            ],
            [
                'name' => 'Lucas Silva',
                'pace' => 71,
                'cornering' => 70,
                'consistency' => 65,
                'overtaking' => 68,
                'defensive' => 64,
                'racecraft' => 62,
                'experience' => 25,
                'salary' => 1400,
            ],
            [
                'name' => 'Tariq Mansoor',
                'pace' => 66,
                'cornering' => 70,
                'consistency' => 78,
                'overtaking' => 60,
                'defensive' => 82,
                'racecraft' => 75,
                'experience' => 60,
                'salary' => 2100,
            ],
            [
                'name' => 'Kenji Sato',
                'pace' => 78,
                'cornering' => 77,
                'consistency' => 82,
                'overtaking' => 76,
                'defensive' => 75,
                'racecraft' => 80,
                'experience' => 70,
                'salary' => 3500,
            ],
        ];

        foreach ($drivers as $driverData) {
            Driver::firstOrCreate(
                ['name' => $driverData['name']],
                $driverData
            );
        }
    }
}
