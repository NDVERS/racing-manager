<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarUpgrade;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarUpgradeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when attempting to upgrade.
     */
    public function test_guest_cannot_purchase_upgrade(): void
    {
        $car = Car::factory()->create();

        $response = $this->post(route('garage.upgrades.purchase', $car), [
            'part_type' => 'engine',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Test user without a team is redirected to onboarding.
     */
    public function test_user_without_team_cannot_purchase_upgrade(): void
    {
        $user = User::factory()->create();
        $car = Car::factory()->create();

        $response = $this->actingAs($user)->post(route('garage.upgrades.purchase', $car), [
            'part_type' => 'engine',
        ]);

        $response->assertRedirect(route('team.create'));
    }

    /**
     * Test user can view the R&D upgrade workshop matrix in car technical inspection.
     */
    public function test_user_can_view_upgrade_workshop_matrix(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
        ]);

        $car = Car::factory()->forTeam($team)->create([
            'name' => 'Kuro GT',
            'speed' => 65,
            'acceleration' => 60,
            'handling' => 58,
            'braking' => 55,
            'reliability' => 80,
        ]);

        CarUpgrade::factory()->create([
            'car_id' => $car->id,
            'part_type' => 'engine',
            'level' => 1,
            'cost' => 3000,
            'stat_increases' => ['speed' => 3],
        ]);

        $response = $this->actingAs($user)->get(route('garage.show', $car));

        $response->assertOk();
        $response->assertViewIs('garage.show');
        $response->assertSee('Powertrain & Turbo');
        $response->assertSee('Drivetrain & Gearbox');
        $response->assertSee('Aero Package & Downforce');
        $response->assertSee('Carbon-Ceramic Braking System');
        $response->assertSee('Cooling & Structural Durability');
        $response->assertSee('TIER 1 / 5');
        $response->assertSee('50,000');
    }

    /**
     * Test user is strictly blocked (404) when trying to upgrade another team's car.
     */
    public function test_user_cannot_upgrade_other_teams_car(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id, 'money' => 50000]);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id]);

        $carB = Car::factory()->forTeam($teamB)->create([
            'speed' => 60,
        ]);

        $response = $this->actingAs($userA)->post(route('garage.upgrades.purchase', $carB), [
            'part_type' => 'engine',
        ]);

        $response->assertNotFound();
        $this->assertSame(60, $carB->fresh()->speed);
        $this->assertSame(50000, $teamA->fresh()->money);
        $this->assertDatabaseCount('car_upgrades', 0);
    }

    /**
     * Test successfully purchasing a Tier 1 upgrade atomically.
     */
    public function test_successful_tier_1_upgrade_purchase(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 50000,
        ]);

        $car = Car::factory()->forTeam($team)->create([
            'speed' => 60,
            'level' => 1,
        ]);

        $cost = CarUpgrade::getCostForLevel('engine', 1); // 3000

        $response = $this->actingAs($user)->post(route('garage.upgrades.purchase', $car), [
            'part_type' => 'engine',
        ]);

        $response->assertRedirect(route('garage.show', $car));
        $response->assertSessionHas('success');

        // 1. Team balance deducted
        $this->assertSame(50000 - $cost, $team->fresh()->money);

        // 2. Transaction expense logged
        $this->assertDatabaseHas('transactions', [
            'team_id' => $team->id,
            'type' => 'expense',
            'amount' => -$cost,
            'balance_after' => 50000 - $cost,
            'reference_id' => $car->id,
            'reference_type' => Car::class,
        ]);

        // 3. CarUpgrade record created
        $this->assertDatabaseHas('car_upgrades', [
            'car_id' => $car->id,
            'part_type' => 'engine',
            'level' => 1,
            'cost' => $cost,
        ]);

        // 4. Car attribute boosted (+3 speed)
        $this->assertSame(63, $car->fresh()->speed);
    }

    /**
     * Test purchasing subsequent upgrade tiers correctly updates existing records and increases stats.
     */
    public function test_upgrading_existing_part_to_tier_2(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 47000,
        ]);

        $car = Car::factory()->forTeam($team)->create([
            'acceleration' => 60,
        ]);

        // Existing Level 1 Transmission Upgrade
        CarUpgrade::factory()->create([
            'car_id' => $car->id,
            'part_type' => 'transmission',
            'level' => 1,
            'cost' => 3000,
            'stat_increases' => ['acceleration' => 3],
        ]);

        $tier2Cost = CarUpgrade::getCostForLevel('transmission', 2); // 6000

        $response = $this->actingAs($user)->post(route('garage.upgrades.purchase', $car), [
            'part_type' => 'transmission',
        ]);

        $response->assertRedirect(route('garage.show', $car));
        $response->assertSessionHas('success');

        // Verify balance
        $this->assertSame(47000 - $tier2Cost, $team->fresh()->money);

        // Verify upgraded record
        $upgrade = CarUpgrade::where('car_id', $car->id)->where('part_type', 'transmission')->first();
        $this->assertNotNull($upgrade);
        $this->assertSame(2, $upgrade->level);
        $this->assertSame(3000 + $tier2Cost, $upgrade->cost);
        $this->assertSame(['acceleration' => 6], $upgrade->stat_increases);

        // Verify stat increment
        $this->assertSame(63, $car->fresh()->acceleration);
    }

    /**
     * Test upgrade rejection when team treasury is insufficient.
     */
    public function test_upgrade_rejected_when_credits_are_insufficient(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 1000, // Not enough for 3000 CR upgrade
        ]);

        $car = Car::factory()->forTeam($team)->create([
            'handling' => 50,
        ]);

        $response = $this->actingAs($user)->post(route('garage.upgrades.purchase', $car), [
            'part_type' => 'aerodynamics',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame(1000, $team->fresh()->money);
        $this->assertSame(50, $car->fresh()->handling);
        $this->assertDatabaseCount('car_upgrades', 0);
        $this->assertDatabaseCount('transactions', 0);
    }

    /**
     * Test upgrade rejection when component has reached Max Level (Level 5).
     */
    public function test_upgrade_rejected_when_component_is_at_max_level(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 100000,
        ]);

        $car = Car::factory()->forTeam($team)->create([
            'braking' => 70,
        ]);

        CarUpgrade::factory()->create([
            'car_id' => $car->id,
            'part_type' => 'brakes',
            'level' => CarUpgrade::MAX_LEVEL, // Level 5
            'cost' => 56000,
            'stat_increases' => ['braking' => 15],
        ]);

        $response = $this->actingAs($user)->post(route('garage.upgrades.purchase', $car), [
            'part_type' => 'brakes',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame(100000, $team->fresh()->money);
        $this->assertSame(70, $car->fresh()->braking);
        $this->assertDatabaseCount('transactions', 0);
    }

    /**
     * Test accumulation of upgrades elevates the overall Chassis Tier.
     */
    public function test_accumulating_upgrades_elevates_chassis_tier(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 100000,
        ]);

        $car = Car::factory()->forTeam($team)->create([
            'level' => 1,
            'reliability' => 70,
        ]);

        // Install 3 upgrades to hit total 3 levels (e.g. reliability L1, L2, L3)
        $this->actingAs($user)->post(route('garage.upgrades.purchase', $car), ['part_type' => 'reliability']);
        $this->actingAs($user)->post(route('garage.upgrades.purchase', $car), ['part_type' => 'reliability']);
        $this->actingAs($user)->post(route('garage.upgrades.purchase', $car), ['part_type' => 'reliability']);

        // 3 levels total -> 1 + floor(3 / 3) = Tier 2
        $this->assertSame(2, $car->fresh()->level);
        $this->assertSame(70 + (4 * 3), $car->fresh()->reliability);
    }

    /**
     * Test invalid part_type is rejected with validation error.
     */
    public function test_invalid_part_type_fails_validation(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);
        $car = Car::factory()->forTeam($team)->create();

        $response = $this->actingAs($user)->post(route('garage.upgrades.purchase', $car), [
            'part_type' => 'flux_capacitor',
        ]);

        $response->assertSessionHasErrors('part_type');
    }
}
