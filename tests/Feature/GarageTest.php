<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarUpgrade;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GarageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing garage endpoints.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $car = Car::factory()->create();

        $this->get(route('garage.index'))->assertRedirect(route('login'));
        $this->get(route('garage.show', $car))->assertRedirect(route('login'));
        $this->post(route('garage.set-active', $car))->assertRedirect(route('login'));
    }

    /**
     * Test user without a team is redirected to onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();
        $car = Car::factory()->create();

        $this->actingAs($user)->get(route('garage.index'))->assertRedirect(route('team.create'));
        $this->actingAs($user)->get(route('garage.show', $car))->assertRedirect(route('team.create'));
        $this->actingAs($user)->post(route('garage.set-active', $car))->assertRedirect(route('team.create'));
    }

    /**
     * Test user can view their team's garage roster.
     */
    public function test_user_can_view_garage_fleet_roster(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'name' => 'Apex Works']);

        $car1 = Car::factory()->forTeam($team)->create([
            'name' => 'Apex GT-1',
            'speed' => 70,
            'is_active' => true,
        ]);

        $car2 = Car::factory()->forTeam($team)->create([
            'name' => 'Apex Proto-2',
            'speed' => 60,
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('garage.index'));

        $response->assertOk();
        $response->assertViewIs('garage.index');
        $response->assertSee('Apex GT-1');
        $response->assertSee('Apex Proto-2');
        $response->assertSee('ACTIVE');
        $response->assertSee('STANDBY');
    }

    /**
     * Test user can inspect technical specs of their own car.
     */
    public function test_user_can_view_car_technical_inspection_sheet(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $car = Car::factory()->forTeam($team)->create([
            'name' => 'Velocity V8',
            'speed' => 85,
            'acceleration' => 80,
            'handling' => 75,
            'braking' => 70,
            'reliability' => 95,
        ]);

        CarUpgrade::factory()->create([
            'car_id' => $car->id,
            'part_type' => 'engine',
            'level' => 1,
            'cost' => 5000,
            'stat_increases' => ['speed' => 4],
        ]);

        $response = $this->actingAs($user)->get(route('garage.show', $car));

        $response->assertOk();
        $response->assertViewIs('garage.show');
        $response->assertSee('Velocity V8');
        $response->assertSee('Engine Package');
        $response->assertSee('85'); // Speed
        $response->assertSee('80'); // Acceleration
        $response->assertSee('95%'); // Reliability
    }

    /**
     * Test user is strictly blocked (404) when inspecting another team's car.
     */
    public function test_user_cannot_view_other_teams_car(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id]);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id]);

        $carB = Car::factory()->forTeam($teamB)->create([
            'name' => 'Rival Speedster',
        ]);

        $response = $this->actingAs($userA)->get(route('garage.show', $carB));

        $response->assertNotFound();
    }

    /**
     * Test user is strictly blocked (404) when attempting to set another team's car as active.
     */
    public function test_user_cannot_set_other_teams_car_as_active(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id]);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id]);

        $carB = Car::factory()->forTeam($teamB)->create([
            'is_active' => false,
        ]);

        $response = $this->actingAs($userA)->post(route('garage.set-active', $carB));

        $response->assertNotFound();
        $this->assertFalse($carB->fresh()->is_active);
    }

    /**
     * Test user can successfully switch and set active primary car.
     */
    public function test_user_can_set_active_primary_race_car(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $car1 = Car::factory()->forTeam($team)->create([
            'name' => 'Chassis A',
            'is_active' => true,
        ]);

        $car2 = Car::factory()->forTeam($team)->create([
            'name' => 'Chassis B',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->post(route('garage.set-active', $car2));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertFalse($car1->fresh()->is_active);
        $this->assertTrue($car2->fresh()->is_active);
        $this->assertSame($car2->id, $team->fresh()->activeCar()->id);
    }
}
