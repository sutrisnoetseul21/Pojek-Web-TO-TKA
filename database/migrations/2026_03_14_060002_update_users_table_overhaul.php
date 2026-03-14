<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sekolah_id')->nullable()->after('npsn')->constrained('sekolah')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->after('sekolah_id')->constrained('kelas')->nullOnDelete();
            $table->string('nomor_peserta')->unique()->nullable()->after('kelas_id');
            $table->softDeletes();

            $table->index('sekolah_id');
            $table->index('kelas_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sekolah_id']);
            $table->dropForeign(['kelas_id']);
            $table->dropIndex(['sekolah_id']);
            $table->dropIndex(['kelas_id']);
            $table->dropColumn(['sekolah_id', 'kelas_id', 'nomor_peserta', 'deleted_at']);
        });
    }
};
