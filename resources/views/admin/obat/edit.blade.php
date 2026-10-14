<x-layouts.app title="Edit Obat">
    <div class="px-6 py-6 max-w-xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit Data Obat</h1>

        <div class="bg-base-100 p-6 rounded-xl shadow-sm border border-base-300">
            <form action="{{ route('obat.update', $obat->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="label font-semibold">Nama Obat</label>
                    <input type="text" name="nama_obat" value="{{ old('nama_obat', $obat->nama_obat) }}" class="input input-bordered w-full @error('nama_obat') input-error @enderror" required>
                    @error('nama_obat')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="label font-semibold">Kemasan</label>
                    <input type="text" name="kemasan" value="{{ old('kemasan', $obat->kemasan) }}" class="input input-bordered w-full @error('kemasan') input-error @enderror" required>
                    @error('kemasan')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="label font-semibold">Harga (Rp)</label>
                    <input type="number" name="harga" value="{{ old('harga', $obat->harga) }}" class="input input-bordered w-full @error('harga') input-error @enderror" required>
                    @error('harga')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="{{ route('obat.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>