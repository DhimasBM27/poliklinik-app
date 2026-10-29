<?php

namespace App\Notifications;

use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * Untuk ADMIN dan DOKTER: pasien baru saja mengunggah bukti bayar.
 * - Admin diarahkan ke halaman verifikasi pembayaran.
 * - Dokter diarahkan ke detail riwayat pemeriksaan pasien tersebut.
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
        $jumlah = 'Rp ' . number_format($this->pembayaran->jumlah, 0, ',', '.');

        // URL relatif supaya tetap benar walau host/port berubah.
        if ($notifiable->role === 'admin') {
            return [
                'judul' => 'Pembayaran menunggu verifikasi',
                'pesan' => $this->pasien->nama . ' mengunggah bukti pembayaran sebesar ' . $jumlah . '. Silakan verifikasi.',
                'url'   => route('admin.pembayaran.index', [], false),
            ];
        }

        return [
            'judul' => 'Bukti pembayaran diunggah',
            'pesan' => $this->pasien->nama . ' mengunggah bukti pembayaran sebesar ' . $jumlah . '.',
            'url'   => route('riwayat-pasien.show', $this->pembayaran->id_periksa, false),
        ];
    }
}
