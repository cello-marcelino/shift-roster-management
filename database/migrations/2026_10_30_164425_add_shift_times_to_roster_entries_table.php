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
        Schema::table('roster_entries', function (Blueprint $table) {
            $table->string('start_shift', 10)->nullable()->after('station_id');
            $table->string('end_shift', 10)->nullable()->after('start_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roster_entries', function (Blueprint $table) {
            $table->dropColumn(['start_shift', 'end_shift']);
        });
    }
};
