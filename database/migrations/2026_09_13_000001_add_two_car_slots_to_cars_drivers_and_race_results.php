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
        Schema::table('cars', function (Blueprint $table) {
            $table->unsignedTinyInteger('slot')->nullable()->after('is_active');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->unsignedTinyInteger('slot')->nullable()->after('is_lead');
        });

        Schema::table('race_results', function (Blueprint $table) {
            $table->unsignedTinyInteger('car_slot')->default(1)->after('driver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('slot');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('slot');
        });

        Schema::table('race_results', function (Blueprint $table) {
            $table->dropColumn('car_slot');
        });
    }
};
