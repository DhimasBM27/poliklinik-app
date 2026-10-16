<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriksaPasienController extends Controller
{
    /**
     * Biaya jasa dokter yang selalu ditambahkan ke total harga.
     * Ubah menjadi 0 kalau tugasmu tidak memakai biaya jasa.
     */
    private const BIAYA_JASA_DOKTER = 150000;

    /**
     * Daftar pasien yang mendaftar ke jadwal milik dokter yang login.
     */
    public function index()
    {
        $daftarPolis = DaftarPoli::with(['pasien', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) {
                $query->where('id_dokter', Auth::user()->id);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPolis'));
    }

    /**
     * Form periksa pasien. {id} adalah id dari tabel daftar_poli.
     */
    public function create($id)
    {
        $daftarPoli = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])->findOrFail($id);

        $this->pastikanMilikSendiri($daftarPoli);

        if ($daftarPoli->periksas->isNotEmpty()) {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('error', 'Pasien ini sudah diperiksa.');
        }

        $obats      = Obat::orderBy('nama_obat')->get();
        $biayaJasa  = self::BIAYA_JASA_DOKTER;

        return view('dokter.periksa-pasien.create', compact('daftarPoli', 'obats', 'biayaJasa'));
    }

    /**
     * Menyimpan hasil pemeriksaan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_daftar_poli' => 'required|exists:daftar_poli,id',
            'obat'           => 'required|array|min:1',
            'obat.*'         => 'required|exists:obat,id',
            'catatan'        => 'nullable|string',
        ], [
            'obat.required' => 'Pilih minimal satu obat.',
            'obat.min'      => 'Pilih minimal satu obat.',
        ]);

        $daftarPoli = DaftarPoli::with(['jadwalPeriksa', 'periksas'])->findOrFail($request->id_daftar_poli);

        $this->pastikanMilikSendiri($daftarPoli);

        if ($daftarPoli->periksas->isNotEmpty()) {
            return redirect()
                ->route('periksa-pasien.index')
                ->with('error', 'Pasien ini sudah diperiksa.');
        }

        // Total dihitung di server dari harga di database, bukan dari data browser.
        $obatIds   = array_unique($request->obat);
        $totalObat = Obat::whereIn('id', $obatIds)->sum('harga');

        DB::transaction(function () use ($request, $daftarPoli, $obatIds, $totalObat) {
            $periksa = Periksa::create([
                'id_daftar_poli' => $daftarPoli->id,
                'tgl_periksa'    => now(),
                'catatan'        => $request->catatan,
                'biaya_periksa'  => self::BIAYA_JASA_DOKTER + $totalObat,
            ]);

            foreach ($obatIds as $idObat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat'    => $idObat,
                ]);
            }
        });

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data berhasil disimpan!');
    }

    /**
     * Dokter hanya boleh memeriksa pasien yang mendaftar ke jadwalnya sendiri.
     */
    private function pastikanMilikSendiri(DaftarPoli $daftarPoli): void
    {
        abort_unless($daftarPoli->jadwalPeriksa->id_dokter === Auth::user()->id, 403);
    }
}