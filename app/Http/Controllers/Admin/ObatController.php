<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    /**
     * Menampilkan daftar obat.
     */
    public function index()
    {
        $obats = Obat::all();
        return view('admin.obat.index', compact('obats'));
    }

    /**
     * Menampilkan form tambah obat.
     */
    public function create()
    {
        return view('admin.obat.create');
    }

    /**
     * Menyimpan data obat baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'kemasan'   => 'required|string|max:35',
            'harga'     => 'required|integer|min:0',
        ]);

        Obat::create($validated);

        return redirect()
            ->route('obat.index')
            ->with('success', 'Data berhasil disimpan!');
    }

    /**
     * Menampilkan form edit obat.
     */
    public function edit(string $id)
    {
        $obat = Obat::findOrFail($id);
        return view('admin.obat.edit', compact('obat'));
    }

    /**
     * Memperbarui data obat.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_obat' => 'required|string|max:255',
            'kemasan'   => 'required|string|max:35',
            'harga'     => 'required|integer|min:0',
        ]);

        $obat = Obat::findOrFail($id);
        $obat->update($validated);

        return redirect()
            ->route('obat.index')
            ->with('success', 'Data berhasil diperbarui!');
    }

    /**
     * Menghapus data obat.
     */
    public function destroy(string $id)
    {
        $obat = Obat::findOrFail($id);
        $obat->delete();

        return redirect()
            ->route('obat.index')
            ->with('success', 'Data berhasil dihapus!');
    }
}