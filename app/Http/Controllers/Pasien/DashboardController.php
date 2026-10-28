<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Antrian aktif = pendaftaran terbaru milik pasien yang belum diperiksa.
        $antrianAktif = DaftarPoli::with('jadwalPeriksa.dokter.poli')
            ->where('id_pasien', $user->id)
            ->belumDiperiksa()
            ->latest()
            ->first();

        $jadwals = JadwalPeriksa::with('dokter.poli')
            ->where('status', 'Aktif')
            ->whereHas('dokter', function ($query) {
                $query->whereNotNull('id_poli');
            })
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        // Nomor yang sedang dilayani per jadwal, diambil dengan SATU query:
        // [id_jadwal => nomor antrian terkecil yang belum diperiksa].
        $dilayani = DaftarPoli::belumDiperiksa()
            ->selectRaw('id_jadwal, MIN(no_antrian) as nomor')
            ->groupBy('id_jadwal')
            ->pluck('nomor', 'id_jadwal');

        return view('pasien.dashboard', compact('antrianAktif', 'jadwals', 'dilayani'));
    }
}
