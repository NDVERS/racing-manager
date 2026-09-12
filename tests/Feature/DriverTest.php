<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login from all driver routes.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $driver = Driver::factory()->create();

        $this->get(route('drivers.index'))->assertRedirect(route('login'));
        $this->get(route('drivers.market'))->assertRedirect(route('login'));
        $this->get(route('drivers.show', $driver))->assertRedirect(route('login'));
        $this->post(route('drivers.set-lead', $driver))->assertRedirect(route('login'));
        $this->post(route('drivers.hire', $driver))->assertRedirect(route('login'));
    }

    /**
     * Test user without a team is redirected to onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();
        $driver = Driver::factory()->create();

        $this->actingAs($user)->get(route('drivers.index'))->assertRedirect(route('team.create'));
        $this->actingAs($user)->get(route('drivers.market'))->assertRedirect(route('team.create'));
        $this->actingAs($user)->get(route('drivers.show', $driver))->assertRedirect(route('team.create'));
        $this->actingAs($user)->post(route('drivers.set-lead', $driver))->assertRedirect(route('team.create'));
        $this->actingAs($user)->post(route('drivers.hire', $driver))->assertRedirect(route('team.create'));
    }

    /**
     * Test user can view their team's contracted drivers.
     */
    public function test_user_can_view_driver_lineup_roster(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'name' => 'Redline Racing']);

        $driver1 = Driver::factory()->forTeam($team)->create([
            'name' => 'Alex Carter',
            'pace' => 70,
            'is_lead' => true,
            'salary' => 1500,
        ]);

        $driver2 = Driver::factory()->forTeam($team)->create([
            'name' => 'Marcus Vance',
            'pace' => 65,
            'is_lead' => false,
            'salary' => 1200,
        ]);

        $response = $this->actingAs($user)->get(route('drivers.index'));

        $response->assertOk();
        $response->assertViewIs('drivers.index');
        $response->assertSee('Alex Carter');
        $response->assertSee('Marcus Vance');
        $response->assertSee('LEAD DRIVER');
        $response->assertSee('RESERVE SEAT');
    }

    /**
     * Test user can view complete dossier of their own driver.
     */
    public function test_user_can_view_driver_technical_dossier(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $driver = Driver::factory()->forTeam($team)->create([
            'name' => 'Elena Rostova',
            'pace' => 82,
            'cornering' => 80,
            'consistency' => 78,
            'overtaking' => 75,
            'defensive' => 74,
            'racecraft' => 76,
            'experience' => 60,
            'salary' => 2500,
        ]);

        $race = Race::factory()->create(['name' => 'Monza Speed Trophy']);
        RaceResult::factory()->create([
            'race_id' => $race->id,
            'team_id' => $team->id,
            'driver_id' => $driver->id,
            'position' => 2,
            'prize_money' => 15000,
            'reputation_earned' => 25,
        ]);

        $response = $this->actingAs($user)->get(route('drivers.show', $driver));

        $response->assertOk();
        $response->assertViewIs('drivers.show');
        $response->assertSee('Elena Rostova');
        $response->assertSee('82'); // Pace
        $response->assertSee('2,500 CR');
        $response->assertSee('Monza Speed Trophy');
        $response->assertSee('P2');
    }

    /**
     * Test user is strictly blocked (404) when inspecting another team's driver.
     */
    public function test_user_cannot_view_other_teams_driver(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id]);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id]);

        $driverB = Driver::factory()->forTeam($teamB)->create([
            'name' => 'Rival Driver',
        ]);

        $response = $this->actingAs($userA)->get(route('drivers.show', $driverB));

        $response->assertNotFound();
    }

    /**
     * Test user cannot promote another team's driver to lead.
     */
    public function test_user_cannot_set_lead_on_other_teams_driver(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id]);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id]);

        $driverB = Driver::factory()->forTeam($teamB)->create([
            'is_lead' => false,
        ]);

        $response = $this->actingAs($userA)->post(route('drivers.set-lead', $driverB));

        $response->assertNotFound();
        $this->assertFalse($driverB->fresh()->is_lead);
    }

    /**
     * Test user can successfully promote a driver to lead driver.
     */
    public function test_user_can_set_lead_race_driver(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $driver1 = Driver::factory()->forTeam($team)->create([
            'name' => 'Driver One',
            'is_lead' => true,
        ]);

        $driver2 = Driver::factory()->forTeam($team)->create([
            'name' => 'Driver Two',
            'is_lead' => false,
        ]);

        $response = $this->actingAs($user)->post(route('drivers.set-lead', $driver2));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertFalse($driver1->fresh()->is_lead);
        $this->assertTrue($driver2->fresh()->is_lead);
        $this->assertSame($driver2->id, $team->fresh()->primaryDriver()->id);
    }

    /**
     * Test user can view the free agent driver market.
     */
    public function test_user_can_view_free_agent_market(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $freeAgent = Driver::factory()->create([
            'team_id' => null,
            'name' => 'Uncontracted Prospect',
            'salary' => 1400,
        ]);

        $response = $this->actingAs($user)->get(route('drivers.market'));

        $response->assertOk();
        $response->assertViewIs('drivers.market');
        $response->assertSee('Uncontracted Prospect');
        $response->assertSee('1,400 CR');
        $response->assertSee('4,200 CR'); // 1400 * 3 signing fee
    }

    /**
     * Test user can successfully recruit a free agent driver.
     */
    public function test_user_can_recruit_free_agent_driver(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
        ]);

        $freeAgent = Driver::factory()->create([
            'team_id' => null,
            'name' => 'Kenji Sato',
            'salary' => 3000, // 3000 * 3 = 9000 hiring fee
        ]);

        $response = $this->actingAs($user)->post(route('drivers.hire', $freeAgent));

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('success');

        $this->assertSame(41000, $team->fresh()->money);
        $this->assertSame($team->id, $freeAgent->fresh()->team_id);

        $this->assertDatabaseHas('transactions', [
            'team_id' => $team->id,
            'type' => 'expense',
            'amount' => -9000,
            'balance_after' => 41000,
            'reference_id' => $freeAgent->id,
            'reference_type' => Driver::class,
        ]);
    }

    /**
     * Test recruitment is rejected if team has insufficient funds.
     */
    public function test_recruitment_fails_with_insufficient_funds(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 2000,
        ]);

        $freeAgent = Driver::factory()->create([
            'team_id' => null,
            'name' => 'Top Tier Legend',
            'salary' => 3000, // hiring cost 9000
        ]);

        $response = $this->actingAs($user)->post(route('drivers.hire', $freeAgent));

        $response->assertRedirect(route('drivers.market'));
        $response->assertSessionHas('warning');

        $this->assertSame(2000, $team->fresh()->money);
        $this->assertNull($freeAgent->fresh()->team_id);
        $this->assertSame(0, Transaction::where('team_id', $team->id)->count());
    }

    /**
     * Test user cannot hire a driver who is already under contract with another team.
     */
    public function test_cannot_hire_already_contracted_driver(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create([
            'user_id' => $userA->id,
            'money' => 50000,
        ]);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id]);

        $driverB = Driver::factory()->forTeam($teamB)->create([
            'salary' => 2000,
        ]);

        $response = $this->actingAs($userA)->post(route('drivers.hire', $driverB));

        $response->assertRedirect(route('drivers.market'));
        $response->assertSessionHas('warning');

        $this->assertSame(50000, $teamA->fresh()->money);
        $this->assertSame($teamB->id, $driverB->fresh()->team_id);
    }
}
