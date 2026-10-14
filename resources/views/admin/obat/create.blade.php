<x-layouts.app title="Tambah Obat">
    <div class="px-6 py-6 max-w-xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Tambah Data Obat</h1>

        <div class="bg-base-100 p-6 rounded-xl shadow-sm border border-base-300">
            <form action="{{ route('obat.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="label font-semibold">Nama Obat</label>
                    <input type="text" name="nama_obat" value="{{ old('nama_obat') }}" class="input input-bordered w-full @error('nama_obat') input-error @enderror" placeholder="Contoh: Paracetamol 500mg" required>
                    @error('nama_obat')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="label font-semibold">Kemasan</label>
                    <input type="text" name="kemasan" value="{{ old('kemasan') }}" class="input input-bordered w-full @error('kemasan') input-error @enderror" placeholder="Contoh: Strip / Botol 100ml" required>
                    @error('kemasan')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="label font-semibold">Harga (Rp)</label>
                    <input type="number" name="harga" value="{{ old('harga') }}" class="input input-bordered w-full @error('harga') input-error @enderror" placeholder="Contoh: 5000" required>
                    @error('harga')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="{{ route('obat.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>