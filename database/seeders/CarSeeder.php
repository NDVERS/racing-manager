<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cars = [
            [
                'name' => 'Kuro GT',
                'speed' => 65,
                'acceleration' => 62,
                'handling' => 64,
                'braking' => 63,
                'reliability' => 75,
                'level' => 1,
                'purchase_price' => 25000,
            ],
            [
                'name' => 'Apex Cyclone',
                'speed' => 75,
                'acceleration' => 72,
                'handling' => 58,
                'braking' => 60,
                'reliability' => 68,
                'level' => 1,
                'purchase_price' => 35000,
            ],
            [
                'name' => 'Vortex R1',
                'speed' => 60,
                'acceleration' => 66,
                'handling' => 76,
                'braking' => 74,
                'reliability' => 70,
                'level' => 1,
                'purchase_price' => 32000,
            ],
            [
                'name' => 'Phantom GTS',
                'speed' => 78,
                'acceleration' => 75,
                'handling' => 73,
                'braking' => 72,
                'reliability' => 80,
                'level' => 1,
                'purchase_price' => 45000,
            ],
            [
                'name' => 'Falcon RS',
                'speed' => 70,
                'acceleration' => 78,
                'handling' => 62,
                'braking' => 65,
                'reliability' => 65,
                'level' => 1,
                'purchase_price' => 38000,
            ],
        ];

        foreach ($cars as $carData) {
            Car::firstOrCreate(
                ['name' => $carData['name'], 'team_id' => null],
                $carData
            );
        }
    }
}
