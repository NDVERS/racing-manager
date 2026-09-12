<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing dashboard.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test authenticated user without a team is redirected to team onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('team.create'));
    }

    /**
     * Test authenticated user with team sees full dashboard metrics and assets.
     */
    public function test_user_with_team_can_view_dashboard_with_team_metrics(): void
    {
        $user = User::factory()->create([
            'name' => 'Jean Todt',
        ]);

        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Scuderia Redline',
            'money' => 85000,
            'reputation' => 120,
        ]);

        $car = Car::factory()->forTeam($team)->create([
            'name' => 'Redline F1-26',
            'speed' => 78,
            'acceleration' => 74,
            'handling' => 80,
            'braking' => 75,
            'reliability' => 90,
            'is_active' => true,
        ]);

        $driver = Driver::factory()->forTeam($team)->create([
            'name' => 'Charles Leclerc',
            'salary' => 4500,
            'pace' => 88,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('dashboard.index');
        $response->assertSee('Scuderia Redline');
        $response->assertSee('Jean Todt');
        $response->assertSee('85,000');
        $response->assertSee('120');
        $response->assertSee('Redline F1-26');
        $response->assertSee('Charles Leclerc');
        $response->assertSee('STATUS: GREEN FLAG // READY TO COMPETE');
    }

    /**
     * Test dashboard shows incomplete setup status when team has no active car.
     */
    public function test_dashboard_shows_incomplete_status_when_no_active_car(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
        ]);

        Car::factory()->forTeam($team)->create([
            'is_active' => false,
        ]);

        Driver::factory()->forTeam($team)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('STATUS: INCOMPLETE SETUP // CONFIGURE ASSETS');
    }
}
