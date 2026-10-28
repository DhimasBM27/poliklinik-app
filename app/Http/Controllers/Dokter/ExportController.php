<?php

namespace App\Http\Controllers\Dokter;

use App\Exports\JadwalPeriksaExport;
use App\Exports\RiwayatPasienExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Jadwal periksa milik dokter yang sedang login.
     */
    public function jadwalPeriksa()
    {
        return Excel::download(
            new JadwalPeriksaExport(Auth::user()->id),
            'jadwal-periksa.xlsx'
        );
    }

    /**
     * Riwayat pasien yang pernah diperiksa dokter yang sedang login.
     */
    public function riwayatPasien()
    {
        return Excel::download(
            new RiwayatPasienExport(Auth::user()->id),
            'riwayat-pasien.xlsx'
        );
    }
}
