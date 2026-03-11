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
        Schema::create('paket_tryout_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_tryout_id')->constrained('paket_tryout')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('ref_mapel')->cascadeOnDelete();
            $table->json('kategori_ids')->nullable()->comment('List ID kategori sumber soal');
            $table->integer('jumlah_soal')->default(10)->comment('Jumlah soal yang diambil');
            $table->integer('waktu_mapel')->default(30)->comment('Waktu dalam menit');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_tryout_mapel');
    }
};
