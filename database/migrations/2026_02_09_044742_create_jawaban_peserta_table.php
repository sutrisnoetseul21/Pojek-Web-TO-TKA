<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jawaban_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_jadwal_id')->constrained('peserta_jadwal')->onDelete('cascade');
            $table->foreignId('bank_soal_id')->constrained('bank_soal')->onDelete('cascade');
            $table->json('jawaban')->nullable(); // Bisa array untuk PGK atau BS
            $table->boolean('is_ragu')->default(false);
            $table->timestamps();

            // Unique: 1 peserta hanya bisa menjawab 1x per soal
            $table->unique(['peserta_jadwal_id', 'bank_soal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_peserta');
    }
};
