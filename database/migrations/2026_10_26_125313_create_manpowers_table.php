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
        Schema::create('manpowers', function (Blueprint $table) {
            $table->id();
            $table->string('badge_id')->unique();
            $table->string('nama');
            $table->string('station_utama_1')->nullable();
            $table->string('station_utama_2')->nullable();
            $table->string('station_utama_3')->nullable();
            $table->enum('status', ['aktif', 'cuti'])->default('aktif');
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manpowers');
    }
};
