<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_stimulus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mapel_id')->constrained('ref_mapel')->onDelete('cascade');
            $table->foreignId('paket_id')->nullable()->constrained('ref_paket_soal')->onDelete('cascade'); // Nullable if used across packets? But constraints say constrained.
            $table->string('judul');
            $table->longText('konten');
            $table->enum('tipe', ['TEKS', 'AUDIO', 'VIDEO', 'GAMBAR'])->default('TEKS');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_stimulus');
    }
};
