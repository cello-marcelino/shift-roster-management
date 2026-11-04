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
        Schema::create('roster_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roster_id')->constrained('rosters')->cascadeOnDelete();
            $table->unsignedTinyInteger('hari')->default(1); // 1 = Senin, 7 = Minggu
            $table->unsignedTinyInteger('shift')->default(1); // 1, 2, 3
            $table->foreignId('station_id')->nullable()->constrained('stations')->cascadeOnDelete();
            $table->foreignId('manpower_id')->constrained('manpowers')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['roster_id', 'shift']);
            $table->index(['manpower_id', 'hari', 'shift']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_entries');
    }
};
