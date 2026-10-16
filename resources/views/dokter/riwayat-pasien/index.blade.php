<x-layouts.app title="Riwayat Pasien">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Riwayat Pasien
        </h2>
    </div>

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border">
        <div class="card-body p-0">

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">

                    {{-- Head --}}
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Nama Pasien</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">Tanggal Pemeriksaan</th>
                            <th class="px-6 py-4">Biaya Pemeriksaan</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Body --}}
                    <tbody>
                        @forelse ($periksas as $periksa)
                        <tr class="hover:bg-slate-50 transition">

                            <td class="px-6 py-4 text-slate-500">
                                {{ $periksa->id }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $periksa->daftarPoli->pasien->nama ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $periksa->daftarPoli->keluhan }}
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ \Carbon\Carbon::parse($periksa->tgl_periksa)->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('riwayat-pasien.show', $periksa->id) }}"
                                    class="btn btn-sm bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-4">
                                    <i class="fas fa-eye"></i>
                                    Detail
                                </a>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-14 text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i class="fas fa-inbox text-3xl"></i>
                                    <span>Belum ada riwayat pemeriksaan</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>