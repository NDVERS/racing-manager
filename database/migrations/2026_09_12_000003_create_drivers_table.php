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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('pace')->default(50);
            $table->unsignedTinyInteger('cornering')->default(50);
            $table->unsignedTinyInteger('consistency')->default(50);
            $table->unsignedTinyInteger('overtaking')->default(50);
            $table->unsignedTinyInteger('defensive')->default(50);
            $table->unsignedTinyInteger('racecraft')->default(50);
            $table->unsignedTinyInteger('experience')->default(50);
            $table->unsignedInteger('salary')->default(1000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
