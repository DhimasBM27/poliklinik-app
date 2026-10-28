<?php

namespace App\Events;

use App\Models\DaftarPoli;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dikirim lewat Laravel Reverb setiap kali antrian sebuah jadwal berubah
 * (ada pasien baru mendaftar atau dokter selesai memeriksa pasien).
 *
 * ShouldBroadcastNow: dikirim langsung tanpa perlu queue worker.
 */
class AntrianDiperbarui implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $idJadwal,
        public ?int $sedangDilayani,
    ) {
    }

    /**
     * Channel publik "antrian" (data antrian tidak sensitif).
     */
    public function broadcastOn(): array
    {
        return [new Channel('antrian')];
    }

    public function broadcastAs(): string
    {
        return 'antrian.diperbarui';
    }

    public function broadcastWith(): array
    {
        return [
            'id_jadwal'       => $this->idJadwal,
            'sedang_dilayani' => $this->sedangDilayani,
        ];
    }

    /**
     * Hitung nomor yang sedang dilayani pada sebuah jadwal, lalu siarkan.
     *
     * Nomor yang sedang dilayani = nomor terkecil di antara pasien yang
     * belum diperiksa. Jika server Reverb belum menyala, kegagalan hanya
     * dicatat di log supaya pendaftaran/pemeriksaan tetap berhasil.
     */
    public static function kirim(int $idJadwal): void
    {
        $nomor = DaftarPoli::where('id_jadwal', $idJadwal)
            ->belumDiperiksa()
            ->min('no_antrian');

        rescue(
            fn () => event(new self($idJadwal, $nomor !== null ? (int) $nomor : null)),
            null,
            true
        );
    }
}
