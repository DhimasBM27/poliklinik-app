<x-layouts.app title="Tambah Jadwal Periksa">
    <div class="px-6 py-6 max-w-xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Tambah Jadwal Periksa</h1>

        <div class="bg-base-100 p-6 rounded-xl shadow-sm border border-base-300">
            <form action="{{ route('jadwal-periksa.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="label font-semibold">Hari</label>
                    <select name="hari" class="select select-bordered w-full @error('hari') select-error @enderror" required>
                        <option value="" disabled selected>Pilih Hari</option>
                        <option value="Senin" {{ old('hari') == 'Senin' ? 'selected' : '' }}>Senin</option>
                        <option value="Selasa" {{ old('hari') == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                        <option value="Rabu" {{ old('hari') == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                        <option value="Kamis" {{ old('hari') == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                        <option value="Jumat" {{ old('hari') == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                        <option value="Sabtu" {{ old('hari') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                    </select>
                    @error('hari')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="label font-semibold">Jam Mulai</label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" class="input input-bordered w-full @error('jam_mulai') input-error @enderror" required>
                    @error('jam_mulai')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="label font-semibold">Jam Selesai</label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}" class="input input-bordered w-full @error('jam_selesai') input-error @enderror" required>
                    @error('jam_selesai')
                        <span class="text-error text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="{{ route('jadwal-periksa.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>