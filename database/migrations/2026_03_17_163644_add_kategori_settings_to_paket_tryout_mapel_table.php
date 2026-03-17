<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('paket_tryout_mapel', function (Blueprint $table) {
            $table->json('kategori_settings')->nullable()->after('kategori_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paket_tryout_mapel', function (Blueprint $table) {
            $table->dropColumn('kategori_settings');
        });
    }
};
