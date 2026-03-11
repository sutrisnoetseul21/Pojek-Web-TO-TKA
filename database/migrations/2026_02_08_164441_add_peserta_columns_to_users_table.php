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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('id');
            $table->enum('role', ['admin', 'peserta'])->default('peserta')->after('email');
            $table->string('nama_lengkap')->nullable()->after('role');
            $table->string('tempat_lahir')->nullable()->after('nama_lengkap');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('sekolah')->nullable()->after('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('sekolah');
            $table->boolean('is_biodata_complete')->default(false)->after('jenis_kelamin');
            $table->string('plain_password')->nullable()->after('password'); // Untuk admin lihat password asli
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'role',
                'nama_lengkap',
                'tempat_lahir',
                'tanggal_lahir',
                'sekolah',
                'jenis_kelamin',
                'is_biodata_complete',
                'plain_password'
            ]);
        });
    }
};
