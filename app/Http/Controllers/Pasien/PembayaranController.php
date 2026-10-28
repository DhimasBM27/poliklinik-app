<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Notifications\PembayaranDiunggah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    /**
     * Daftar tagihan hasil pemeriksaan milik pasien yang login.
     */
    public function index()
    {
        $pembayarans = Pembayaran::with('periksa.daftarPoli.jadwalPeriksa.dokter.poli')
            ->whereHas('periksa.daftarPoli', function ($query) {
                $query->where('id_pasien', Auth::user()->id);
            })
            ->latest()
            ->get();

        return view('pasien.pembayaran.index', compact('pembayarans'));
    }

    /**
     * Upload foto bukti pembayaran.
     */
    public function upload(Request $request, Pembayaran $pembayaran)
    {
        $pembayaran->load('periksa.daftarPoli.jadwalPeriksa.dokter');
        $daftarPoli = $pembayaran->periksa->daftarPoli;

        // Pasien hanya boleh mengupload untuk tagihannya sendiri.
        abort_unless((int) $daftarPoli->id_pasien === (int) Auth::user()->id, 403);

        $request->validate([
            'bukti_bayar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'bukti_bayar.required' => 'Foto bukti pembayaran wajib diunggah.',
            'bukti_bayar.image'    => 'File harus berupa gambar.',
            'bukti_bayar.mimes'    => 'Format gambar harus JPG atau PNG.',
            'bukti_bayar.max'      => 'Ukuran gambar maksimal 2 MB.',
        ]);

        if ($pembayaran->status !== Pembayaran::BELUM_BAYAR) {
            return back()->with('error', 'Tagihan ini sudah dibayar atau sedang diverifikasi.');
        }

        $path = $request->file('bukti_bayar')->store('bukti-bayar', 'public');

        // Update bersyarat: hanya berhasil jika status MASIH belum_bayar.
        // Dengan begitu klik ganda tidak menghasilkan notifikasi ganda.
        $diperbarui = Pembayaran::where('id', $pembayaran->id)
            ->where('status', Pembayaran::BELUM_BAYAR)
            ->update([
                'bukti_bayar' => $path,
                'status'      => Pembayaran::MENUNGGU_VERIFIKASI,
                'tgl_upload'  => now(),
            ]);

        if (!$diperbarui) {
            Storage::disk('public')->delete($path);

            return back()->with('error', 'Tagihan ini sudah dibayar atau sedang diverifikasi.');
        }

        // Satu kejadian = satu notifikasi, untuk dokter yang memeriksa pasien ini.
        $dokter = $daftarPoli->jadwalPeriksa->dokter;
        $dokter->notify(new PembayaranDiunggah($pembayaran, Auth::user()));

        return redirect()
            ->route('pasien.pembayaran.index')
            ->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }
}
