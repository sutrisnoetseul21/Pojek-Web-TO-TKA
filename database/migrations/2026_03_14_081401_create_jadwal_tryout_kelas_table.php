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
        Schema::create('jadwal_tryout_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_tryout_id')->constrained('jadwal_tryout')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->timestamps();

            // Unique constraint to prevent duplicate entry
            $table->unique(['jadwal_tryout_id', 'kelas_id'], 'jadwal_kelas_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_tryout_kelas');
    }
};
