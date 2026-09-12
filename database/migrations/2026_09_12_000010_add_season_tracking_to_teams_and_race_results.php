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
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedInteger('current_season')->default(1)->after('reputation');
        });

        Schema::table('race_results', function (Blueprint $table) {
            $table->unsignedInteger('season')->default(1)->after('driver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('current_season');
        });

        Schema::table('race_results', function (Blueprint $table) {
            $table->dropColumn('season');
        });
    }
};
