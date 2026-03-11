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
        Schema::create('peserta_jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('jadwal_tryout_id')->constrained('jadwal_tryout')->onDelete('cascade');
            $table->string('token_used', 6);
            $table->enum('status', ['registered', 'started', 'completed'])->default('registered');
            $table->datetime('waktu_mulai')->nullable();
            $table->datetime('waktu_selesai')->nullable();
            $table->integer('sisa_waktu')->nullable()->comment('Menit tersisa jika pause');
            $table->integer('total_nilai')->nullable();
            $table->timestamps();

            // Unique constraint: 1 user hanya bisa 1x ikut 1 jadwal
            $table->unique(['user_id', 'jadwal_tryout_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_jadwal');
    }
};
