<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('tier')->default('secondary'); // primary, secondary, tertiary
            $table->unsignedInteger('min_reputation')->default(0);
            $table->unsignedBigInteger('signing_bonus')->default(0);
            $table->string('target_objective')->default('finish_race'); // finish_race, finish_top_5, finish_top_3, score_fastest_lap
            $table->unsignedBigInteger('bonus_per_race')->default(0);
            $table->unsignedInteger('duration_races')->default(3);
            $table->timestamps();
        });

        Schema::create('team_sponsors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('sponsor_id')->constrained('sponsors')->cascadeOnDelete();
            $table->unsignedInteger('races_remaining');
            $table->boolean('is_active')->default(true);
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_sponsors');
        Schema::dropIfExists('sponsors');
    }
};
