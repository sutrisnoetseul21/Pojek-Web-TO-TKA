<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ujian_siswa_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian_siswa')->onDelete('cascade');
            $table->foreignId('soal_id')->constrained('bank_soal')->onDelete('cascade');
            $table->text('jawaban_siswa')->nullable();
            $table->boolean('is_ragu')->default(false);
            $table->decimal('nilai_didapat', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_siswa_details');
    }
};
