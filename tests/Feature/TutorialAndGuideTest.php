<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarUpgrade;
use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorialAndGuideTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing handbook and bonus claim.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('guide'))->assertRedirect(route('login'));
        $this->post(route('tutorial.claim-bonus'))->assertRedirect(route('login'));
    }

    /**
     * Test user without team is redirected to onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('guide'))->assertRedirect(route('team.create'));
        $this->actingAs($user)->post(route('tutorial.claim-bonus'))->assertRedirect(route('team.create'));
    }

    /**
     * Test user can access Team Principal Handbook page.
     */
    public function test_user_can_view_handbook_guide(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'name' => 'Kuro Racing']);

        $response = $this->actingAs($user)->get(route('guide'));

        $response->assertOk();
        $response->assertViewIs('guide.index');
        $response->assertSee('Team Principal Handbook');
        $response->assertSee('Constructor Economics', false);
        $response->assertSee('R&D Component Physics', false);
        $response->assertSee('Race Tactics', false);
        $response->assertSee('Official FIA Championship Scoring', false);
    }

    /**
     * Test onboarding directives checklist progress detection on dashboard.
     */
    public function test_dashboard_directives_progress_tracking(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $car = Car::factory()->forTeam($team)->create(['is_active' => true]);
        $driver = Driver::factory()->forTeam($team)->create(['is_lead' => true]);

        // Initial: 2 of 4 (Car & Driver ready) -> 50%
        $response1 = $this->actingAs($user)->get(route('dashboard'));
        $response1->assertOk();
        $response1->assertSee('2 / 4 MILESTONES');
        $response1->assertSee('50%');

        // Add an upgrade -> 3 of 4 -> 75%
        CarUpgrade::factory()->create([
            'car_id' => $car->id,
            'part_type' => 'engine',
            'level' => 1,
            'cost' => 3000,
        ]);

        $response2 = $this->actingAs($user)->get(route('dashboard'));
        $response2->assertOk();
        $response2->assertSee('3 / 4 MILESTONES');
        $response2->assertSee('75%');

        // Add a completed race result -> 4 of 4 -> 100%
        $race = Race::factory()->create();
        RaceResult::factory()->create([
            'race_id' => $race->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 2,
        ]);

        $response3 = $this->actingAs($user)->get(route('dashboard'));
        $response3->assertOk();
        $response3->assertSee('100%');
        $response3->assertSee('READY TO CLAIM GRANT');
        $response3->assertSee('Claim +5,000 CR', false);
    }

    /**
     * Test claiming tutorial onboarding bonus when all milestones are completed.
     */
    public function test_claiming_onboarding_bonus_grant(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
            'reputation' => 10,
        ]);

        $car = Car::factory()->forTeam($team)->create(['is_active' => true]);
        $driver = Driver::factory()->forTeam($team)->create(['is_lead' => true]);

        CarUpgrade::factory()->create([
            'car_id' => $car->id,
            'part_type' => 'engine',
            'level' => 1,
        ]);

        $race = Race::factory()->create();
        RaceResult::factory()->create([
            'race_id' => $race->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
            'position' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('tutorial.claim-bonus'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Verify balance and reputation additions
        $this->assertSame(55000, $team->fresh()->money);
        $this->assertSame(35, $team->fresh()->reputation);

        // Verify transaction entry
        $this->assertDatabaseHas('transactions', [
            'team_id' => $team->id,
            'type' => 'income',
            'amount' => 5000,
            'balance_after' => 55000,
            'description' => 'Team Principal Onboarding Bonus Grant',
        ]);

        // Prevent duplicate claim
        $duplicateResponse = $this->actingAs($user)->post(route('tutorial.claim-bonus'));
        $duplicateResponse->assertRedirect();
        $duplicateResponse->assertSessionHas('warning');
        $this->assertSame(55000, $team->fresh()->money);
    }

    /**
     * Test claiming bonus is rejected if milestones are incomplete.
     */
    public function test_claiming_bonus_rejected_when_incomplete(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
        ]);

        // No car, driver, or upgrades
        $response = $this->actingAs($user)->post(route('tutorial.claim-bonus'));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame(50000, $team->fresh()->money);
        $this->assertDatabaseCount('transactions', 0);
    }
}
