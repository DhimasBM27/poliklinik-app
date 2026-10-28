<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_periksa')->unique()->constrained('periksa')->cascadeOnDelete();
            $table->unsignedInteger('jumlah');
            $table->string('bukti_bayar')->nullable();
            // belum_bayar -> menunggu_verifikasi -> lunas
            $table->string('status', 30)->default('belum_bayar');
            $table->timestamp('tgl_upload')->nullable();
            $table->timestamp('tgl_verifikasi')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Pemeriksaan yang sudah tersimpan sebelum fitur ini ikut dibuatkan tagihannya.
        foreach (DB::table('periksa')->get() as $periksa) {
            DB::table('pembayaran')->insert([
                'id_periksa' => $periksa->id,
                'jumlah'     => $periksa->biaya_periksa,
                'status'     => 'belum_bayar',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
