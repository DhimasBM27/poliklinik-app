<x-layouts.app title="Periksa Pasien">

    @php
        $obatData = $obats->map(function ($obat) {
            return [
                'id'      => $obat->id,
                'nama'    => $obat->nama_obat,
                'kemasan' => $obat->kemasan,
                'harga'   => (int) $obat->harga,
            ];
        })->values();

        $obatLama = array_map('intval', old('obat', []));
    @endphp

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('periksa-pasien.index') }}" class="inline-flex items-center justify-center w-9 h-9 
                  rounded-lg bg-slate-100 text-slate-500 
                  hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>

        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
        </h2>
    </div>

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            {{-- Info Pasien --}}
            <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-600">
                <div><span class="font-semibold text-slate-800">Pasien:</span> {{ $daftarPoli->pasien->nama ?? '-' }}</div>
                <div><span class="font-semibold text-slate-800">Keluhan:</span> {{ $daftarPoli->keluhan }}</div>
                <div><span class="font-semibold text-slate-800">No Antrian:</span> {{ $daftarPoli->no_antrian }}</div>
            </div>

            <form action="{{ route('periksa-pasien.store') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="id_daftar_poli" value="{{ $daftarPoli->id }}">

                {{-- Pilih Obat --}}
                <div class="form-control mb-6">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">
                            Pilih Obat <span class="text-red-500">*</span>
                        </span>
                    </label>

                    <select id="pilih-obat"
                        class="select select-bordered border-2 px-4 w-full rounded-lg @error('obat') select-error @enderror">
                        <option value="">-- Pilih Obat --</option>
                        @foreach ($obats as $obat)
                            <option value="{{ $obat->id }}">
                                {{ $obat->nama_obat }} - {{ $obat->kemasan }} (Rp {{ number_format($obat->harga, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>

                    @error('obat')
                    <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Obat Terpilih --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Obat Terpilih</label>
                    <div id="obat-terpilih" class="space-y-2"></div>
                    <div id="obat-inputs"></div>
                </div>

                {{-- Total Harga --}}
                <div class="form-control mb-6">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Total Harga</span>
                    </label>
                    <input type="text" id="total-harga" readonly
                        class="input input-bordered border-2 px-4 w-full rounded-lg bg-slate-50 text-slate-700">
                    @if ($biayaJasa > 0)
                    <span class="text-xs text-slate-400 mt-1">
                        Sudah termasuk biaya jasa dokter Rp {{ number_format($biayaJasa, 0, ',', '.') }}
                    </span>
                    @endif
                </div>

                {{-- Catatan --}}
                <div class="form-control mb-8">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">
                            Catatan <span class="text-slate-400 font-normal">(Opsional)</span>
                        </span>
                    </label>
                    <textarea name="catatan" rows="4" placeholder="Masukkan catatan..."
                        class="textarea textarea-bordered border-2 px-4 py-3 w-full rounded-lg @error('catatan') textarea-error @enderror">{{ old('catatan') }}</textarea>

                    @error('catatan')
                    <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3">
                    <button type="submit" class="btn bg-[#2d4499] hover:bg-[#1e2d6b]
                                   text-white border-none rounded-lg px-6">
                        <i class="fas fa-save"></i>
                        Simpan
                    </button>

                    <a href="{{ route('periksa-pasien.index') }}" class="btn btn-ghost bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-lg px-6">
                        Batal
                    </a>
                </div>

            </form>

        </div>
    </div>

    <script>
        const daftarObat = @json($obatData);
        const biayaJasa  = {{ (int) $biayaJasa }};
        let terpilih     = @json($obatLama);

        const selectEl  = document.getElementById('pilih-obat');
        const listEl    = document.getElementById('obat-terpilih');
        const inputsEl  = document.getElementById('obat-inputs');
        const totalEl   = document.getElementById('total-harga');

        function rupiah(angka) {
            return 'Rp ' + Number(angka).toLocaleString('id-ID');
        }

        function hapusObat(id) {
            terpilih = terpilih.filter(x => x !== id);
            render();
        }

        function render() {
            listEl.innerHTML = '';
            inputsEl.innerHTML = '';
            let total = biayaJasa;

            if (terpilih.length === 0) {
                const kosong = document.createElement('p');
                kosong.className = 'text-sm text-slate-400';
                kosong.textContent = 'Belum ada obat dipilih';
                listEl.appendChild(kosong);
            }

            terpilih.forEach(id => {
                const obat = daftarObat.find(x => x.id === id);
                if (!obat) return;

                total += obat.harga;

                // Baris obat terpilih
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between px-4 py-2 rounded-lg border border-slate-200 bg-white';

                const info = document.createElement('span');
                info.className = 'text-sm text-slate-700';
                info.textContent = obat.nama + ' - ' + (obat.kemasan ?? '') + ' (' + rupiah(obat.harga) + ')';

                const tombol = document.createElement('button');
                tombol.type = 'button';
                tombol.className = 'text-xs font-semibold text-red-500 hover:text-red-700';
                tombol.textContent = 'Hapus';
                tombol.addEventListener('click', () => hapusObat(id));

                row.appendChild(info);
                row.appendChild(tombol);
                listEl.appendChild(row);

                // Input tersembunyi yang dikirim ke server
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'obat[]';
                input.value = id;
                inputsEl.appendChild(input);
            });

            totalEl.value = rupiah(total);
        }

        selectEl.addEventListener('change', () => {
            const id = parseInt(selectEl.value);
            if (id && !terpilih.includes(id)) {
                terpilih.push(id);
            }
            selectEl.value = '';
            render();
        });

        render();
    </script>

</x-layouts.app>