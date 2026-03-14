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
        Schema::table('paket_tryout', function (Blueprint $table) {
            $table->foreignId('sekolah_id')->nullable()->after('id')->constrained('sekolah')->onDelete('cascade');
        });

        Schema::table('jadwal_tryout', function (Blueprint $table) {
            $table->foreignId('sekolah_id')->nullable()->after('id')->constrained('sekolah')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paket_tryout', function (Blueprint $table) {
            $table->dropForeign(['sekolah_id']);
            $table->dropColumn('sekolah_id');
        });

        Schema::table('jadwal_tryout', function (Blueprint $table) {
            $table->dropForeign(['sekolah_id']);
            $table->dropColumn('sekolah_id');
        });
    }
};
