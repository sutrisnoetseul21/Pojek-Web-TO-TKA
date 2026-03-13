<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ref_mapel MODIFY COLUMN jenjang ENUM('SD', 'SMP', 'SMA', 'SMK', 'UMUM')");
        DB::statement("ALTER TABLE paket_tryout MODIFY COLUMN jenjang ENUM('SD', 'SMP', 'SMA', 'SMK', 'UMUM') DEFAULT 'UMUM'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE ref_mapel MODIFY COLUMN jenjang ENUM('SD', 'SMP', 'SMA', 'UMUM')");
        DB::statement("ALTER TABLE paket_tryout MODIFY COLUMN jenjang ENUM('SD', 'SMP', 'SMA', 'UMUM') DEFAULT 'UMUM'");
    }
};
