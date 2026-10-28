<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use Illuminate\Support\Facades\Auth;

class RiwayatPendaftaranController extends Controller
{
    /**
     * Semua pendaftaran milik pasien yang login, terbaru lebih dulu.
     * Eager loading dipakai supaya tidak terjadi query berlebihan (N+1).
     */
    public function index()
    {
        $riwayat = DaftarPoli::with(['jadwalPeriksa.dokter.poli', 'periksas'])
            ->where('id_pasien', Auth::user()->id)
            ->latest()
            ->get();

        return view('pasien.riwayat.index', compact('riwayat'));
    }

    /**
     * Detail pemeriksaan: catatan dokter, daftar obat, dan total biaya.
     */
    public function show($id)
    {
        $daftarPoli = DaftarPoli::with([
            'jadwalPeriksa.dokter.poli',
            'periksas.detailPeriksas.obat',
            'periksas.pembayaran',
        ])
            ->where('id_pasien', Auth::user()->id) // hanya data milik sendiri
            ->findOrFail($id);

        $periksa = $daftarPoli->periksas->first();

        // Belum diperiksa -> belum ada detail yang bisa dilihat.
        abort_if($periksa === null, 404);

        return view('pasien.riwayat.show', compact('daftarPoli', 'periksa'));
    }
}
