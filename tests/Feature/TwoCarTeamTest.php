<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;
use App\Models\User;
use App\Services\ChampionshipService;
use App\Services\RaceSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoCarTeamTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test assigning slot 1 and slot 2 to cars in team garage.
     */
    public function test_can_assign_car_slots_in_garage(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $carA = Car::factory()->forTeam($team)->create(['name' => 'Chassis Alpha', 'slot' => 1, 'is_active' => true]);
        $carB = Car::factory()->forTeam($team)->create(['name' => 'Chassis Beta', 'slot' => null, 'is_active' => false]);

        // Assign carB to slot 2
        $response = $this->actingAs($user)->post(route('garage.assign-slot', $carB), [
            'slot' => 2,
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, $carB->fresh()->slot);
        $this->assertEquals(1, $carA->fresh()->slot);

        // Switch carB to slot 1 (should automatically displace carA from slot 1)
        $this->actingAs($user)->post(route('garage.assign-slot', $carB), [
            'slot' => 1,
        ]);

        $this->assertEquals(1, $carB->fresh()->slot);
        $this->assertTrue($carB->fresh()->is_active);
        $this->assertNull($carA->fresh()->slot);
        $this->assertFalse($carA->fresh()->is_active);
    }

    /**
     * Test assigning slot 1 and slot 2 to drivers in driver roster.
     */
    public function test_can_assign_driver_slots_in_roster(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $driverA = Driver::factory()->forTeam($team)->create(['name' => 'Driver Alpha', 'slot' => 1, 'is_lead' => true]);
        $driverB = Driver::factory()->forTeam($team)->create(['name' => 'Driver Beta', 'slot' => null, 'is_lead' => false]);

        // Assign driverB to slot 2
        $response = $this->actingAs($user)->post(route('drivers.assign-slot', $driverB), [
            'slot' => 2,
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, $driverB->fresh()->slot);
        $this->assertEquals(1, $driverA->fresh()->slot);

        // Switch driverB to slot 1
        $this->actingAs($user)->post(route('drivers.assign-slot', $driverB), [
            'slot' => 1,
        ]);

        $this->assertEquals(1, $driverB->fresh()->slot);
        $this->assertTrue($driverB->fresh()->is_lead);
        $this->assertNull($driverA->fresh()->slot);
        $this->assertFalse($driverA->fresh()->is_lead);
    }

    /**
     * Test team helper methods for two-car readiness.
     */
    public function test_team_two_car_helpers_and_readiness(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $car1 = Car::factory()->forTeam($team)->create(['slot' => 1, 'is_active' => true]);
        $driver1 = Driver::factory()->forTeam($team)->create(['slot' => 1, 'is_lead' => true]);

        $this->assertFalse($team->isTwoCarReady());
        $this->assertEquals($car1->id, $team->car1()->id);
        $this->assertEquals($driver1->id, $team->driver1()->id);
        $this->assertNull($team->car2());
        $this->assertNull($team->driver2());

        $car2 = Car::factory()->forTeam($team)->create(['slot' => 2, 'is_active' => false]);
        $driver2 = Driver::factory()->forTeam($team)->create(['slot' => 2, 'is_lead' => false]);

        $this->assertTrue($team->isTwoCarReady());
        $this->assertEquals($car2->id, $team->car2()->id);
        $this->assertEquals($driver2->id, $team->driver2()->id);
        $this->assertCount(2, $team->activeCars());
        $this->assertCount(2, $team->activeDrivers());
    }

    /**
     * Test simulation engine produces 20 cars when 2-car entry is simulated.
     */
    public function test_simulation_service_simulates_20_car_grid_for_two_car_entry(): void
    {
        $team = Team::factory()->create(['name' => 'Monza Scuderia']);
        $car1 = Car::factory()->forTeam($team)->create(['slot' => 1, 'speed' => 80, 'reliability' => 90]);
        $driver1 = Driver::factory()->forTeam($team)->create(['slot' => 1, 'pace' => 82, 'consistency' => 80]);

        $car2 = Car::factory()->forTeam($team)->create(['slot' => 2, 'speed' => 78, 'reliability' => 88]);
        $driver2 = Driver::factory()->forTeam($team)->create(['slot' => 2, 'pace' => 76, 'consistency' => 75]);

        $race = Race::factory()->create([
            'name' => 'Silverstone GP',
            'laps' => 8,
            'weather' => 'dry',
            'track_type' => 'high_speed',
        ]);

        $service = new RaceSimulationService;
        $sim = $service->simulate(
            $race,
            $team,
            $car1,
            $driver1,
            'soft',
            'push',
            $car2,
            $driver2,
            'medium',
            'balanced'
        );

        $this->assertTrue($sim['is_two_car']);
        $this->assertCount(20, $sim['standings']);
        $this->assertNotNull($sim['player_result_1']);
        $this->assertNotNull($sim['player_result_2']);

        // Check positions are exactly 1 to 20
        $positions = array_column($sim['standings'], 'position');
        $this->assertSame(range(1, 20), $positions);

        // Check both player cars are classified with unique positions
        $this->assertNotEquals($sim['player_result_1']['position'], $sim['player_result_2']['position']);
    }

    /**
     * Test complete two-car Grand Prix race execution and dual result records creation.
     */
    public function test_user_can_run_two_car_grand_prix_with_dual_results_and_split_strategy(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Apex Horizon GP',
            'money' => 50000,
            'reputation' => 200,
        ]);

        $car1 = Car::factory()->forTeam($team)->create(['name' => 'Apex Mark I', 'slot' => 1, 'is_active' => true]);
        $driver1 = Driver::factory()->forTeam($team)->create(['name' => 'Carlos Sainz Jr', 'slot' => 1, 'is_lead' => true]);

        $car2 = Car::factory()->forTeam($team)->create(['name' => 'Apex Mark II', 'slot' => 2, 'is_active' => false]);
        $driver2 = Driver::factory()->forTeam($team)->create(['name' => 'Lando Norris', 'slot' => 2, 'is_lead' => false]);

        $race = Race::factory()->create([
            'name' => 'Spa-Francorchamps GP',
            'entry_fee' => 3000,
            'prize_pool' => 60000,
        ]);

        $response = $this->actingAs($user)->post(route('races.run', $race), [
            'tire_compound' => 'soft',
            'driving_mode' => 'push',
            'tire_compound_2' => 'hard',
            'driving_mode_2' => 'conserve',
        ]);

        $response->assertRedirect(route('races.live', $race));

        // Verify 2 RaceResult records were created
        $results = RaceResult::where('team_id', $team->id)->where('race_id', $race->id)->get();
        $this->assertCount(2, $results);

        $res1 = $results->firstWhere('car_slot', 1);
        $res2 = $results->firstWhere('car_slot', 2);

        $this->assertNotNull($res1);
        $this->assertNotNull($res2);
        $this->assertEquals($car1->id, $res1->car_id);
        $this->assertEquals($driver1->id, $res1->driver_id);
        $this->assertEquals('push_soft', $res1->strategy);

        $this->assertEquals($car2->id, $res2->car_id);
        $this->assertEquals($driver2->id, $res2->driver_id);
        $this->assertEquals('conserve_hard', $res2->strategy);

        // Verify live and debrief views render cleanly
        $liveView = $this->actingAs($user)->get(route('races.live', $race));
        $liveView->assertOk();
        $liveView->assertSee('Carlos Sainz Jr');
        $liveView->assertSee('Lando Norris');
        $liveView->assertSee('CAR #1 (YOU)');
        $liveView->assertSee('CAR #2 (YOU)');

        $resultsView = $this->actingAs($user)->get(route('races.results', $race));
        $resultsView->assertOk();
        $resultsView->assertSee('Combined Constructor Payout');
        $resultsView->assertSee('Carlos Sainz Jr');
        $resultsView->assertSee('Lando Norris');
    }

    /**
     * Test Constructors Championship aggregates points from both Car 1 and Car 2.
     */
    public function test_championship_service_aggregates_constructors_points_from_both_cars(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Velocity F1 Team',
            'current_season' => 1,
        ]);

        $car1 = Car::factory()->forTeam($team)->create(['slot' => 1, 'is_active' => true]);
        $driver1 = Driver::factory()->forTeam($team)->create(['name' => 'Pilot One', 'slot' => 1, 'is_lead' => true]);

        $car2 = Car::factory()->forTeam($team)->create(['slot' => 2, 'is_active' => false]);
        $driver2 = Driver::factory()->forTeam($team)->create(['name' => 'Pilot Two', 'slot' => 2, 'is_lead' => false]);

        $race = Race::factory()->create();

        // Simulate a race where Car 1 finished P1 (25 pts) and Car 2 finished P2 (18 pts)
        $simLog = [
            'standings' => [
                [
                    'position' => 1,
                    'is_player' => true,
                    'car_slot' => 1,
                    'driver_name' => 'Pilot One',
                    'team_name' => 'Velocity F1 Team',
                    'car_name' => $car1->name,
                    'total_time' => '1:15.000',
                    'gap' => 'LEADER',
                    'fastest_lap' => '1:14.000',
                ],
                [
                    'position' => 2,
                    'is_player' => true,
                    'car_slot' => 2,
                    'driver_name' => 'Pilot Two',
                    'team_name' => 'Velocity F1 Team',
                    'car_name' => $car2->name,
                    'total_time' => '1:16.200',
                    'gap' => '+1.200s',
                    'fastest_lap' => '1:14.800',
                ],
            ],
            'fastest_lap_overall' => ['driver' => 'Pilot One', 'team' => 'Velocity F1 Team'],
        ];

        // Create 2 RaceResult records for the team
        RaceResult::create([
            'race_id' => $race->id,
            'team_id' => $team->id,
            'car_id' => $car1->id,
            'driver_id' => $driver1->id,
            'car_slot' => 1,
            'season' => 1,
            'position' => 1,
            'race_time' => '1:15.000',
            'prize_money' => 20000,
            'reputation_earned' => 50,
            'strategy' => 'push_soft',
            'status' => 'finished',
            'simulation_log' => $simLog,
        ]);

        RaceResult::create([
            'race_id' => $race->id,
            'team_id' => $team->id,
            'car_id' => $car2->id,
            'driver_id' => $driver2->id,
            'car_slot' => 2,
            'season' => 1,
            'position' => 2,
            'race_time' => '1:16.200',
            'prize_money' => 15000,
            'reputation_earned' => 30,
            'strategy' => 'balanced_medium',
            'status' => 'finished',
            'simulation_log' => $simLog,
        ]);

        $service = new ChampionshipService;
        $constructorStandings = $service->getConstructorsStandings($team, 1);
        $playerConstructor = collect($constructorStandings)->firstWhere('team_name', 'Velocity F1 Team');

        $this->assertNotNull($playerConstructor);
        // Points: P1 (25) + Fastest Lap (1) + P2 (18) = 44 Points
        $this->assertEquals(44, $playerConstructor['points']);

        $driverStandings = $service->getDriversStandings($team, 1);
        $d1 = collect($driverStandings)->firstWhere('driver_name', 'Pilot One');
        $d2 = collect($driverStandings)->firstWhere('driver_name', 'Pilot Two');

        $this->assertEquals(26, $d1['points']); // 25 + 1 fastest lap
        $this->assertEquals(18, $d2['points']); // 18
    }
}
