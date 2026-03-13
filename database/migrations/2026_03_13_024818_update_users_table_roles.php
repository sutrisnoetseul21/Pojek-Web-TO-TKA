<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('peserta')->change(); // Temporary change to string to allow enum update or just use DB statement
        });
        
        // Using raw statement for safer enum update in MySQL
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'peserta') DEFAULT 'peserta'");
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('jenjang', ['SD', 'SMP', 'SMA', 'SMK', 'UMUM'])->nullable()->after('sekolah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jenjang');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'peserta') DEFAULT 'peserta'");
    }
};
