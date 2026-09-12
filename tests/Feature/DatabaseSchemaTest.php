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
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all required database tables exist with expected columns.
     */
    public function test_database_schema_has_all_expected_tables(): void
    {
        $expectedTables = [
            'users' => ['id', 'name', 'email', 'password'],
            'teams' => ['id', 'user_id', 'name', 'money', 'reputation'],
            'cars' => ['id', 'team_id', 'name', 'speed', 'acceleration', 'handling', 'braking', 'reliability', 'level', 'purchase_price'],
            'drivers' => ['id', 'team_id', 'name', 'pace', 'cornering', 'consistency', 'overtaking', 'defensive', 'racecraft', 'experience', 'salary'],
            'races' => ['id', 'name', 'location', 'laps', 'track_type', 'weather', 'entry_fee', 'prize_pool'],
            'car_upgrades' => ['id', 'car_id', 'part_type', 'level', 'cost', 'stat_increases'],
            'race_results' => ['id', 'race_id', 'team_id', 'car_id', 'driver_id', 'position', 'race_time', 'prize_money', 'reputation_earned', 'strategy', 'status', 'simulation_log'],
            'transactions' => ['id', 'team_id', 'type', 'amount', 'balance_after', 'description', 'reference_id', 'reference_type'],
        ];

        foreach ($expectedTables as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist.");
            foreach ($columns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "Column {$column} is missing in {$table}.");
            }
        }
    }

    /**
     * Test that model factories can create instances properly.
     */
    public function test_model_factories_can_create_records(): void
    {
        $user = User::factory()->create();
        $this->assertModelExists($user);

        $team = Team::factory()->create(['user_id' => $user->id]);
        $this->assertModelExists($team);

        $car = Car::factory()->forTeam($team)->create();
        $this->assertModelExists($car);

        $driver = Driver::factory()->forTeam($team)->create();
        $this->assertModelExists($driver);

        $race = Race::factory()->create();
        $this->assertModelExists($race);

        $upgrade = CarUpgrade::factory()->create(['car_id' => $car->id]);
        $this->assertModelExists($upgrade);

        $raceResult = RaceResult::factory()->create([
            'race_id' => $race->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
        ]);
        $this->assertModelExists($raceResult);

        $transaction = Transaction::factory()->create(['team_id' => $team->id]);
        $this->assertModelExists($transaction);
    }

    /**
     * Test all defined Eloquent relationships between models.
     */
    public function test_model_relationships_work_correctly(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->team->is($team));
        $this->assertTrue($team->user->is($user));

        $car = Car::factory()->forTeam($team)->create();
        $driver = Driver::factory()->forTeam($team)->create();

        $this->assertTrue($team->cars->contains($car));
        $this->assertTrue($team->drivers->contains($driver));
        $this->assertTrue($car->team->is($team));
        $this->assertTrue($driver->team->is($team));

        $upgrade = CarUpgrade::factory()->create(['car_id' => $car->id]);
        $this->assertTrue($car->upgrades->contains($upgrade));
        $this->assertTrue($upgrade->car->is($car));

        $race = Race::factory()->create();
        $result = RaceResult::factory()->create([
            'race_id' => $race->id,
            'team_id' => $team->id,
            'car_id' => $car->id,
            'driver_id' => $driver->id,
        ]);

        $this->assertTrue($race->results->contains($result));
        $this->assertTrue($result->race->is($race));
        $this->assertTrue($result->team->is($team));
        $this->assertTrue($result->car->is($car));
        $this->assertTrue($result->driver->is($driver));
        $this->assertTrue($team->raceResults->contains($result));
        $this->assertTrue($car->raceResults->contains($result));
        $this->assertTrue($driver->raceResults->contains($result));

        $transaction = Transaction::factory()->create(['team_id' => $team->id]);
        $this->assertTrue($team->transactions->contains($transaction));
        $this->assertTrue($transaction->team->is($team));
    }

    /**
     * Test unique constraint: a user can only have one team.
     */
    public function test_user_can_only_have_one_team(): void
    {
        $user = User::factory()->create();
        Team::factory()->create(['user_id' => $user->id]);

        $this->expectException(QueryException::class);
        Team::factory()->create(['user_id' => $user->id]);
    }

    /**
     * Test that database seeders populate the expected initial data pool.
     */
    public function test_database_seeder_populates_required_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(5, Car::count());
        $this->assertGreaterThanOrEqual(8, Driver::count());
        $this->assertGreaterThanOrEqual(5, Race::count());

        $this->assertDatabaseHas('cars', ['name' => 'Kuro GT']);
        $this->assertDatabaseHas('drivers', ['name' => 'Alex Carter']);
        $this->assertDatabaseHas('races', ['name' => 'Cimahi GP']);

        $testUser = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($testUser);
        $this->assertNotNull($testUser->team);
        $this->assertSame('Kuro Racing', $testUser->team->name);
        $this->assertSame(50000, $testUser->team->money);
        $this->assertSame(0, $testUser->team->reputation);

        $this->assertCount(1, $testUser->team->cars);
        $this->assertSame('Kuro GT', $testUser->team->cars->first()->name);

        $this->assertCount(1, $testUser->team->drivers);
        $this->assertSame('Alex Carter', $testUser->team->drivers->first()->name);
    }
}
