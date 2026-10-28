<?php

namespace App\Notifications;

use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * Untuk DOKTER: pasien yang ia periksa baru saja mengunggah bukti bayar.
 */
class PembayaranDiunggah extends Notification
{
    public function __construct(
        private Pembayaran $pembayaran,
        private User $pasien,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'judul' => 'Bukti pembayaran diunggah',
            'pesan' => $this->pasien->nama . ' mengunggah bukti pembayaran sebesar Rp '
                . number_format($this->pembayaran->jumlah, 0, ',', '.') . '.',
            // URL relatif supaya tetap benar walau host/port berubah.
            'url'   => route('riwayat-pasien.show', $this->pembayaran->id_periksa, false),
        ];
    }
}
