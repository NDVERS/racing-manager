<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing race history.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('races.history'))->assertRedirect(route('login'));
        $this->get(route('history'))->assertRedirect(route('login'));
    }

    /**
     * Test user without team is redirected to onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('races.history'))->assertRedirect(route('team.create'));
        $this->actingAs($user)->get(route('history'))->assertRedirect(route('team.create'));
    }

    /**
     * Test user can view empty race history state.
     */
    public function test_user_can_view_empty_race_history(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('races.history'));

        $response->assertOk();
        $response->assertViewIs('races.history');
        $response->assertSee('Championship Race Archives');
        $response->assertSee('No Championship Telemetry Recorded');
        $response->assertSee('0 SESSIONS LOGGED');
    }

    /**
     * Test user can view their team's recorded race archives.
     */
    public function test_user_can_view_recorded_race_archives(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'name' => 'Apex Works']);

        $car = Car::factory()->forTeam($team)->create(['name' => 'Apex GT-1']);
        $driver = Driver::factory()->forTeam($team)->create(['name' => 'Alex Carter']);
        $race = Race::factory()->create(['name' => 'Monza Grand Prix']);

        $result = RaceResult::factory()->create([
            'race_id' => $race->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 1,
            'race_time' => '1:18.420',
            'prize_money' => 20000,
            'reputation_earned' => 50,
            'strategy' => 'aggressive',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user)->get(route('races.history'));

        $response->assertOk();
        $response->assertViewIs('races.history');
        $response->assertSee('Monza Grand Prix');
        $response->assertSee('Apex GT-1');
        $response->assertSee('Alex Carter');
        $response->assertSee('P1 WINNER');
        $response->assertSee('20,000');
        $response->assertSee('1:18.420');
        $response->assertSee(route('race-results.show', $result));
    }

    /**
     * Test cumulative metrics (wins, podiums, total earnings, points) are calculated accurately.
     */
    public function test_cumulative_performance_metrics_calculation(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $car = Car::factory()->forTeam($team)->create();
        $driver = Driver::factory()->forTeam($team)->create();

        $race1 = Race::factory()->create(['name' => 'Silverstone GP']);
        $race2 = Race::factory()->create(['name' => 'Spa-Francorchamps GP']);
        $race3 = Race::factory()->create(['name' => 'Suzuka GP']);
        $race4 = Race::factory()->create(['name' => 'Monaco GP']);

        // Race 1: P1 (Win & Podium, 25 PTS, 30,000 CR)
        RaceResult::factory()->create([
            'race_id' => $race1->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 1,
            'prize_money' => 30000,
            'reputation_earned' => 50,
        ]);

        // Race 2: P3 (Podium, 15 PTS, 15,000 CR)
        RaceResult::factory()->create([
            'race_id' => $race2->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 3,
            'prize_money' => 15000,
            'reputation_earned' => 20,
        ]);

        // Race 3: P5 (No podium, 10 PTS, 5,000 CR)
        RaceResult::factory()->create([
            'race_id' => $race3->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 5,
            'prize_money' => 5000,
            'reputation_earned' => 8,
        ]);

        // Race 4: P8 (No podium, 4 PTS, 2,000 CR)
        RaceResult::factory()->create([
            'race_id' => $race4->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 8,
            'prize_money' => 2000,
            'reputation_earned' => 3,
        ]);

        $response = $this->actingAs($user)->get(route('races.history'));

        $response->assertOk();

        // 4 starts
        $response->assertSee('4 SESSIONS LOGGED');
        // 1 victory (P1)
        $response->assertSee('25%'); // Win rate (1 / 4)
        // 2 podiums (P1, P3)
        $response->assertSee('50%'); // Podium rate (2 / 4)
        // Total points: 25 + 15 + 10 + 4 = 54 PTS
        $response->assertSee('54');
        // Total prize earnings: 30000 + 15000 + 5000 + 2000 = 52,000 CR
        $response->assertSee('52,000');
        // Best finish: P1
        $response->assertSee('P1');
    }

    /**
     * Test strict cross-team isolation: user only sees their own team's race results and stats.
     */
    public function test_cross_team_race_history_isolation(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id, 'name' => 'Team Alpha']);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id, 'name' => 'Team Beta']);

        $carA = Car::factory()->forTeam($teamA)->create(['name' => 'Alpha 01']);
        $driverA = Driver::factory()->forTeam($teamA)->create(['name' => 'Driver Alpha']);

        $carB = Car::factory()->forTeam($teamB)->create(['name' => 'Beta 02']);
        $driverB = Driver::factory()->forTeam($teamB)->create(['name' => 'Driver Beta']);

        $race = Race::factory()->create(['name' => 'Interlagos GP']);

        // Team A result
        $resultA = RaceResult::factory()->create([
            'race_id' => $race->id,
            'team_id' => $teamA->id,
            'car_id' => $carA->id,
            'driver_id' => $driverA->id,
            'position' => 2,
            'prize_money' => 18000,
        ]);

        // Team B result
        $resultB = RaceResult::factory()->create([
            'race_id' => $race->id,
            'team_id' => $teamB->id,
            'car_id' => $carB->id,
            'driver_id' => $driverB->id,
            'position' => 1,
            'prize_money' => 40000,
        ]);

        $responseA = $this->actingAs($userA)->get(route('races.history'));

        $responseA->assertOk();
        $responseA->assertSee('Alpha 01');
        $responseA->assertSee('Driver Alpha');
        $responseA->assertSee('18,000');
        $responseA->assertDontSee('Beta 02');
        $responseA->assertDontSee('Driver Beta');
        $responseA->assertDontSee('40,000');

        // Verify debrief protection: User A cannot access Team B's debrief
        $this->actingAs($userA)->get(route('race-results.show', $resultA))->assertOk();
        $this->actingAs($userA)->get(route('race-results.show', $resultB))->assertNotFound();
    }
}
