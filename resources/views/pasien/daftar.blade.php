<x-layouts.app title="Daftar Poli">

    @php
        $jadwalData = $jadwals->map(function ($jadwal) {
            return [
                'id'      => $jadwal->id,
                'id_poli' => $jadwal->dokter->id_poli,
                'label'   => $jadwal->hari . ', '
                    . \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') . ' - '
                    . \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i')
                    . ' | ' . $jadwal->dokter->nama,
            ];
        })->values();
    @endphp

    <div class="max-w-xl mx-auto">
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
            <div class="card-body p-8">

                {{-- Judul --}}
                <h2 class="text-xl font-bold text-slate-800 text-center mb-6">
                    <i class="fas fa-book text-purple-600 mr-2"></i>
                    Pendaftaran Poli
                </h2>

                <form action="{{ route('pasien.daftar.submit') }}" method="POST">
                    @csrf

                    {{-- Nomor Rekam Medis --}}
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Nomor Rekam Medis
                        </label>
                        <input type="text" value="{{ $user->no_rm ?? '-' }}" readonly
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-slate-100 text-slate-600 cursor-not-allowed">
                    </div>

                    {{-- Pilih Poli --}}
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Pilih Poli
                        </label>
                        <select name="id_poli" id="id_poli" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-700 focus:border-primary focus:outline-none @error('id_poli') border-red-500 @enderror">
                            <option value="">-- Pilih Poli --</option>
                            @foreach ($polis as $poli)
                                <option value="{{ $poli->id }}" {{ old('id_poli') == $poli->id ? 'selected' : '' }}>
                                    {{ $poli->nama_poli }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_poli')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pilih Jadwal Periksa --}}
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Pilih Jadwal Periksa
                        </label>
                        <select name="id_jadwal" id="id_jadwal" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-700 focus:border-primary focus:outline-none @error('id_jadwal') border-red-500 @enderror">
                        </select>
                        @error('id_jadwal')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Keluhan --}}
                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Keluhan
                        </label>
                        <textarea name="keluhan" rows="3" placeholder="Tulis keluhan anda..." required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-700 focus:border-primary focus:outline-none @error('keluhan') border-red-500 @enderror">{{ old('keluhan') }}</textarea>
                        @error('keluhan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol --}}
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                            Daftar Poli
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script>
        const semuaJadwal = @json($jadwalData);
        const oldJadwal   = @json((int) old('id_jadwal', 0));

        const poliEl   = document.getElementById('id_poli');
        const jadwalEl = document.getElementById('id_jadwal');

        function isiJadwal() {
            const idPoli = parseInt(poliEl.value);
            jadwalEl.innerHTML = '';

            if (!idPoli) {
                jadwalEl.add(new Option('-- Pilih Jadwal --', ''));
                return;
            }

            const daftar = semuaJadwal.filter(j => j.id_poli === idPoli);

            if (daftar.length === 0) {
                jadwalEl.add(new Option('Tidak ada jadwal aktif di poli ini', ''));
                return;
            }

            jadwalEl.add(new Option('-- Pilih Jadwal --', ''));
            daftar.forEach(j => {
                const opsi = new Option(j.label, j.id);
                if (j.id === oldJadwal) opsi.selected = true;
                jadwalEl.add(opsi);
            });
        }

        poliEl.addEventListener('change', isiJadwal);
        isiJadwal();
    </script>

</x-layouts.app>