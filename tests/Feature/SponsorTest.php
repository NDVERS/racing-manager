<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\Sponsor;
use App\Models\Team;
use App\Models\TeamSponsor;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\SponsorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing sponsors page.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('sponsors.index'))->assertRedirect(route('login'));
    }

    /**
     * Test user without team is redirected to onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('sponsors.index'))->assertRedirect(route('team.create'));
    }

    /**
     * Test user can view the sponsors hub with active contracts and market deals.
     */
    public function test_user_can_view_sponsors_hub(): void
    {
        $this->seed(SponsorSeeder::class);

        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
            'reputation' => 20,
        ]);

        $response = $this->actingAs($user)->get(route('sponsors.index'));

        $response->assertOk();
        $response->assertViewIs('sponsors.index');
        $response->assertSee('Sponsors & Commercial Hub', false);
        $response->assertSee('Apex Energy');
        $response->assertSee('K-Battery Tech');
        $response->assertSee('No Active Sponsor Contracts');
    }

    /**
     * Test cannot sign sponsor if team reputation is below minimum requirement.
     */
    public function test_cannot_sign_sponsor_if_reputation_too_low(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
            'reputation' => 10,
        ]);

        $eliteSponsor = Sponsor::factory()->create([
            'name' => 'Titan Global Partners',
            'tier' => 'primary',
            'min_reputation' => 50,
            'signing_bonus' => 40000,
            'bonus_per_race' => 5000,
            'duration_races' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('sponsors.sign', $eliteSponsor));

        $response->assertRedirect(route('sponsors.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('team_sponsors', [
            'team_id' => $team->id,
            'sponsor_id' => $eliteSponsor->id,
        ]);
        $this->assertEquals(50000, $team->fresh()->money);
    }

    /**
     * Test cannot sign the same sponsor twice when actively under contract.
     */
    public function test_cannot_sign_same_active_sponsor_twice(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
            'reputation' => 50,
        ]);

        $sponsor = Sponsor::factory()->create([
            'tier' => 'secondary',
            'min_reputation' => 10,
            'signing_bonus' => 10000,
            'duration_races' => 3,
        ]);

        TeamSponsor::create([
            'team_id' => $team->id,
            'sponsor_id' => $sponsor->id,
            'races_remaining' => 3,
            'is_active' => true,
            'signed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('sponsors.sign', $sponsor));

        $response->assertRedirect(route('sponsors.index'));
        $response->assertSessionHas('warning');

        $this->assertCount(1, TeamSponsor::where('team_id', $team->id)->where('sponsor_id', $sponsor->id)->get());
    }

    /**
     * Test cannot exceed sponsor slot limits (max 1 primary, max 2 secondary).
     */
    public function test_cannot_exceed_sponsor_slot_limits(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
            'reputation' => 100,
        ]);

        $primarySponsor1 = Sponsor::factory()->create(['name' => 'P1', 'tier' => 'primary', 'min_reputation' => 0]);
        $primarySponsor2 = Sponsor::factory()->create(['name' => 'P2', 'tier' => 'primary', 'min_reputation' => 0]);

        // Sign 1st primary
        $this->actingAs($user)->post(route('sponsors.sign', $primarySponsor1))->assertSessionHas('success');

        // Attempt 2nd primary -> should be rejected
        $this->actingAs($user)->post(route('sponsors.sign', $primarySponsor2))->assertSessionHas('error');
        $this->assertDatabaseMissing('team_sponsors', ['team_id' => $team->id, 'sponsor_id' => $primarySponsor2->id]);

        // Secondary sponsors limit (max 2)
        $secondary1 = Sponsor::factory()->create(['name' => 'S1', 'tier' => 'secondary', 'min_reputation' => 0]);
        $secondary2 = Sponsor::factory()->create(['name' => 'S2', 'tier' => 'secondary', 'min_reputation' => 0]);
        $secondary3 = Sponsor::factory()->create(['name' => 'S3', 'tier' => 'secondary', 'min_reputation' => 0]);

        $this->actingAs($user)->post(route('sponsors.sign', $secondary1))->assertSessionHas('success');
        $this->actingAs($user)->post(route('sponsors.sign', $secondary2))->assertSessionHas('success');

        // 3rd secondary attempt -> should fail
        $this->actingAs($user)->post(route('sponsors.sign', $secondary3))->assertSessionHas('error');
        $this->assertDatabaseMissing('team_sponsors', ['team_id' => $team->id, 'sponsor_id' => $secondary3->id]);
    }

    /**
     * Test signing a sponsor grants immediate signing bonus and logs transaction.
     */
    public function test_signing_sponsor_grants_immediate_bonus_and_creates_transaction(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
            'reputation' => 30,
        ]);

        $sponsor = Sponsor::factory()->create([
            'name' => 'Velocity Petrol',
            'tier' => 'primary',
            'min_reputation' => 10,
            'signing_bonus' => 25000,
            'duration_races' => 4,
        ]);

        $response = $this->actingAs($user)->post(route('sponsors.sign', $sponsor));

        $response->assertRedirect(route('sponsors.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('team_sponsors', [
            'team_id' => $team->id,
            'sponsor_id' => $sponsor->id,
            'races_remaining' => 4,
            'is_active' => 1,
        ]);

        $this->assertEquals(75000, $team->fresh()->money);

        $this->assertDatabaseHas('transactions', [
            'team_id' => $team->id,
            'type' => 'income',
            'amount' => 25000,
            'balance_after' => 75000,
            'description' => 'Sponsor Signing Bonus: Velocity Petrol',
            'reference_id' => $sponsor->id,
            'reference_type' => Sponsor::class,
        ]);
    }

    /**
     * Test race execution decrements contract duration, evaluates objective, and pays bonus.
     */
    public function test_race_settlement_evaluates_sponsor_objective_and_decrements_duration(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
            'reputation' => 30,
        ]);

        $car = Car::factory()->create([
            'team_id' => $team->id,
            'is_active' => true,
            'speed' => 90,
            'acceleration' => 90,
            'handling' => 90,
            'braking' => 90,
            'reliability' => 100,
        ]);

        $driver = Driver::factory()->create([
            'team_id' => $team->id,
            'is_lead' => true,
            'pace' => 95,
            'cornering' => 95,
            'consistency' => 95,
            'experience' => 95,
        ]);

        $race = Race::factory()->create([
            'name' => 'Monza GP',
            'entry_fee' => 1000,
            'prize_pool' => 50000,
        ]);

        $sponsor = Sponsor::factory()->create([
            'name' => 'Speedline Aero',
            'tier' => 'secondary',
            'min_reputation' => 0,
            'signing_bonus' => 5000,
            'target_objective' => 'finish_race',
            'bonus_per_race' => 3000,
            'duration_races' => 3,
        ]);

        $contract = TeamSponsor::create([
            'team_id' => $team->id,
            'sponsor_id' => $sponsor->id,
            'races_remaining' => 3,
            'is_active' => true,
            'signed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('races.run', $race));

        $response->assertRedirect(route('races.live', $race));

        $contract->refresh();
        $this->assertEquals(2, $contract->races_remaining);
        $this->assertTrue($contract->is_active);

        // Verify sponsor objective bonus transaction was recorded
        $this->assertDatabaseHas('transactions', [
            'team_id' => $team->id,
            'type' => 'income',
            'amount' => 3000,
            'reference_id' => $sponsor->id,
            'reference_type' => Sponsor::class,
        ]);
    }

    /**
     * Test sponsor contract expires and is deactivated when races_remaining reaches 0.
     */
    public function test_sponsor_contract_expires_when_duration_reaches_zero(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
            'reputation' => 30,
        ]);

        $car = Car::factory()->create([
            'team_id' => $team->id,
            'is_active' => true,
        ]);

        $driver = Driver::factory()->create([
            'team_id' => $team->id,
            'is_lead' => true,
        ]);

        $race = Race::factory()->create(['entry_fee' => 500]);

        $sponsor = Sponsor::factory()->create([
            'name' => 'One-Race Partner',
            'tier' => 'primary',
            'min_reputation' => 0,
            'target_objective' => 'finish_race',
            'bonus_per_race' => 1000,
            'duration_races' => 1,
        ]);

        $contract = TeamSponsor::create([
            'team_id' => $team->id,
            'sponsor_id' => $sponsor->id,
            'races_remaining' => 1,
            'is_active' => true,
            'signed_at' => now(),
        ]);

        $this->actingAs($user)->post(route('races.run', $race));

        $contract->refresh();
        $this->assertEquals(0, $contract->races_remaining);
        $this->assertFalse($contract->is_active);
    }

    /**
     * Test sponsor contracts are properly isolated between different teams.
     */
    public function test_sponsors_are_isolated_between_teams(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id, 'name' => 'Team Alpha']);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id, 'name' => 'Team Beta']);

        $sponsorA = Sponsor::factory()->create(['name' => 'Sponsor For Alpha']);
        $sponsorB = Sponsor::factory()->create(['name' => 'Sponsor For Beta']);

        TeamSponsor::create([
            'team_id' => $teamA->id,
            'sponsor_id' => $sponsorA->id,
            'races_remaining' => 3,
            'is_active' => true,
        ]);

        TeamSponsor::create([
            'team_id' => $teamB->id,
            'sponsor_id' => $sponsorB->id,
            'races_remaining' => 3,
            'is_active' => true,
        ]);

        $responseA = $this->actingAs($userA)->get(route('sponsors.index'));
        $responseA->assertSee('Sponsor For Alpha');
        $responseA->assertDontSee('Sponsor For Beta (Active)');

        $this->assertTrue($teamA->hasActiveSponsor($sponsorA->id));
        $this->assertFalse($teamA->hasActiveSponsor($sponsorB->id));
    }

    /**
     * Test SponsorSeeder populates at least 18 diverse corporate sponsors.
     */
    public function test_seeder_provides_at_least_18_diverse_sponsors(): void
    {
        $this->seed(SponsorSeeder::class);

        $this->assertGreaterThanOrEqual(18, Sponsor::count());
        $this->assertGreaterThanOrEqual(5, Sponsor::where('tier', 'primary')->count());
        $this->assertGreaterThanOrEqual(10, Sponsor::where('tier', 'secondary')->count());
    }

    /**
     * Test sponsors market displays 9 open deals and rotates when a contract expires.
     */
    public function test_sponsors_market_displays_nine_open_deals_and_rotates_expired_sponsors(): void
    {
        $this->seed(SponsorSeeder::class);

        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Chronos GP',
            'reputation' => 100,
        ]);

        // Access market initially
        $response = $this->actingAs($user)->get(route('sponsors.index'));
        $response->assertOk();

        $marketSponsors = $response->viewData('allSponsors');
        $this->assertCount(9, $marketSponsors);

        // Sign 1 sponsor from the initial 9
        $firstSponsor = $marketSponsors->first();
        $this->actingAs($user)->post(route('sponsors.sign', $firstSponsor));

        // Now view market again: active sponsor must NOT be in the open market proposals
        $responseAfterSign = $this->actingAs($user)->get(route('sponsors.index'));
        $marketAfterSign = $responseAfterSign->viewData('allSponsors');
        $this->assertCount(9, $marketAfterSign);
        $this->assertFalse($marketAfterSign->contains('id', $firstSponsor->id));

        // Simulate expiration of the signed sponsor contract
        $contract = $team->teamSponsors()->where('sponsor_id', $firstSponsor->id)->first();
        $contract->update(['is_active' => false, 'races_remaining' => 0]);

        // After expiration, fresh sponsors from pool are prioritized over the expired sponsor
        $responseAfterExpire = $this->actingAs($user)->get(route('sponsors.index'));
        $marketAfterExpire = $responseAfterExpire->viewData('allSponsors');
        $this->assertCount(9, $marketAfterExpire);
        // The newly proposed 9 deals are fresh uncontracted brands from the large pool
        $this->assertFalse($marketAfterExpire->contains('id', $firstSponsor->id));
    }
}
