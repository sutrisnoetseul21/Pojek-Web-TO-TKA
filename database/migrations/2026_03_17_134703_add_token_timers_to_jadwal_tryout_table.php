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
        Schema::table('jadwal_tryout', function (Blueprint $table) {
            $table->timestamp('token_released_at')->nullable()->after('token');
            $table->timestamp('token_active_until')->nullable()->after('token_released_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_tryout', function (Blueprint $table) {
            $table->dropColumn(['token_released_at', 'token_active_until']);
        });
    }
};
