<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\CarSeeder;
use Database\Seeders\DriverSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamOnboardingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an authenticated user without a team is redirected to onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('team.create'));
    }

    /**
     * Test that an authenticated user can view the team creation page.
     */
    public function test_user_can_view_team_creation_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('team.create'));

        $response->assertOk();
        $response->assertViewIs('team.create');
        $response->assertSee('Kuro GT');
        $response->assertSee('Alex Carter');
        $response->assertSee('50,000');
    }

    /**
     * Test that user can create a team and receives starter assets with seeded pool.
     */
    public function test_user_can_create_team_with_starting_funds_and_starter_assets(): void
    {
        $this->seed(CarSeeder::class);
        $this->seed(DriverSeeder::class);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('team.store'), [
            'name' => 'Apex Motorsport',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('teams', [
            'user_id' => $user->id,
            'name' => 'Apex Motorsport',
            'money' => 50000,
            'reputation' => 0,
        ]);

        $team = $user->fresh()->team;
        $this->assertNotNull($team);

        // Check starting car
        $this->assertCount(1, $team->cars);
        $car = $team->cars->first();
        $this->assertSame('Kuro GT', $car->name);
        $this->assertSame(65, $car->speed);
        $this->assertSame(62, $car->acceleration);

        // Check starting driver
        $this->assertCount(1, $team->drivers);
        $driver = $team->drivers->first();
        $this->assertSame('Alex Carter', $driver->name);
        $this->assertSame(65, $driver->pace);
        $this->assertSame(1200, $driver->salary);
    }

    /**
     * Test that starter assets are generated cleanly even if seeder has not run.
     */
    public function test_team_creation_generates_starter_assets_if_pool_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('team.store'), [
            'name' => 'Nova Racing',
        ]);

        $response->assertRedirect(route('dashboard'));

        $team = $user->fresh()->team;
        $this->assertNotNull($team);
        $this->assertSame(50000, $team->money);
        $this->assertSame(0, $team->reputation);
        $this->assertCount(1, $team->cars);
        $this->assertSame('Kuro GT', $team->cars->first()->name);
        $this->assertCount(1, $team->drivers);
        $this->assertSame('Alex Carter', $team->drivers->first()->name);
    }

    /**
     * Test that user cannot create more than one team.
     */
    public function test_user_cannot_create_multiple_teams(): void
    {
        $user = User::factory()->create();
        Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Existing Team',
        ]);

        // Attempting to view create page
        $viewResponse = $this->actingAs($user)->get(route('team.create'));
        $viewResponse->assertRedirect(route('dashboard'));

        // Attempting to post another team
        $postResponse = $this->actingAs($user)->post(route('team.store'), [
            'name' => 'Second Team',
        ]);
        $postResponse->assertRedirect(route('dashboard'));

        $this->assertSame(1, Team::where('user_id', $user->id)->count());
    }

    /**
     * Test that user with a team can access the dashboard.
     */
    public function test_user_with_team_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Thunder GP',
        ]);
        Car::factory()->forTeam($team)->create();
        Driver::factory()->forTeam($team)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard.index');
        $response->assertSee('Thunder GP');
    }

    /**
     * Test team name validation during creation.
     */
    public function test_team_creation_requires_valid_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('team.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertNull($user->fresh()->team);
    }
}
