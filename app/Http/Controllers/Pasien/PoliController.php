<?php

namespace App\Http\Controllers\Pasien;

use App\Events\AntrianDiperbarui;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

        // Pasien yang masih punya antrian aktif belum boleh mendaftar lagi.
        $sudahAktif = DaftarPoli::where('id_pasien', $user->id)->belumDiperiksa()->exists();

        return view('pasien.daftar', [
            'user'       => $user,
            'polis'      => $polis,
            'jadwals'    => $jadwals,
            'sudahAktif' => $sudahAktif,
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

        $daftarPoli = DB::transaction(function () use ($request, $jadwal, $idPasien) {
            // Kunci baris pasien supaya klik ganda tidak membuat dua pendaftaran sekaligus.
            User::where('id', $idPasien)->lockForUpdate()->first();

            // Aturan: tidak boleh mendaftar ke dua poli sekaligus. Pendaftaran baru
            // hanya boleh setelah pemeriksaan sebelumnya selesai.
            $antrianAktif = DaftarPoli::where('id_pasien', $idPasien)->belumDiperiksa()->exists();

            if ($antrianAktif) {
                throw ValidationException::withMessages([
                    'id_jadwal' => 'Anda masih memiliki antrian aktif. Pendaftaran baru hanya bisa dilakukan setelah pemeriksaan selesai.',
                ]);
            }

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

        // Kabari semua dashboard pasien yang sedang terbuka (real-time).
        AntrianDiperbarui::kirim($jadwal->id);

        return redirect()
            ->route('pasien.dashboard')
            ->with('success', 'Berhasil mendaftar ke poli! Nomor antrian Anda: ' . $daftarPoli->no_antrian);
    }
}
