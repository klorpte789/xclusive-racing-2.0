<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('race_results', function (Blueprint $table) {
            $table->string('vehicle')->nullable()->after('car_number');
            $table->decimal('consistency', 5, 2)->nullable()->after('total_time');
            $table->smallInteger('laps_led')->nullable()->after('lap_count');
        });
    }

    public function down(): void
    {
        Schema::table('race_results', function (Blueprint $table) {
            $table->dropColumn(['vehicle', 'consistency', 'laps_led']);
        });
    }
};