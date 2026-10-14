<x-layouts.app title="Edit Jadwal Periksa">
    <div class="px-6 py-6 max-w-xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit Jadwal Periksa</h1>

        <div class="bg-base-100 p-6 rounded-xl shadow-sm border border-base-300">
            <form action="{{ route('jadwal-periksa.update', $jadwalPeriksa->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="label font-semibold">Hari</label>
                    <select name="hari" class="select select-bordered w-full @error('hari') select-error @enderror" required>
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                            <option value="{{ $hari }}" {{ old('hari', $jadwalPeriksa->hari) == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                        @endforeach
                    </select>
                    @error('hari')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="label font-semibold">Jam Mulai</label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai', $jadwalPeriksa->jam_mulai) }}" class="input input-bordered w-full @error('jam_mulai') input-error @enderror" required>
                    @error('jam_mulai')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="label font-semibold">Jam Selesai</label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai', $jadwalPeriksa->jam_selesai) }}" class="input input-bordered w-full @error('jam_selesai') input-error @enderror" required>
                    @error('jam_selesai')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Ubah name="aktif" menjadi name="status" dan valuenya menjadi Aktif / Tidak Aktif -->
                <div>
                    <label class="label font-semibold">Status Keaktifan</label>
                    <select name="status" class="select select-bordered w-full">
                        <option value="Aktif" {{ old('status', $jadwalPeriksa->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ old('status', $jadwalPeriksa->status) == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="{{ route('jadwal-periksa.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>