<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HallOfFameTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing hall of fame page.
     */
    public function test_guest_cannot_access_hall_of_fame(): void
    {
        $this->get(route('hall-of-fame.index'))->assertRedirect(route('login'));
    }

    /**
     * Test user without team is redirected to onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('hall-of-fame.index'))->assertRedirect(route('team.create'));
    }

    /**
     * Test user with team can view hall of fame page and initial zero statistics.
     */
    public function test_user_with_team_can_view_hall_of_fame_with_initial_stats(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Kuro Racing',
        ]);
        Driver::factory()->create([
            'team_id' => $team->id,
            'name' => 'Alex Carter',
            'is_lead' => true,
        ]);
        Car::factory()->create([
            'team_id' => $team->id,
            'name' => 'Kuro GT',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('hall-of-fame.index'));

        $response->assertOk();
        $response->assertViewIs('hall-of-fame.index');
        $response->assertSee('Trophy Room');
        $response->assertSee('Kuro Racing');
        $response->assertSee('All-Time Entries');
        $response->assertSee('World Championship Titles');
    }

    /**
     * Test hall of fame calculates career stats, victory trophies, and podiums accurately.
     */
    public function test_hall_of_fame_calculates_all_time_stats_and_trophies_accurately(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Apex Titans',
        ]);
        $driver = Driver::factory()->create([
            'team_id' => $team->id,
            'name' => 'Marcus Speed',
            'is_lead' => true,
        ]);
        $car = Car::factory()->create([
            'team_id' => $team->id,
            'name' => 'Titan Evo',
            'is_active' => true,
        ]);

        $race1 = Race::factory()->create(['name' => 'Monza GP', 'location' => 'Autodromo Nazionale Monza']);
        $race2 = Race::factory()->create(['name' => 'Silverstone GP', 'location' => 'Silverstone Circuit']);
        $race3 = Race::factory()->create(['name' => 'Spa GP', 'location' => 'Spa-Francorchamps']);

        // Race 1: P1 finish (Win & Trophy)
        RaceResult::create([
            'race_id' => $race1->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'car_slot' => 1,
            'season' => 1,
            'position' => 1,
            'race_time' => '14:20.100',
            'prize_money' => 25000,
            'reputation_earned' => 25,
            'strategy' => 'balanced',
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    ['position' => 1, 'driver_name' => 'Marcus Speed', 'team_name' => 'Apex Titans', 'car_name' => 'Titan Evo', 'is_player' => true],
                    ['position' => 2, 'driver_name' => 'Marco Rossi', 'team_name' => 'Scuderia Veloce', 'car_name' => 'Veloce C26', 'is_player' => false],
                ],
                'fastest_lap_overall' => ['driver' => 'Marcus Speed', 'lap_time' => '1:12.500'],
            ],
        ]);

        // Race 2: P2 finish (Podium)
        RaceResult::create([
            'race_id' => $race2->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'car_slot' => 1,
            'season' => 1,
            'position' => 2,
            'race_time' => '15:10.300',
            'prize_money' => 18000,
            'reputation_earned' => 18,
            'strategy' => 'balanced',
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    ['position' => 1, 'driver_name' => 'Marco Rossi', 'team_name' => 'Scuderia Veloce', 'car_name' => 'Veloce C26', 'is_player' => false],
                    ['position' => 2, 'driver_name' => 'Marcus Speed', 'team_name' => 'Apex Titans', 'car_name' => 'Titan Evo', 'is_player' => true],
                ],
                'fastest_lap_overall' => ['driver' => 'Marco Rossi', 'lap_time' => '1:11.900'],
            ],
        ]);

        // Race 3: P5 finish (Points)
        RaceResult::create([
            'race_id' => $race3->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'car_slot' => 1,
            'season' => 1,
            'position' => 5,
            'race_time' => '16:00.000',
            'prize_money' => 8000,
            'reputation_earned' => 8,
            'strategy' => 'balanced',
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    ['position' => 1, 'driver_name' => 'Marco Rossi', 'team_name' => 'Scuderia Veloce', 'car_name' => 'Veloce C26', 'is_player' => false],
                    ['position' => 5, 'driver_name' => 'Marcus Speed', 'team_name' => 'Apex Titans', 'car_name' => 'Titan Evo', 'is_player' => true],
                ],
                'fastest_lap_overall' => ['driver' => 'Marco Rossi', 'lap_time' => '1:11.900'],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('hall-of-fame.index'));

        $response->assertOk();
        $stats = $response->viewData('stats');
        $this->assertEquals(3, $stats['total_races']);
        $this->assertEquals(1, $stats['total_wins']);
        $this->assertEquals(2, $stats['total_podiums']);
        $this->assertEquals(51000, $stats['total_prize_money']);
        // Points: Race 1 (25) + Race 2 (18) + Race 3 (10) = 53 pts
        $this->assertEquals(53, $stats['total_points']);

        // Assert Trophy is displayed
        $response->assertSee('Monza GP');
        $response->assertSee('Autodromo Nazionale Monza');
        $response->assertSee('Marcus Speed');
    }

    /**
     * Test results view renders Next Race CTA and handles final race of season gracefully.
     */
    public function test_results_view_quick_flow_and_graceful_final_race(): void
    {
        $this->seed(RaceSeeder::class);

        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);
        $driver = Driver::factory()->create(['team_id' => $team->id, 'is_lead' => true]);
        $car = Car::factory()->create(['team_id' => $team->id, 'is_active' => true]);

        $races = Race::orderBy('id')->get();
        $firstRace = $races->first();
        $lastRace = $races->last();

        // Simulate finish for race 1
        RaceResult::create([
            'race_id' => $firstRace->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'car_slot' => 1,
            'season' => 1,
            'position' => 1,
            'race_time' => '14:20.000',
            'prize_money' => 25000,
            'reputation_earned' => 25,
            'strategy' => 'balanced',
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    ['position' => 1, 'driver_name' => $driver->name, 'team_name' => $team->name, 'car_name' => $car->name, 'is_player' => true],
                ],
            ],
        ]);

        $response1 = $this->actingAs($user)->get(route('races.results', $firstRace));
        $response1->assertOk();
        $response1->assertSee('Proceed to '.$races[1]->name);

        // Mark all remaining races finished for team
        foreach ($races as $r) {
            RaceResult::firstOrCreate([
                'race_id' => $r->id,
                'team_id' => $team->id,
            ], [
                'car_id' => $car->id,
                'driver_id' => $driver->id,
                'car_slot' => 1,
                'season' => 1,
                'position' => 2,
                'race_time' => '14:30.000',
                'prize_money' => 18000,
                'reputation_earned' => 18,
                'strategy' => 'balanced',
                'status' => 'finished',
                'simulation_log' => ['standings' => []],
            ]);
        }

        $responseLast = $this->actingAs($user)->get(route('races.results', $lastRace));
        $responseLast->assertOk();
        $responseLast->assertSee('Advance to Season Finale');
    }
}
