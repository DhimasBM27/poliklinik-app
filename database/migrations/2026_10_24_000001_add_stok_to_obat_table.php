<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('obat', 'stok')) {
            Schema::table('obat', function (Blueprint $table) {
                $table->unsignedInteger('stok')->default(0)->after('harga');
            });

            // Obat yang sudah ada sebelum kolom stok dibuat diberi stok awal 50.
            // Ubah lewat menu Manajemen Obat > Edit sesuai stok sebenarnya.
            DB::table('obat')->update(['stok' => 50]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('obat', 'stok')) {
            Schema::table('obat', function (Blueprint $table) {
                $table->dropColumn('stok');
            });
        }
    }
};
