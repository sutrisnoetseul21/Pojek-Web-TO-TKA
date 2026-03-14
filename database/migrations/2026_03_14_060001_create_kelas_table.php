<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas', 50);
            $table->string('jenjang');
            $table->foreignId('sekolah_id')->constrained('sekolah')->onDelete('cascade');
            $table->text('keterangan')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['sekolah_id', 'nama_kelas']);
            $table->index('sekolah_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
