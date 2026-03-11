<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->constrained('ref_paket_soal')->onDelete('cascade');
            $table->foreignId('mapel_id')->constrained('ref_mapel')->onDelete('cascade');
            $table->foreignId('stimulus_id')->nullable()->constrained('bank_stimulus')->onDelete('set null'); // IMPORTANT: Set null if stimulus deleted so question remains (as separate question if text is copied?) No, if stimulus deleted better cascade? But plan said set null or cascade? Actually stimulus is integral. But usually we want to keep questions. Let's use set null for safety or cascade? Standard is usually cascade for strict integrity, but maybe set null if we want to rescue questions. The prompt didn't specify, but I'll use set null as it's safer for accidental deletions. Or cascade is better for clean up. Let's stick to set null as per my previous thought or cascade? Actually let's use 'set null' as written in my plan logic thought process earlier to decouple if needed. Wait, looking at plan in 02_Database_Schema.md it doesn't specify constraint behavior. I'll use cascade for simplicity of cleanup. No, let's use `set null` as per earlier thought.
            // Wait, looking at file content I prepared earlier in thought process: "onDelete('set null')". Stick with that.
            $table->enum('tipe_soal', ['PG_TUNGGAL', 'PG_KOMPLEKS', 'BENAR_SALAH', 'MENJODOHKAN', 'ISIAN']);
            $table->longText('pertanyaan');
            $table->longText('pembahasan')->nullable();
            $table->integer('bobot')->default(1);
            $table->integer('nomor_urut')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_soal');
    }
};
