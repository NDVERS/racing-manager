<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\Team;
use App\Models\User;
use App\Services\RaceSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceSimulationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test simulation service produces valid grid classification and telemetry events.
     */
    public function test_simulation_service_generates_valid_grid_standings(): void
    {
        $team = Team::factory()->create(['name' => 'Kuro Works']);
        $car = Car::factory()->forTeam($team)->create([
            'speed' => 75,
            'acceleration' => 70,
            'handling' => 72,
            'braking' => 70,
            'reliability' => 85,
        ]);
        $driver = Driver::factory()->forTeam($team)->lead()->create([
            'pace' => 78,
            'cornering' => 75,
            'consistency' => 72,
            'racecraft' => 76,
        ]);
        $race = Race::factory()->create([
            'name' => 'Sentul Speed Circuit',
            'laps' => 10,
            'track_type' => 'high_speed',
            'weather' => 'dry',
        ]);

        $service = new RaceSimulationService;
        $results = $service->simulate($race, $team, $car, $driver);

        $this->assertArrayHasKey('standings', $results);
        $this->assertArrayHasKey('player_result', $results);
        $this->assertArrayHasKey('fastest_lap_overall', $results);
        $this->assertArrayHasKey('lap_events', $results);

        $this->assertCount(10, $results['standings']);

        // Check positions are unique from 1 to 10
        $positions = array_column($results['standings'], 'position');
        $this->assertSame(range(1, 10), $positions);

        // Check player is classified
        $this->assertNotNull($results['player_result']);
        $this->assertGreaterThanOrEqual(1, $results['player_result']['position']);
        $this->assertLessThanOrEqual(10, $results['player_result']['position']);

        // Check lap events exist
        $this->assertNotEmpty($results['lap_events']);
    }

    /**
     * Test performance index calculation accounts for track type.
     */
    public function test_performance_index_weights_track_type_attributes(): void
    {
        $highSpeedCar = Car::factory()->make([
            'speed' => 90,
            'acceleration' => 85,
            'handling' => 50,
            'braking' => 50,
            'reliability' => 80,
        ]);

        $techCar = Car::factory()->make([
            'speed' => 50,
            'acceleration' => 55,
            'handling' => 90,
            'braking' => 85,
            'reliability' => 80,
        ]);

        $driver = Driver::factory()->make([
            'pace' => 70,
            'cornering' => 70,
            'consistency' => 70,
            'racecraft' => 70,
        ]);

        $service = new RaceSimulationService;

        $highSpeedOnStraight = $service->calculatePerformanceIndex($highSpeedCar, $driver, 'high_speed', 'dry');
        $techOnStraight = $service->calculatePerformanceIndex($techCar, $driver, 'high_speed', 'dry');
        $this->assertGreaterThan($techOnStraight, $highSpeedOnStraight);

        $highSpeedOnTech = $service->calculatePerformanceIndex($highSpeedCar, $driver, 'technical', 'dry');
        $techOnTech = $service->calculatePerformanceIndex($techCar, $driver, 'technical', 'dry');
        $this->assertGreaterThan($highSpeedOnTech, $techOnTech);
    }

    /**
     * Test guest is redirected to login from simulation endpoints.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $race = Race::factory()->create();

        $this->post(route('races.run', $race))->assertRedirect(route('login'));
        $this->get(route('races.live', $race))->assertRedirect(route('login'));
    }

    /**
     * Test user cannot run simulation without complete readiness.
     */
    public function test_cannot_run_simulation_without_active_car(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 20000]);

        Car::factory()->forTeam($team)->create(['is_active' => false]);
        Driver::factory()->forTeam($team)->lead()->create();
        $race = Race::factory()->create(['entry_fee' => 1000]);

        $response = $this->actingAs($user)->post(route('races.run', $race));

        $response->assertRedirect(route('garage.index'));
        $response->assertSessionHas('warning');
    }

    /**
     * Test user cannot run simulation without lead driver.
     */
    public function test_cannot_run_simulation_without_lead_driver(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 20000]);

        Car::factory()->forTeam($team)->create(['is_active' => true]);
        Driver::factory()->forTeam($team)->create(['is_lead' => false]);
        $race = Race::factory()->create(['entry_fee' => 1000]);

        $response = $this->actingAs($user)->post(route('races.run', $race));

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('warning');
    }

    /**
     * Test user cannot run simulation with insufficient funds.
     */
    public function test_cannot_run_simulation_with_insufficient_funds(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 200]);

        Car::factory()->forTeam($team)->create(['is_active' => true]);
        Driver::factory()->forTeam($team)->lead()->create();
        $race = Race::factory()->create(['entry_fee' => 1500]);

        $response = $this->actingAs($user)->post(route('races.run', $race));

        $response->assertRedirect(route('races.show', $race));
        $response->assertSessionHas('warning');
    }

    /**
     * Test user can successfully run race simulation and view live telemetry.
     */
    public function test_user_can_run_simulation_and_view_live_pitwall(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Scuderia Redline',
            'money' => 25000,
        ]);

        Car::factory()->forTeam($team)->create([
            'name' => 'Redline GT-X',
            'is_active' => true,
        ]);

        Driver::factory()->forTeam($team)->lead()->create([
            'name' => 'Alex Carter',
        ]);

        $race = Race::factory()->create([
            'name' => 'Mandalika Grand Prix',
            'entry_fee' => 2000,
            'prize_pool' => 35000,
        ]);

        // 1. Post to run simulation
        $runResponse = $this->actingAs($user)->post(route('races.run', $race));

        $runResponse->assertRedirect(route('races.live', $race));
        $this->assertNotNull(session('race_simulation_'.$race->id));

        // 2. View Live telemetry page
        $liveResponse = $this->actingAs($user)->get(route('races.live', $race));

        $liveResponse->assertOk();
        $liveResponse->assertViewIs('races.live');
        $liveResponse->assertSee('Mandalika Grand Prix');
        $liveResponse->assertSee('Alex Carter');
        $liveResponse->assertSee('Redline GT-X');
        $liveResponse->assertSee('Grand Prix Final Classification');
        $liveResponse->assertSee('Pit-Wall Radio');
    }
}
