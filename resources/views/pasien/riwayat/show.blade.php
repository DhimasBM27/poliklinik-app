<x-layouts.app title="Detail Riwayat Pendaftaran">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('pasien.riwayat.index') }}" class="inline-flex items-center justify-center w-9 h-9
                  rounded-lg bg-slate-100 text-slate-500
                  hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>

        <h2 class="text-2xl font-bold text-slate-800">
            Detail Pemeriksaan
        </h2>
    </div>

    {{-- Informasi Pendaftaran --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 mb-6">
        <div class="card-body p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">
                Informasi Pemeriksaan
            </h3>

            <table class="text-sm w-full">
                <tbody>
                    <tr>
                        <td class="py-1.5 w-40 text-slate-500">Poli</td>
                        <td class="py-1.5 font-semibold text-slate-800">
                            {{ $daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1.5 text-slate-500">Dokter</td>
                        <td class="py-1.5 text-slate-800">{{ $daftarPoli->jadwalPeriksa->dokter->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-1.5 text-slate-500">Jadwal</td>
                        <td class="py-1.5 text-slate-800">
                            {{ $daftarPoli->jadwalPeriksa->hari }},
                            {{ \Carbon\Carbon::parse($daftarPoli->jadwalPeriksa->jam_mulai)->format('H:i') }}
                            -
                            {{ \Carbon\Carbon::parse($daftarPoli->jadwalPeriksa->jam_selesai)->format('H:i') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1.5 text-slate-500">No. Antrian</td>
                        <td class="py-1.5 text-slate-800">{{ $daftarPoli->no_antrian }}</td>
                    </tr>
                    <tr>
                        <td class="py-1.5 text-slate-500">Keluhan</td>
                        <td class="py-1.5 text-slate-800">{{ $daftarPoli->keluhan }}</td>
                    </tr>
                    <tr>
                        <td class="py-1.5 text-slate-500">Tanggal Periksa</td>
                        <td class="py-1.5 text-slate-800">
                            {{ \Carbon\Carbon::parse($periksa->tgl_periksa)->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Catatan Dokter --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 mb-6">
        <div class="card-body p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">
                Catatan Dokter
            </h3>
            <p class="text-sm text-slate-700">{{ $periksa->catatan ?: '-' }}</p>
        </div>
    </div>

    {{-- Daftar Obat --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200 mb-6">
        <div class="card-body p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">
                Obat yang Diresepkan
            </h3>

            <div class="overflow-x-auto">
                <table class="table w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Nama Obat</th>
                            <th class="px-4 py-3">Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periksa->detailPeriksas as $index => $detail)
                        <tr>
                            <td class="px-4 py-3 text-slate-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $detail->obat->nama_obat ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                Rp {{ number_format($detail->obat->harga ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-slate-400">Tidak ada obat yang diresepkan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Total Biaya --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-6 items-center text-center">
            <p class="text-sm text-slate-500">Total Biaya Periksa</p>
            <p class="text-3xl font-bold text-[#2d4499]">
                Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
            </p>

            @if ($periksa->pembayaran)
                <p class="text-sm mt-2">
                    Status pembayaran:
                    <span class="font-semibold">{{ $periksa->pembayaran->label_status }}</span>
                    ·
                    <a href="{{ route('pasien.pembayaran.index') }}" class="text-indigo-600 font-semibold hover:underline">
                        Lihat menu Pembayaran
                    </a>
                </p>
            @endif
        </div>
    </div>

</x-layouts.app>
