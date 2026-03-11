<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('paket_tryout_mapel', function (Blueprint $table) {
            $table->enum('mode', ['ACAK', 'MANUAL'])->default('ACAK')->after('kategori_id');
            $table->json('soal_ids')->nullable()->after('mode')->comment('ID soal untuk mode manual');
        });
    }

    public function down(): void
    {
        Schema::table('paket_tryout_mapel', function (Blueprint $table) {
            $table->dropColumn(['mode', 'soal_ids']);
        });
    }
};
