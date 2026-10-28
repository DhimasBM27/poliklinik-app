<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Notifications\PembayaranDiverifikasi;
use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    /**
     * Daftar tagihan. Yang menunggu verifikasi ditampilkan paling atas.
     */
    public function index()
    {
        $pembayarans = Pembayaran::with([
            'periksa.daftarPoli.pasien',
            'periksa.daftarPoli.jadwalPeriksa.dokter.poli',
        ])
            ->orderByRaw("CASE status WHEN 'menunggu_verifikasi' THEN 0 WHEN 'belum_bayar' THEN 1 ELSE 2 END")
            ->latest()
            ->get();

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    /**
     * Konfirmasi pembayaran -> status menjadi lunas, pasien dinotifikasi.
     */
    public function verifikasi(Pembayaran $pembayaran)
    {
        // Update bersyarat: hanya berhasil jika status masih menunggu_verifikasi,
        // jadi verifikasi ganda tidak mengirim notifikasi ganda.
        $diperbarui = Pembayaran::where('id', $pembayaran->id)
            ->where('status', Pembayaran::MENUNGGU_VERIFIKASI)
            ->update([
                'status'            => Pembayaran::LUNAS,
                'tgl_verifikasi'    => now(),
                'diverifikasi_oleh' => Auth::user()->id,
            ]);

        if (!$diperbarui) {
            return back()->with('error', 'Pembayaran ini tidak sedang menunggu verifikasi.');
        }

        $pembayaran->load('periksa.daftarPoli.pasien');
        $pembayaran->periksa->daftarPoli->pasien->notify(new PembayaranDiverifikasi($pembayaran));

        return back()->with('success', 'Pembayaran berhasil diverifikasi. Status menjadi Lunas.');
    }
}
