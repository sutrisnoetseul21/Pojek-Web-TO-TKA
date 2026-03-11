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
        Schema::table('ref_paket_soal', function (Blueprint $table) {
            $table->foreignId('mapel_id')->after('id')->nullable()->constrained('ref_mapel')->onDelete('cascade');
            $table->dropColumn(['waktu_pengerjaan', 'tgl_mulai', 'tgl_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ref_paket_soal', function (Blueprint $table) {
            //
        });
    }
};
