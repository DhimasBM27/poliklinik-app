<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Buka notifikasi: tandai sudah dibaca lalu arahkan ke halaman terkait.
     *
     * Notifikasi selalu dicari lewat Auth::user()->notifications(), sehingga
     * pengguna tidak bisa membuka/menandai notifikasi milik orang lain (404).
     */
    public function buka(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        if (is_string($url) && str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return redirect($url);
        }

        return back();
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function baca(string $id)
    {
        Auth::user()->notifications()->findOrFail($id)->markAsRead();

        return back();
    }

    /**
     * Tandai semua notifikasi milik pengguna yang login sebagai sudah dibaca.
     */
    public function bacaSemua()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }
}
