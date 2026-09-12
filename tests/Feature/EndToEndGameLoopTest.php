<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Sponsor;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CarSeeder;
use Database\Seeders\DriverSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndGameLoopTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the complete End-to-End player lifecycle across all core and extended mechanics.
     */
    public function test_complete_player_journey_end_to_end(): void
    {
        // Pre-seed starter pool
        $this->seed(CarSeeder::class);
        $this->seed(DriverSeeder::class);

        // -------------------------------------------------------------
        // STEP 1: User Registration & Starter Team Provisioning
        // -------------------------------------------------------------
        $registerResponse = $this->post(route('register'), [
            'name' => 'Jean Todt',
            'email' => 'principal@antigravity.gp',
            'password' => 'supersecret123',
            'password_confirmation' => 'supersecret123',
        ]);

        $registerResponse->assertRedirect(route('team.create'));
        $this->assertAuthenticated();

        /** @var User $user */
        $user = User::where('email', 'principal@antigravity.gp')->first();
        $this->assertNotNull($user);

        $teamCreateResponse = $this->actingAs($user)->post(route('team.store'), [
            'name' => 'Scuderia Antigravity',
        ]);
        $teamCreateResponse->assertRedirect(route('dashboard'));

        $user->refresh();
        $team = $user->team;
        $this->assertNotNull($team);
        $this->assertSame('Scuderia Antigravity', $team->name);
        $this->assertSame(50000, $team->money);

        // Verify default starter car and lead driver
        $activeCar = $team->activeCar();
        $this->assertNotNull($activeCar);
        $this->assertTrue((bool) $activeCar->is_active);

        $leadDriver = $team->primaryDriver();
        $this->assertNotNull($leadDriver);
        $this->assertTrue((bool) $leadDriver->is_lead);

        // -------------------------------------------------------------
        // STEP 2: Driver Market Exploration & Lineup Management
        // -------------------------------------------------------------
        $marketDriver = Driver::factory()->create([
            'name' => 'Ayrton Senna',
            'team_id' => null,
            'salary' => 2500,
            'pace' => 88,
            'cornering' => 85,
            'consistency' => 82,
            'is_lead' => false,
        ]);

        // Visit driver market
        $marketPage = $this->actingAs($user)->get(route('drivers.market'));
        $marketPage->assertOk();
        $marketPage->assertSee('Ayrton Senna');

        // Hire the new driver
        $hireResponse = $this->actingAs($user)->post(route('drivers.hire', $marketDriver));
        $hireResponse->assertRedirect(route('drivers.index'));
        $this->assertSame($team->id, $marketDriver->fresh()->team_id);

        // Visit team roster page and verify driver is present
        $rosterPage = $this->actingAs($user)->get(route('drivers.index'));
        $rosterPage->assertOk();
        $rosterPage->assertSee('Ayrton Senna');

        // Promote new driver to Lead Driver
        $assignResponse = $this->actingAs($user)->post(route('drivers.set-lead', $marketDriver));
        $assignResponse->assertRedirect(route('drivers.index'));
        $this->assertTrue((bool) $marketDriver->fresh()->is_lead);
        $this->assertFalse((bool) $leadDriver->fresh()->is_lead);

        // -------------------------------------------------------------
        // STEP 3: Commercial Sponsorship Contract Signing (Option A)
        // -------------------------------------------------------------
        $sponsor = Sponsor::factory()->create([
            'name' => 'Shell Helix Ultra',
            'tier' => 'primary',
            'min_reputation' => 0,
            'signing_bonus' => 20000,
            'target_objective' => 'finish_race',
            'bonus_per_race' => 3000,
            'duration_races' => 4,
        ]);

        // Browse sponsors
        $sponsorsPage = $this->actingAs($user)->get(route('sponsors.index'));
        $sponsorsPage->assertOk();
        $sponsorsPage->assertSee('Shell Helix Ultra');

        // Sign contract
        $initialBalance = $team->fresh()->money;
        $signResponse = $this->actingAs($user)->post(route('sponsors.sign', $sponsor));
        $signResponse->assertRedirect(route('sponsors.index'));

        // Verify signing bonus was credited
        $team->refresh();
        $this->assertSame($initialBalance + 20000, $team->money);
        $this->assertDatabaseHas('team_sponsors', [
            'team_id' => $team->id,
            'sponsor_id' => $sponsor->id,
            'is_active' => true,
        ]);

        // -------------------------------------------------------------
        // STEP 4: Garage Development & Chassis Upgrades
        // -------------------------------------------------------------
        $garagePage = $this->actingAs($user)->get(route('garage.index'));
        $garagePage->assertOk();

        $initialSpeed = $activeCar->speed;
        $upgradeResponse = $this->actingAs($user)->post(route('garage.upgrades.purchase', $activeCar), [
            'part_type' => 'engine',
        ]);
        $upgradeResponse->assertRedirect(route('garage.show', $activeCar));

        $activeCar->refresh();
        $this->assertGreaterThan($initialSpeed, $activeCar->speed);

        // -------------------------------------------------------------
        // STEP 5: Race Briefing & Dynamic Tactics Configuration (Option C)
        // -------------------------------------------------------------
        $race = Race::factory()->create([
            'name' => 'Monaco Grand Prix',
            'laps' => 8,
            'track_type' => 'technical',
            'weather' => 'dry',
            'entry_fee' => 1500,
            'prize_pool' => 30000,
        ]);

        // View pre-race briefing
        $briefingPage = $this->actingAs($user)->get(route('races.show', $race));
        $briefingPage->assertOk();
        $briefingPage->assertSee('Pit-Wall Strategy Selector');

        // Run race with Soft Tires and Push Mode
        $raceResponse = $this->actingAs($user)->post(route('races.run', $race), [
            'tire_compound' => 'soft',
            'driving_mode' => 'push',
        ]);
        $raceResponse->assertRedirect(route('races.live', $race));

        // -------------------------------------------------------------
        // STEP 6: Live Pit-Wall Telemetry Monitor
        // -------------------------------------------------------------
        $livePage = $this->actingAs($user)->get(route('races.live', $race));
        $livePage->assertOk();
        $livePage->assertSee('SOFT (C3)');
        $livePage->assertSee('PUSH MODE');
        $livePage->assertSee('Ayrton Senna');

        // -------------------------------------------------------------
        // STEP 7: Official Post-Race Debrief & Financial Ledger Verification
        // -------------------------------------------------------------
        $resultsPage = $this->actingAs($user)->get(route('races.results', $race));
        $resultsPage->assertOk();
        $resultsPage->assertSee('Debrief & Financial Ledger');
        $resultsPage->assertSee('PUSH / SOFT');

        // Check RaceResult database record
        $result = RaceResult::where('team_id', $team->id)->where('race_id', $race->id)->first();
        $this->assertNotNull($result);
        $this->assertSame('push_soft', $result->strategy);
        $this->assertSame('finished', $result->status);

        // Check double-entry transaction records
        $this->assertTrue(Transaction::where('team_id', $team->id)->where('description', 'like', '%Entry Fee%')->exists());

        // -------------------------------------------------------------
        // STEP 8: Championship Standings & History Verification (Option B)
        // -------------------------------------------------------------
        $standingsPage = $this->actingAs($user)->get(route('standings'));
        $standingsPage->assertOk();
        $standingsPage->assertSee('Scuderia Antigravity');
        $standingsPage->assertSee('Ayrton Senna');

        // Race history archives
        $historyPage = $this->actingAs($user)->get(route('races.history'));
        $historyPage->assertOk();
        $historyPage->assertSee('Monaco Grand Prix');
    }
}
