<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ref_paket_soal', function (Blueprint $table) {
            $table->id();
            $table->string('nama_paket');
            $table->enum('jenjang', ['SD', 'SMP', 'SMA', 'UMUM']);
            $table->integer('waktu_pengerjaan')->nullable(); // Minutes
            $table->dateTime('tgl_mulai')->nullable();
            $table->dateTime('tgl_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_paket_soal');
    }
};
