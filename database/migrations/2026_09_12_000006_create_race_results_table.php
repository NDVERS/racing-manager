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
        Schema::create('race_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('race_time');
            $table->unsignedInteger('prize_money')->default(0);
            $table->unsignedInteger('reputation_earned')->default(0);
            $table->string('strategy')->default('balanced');
            $table->string('status')->default('finished');
            $table->json('simulation_log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_results');
    }
};
