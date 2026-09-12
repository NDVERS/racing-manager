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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('speed')->default(50);
            $table->unsignedTinyInteger('acceleration')->default(50);
            $table->unsignedTinyInteger('handling')->default(50);
            $table->unsignedTinyInteger('braking')->default(50);
            $table->unsignedTinyInteger('reliability')->default(50);
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedInteger('purchase_price')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
