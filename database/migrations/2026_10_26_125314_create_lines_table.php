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
        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->unsignedInteger('mp_standard')->default(0);
            $table->enum('pola_kerja', ['5_days_1_shift', '5_days_2_shift', '6_days_3_shift'])->default('5_days_2_shift');
            $table->json('jam_shift')->nullable();
            $table->enum('status', ['aktif', 'off', 'waiting_material'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lines');
    }
};
