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

class ChampionshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing standings page.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('standings'))->assertRedirect(route('login'));
        $this->get(route('championship'))->assertRedirect(route('login'));
    }

    /**
     * Test user without team is redirected to onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('standings'))->assertRedirect(route('team.create'));
        $this->actingAs($user)->get(route('championship'))->assertRedirect(route('team.create'));
    }

    /**
     * Test user can view the championship standings with clean initial 0-point state.
     */
    public function test_user_can_view_empty_season_standings(): void
    {
        $this->seed(RaceSeeder::class);

        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Kuro Racing',
        ]);
        $driver = Driver::factory()->create([
            'team_id' => $team->id,
            'name' => 'Alex Carter',
            'is_lead' => true,
        ]);
        $car = Car::factory()->create([
            'team_id' => $team->id,
            'name' => 'Kuro GT',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('standings'));

        $response->assertOk();
        $response->assertViewIs('standings.index');
        $response->assertSee('Season Championship Standings');
        $response->assertSee('Constructors', false);
        $response->assertSee('Drivers', false);
        $response->assertSee('Kuro Racing');
        $response->assertSee('Alex Carter');
        $response->assertSee('Scuderia Veloce');
        $response->assertSee('Rounds Done');
    }

    /**
     * Test standings accurately aggregate points, wins, and podiums after simulated races.
     */
    public function test_standings_accurately_aggregate_points_wins_and_podiums_after_races(): void
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

        $race1 = Race::factory()->create(['name' => 'Monza GP']);
        $race2 = Race::factory()->create(['name' => 'Silverstone GP']);

        // Race 1: P1 finish (25 pts)
        RaceResult::create([
            'race_id' => $race1->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 1,
            'race_time' => '15:20.100',
            'prize_money' => 25000,
            'reputation_earned' => 25,
            'strategy' => 'balanced',
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    ['position' => 1, 'driver_name' => 'Marcus Speed', 'team_name' => 'Apex Titans', 'car_name' => 'Titan Evo', 'is_player' => true],
                    ['position' => 2, 'driver_name' => 'Marco Rossi', 'team_name' => 'Scuderia Veloce', 'car_name' => 'Veloce C26', 'is_player' => false],
                    ['position' => 3, 'driver_name' => 'Liam Vance', 'team_name' => 'Silverstone Dynamics', 'car_name' => 'SD-08 Arrow', 'is_player' => false],
                ],
                'fastest_lap_overall' => ['driver' => 'Marcus Speed', 'lap_time' => '1:12.500'],
            ],
        ]);

        // Race 2: P3 finish (15 pts)
        RaceResult::create([
            'race_id' => $race2->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 3,
            'race_time' => '16:05.300',
            'prize_money' => 12000,
            'reputation_earned' => 15,
            'strategy' => 'balanced',
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    ['position' => 1, 'driver_name' => 'Marco Rossi', 'team_name' => 'Scuderia Veloce', 'car_name' => 'Veloce C26', 'is_player' => false],
                    ['position' => 2, 'driver_name' => 'Elena Rostova', 'team_name' => 'AeroTech Motorsport', 'car_name' => 'AT-Aero Pro', 'is_player' => false],
                    ['position' => 3, 'driver_name' => 'Marcus Speed', 'team_name' => 'Apex Titans', 'car_name' => 'Titan Evo', 'is_player' => true],
                ],
                'fastest_lap_overall' => ['driver' => 'Marco Rossi', 'lap_time' => '1:11.900'],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('standings'));

        $response->assertOk();
        // Marcus Speed: 25 (P1) + 1 (FL) + 15 (P3) = 41 PTS, 1 win, 2 podiums
        // Marco Rossi: 18 (P2) + 25 (P1) + 1 (FL) = 44 PTS, 1 win, 2 podiums
        $response->assertSee('41');
        $response->assertSee('44');
    }

    /**
     * Test player team and driver are correctly flagged and highlighted.
     */
    public function test_player_team_and_driver_are_correctly_flagged(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Horizon Racing',
        ]);
        Driver::factory()->create([
            'team_id' => $team->id,
            'name' => 'David Fox',
            'is_lead' => true,
        ]);
        Car::factory()->create([
            'team_id' => $team->id,
            'name' => 'Horizon H1',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('standings'));

        $response->assertOk();
        $response->assertSee('YOU');
        $response->assertSee('Horizon Racing');
        $response->assertSee('David Fox');
    }

    /**
     * Test championship standings are isolated between different users/teams.
     */
    public function test_standings_are_isolated_between_teams(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id, 'name' => 'Team Alpha']);
        $driverA = Driver::factory()->create(['team_id' => $teamA->id, 'name' => 'Alpha Pilot', 'is_lead' => true]);
        $carA = Car::factory()->create(['team_id' => $teamA->id, 'is_active' => true]);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id, 'name' => 'Team Beta']);
        $driverB = Driver::factory()->create(['team_id' => $teamB->id, 'name' => 'Beta Pilot', 'is_lead' => true]);
        $carB = Car::factory()->create(['team_id' => $teamB->id, 'is_active' => true]);

        $race = Race::factory()->create();

        // Team A won race (25 pts)
        RaceResult::create([
            'race_id' => $race->id,
            'team_id' => $teamA->id,
            'car_id' => $carA->id,
            'driver_id' => $driverA->id,
            'position' => 1,
            'race_time' => '14:00.000',
            'prize_money' => 20000,
            'reputation_earned' => 25,
            'strategy' => 'balanced',
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    ['position' => 1, 'driver_name' => 'Alpha Pilot', 'team_name' => 'Team Alpha', 'car_name' => 'Car A', 'is_player' => true],
                ],
            ],
        ]);

        // Team A sees their 25 pts
        $responseA = $this->actingAs($userA)->get(route('standings'));
        $responseA->assertSee('Team Alpha');
        $responseA->assertSee('Alpha Pilot');
        $responseA->assertDontSee('Team Beta');

        // Team B sees 0 pts for their own team
        $responseB = $this->actingAs($userB)->get(route('standings'));
        $responseB->assertSee('Team Beta');
        $responseB->assertSee('Beta Pilot');
        $responseB->assertDontSee('Alpha Pilot');
    }

    /**
     * Test dashboard displays championship overview metrics accurately.
     */
    public function test_dashboard_displays_championship_status_overview(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'name' => 'Vortex Motorsport']);
        Driver::factory()->create(['team_id' => $team->id, 'is_lead' => true]);
        Car::factory()->create(['team_id' => $team->id, 'is_active' => true]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('CHAMPIONSHIP');
        $response->assertSee('Championship Standings');
    }

    /**
     * Test overview card rank matches leaderboard table rank when player wins P1 and has AI-pool driver name.
     */
    public function test_overview_card_rank_matches_leaderboard_when_player_is_p1(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'name' => 'Kuro Racing']);
        // Use a driver whose name happens to be in aiGridPool ("Liam Vance")
        $driver = Driver::factory()->create([
            'team_id' => $team->id,
            'name' => 'Liam Vance',
            'is_lead' => true,
        ]);
        $car = Car::factory()->create([
            'team_id' => $team->id,
            'name' => 'Kuro GT',
            'is_active' => true,
        ]);

        $race = Race::factory()->create(['name' => 'Suzuka GP']);

        RaceResult::create([
            'race_id' => $race->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 1,
            'race_time' => '13:45.100',
            'prize_money' => 25000,
            'reputation_earned' => 25,
            'strategy' => 'balanced',
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    ['position' => 1, 'driver_name' => 'Liam Vance', 'team_name' => 'Kuro Racing', 'car_name' => 'Kuro GT', 'is_player' => true],
                    ['position' => 2, 'driver_name' => 'Marco Rossi', 'team_name' => 'Scuderia Veloce', 'car_name' => 'Veloce C26', 'is_player' => false],
                ],
                'fastest_lap_overall' => ['driver' => 'Liam Vance', 'lap_time' => '1:10.000'],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('standings'));
        $response->assertOk();

        $season = $response->viewData('season');
        $this->assertEquals(1, $season['player_constructor_rank']);
        $this->assertEquals(1, $season['player_driver_rank']);
        $this->assertEquals(26, $season['player_constructor_points']);
        $this->assertEquals(26, $season['player_driver_points']);
    }
}
