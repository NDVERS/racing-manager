<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CarSeeder::class,
            DriverSeeder::class,
            RaceSeeder::class,
            SponsorSeeder::class,
        ]);

        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
            ]
        );

        if (! $testUser->team) {
            $team = Team::create([
                'user_id' => $testUser->id,
                'name' => 'Kuro Racing',
                'money' => 50000,
                'reputation' => 0,
            ]);

            $kuroGt = Car::where('name', 'Kuro GT')->whereNull('team_id')->first();
            if ($kuroGt) {
                $userCar = $kuroGt->replicate();
                $userCar->team_id = $team->id;
                $userCar->is_active = true;
                $userCar->save();
            }

            $alexCarter = Driver::where('name', 'Alex Carter')->whereNull('team_id')->first();
            if ($alexCarter) {
                $alexCarter->update([
                    'team_id' => $team->id,
                    'is_lead' => true,
                ]);
            }
        }
    }
}
