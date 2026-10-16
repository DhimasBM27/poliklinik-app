<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatPasienController extends Controller
{
    public function index()
    {
        $periksas = Periksa::whereHas('daftarPoli.jadwalPeriksa', function ($query) {
            $query->where('id_dokter', Auth::id());
        })->with([
            'daftarPoli.pasien',
            'daftarPoli.jadwalPeriksa.dokter',
            'detailPeriksas.obat',
        ])->orderBy('tgl_periksa', 'desc')->get();

        return view('dokter.riwayat-pasien.index', compact('periksas'));
    }

    public function show($id)
    {
        $periksa = Periksa::whereHas('daftarPoli.jadwalPeriksa', function ($query) {
            $query->where('id_dokter', Auth::id());
        })->with([
            'daftarPoli.pasien',
            'daftarPoli.jadwalPeriksa.dokter.poli',
            'detailPeriksas.obat',
        ])->findOrFail($id);

        return view('dokter.riwayat-pasien.show', compact('periksa'));
    }
}