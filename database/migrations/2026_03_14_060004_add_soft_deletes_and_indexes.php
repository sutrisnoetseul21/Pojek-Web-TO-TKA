<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Soft deletes for bank_soal
        if (!Schema::hasColumn('bank_soal', 'deleted_at')) {
            Schema::table('bank_soal', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Soft deletes for paket_tryout
        if (!Schema::hasColumn('paket_tryout', 'deleted_at')) {
            Schema::table('paket_tryout', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Soft deletes for jadwal_tryout
        if (!Schema::hasColumn('jadwal_tryout', 'deleted_at')) {
            Schema::table('jadwal_tryout', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Performance indexes
        // bank_soal(paket_id)
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->index('paket_id');
        });

        // jadwal_tryout - check column name
        if (Schema::hasColumn('jadwal_tryout', 'paket_tryout_id')) {
            Schema::table('jadwal_tryout', function (Blueprint $table) {
                $table->index('paket_tryout_id');
            });
        }

        // peserta_jadwal performance indexes
        Schema::table('peserta_jadwal', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('jadwal_tryout_id');
        });
    }

    public function down(): void
    {
        Schema::table('bank_soal', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['paket_id']);
        });

        Schema::table('paket_tryout', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('jadwal_tryout', function (Blueprint $table) {
            $table->dropSoftDeletes();
            if (Schema::hasColumn('jadwal_tryout', 'paket_tryout_id')) {
                $table->dropIndex(['paket_tryout_id']);
            }
        });

        Schema::table('peserta_jadwal', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['jadwal_tryout_id']);
        });
    }
};
