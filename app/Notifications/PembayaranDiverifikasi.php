<?php

namespace App\Notifications;

use App\Models\Pembayaran;
use Illuminate\Notifications\Notification;

/**
 * Untuk PASIEN: pembayarannya sudah diverifikasi admin (lunas).
 */
class PembayaranDiverifikasi extends Notification
{
    public function __construct(private Pembayaran $pembayaran)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'judul' => 'Pembayaran diverifikasi',
            'pesan' => 'Pembayaran Anda sebesar Rp '
                . number_format($this->pembayaran->jumlah, 0, ',', '.')
                . ' telah diverifikasi admin dan berstatus LUNAS.',
            'url'   => route('pasien.pembayaran.index', [], false),
        ];
    }
}
