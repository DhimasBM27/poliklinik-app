<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom ini sebelumnya ditambahkan manual lewat phpMyAdmin,
        // jadi dicek dulu supaya migrasi aman dijalankan di database mana pun.
        if (!Schema::hasColumn('jadwal_periksa', 'status')) {
            Schema::table('jadwal_periksa', function (Blueprint $table) {
                $table->string('status', 50)->default('Aktif');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jadwal_periksa', 'status')) {
            Schema::table('jadwal_periksa', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
