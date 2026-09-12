<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;
use App\Models\User;
use App\Services\ChampionshipService;
use Database\Seeders\RaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonProgressionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test team cannot advance to next season if Grand Prix rounds are incomplete.
     */
    public function test_team_cannot_advance_season_if_grand_prix_races_incomplete(): void
    {
        $this->seed(RaceSeeder::class);

        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Kuro Racing',
            'current_season' => 1,
            'money' => 50000,
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

        // Complete only 1 of 5 races
        $races = Race::all();
        RaceResult::create([
            'race_id' => $races[0]->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'season' => 1,
            'position' => 3,
            'race_time' => '1:30.000',
            'prize_money' => 10000,
            'reputation_earned' => 50,
            'strategy' => 'balanced_medium',
            'status' => 'finished',
        ]);

        $this->assertFalse($team->isCurrentSeasonCompleted());

        $response = $this->actingAs($user)->post(route('season.advance'));
        $response->assertRedirect(route('races.index'));
        $response->assertSessionHas('warning');

        $team->refresh();
        $this->assertSame(1, (int) $team->current_season);
    }

    /**
     * Test team can successfully advance to next season when all races in current season are finished.
     */
    public function test_team_can_advance_to_next_season_when_all_races_finished(): void
    {
        $this->seed(RaceSeeder::class);

        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Kuro Racing',
            'current_season' => 1,
            'money' => 50000,
            'reputation' => 100,
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

        $races = Race::all();
        foreach ($races as $race) {
            RaceResult::create([
                'race_id' => $race->id,
                'team_id' => $team->id,
                'car_id' => $car->id,
                'driver_id' => $driver->id,
                'season' => 1,
                'position' => 1,
                'race_time' => '1:30.000',
                'prize_money' => 15000,
                'reputation_earned' => 100,
                'strategy' => 'balanced_medium',
                'status' => 'finished',
                'simulation_log' => [
                    'standings' => [
                        ['position' => 1, 'driver_name' => $driver->name, 'team_name' => $team->name, 'car_name' => $car->name, 'is_player' => true],
                    ],
                ],
            ]);
        }

        $this->assertTrue($team->isCurrentSeasonCompleted());

        $initialMoney = $team->money;
        $initialRep = $team->reputation;

        $response = $this->actingAs($user)->post(route('season.advance'));
        $response->assertRedirect(route('races.index'));
        $response->assertSessionHas('success');

        $team->refresh();
        $this->assertSame(2, (int) $team->current_season);
        $this->assertGreaterThan($initialMoney, $team->money);
        $this->assertGreaterThan($initialRep, $team->reputation);

        // Verify transaction logged
        $this->assertDatabaseHas('transactions', [
            'team_id' => $team->id,
            'type' => 'income',
            'reference_id' => 1,
        ]);
    }

    /**
     * Test season standings and results remain isolated between seasons.
     */
    public function test_season_standings_and_results_are_isolated_per_season(): void
    {
        $this->seed(RaceSeeder::class);

        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Kuro Racing',
            'current_season' => 2,
            'money' => 50000,
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

        $races = Race::all();

        // Season 1 Result: Won Race 1
        RaceResult::create([
            'race_id' => $races[0]->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'season' => 1,
            'position' => 1,
            'race_time' => '1:30.000',
            'prize_money' => 15000,
            'reputation_earned' => 100,
            'strategy' => 'balanced_medium',
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    ['position' => 1, 'driver_name' => $driver->name, 'team_name' => $team->name, 'car_name' => $car->name, 'is_player' => true],
                ],
            ],
        ]);

        // Season 2 Result: Finished P10 in Race 1
        RaceResult::create([
            'race_id' => $races[0]->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'season' => 2,
            'position' => 10,
            'race_time' => '1:35.000',
            'prize_money' => 1000,
            'reputation_earned' => 10,
            'strategy' => 'balanced_medium',
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    ['position' => 10, 'driver_name' => $driver->name, 'team_name' => $team->name, 'car_name' => $car->name, 'is_player' => true],
                ],
            ],
        ]);

        $championshipService = app(ChampionshipService::class);

        $season1Standings = $championshipService->getConstructorsStandings($team, 1);
        $season2Standings = $championshipService->getConstructorsStandings($team, 2);

        $s1PlayerPoints = collect($season1Standings)->firstWhere('is_player', true)['points'];
        $s2PlayerPoints = collect($season2Standings)->firstWhere('is_player', true)['points'];

        $this->assertSame(25, $s1PlayerPoints);
        $this->assertSame(1, $s2PlayerPoints);

        // View standings for season 1 vs season 2
        $this->actingAs($user)->get(route('standings', ['season' => 1]))
            ->assertOk()
            ->assertSee('SEASON 1 ARCHIVE')
            ->assertSee('25 PTS');

        $this->actingAs($user)->get(route('standings', ['season' => 2]))
            ->assertOk()
            ->assertSee('CURRENT // SEASON 2')
            ->assertSee('1 PTS');
    }
}
