<x-layouts.app title="Riwayat Pendaftaran">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Riwayat Pendaftaran Poli
        </h2>
    </div>

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">

                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Poli</th>
                            <th class="px-6 py-4">Dokter</th>
                            <th class="px-6 py-4">Jadwal</th>
                            <th class="px-6 py-4">Tanggal Daftar</th>
                            <th class="px-6 py-4">No Antrian</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($riwayat as $index => $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-slate-500">{{ $index + 1 }}</td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $item->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $item->jadwalPeriksa->dokter->nama ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $item->jadwalPeriksa->hari }},
                                {{ \Carbon\Carbon::parse($item->jadwalPeriksa->jam_mulai)->format('H:i') }}
                                -
                                {{ \Carbon\Carbon::parse($item->jadwalPeriksa->jam_selesai)->format('H:i') }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $item->created_at->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $item->no_antrian }}</td>

                            <td class="px-6 py-4">
                                @if ($item->periksas->isNotEmpty())
                                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-600">
                                        Sudah Diperiksa
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-600">
                                        Menunggu
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                @if ($item->periksas->isNotEmpty())
                                    <a href="{{ route('pasien.riwayat.show', $item->id) }}"
                                        class="btn btn-sm bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-4">
                                        <i class="fas fa-eye"></i>
                                        Detail
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-14 text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i class="fas fa-inbox text-3xl"></i>
                                    <span>Belum ada riwayat pendaftaran</span>
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
