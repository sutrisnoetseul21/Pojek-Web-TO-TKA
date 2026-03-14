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
        Schema::create('ujian_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_jadwal_id')->constrained('peserta_jadwal')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('jadwal_tryout_id')->constrained('jadwal_tryout')->onDelete('cascade');
            $table->string('aktivitas'); // login, mulai_ujian, simpan_jawaban, submit, logout, timeout, disconnect, login_ulang, force_submit
            $table->text('keterangan')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_activity_log');
    }
};
