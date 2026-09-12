<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\Team;
use App\Models\User;
use App\Services\RaceSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceTacticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fallback defaults when strategy parameters are omitted.
     */
    public function test_default_strategy_fallback_when_omitted(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 50000]);
        $car = Car::factory()->forTeam($team)->create(['is_active' => true]);
        $driver = Driver::factory()->forTeam($team)->lead()->create();
        $race = Race::factory()->create(['entry_fee' => 1000, 'weather' => 'dry']);

        $response = $this->actingAs($user)->post(route('races.run', $race));

        $response->assertRedirect(route('races.live', $race));

        $result = RaceResult::where('team_id', $team->id)->where('race_id', $race->id)->first();
        $this->assertNotNull($result);
        $this->assertSame('balanced_medium', $result->strategy);
        $this->assertSame('medium', $result->simulation_log['tactics']['tire_compound']);
        $this->assertSame('balanced', $result->simulation_log['tactics']['driving_mode']);
    }

    /**
     * Test custom tactics parameter submission and persistence.
     */
    public function test_custom_tactics_submission_and_persistence(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 50000]);
        $car = Car::factory()->forTeam($team)->create(['is_active' => true]);
        $driver = Driver::factory()->forTeam($team)->lead()->create();
        $race = Race::factory()->create(['entry_fee' => 1000, 'weather' => 'dry']);

        $response = $this->actingAs($user)->post(route('races.run', $race), [
            'tire_compound' => 'soft',
            'driving_mode' => 'push',
        ]);

        $response->assertRedirect(route('races.live', $race));

        $result = RaceResult::where('team_id', $team->id)->where('race_id', $race->id)->first();
        $this->assertNotNull($result);
        $this->assertSame('push_soft', $result->strategy);
        $this->assertSame('soft', $result->simulation_log['tactics']['tire_compound']);
        $this->assertSame('push', $result->simulation_log['tactics']['driving_mode']);
    }

    /**
     * Test invalid tactics fallback safely to defaults.
     */
    public function test_invalid_tactics_fallback_to_defaults(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 50000]);
        $car = Car::factory()->forTeam($team)->create(['is_active' => true]);
        $driver = Driver::factory()->forTeam($team)->lead()->create();
        $race = Race::factory()->create(['entry_fee' => 1000, 'weather' => 'dry']);

        $response = $this->actingAs($user)->post(route('races.run', $race), [
            'tire_compound' => 'ultra_soft_invalid',
            'driving_mode' => 'hyper_mode_invalid',
        ]);

        $response->assertRedirect(route('races.live', $race));

        $result = RaceResult::where('team_id', $team->id)->where('race_id', $race->id)->first();
        $this->assertNotNull($result);
        $this->assertSame('balanced_medium', $result->strategy);
    }

    /**
     * Test wet weather scenario gives wet compound significant pace advantage over slicks.
     */
    public function test_wet_weather_compound_advantage_in_rain(): void
    {
        $team = Team::factory()->create();
        $car = Car::factory()->forTeam($team)->create([
            'speed' => 70,
            'acceleration' => 70,
            'handling' => 70,
            'braking' => 70,
            'reliability' => 95,
        ]);
        $driver = Driver::factory()->forTeam($team)->lead()->create([
            'pace' => 70,
            'cornering' => 70,
            'consistency' => 95,
            'racecraft' => 70,
        ]);
        $wetRace = Race::factory()->create([
            'laps' => 5,
            'track_type' => 'balanced',
            'weather' => 'wet',
        ]);

        $service = new RaceSimulationService;

        $simWetTire = $service->simulate($wetRace, $team, $car, $driver, 'wet', 'balanced');
        $simSlickTire = $service->simulate($wetRace, $team, $car, $driver, 'medium', 'balanced');

        // Wet tire total time should be substantially faster than slick tire on wet surface (due to +4.2s/lap penalty on slicks)
        $this->assertLessThan(
            $simSlickTire['player_result']['total_seconds'],
            $simWetTire['player_result']['total_seconds']
        );
    }

    /**
     * Test dry weather scenario penalizes wet compound heavily.
     */
    public function test_dry_weather_penalty_on_wet_tires(): void
    {
        $team = Team::factory()->create();
        $car = Car::factory()->forTeam($team)->create([
            'speed' => 70,
            'acceleration' => 70,
            'handling' => 70,
            'braking' => 70,
            'reliability' => 95,
        ]);
        $driver = Driver::factory()->forTeam($team)->lead()->create([
            'pace' => 70,
            'cornering' => 70,
            'consistency' => 95,
            'racecraft' => 70,
        ]);
        $dryRace = Race::factory()->create([
            'laps' => 5,
            'track_type' => 'balanced',
            'weather' => 'dry',
        ]);

        $service = new RaceSimulationService;

        $simMediumTire = $service->simulate($dryRace, $team, $car, $driver, 'medium', 'balanced');
        $simWetOnDry = $service->simulate($dryRace, $team, $car, $driver, 'wet', 'balanced');

        // Medium tire on dry should be substantially faster than wet tire on dry
        $this->assertLessThan(
            $simWetOnDry['player_result']['total_seconds'],
            $simMediumTire['player_result']['total_seconds']
        );
    }

    /**
     * Test soft tires provide faster early pace compared to hard tires.
     */
    public function test_soft_vs_hard_early_lap_pace(): void
    {
        $team = Team::factory()->create();
        $car = Car::factory()->forTeam($team)->create([
            'speed' => 70,
            'acceleration' => 70,
            'handling' => 70,
            'braking' => 70,
            'reliability' => 99,
        ]);
        $driver = Driver::factory()->forTeam($team)->lead()->create([
            'pace' => 70,
            'cornering' => 70,
            'consistency' => 99, // High consistency to eliminate random variance
            'racecraft' => 70,
        ]);
        $race = Race::factory()->create([
            'laps' => 5,
            'track_type' => 'balanced',
            'weather' => 'dry',
        ]);

        $service = new RaceSimulationService;

        $simSoft = $service->simulate($race, $team, $car, $driver, 'soft', 'balanced');
        $simHard = $service->simulate($race, $team, $car, $driver, 'hard', 'balanced');

        // Soft compound initial pace delta is -0.85s vs +0.50s for hard
        // Therefore soft fastest lap is faster than hard fastest lap
        $this->assertLessThan(
            $simHard['player_result']['fastest_lap_seconds'],
            $simSoft['player_result']['fastest_lap_seconds']
        );
    }

    /**
     * Test push driving mode produces faster lap times than conserve driving mode.
     */
    public function test_push_vs_conserve_driving_modes(): void
    {
        $team = Team::factory()->create();
        $car = Car::factory()->forTeam($team)->create([
            'speed' => 70,
            'acceleration' => 70,
            'handling' => 70,
            'braking' => 70,
            'reliability' => 99,
        ]);
        $driver = Driver::factory()->forTeam($team)->lead()->create([
            'pace' => 70,
            'cornering' => 70,
            'consistency' => 99,
            'racecraft' => 70,
        ]);
        $race = Race::factory()->create([
            'laps' => 5,
            'track_type' => 'balanced',
            'weather' => 'dry',
        ]);

        $service = new RaceSimulationService;

        $simPush = $service->simulate($race, $team, $car, $driver, 'medium', 'push');
        $simConserve = $service->simulate($race, $team, $car, $driver, 'medium', 'conserve');

        $this->assertLessThan(
            $simConserve['player_result']['fastest_lap_seconds'],
            $simPush['player_result']['fastest_lap_seconds']
        );
    }

    /**
     * Test pre-race show view renders pit-wall strategy selector cards.
     */
    public function test_pre_race_show_view_renders_strategy_selector(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 50000]);
        Car::factory()->forTeam($team)->create(['is_active' => true]);
        Driver::factory()->forTeam($team)->lead()->create();
        $race = Race::factory()->create(['entry_fee' => 1000, 'weather' => 'dry']);

        $response = $this->actingAs($user)->get(route('races.show', $race));

        $response->assertOk();
        $response->assertSee('Pit-Wall Strategy Selector');
        $response->assertSee('SOFT (C3)');
        $response->assertSee('MEDIUM (C2)');
        $response->assertSee('HARD (C1)');
        $response->assertSee('WET RAIN');
        $response->assertSee('PUSH (AGGRESSIVE)');
        $response->assertSee('CONSERVE (DEFENSE)');
    }

    /**
     * Test live and debrief views render active strategy badges.
     */
    public function test_live_and_debrief_views_render_strategy_badges(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 50000]);
        $car = Car::factory()->forTeam($team)->create(['is_active' => true]);
        $driver = Driver::factory()->forTeam($team)->lead()->create();
        $race = Race::factory()->create(['entry_fee' => 1000, 'weather' => 'dry']);

        // Run simulation with Hard & Conserve
        $this->actingAs($user)->post(route('races.run', $race), [
            'tire_compound' => 'hard',
            'driving_mode' => 'conserve',
        ]);

        // Check Live View
        $liveResponse = $this->actingAs($user)->get(route('races.live', $race));
        $liveResponse->assertOk();
        $liveResponse->assertSee('HARD (C1)');
        $liveResponse->assertSee('CONSERVE');

        // Check Results View
        $resultResponse = $this->actingAs($user)->get(route('races.results', $race));
        $resultResponse->assertOk();
        $resultResponse->assertSee('CONSERVE / HARD');
    }

    /**
     * Test pre-race show view renders 1-Click Strategy Presets bar with options.
     */
    public function test_pre_race_show_view_renders_strategy_presets(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 50000]);
        Car::factory()->forTeam($team)->create(['is_active' => true]);
        Driver::factory()->forTeam($team)->lead()->create();
        $race = Race::factory()->create(['entry_fee' => 1000, 'weather' => 'dry']);

        $response = $this->actingAs($user)->get(route('races.show', $race));

        $response->assertOk();
        $response->assertSee('1-Click Strategy Presets');
        $response->assertSee('Aggressive Split');
        $response->assertSee('Safe Conserve');
        $response->assertSee('Balanced Standard');
        $response->assertSee('Wet Protocol');
    }

    /**
     * Test live view renders speed multiplier controls and instant skip button.
     */
    public function test_live_view_renders_playback_speed_controls_and_instant_skip(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id, 'money' => 50000]);
        Car::factory()->forTeam($team)->create(['is_active' => true]);
        Driver::factory()->forTeam($team)->lead()->create();
        $race = Race::factory()->create(['entry_fee' => 1000, 'weather' => 'dry']);

        $this->actingAs($user)->post(route('races.run', $race));

        $liveResponse = $this->actingAs($user)->get(route('races.live', $race));
        $liveResponse->assertOk();
        $liveResponse->assertSee('1x');
        $liveResponse->assertSee('2x');
        $liveResponse->assertSee('5x');
        $liveResponse->assertSee('Instant Skip');
        $liveResponse->assertSee('View Official Debrief & Payouts', false);
    }
}
