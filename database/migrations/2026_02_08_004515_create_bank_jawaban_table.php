<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_id')->constrained('bank_soal')->onDelete('cascade');
            $table->text('teks_jawaban');
            $table->string('kunci_jawaban');
            $table->char('label', 1)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_jawaban');
    }
};
