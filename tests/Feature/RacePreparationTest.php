<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RacePreparationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing race routes.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $race = Race::factory()->create();

        $this->get(route('races.index'))->assertRedirect(route('login'));
        $this->get(route('races.show', $race))->assertRedirect(route('login'));
        $this->post(route('races.enter', $race))->assertRedirect(route('login'));
    }

    /**
     * Test user without a team is redirected to onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();
        $race = Race::factory()->create();

        $this->actingAs($user)->get(route('races.index'))->assertRedirect(route('team.create'));
        $this->actingAs($user)->get(route('races.show', $race))->assertRedirect(route('team.create'));
        $this->actingAs($user)->post(route('races.enter', $race))->assertRedirect(route('team.create'));
    }

    /**
     * Test user can view the championship race calendar schedule.
     */
    public function test_user_can_view_race_calendar_schedule(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'name' => 'Apex GP']);

        $race1 = Race::factory()->create([
            'name' => 'Cimahi GP',
            'location' => 'Cimahi',
            'laps' => 12,
            'entry_fee' => 1000,
            'prize_pool' => 15000,
        ]);

        $race2 = Race::factory()->create([
            'name' => 'Sentul Speed Circuit',
            'location' => 'Sentul',
            'laps' => 15,
            'entry_fee' => 1500,
            'prize_pool' => 22000,
        ]);

        $response = $this->actingAs($user)->get(route('races.index'));

        $response->assertOk();
        $response->assertViewIs('races.index');
        $response->assertSee('Cimahi GP');
        $response->assertSee('Sentul Speed Circuit');
        $response->assertSee('1,000 CR');
        $response->assertSee('15,000 CR');
    }

    /**
     * Test user can view the pre-race preparation and briefing hub.
     */
    public function test_user_can_view_race_preparation_briefing_hub(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 20000]);

        $car = Car::factory()->forTeam($team)->create([
            'name' => 'Phantom V12',
            'is_active' => true,
        ]);

        $driver = Driver::factory()->forTeam($team)->lead()->create([
            'name' => 'Lewis Hamilton',
        ]);

        $race = Race::factory()->create([
            'name' => 'Mandalika Coastal Challenge',
            'location' => 'Mandalika',
            'laps' => 14,
            'entry_fee' => 2000,
            'prize_pool' => 30000,
        ]);

        $response = $this->actingAs($user)->get(route('races.show', $race));

        $response->assertOk();
        $response->assertViewIs('races.show');
        $response->assertSee('Mandalika Coastal Challenge');
        $response->assertSee('Phantom V12');
        $response->assertSee('Lewis Hamilton');
        $response->assertSee('VERIFIED');
        $response->assertSee('SOLVENT');
    }

    /**
     * Test user can successfully confirm lineup and enter race grid when ready.
     */
    public function test_user_can_confirm_race_lineup_when_ready(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 25000]);

        $car = Car::factory()->forTeam($team)->create([
            'name' => 'Kuro GT',
            'is_active' => true,
        ]);

        $driver = Driver::factory()->forTeam($team)->lead()->create([
            'name' => 'Alex Carter',
        ]);

        $race = Race::factory()->create([
            'name' => 'Jakarta Night Prix',
            'entry_fee' => 2500,
        ]);

        $response = $this->actingAs($user)->post(route('races.enter', $race));

        $response->assertRedirect(route('races.show', $race));
        $response->assertSessionHas('success');
    }

    /**
     * Test race entry fails if team has no active car.
     */
    public function test_cannot_enter_race_without_active_car(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 25000]);

        Car::factory()->forTeam($team)->create([
            'is_active' => false,
        ]);

        Driver::factory()->forTeam($team)->lead()->create();

        $race = Race::factory()->create();

        $response = $this->actingAs($user)->post(route('races.enter', $race));

        $response->assertRedirect(route('garage.index'));
        $response->assertSessionHas('warning');
    }

    /**
     * Test race entry fails if team has no lead driver.
     */
    public function test_cannot_enter_race_without_lead_driver(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 25000]);

        Car::factory()->forTeam($team)->create([
            'is_active' => true,
        ]);

        Driver::factory()->forTeam($team)->create([
            'is_lead' => false,
        ]);

        $race = Race::factory()->create();

        $response = $this->actingAs($user)->post(route('races.enter', $race));

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('warning');
    }

    /**
     * Test race entry fails if team has insufficient funds for entry fee.
     */
    public function test_cannot_enter_race_with_insufficient_funds(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 500]);

        Car::factory()->forTeam($team)->create([
            'is_active' => true,
        ]);

        Driver::factory()->forTeam($team)->lead()->create();

        $race = Race::factory()->create([
            'entry_fee' => 2000,
        ]);

        $response = $this->actingAs($user)->post(route('races.enter', $race));

        $response->assertRedirect(route('races.show', $race));
        $response->assertSessionHas('warning');
    }
}
