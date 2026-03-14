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
        DB::statement("ALTER TABLE peserta_jadwal MODIFY COLUMN status ENUM('registered', 'started', 'completed', 'timeout', 'disconnected') DEFAULT 'registered'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE peserta_jadwal MODIFY COLUMN status ENUM('registered', 'started', 'completed') DEFAULT 'registered'");
    }
};
