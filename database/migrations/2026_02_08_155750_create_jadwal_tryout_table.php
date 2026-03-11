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
        Schema::create('jadwal_tryout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_tryout_id')->constrained('paket_tryout')->onDelete('cascade');
            $table->string('nama_sesi');
            $table->dateTime('tgl_mulai');
            $table->dateTime('tgl_selesai');
            $table->integer('kuota_peserta')->nullable()->comment('Null = unlimited');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_tryout');
    }
};
