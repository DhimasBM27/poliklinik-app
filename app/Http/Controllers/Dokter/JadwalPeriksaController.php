<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{
    /**
     * Menampilkan jadwal periksa milik dokter yang sedang login.
     */
    public function index()
    {
        $jadwalPeriksas = JadwalPeriksa::where('id_dokter', Auth::user()->id)
            ->orderBy('id')
            ->get();

        return view('dokter.jadwal-periksa.index', compact('jadwalPeriksas'));
    }

    /**
     * Menampilkan form tambah jadwal.
     */
    public function create()
    {
        return view('dokter.jadwal-periksa.create');
    }

    /**
     * Menyimpan data jadwal baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
        ]);

        JadwalPeriksa::create([
            'id_dokter'   => Auth::user()->id,
            'hari'        => $request->hari,
            'jam_mulai'   => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'status'      => 'Aktif',
        ]);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Data berhasil disimpan!')
            ->with('type', 'success');
    }

    /**
     * Menampilkan form edit jadwal.
     */
    public function edit(JadwalPeriksa $jadwalPeriksa)
    {
        $this->pastikanMilikSendiri($jadwalPeriksa);

        return view('dokter.jadwal-periksa.edit', compact('jadwalPeriksa'));
    }

    /**
     * Memperbarui data jadwal.
     */
    public function update(Request $request, JadwalPeriksa $jadwalPeriksa)
    {
        $this->pastikanMilikSendiri($jadwalPeriksa);

        $request->validate([
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'status'      => 'required|in:Aktif,Tidak Aktif',
        ]);

        $jadwalPeriksa->update([
            'hari'        => $request->hari,
            'jam_mulai'   => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'status'      => $request->status,
        ]);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Data berhasil diperbarui!')
            ->with('type', 'success');
    }

    /**
     * Menghapus data jadwal.
     */
    public function destroy(JadwalPeriksa $jadwalPeriksa)
    {
        $this->pastikanMilikSendiri($jadwalPeriksa);

        $jadwalPeriksa->delete();

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Data berhasil dihapus!')
            ->with('type', 'success');
    }

    /**
     * Dokter hanya boleh mengubah/menghapus jadwal miliknya sendiri.
     */
    private function pastikanMilikSendiri(JadwalPeriksa $jadwalPeriksa): void
    {
        abort_unless($jadwalPeriksa->id_dokter === Auth::user()->id, 403);
    }
}