<x-layouts.app title="Periksa Pasien">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
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
                            <th class="px-6 py-4">Pasien</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4">No Antrian</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Body --}}
                    <tbody>
                        @forelse ($daftarPolis as $daftarPoli)
                        <tr class="hover:bg-slate-50 transition">

                            <td class="px-6 py-4 text-slate-500">
                                {{ $daftarPoli->id }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $daftarPoli->pasien->nama ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $daftarPoli->keluhan }}
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $daftarPoli->no_antrian }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                @if ($daftarPoli->periksas->isNotEmpty())
                                    <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-600">
                                        <i class="fas fa-circle text-[6px]"></i>
                                        Sudah Diperiksa
                                    </span>
                                @else
                                    <a href="{{ route('periksa-pasien.create', $daftarPoli->id) }}"
                                        class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white border-none rounded-lg px-4">
                                        <i class="fas fa-stethoscope"></i>
                                        Periksa
                                    </a>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-14 text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i class="fas fa-inbox text-3xl"></i>
                                    <span>Belum ada pasien yang mendaftar</span>
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