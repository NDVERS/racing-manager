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

class RaceEconomySettlementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing race results.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $race = Race::factory()->create();
        $raceResult = RaceResult::factory()->create();

        $this->get(route('races.results', $race))->assertRedirect(route('login'));
        $this->get(route('race-results.show', $raceResult))->assertRedirect(route('login'));
    }

    /**
     * Test user cannot access race results belonging to another team.
     */
    public function test_user_cannot_access_other_teams_race_result(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id]);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id]);

        $raceResultB = RaceResult::factory()->create(['team_id' => $teamB->id]);

        $response = $this->actingAs($userA)->get(route('race-results.show', $raceResultB));

        $response->assertNotFound();
    }

    /**
     * Test running a race atomically settles entry fees, prize money, transactions, and reputation.
     */
    public function test_running_race_atomically_settles_economy_and_records_results(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Kuro Works',
            'money' => 50000,
            'reputation' => 10,
        ]);

        $car = Car::factory()->forTeam($team)->create([
            'name' => 'Kuro GT',
            'is_active' => true,
        ]);

        $driver = Driver::factory()->forTeam($team)->lead()->create([
            'name' => 'Alex Carter',
        ]);

        $race = Race::factory()->create([
            'name' => 'Cimahi Grand Prix',
            'entry_fee' => 1500,
            'prize_pool' => 20000,
        ]);

        $initialMoney = 50000;
        $initialRep = 10;

        // Run simulation
        $response = $this->actingAs($user)->post(route('races.run', $race));

        $response->assertRedirect(route('races.live', $race));

        // 1. Verify RaceResult record is created in database
        $this->assertDatabaseHas('race_results', [
            'race_id' => $race->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'status' => 'finished',
        ]);

        $result = RaceResult::where('team_id', $team->id)->where('race_id', $race->id)->first();
        $this->assertNotNull($result);
        $this->assertGreaterThanOrEqual(1, $result->position);
        $this->assertLessThanOrEqual(10, $result->position);
        $this->assertGreaterThan(0, $result->prize_money);
        $this->assertGreaterThan(0, $result->reputation_earned);

        // 2. Verify Transactions (Entry fee expense + Prize income)
        $this->assertDatabaseHas('transactions', [
            'team_id' => $team->id,
            'type' => 'expense',
            'amount' => -1500,
            'reference_id' => $race->id,
            'reference_type' => Race::class,
        ]);

        $this->assertDatabaseHas('transactions', [
            'team_id' => $team->id,
            'type' => 'income',
            'amount' => $result->prize_money,
            'reference_id' => $race->id,
            'reference_type' => Race::class,
        ]);

        // 3. Verify Team financial balances
        $freshTeam = $team->fresh();
        $expectedMoney = $initialMoney - 1500 + $result->prize_money;
        $expectedRep = $initialRep + $result->reputation_earned;

        $this->assertSame($expectedMoney, $freshTeam->money);
        $this->assertSame($expectedRep, $freshTeam->reputation);
    }

    /**
     * Test user can view the official post-race debrief and financial summary page.
     */
    public function test_user_can_view_official_race_debrief_and_financial_ledger(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Apex Motorsport',
            'money' => 62000,
            'reputation' => 45,
        ]);

        $car = Car::factory()->forTeam($team)->create([
            'name' => 'Apex GT-1',
            'is_active' => true,
        ]);

        $driver = Driver::factory()->forTeam($team)->lead()->create([
            'name' => 'Elena Rostova',
        ]);

        $race = Race::factory()->create([
            'name' => 'Jakarta Night Prix',
            'entry_fee' => 2000,
            'prize_pool' => 30000,
        ]);

        $result = RaceResult::factory()->create([
            'race_id' => $race->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 2,
            'race_time' => '14:22.450',
            'prize_money' => 7500,
            'reputation_earned' => 30,
            'status' => 'finished',
            'simulation_log' => [
                'standings' => [
                    [
                        'position' => 1,
                        'is_player' => false,
                        'driver_name' => 'Kenji Sato',
                        'team_name' => 'Zenith Motorsport',
                        'car_name' => 'Zenith Type-R',
                        'total_time' => '14:20.120',
                        'gap' => 'LEADER',
                        'fastest_lap' => '1:19.450',
                    ],
                    [
                        'position' => 2,
                        'is_player' => true,
                        'driver_name' => 'Elena Rostova',
                        'team_name' => 'Apex Motorsport',
                        'car_name' => 'Apex GT-1',
                        'total_time' => '14:22.450',
                        'gap' => '+2.330s',
                        'fastest_lap' => '1:19.880',
                    ],
                ],
            ],
        ]);

        // 1. Access via route('races.results', $race)
        $response = $this->actingAs($user)->get(route('races.results', $race));

        $response->assertOk();
        $response->assertViewIs('races.results');
        $response->assertSee('Jakarta Night Prix');
        $response->assertSee('Elena Rostova');
        $response->assertSee('P2');
        $response->assertSee('7,500'); // Prize money
        $response->assertSee('2,000'); // Entry fee
        $response->assertSee('+5,500'); // Net profit (7500 - 2000)
        $response->assertSee('62,000'); // Current treasury

        // 2. Access via route('race-results.show', $result)
        $directResponse = $this->actingAs($user)->get(route('race-results.show', $result));
        $directResponse->assertOk();
        $directResponse->assertViewIs('races.results');
    }
}
