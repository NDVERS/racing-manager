<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChassisDealershipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest is redirected to login when accessing dealership.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $templateCar = Car::factory()->create(['team_id' => null]);

        $this->get(route('garage.dealership'))->assertRedirect(route('login'));
        $this->post(route('garage.buy', $templateCar))->assertRedirect(route('login'));
    }

    /**
     * Test user without team is redirected to onboarding.
     */
    public function test_user_without_team_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create();
        $templateCar = Car::factory()->create(['team_id' => null]);

        $this->actingAs($user)->get(route('garage.dealership'))->assertRedirect(route('team.create'));
        $this->actingAs($user)->post(route('garage.buy', $templateCar))->assertRedirect(route('team.create'));
    }

    /**
     * Test user can view the chassis showroom catalog with template blueprints.
     */
    public function test_user_can_view_chassis_showroom_catalog(): void
    {
        $this->seed(CarSeeder::class);

        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Solaris Racing',
            'money' => 60000,
        ]);

        // User already has Kuro GT
        Car::factory()->forTeam($team)->create([
            'name' => 'Kuro GT',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('garage.dealership'));

        $response->assertOk();
        $response->assertViewIs('garage.dealership');
        $response->assertSee('Chassis Showroom & Dealership');
        $response->assertSee('Apex Cyclone');
        $response->assertSee('Vortex R1');
        $response->assertSee('Falcon RS');
        $response->assertSee('Phantom GTS');
        $response->assertSee('1/2 IN FLEET');
        $response->assertSee('60,000');
    }

    /**
     * Test user can successfully purchase a chassis template with sufficient credits.
     */
    public function test_user_can_purchase_chassis_with_sufficient_credits(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 60000,
        ]);

        // Existing active starter car
        Car::factory()->forTeam($team)->create([
            'name' => 'Kuro GT',
            'is_active' => true,
        ]);

        $template = Car::create([
            'name' => 'Apex Cyclone',
            'speed' => 75,
            'acceleration' => 72,
            'handling' => 58,
            'braking' => 60,
            'reliability' => 68,
            'level' => 1,
            'purchase_price' => 35000,
            'team_id' => null,
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->post(route('garage.buy', $template));

        $response->assertRedirect(route('garage.index'));
        $response->assertSessionHas('success');

        // Check funds deducted: 60,000 - 35,000 = 25,000
        $team->refresh();
        $this->assertEquals(25000, $team->money);
        $this->assertEquals(25000, $team->credits);

        // Check new car created for the team
        $newCar = $team->cars()->where('name', 'Apex Cyclone')->first();
        $this->assertNotNull($newCar);
        $this->assertEquals(75, $newCar->speed);
        $this->assertFalse($newCar->is_active);

        // Check template car still exists in showroom with team_id null
        $this->assertTrue(Car::where('name', 'Apex Cyclone')->whereNull('team_id')->exists());

        // Check financial transaction recorded
        $this->assertDatabaseHas('transactions', [
            'team_id' => $team->id,
            'type' => 'expense',
            'amount' => -35000,
            'balance_after' => 25000,
        ]);
    }

    /**
     * Test user cannot purchase a chassis with insufficient credits.
     */
    public function test_user_cannot_purchase_chassis_with_insufficient_credits(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 15000,
        ]);

        $template = Car::create([
            'name' => 'Phantom GTS',
            'speed' => 78,
            'acceleration' => 75,
            'handling' => 73,
            'braking' => 72,
            'reliability' => 80,
            'level' => 1,
            'purchase_price' => 45000,
            'team_id' => null,
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->post(route('garage.buy', $template));

        $response->assertRedirect(route('garage.dealership'));
        $response->assertSessionHas('error');

        $team->refresh();
        $this->assertEquals(15000, $team->money);
        $this->assertFalse($team->cars()->where('name', 'Phantom GTS')->exists());
        $this->assertEquals(0, Transaction::where('team_id', $team->id)->count());
    }

    /**
     * Test user can purchase a second unit of the same chassis model for 2-car team fleet.
     */
    public function test_user_can_purchase_second_unit_of_same_chassis_model(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 80000,
        ]);

        // 1st unit already owned in fleet (Slot 1)
        Car::factory()->forTeam($team)->create([
            'name' => 'Vortex R1',
            'slot' => 1,
            'is_active' => true,
        ]);

        $template = Car::create([
            'name' => 'Vortex R1',
            'speed' => 60,
            'acceleration' => 66,
            'handling' => 76,
            'braking' => 74,
            'reliability' => 70,
            'level' => 1,
            'purchase_price' => 32000,
            'team_id' => null,
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->post(route('garage.buy', $template));

        $response->assertRedirect(route('garage.index'));
        $response->assertSessionHas('success');

        $team->refresh();
        $this->assertEquals(48000, $team->money);
        $this->assertEquals(2, $team->cars()->where('name', 'Vortex R1')->count());

        // 2nd unit assigned to Slot 2
        $car2 = $team->cars()->where('name', 'Vortex R1')->where('slot', 2)->first();
        $this->assertNotNull($car2);
    }

    /**
     * Test user cannot purchase more than 2 units of the same chassis model.
     */
    public function test_user_cannot_purchase_more_than_two_units_of_same_chassis_model(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'money' => 100000,
        ]);

        // Already owned 2 units in fleet
        Car::factory()->forTeam($team)->create([
            'name' => 'Vortex R1',
            'slot' => 1,
            'is_active' => true,
        ]);
        Car::factory()->forTeam($team)->create([
            'name' => 'Vortex R1',
            'slot' => 2,
            'is_active' => false,
        ]);

        $template = Car::create([
            'name' => 'Vortex R1',
            'speed' => 60,
            'acceleration' => 66,
            'handling' => 76,
            'braking' => 74,
            'reliability' => 70,
            'level' => 1,
            'purchase_price' => 32000,
            'team_id' => null,
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->post(route('garage.buy', $template));

        $response->assertRedirect(route('garage.dealership'));
        $response->assertSessionHas('warning');

        $team->refresh();
        $this->assertEquals(100000, $team->money);
        $this->assertEquals(2, $team->cars()->where('name', 'Vortex R1')->count());
    }

    /**
     * Test user cannot purchase a car belonging to another team.
     */
    public function test_user_cannot_purchase_car_belonging_to_another_team(): void
    {
        $userA = User::factory()->create();
        $teamA = Team::factory()->create(['user_id' => $userA->id, 'money' => 50000]);

        $userB = User::factory()->create();
        $teamB = Team::factory()->create(['user_id' => $userB->id]);
        $carB = Car::factory()->forTeam($teamB)->create([
            'name' => 'Team B Special',
            'purchase_price' => 20000,
        ]);

        $response = $this->actingAs($userA)->post(route('garage.buy', $carB));

        $response->assertRedirect(route('garage.dealership'));
        $response->assertSessionHas('error');

        $teamA->refresh();
        $this->assertEquals(50000, $teamA->money);
        $this->assertFalse($teamA->cars()->where('id', $carB->id)->exists());
    }

    /**
     * Test user can switch active race chassis and deactivate old active chassis.
     */
    public function test_user_can_switch_active_chassis_and_deactivate_previous(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $car1 = Car::factory()->forTeam($team)->create([
            'name' => 'Car One',
            'is_active' => true,
        ]);

        $car2 = Car::factory()->forTeam($team)->create([
            'name' => 'Car Two',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->post(route('garage.set-active', $car2));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $car1->refresh();
        $car2->refresh();

        $this->assertFalse($car1->is_active);
        $this->assertTrue($car2->is_active);
        $this->assertEquals($car2->id, $team->activeCar()->id);
    }
}
