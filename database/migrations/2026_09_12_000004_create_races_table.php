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
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->unsignedSmallInteger('laps')->default(12);
            $table->string('track_type')->default('balanced');
            $table->string('weather')->default('dry');
            $table->unsignedInteger('entry_fee')->default(1000);
            $table->unsignedInteger('prize_pool')->default(15000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
