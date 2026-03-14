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
        Schema::create('ujian_bantuan_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_jadwal_id')->constrained('peserta_jadwal')->onDelete('cascade');
            $table->foreignId('admin_user_id')->constrained('users')->onDelete('cascade');
            $table->string('tindakan'); // reset_sesi, izin_login_ulang, buka_akses, force_submit, perpanjang_waktu, sinkron_status
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian_bantuan_log');
    }
};
