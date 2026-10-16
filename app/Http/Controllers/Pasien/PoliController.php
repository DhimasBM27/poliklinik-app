<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PoliController extends Controller
{
    /**
     * Halaman pendaftaran periksa.
     */
    public function get()
    {
        $user = Auth::user();

        // Pasien yang dibuat lewat admin belum punya nomor RM: buatkan sekarang.
        if (empty($user->no_rm)) {
            $user->no_rm = date('Ym') . '-' . str_pad($user->id, 3, '0', STR_PAD_LEFT);
            $user->save();
        }

        $polis = Poli::orderBy('nama_poli')->get();

        // Hanya jadwal aktif yang dokternya sudah punya poli.
        $jadwals = JadwalPeriksa::with('dokter', 'dokter.poli')
            ->where('status', 'Aktif')
            ->whereHas('dokter', function ($query) {
                $query->where('role', 'dokter')->whereNotNull('id_poli');
            })
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('pasien.daftar', [
            'user'    => $user,
            'polis'   => $polis,
            'jadwals' => $jadwals,
        ]);
    }

    /**
     * Menyimpan pendaftaran periksa.
     */
    public function submit(Request $request)
    {
        $request->validate([
            'id_poli'   => 'required|exists:poli,id',
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan'   => 'required|string|max:1000',
        ], [
            'id_poli.required'   => 'Pilih poli terlebih dahulu.',
            'id_jadwal.required' => 'Pilih jadwal periksa terlebih dahulu.',
            'keluhan.required'   => 'Keluhan wajib diisi.',
        ]);

        $idPasien = Auth::user()->id;
        $jadwal   = JadwalPeriksa::with('dokter')->findOrFail($request->id_jadwal);

        // Jadwal harus aktif dan milik dokter di poli yang dipilih.
        if ($jadwal->status !== 'Aktif' || (int) $jadwal->dokter->id_poli !== (int) $request->id_poli) {
            return back()
                ->withInput()
                ->withErrors(['id_jadwal' => 'Jadwal tidak valid atau sudah tidak aktif.']);
        }

        // Cegah pendaftaran ganda ke jadwal yang sama sebelum diperiksa.
        $sudahTerdaftar = DaftarPoli::where('id_pasien', $idPasien)
            ->where('id_jadwal', $jadwal->id)
            ->whereDoesntHave('periksas')
            ->exists();

        if ($sudahTerdaftar) {
            return back()
                ->withInput()
                ->withErrors(['id_jadwal' => 'Anda sudah terdaftar di jadwal ini dan belum diperiksa.']);
        }

        $daftarPoli = DB::transaction(function () use ($request, $jadwal, $idPasien) {
            $antrianTerakhir = DaftarPoli::where('id_jadwal', $jadwal->id)
                ->lockForUpdate()
                ->max('no_antrian');

            return DaftarPoli::create([
                'id_pasien'  => $idPasien,
                'id_jadwal'  => $jadwal->id,
                'keluhan'    => $request->keluhan,
                'no_antrian' => ($antrianTerakhir ?? 0) + 1,
            ]);
        });

        return redirect()
            ->route('pasien.daftar')
            ->with('success', 'Berhasil mendaftar ke poli! Nomor antrian Anda: ' . $daftarPoli->no_antrian);
    }
}