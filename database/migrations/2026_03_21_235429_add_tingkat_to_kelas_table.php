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
        Schema::table('kelas', function (Blueprint $table) {
            $table->string('tingkat', 10)->nullable()->after('nama_kelas');
        });

        // Backfill data lama
        $kelas = \DB::table('kelas')->get();
        foreach ($kelas as $k) {
            if (preg_match('/^([0-9]+)/', $k->nama_kelas, $matches)) {
                \DB::table('kelas')->where('id', $k->id)->update(['tingkat' => $matches[1]]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('tingkat');
        });
    }
};
