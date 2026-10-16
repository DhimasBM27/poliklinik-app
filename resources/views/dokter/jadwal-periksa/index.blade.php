<x-layouts.app title="Jadwal Periksa">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Jadwal Periksa
        </h2>

        <a href="{{ route('jadwal-periksa.create') }}" class="btn bg-[#2d4499] hover:bg-[#1e2d6b] 
                  text-white border-none rounded-lg px-5">
            <i class="fas fa-plus"></i>
            Tambah Jadwal Periksa
        </a>
    </div>

    {{-- Alert Flash Message --}}
    @if (session('message'))
    <div id="flash-alert" class="alert alert-{{ session('type', 'success') }} mb-4 rounded-xl shadow-sm" role="alert">
        <i class="fas fa-circle-check"></i>
        <span>{{ session('message') }}</span>
    </div>
    @endif

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border">
        <div class="card-body p-0">

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">

                    {{-- Head --}}
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Hari</th>
                            <th class="px-6 py-4">Jam Mulai</th>
                            <th class="px-6 py-4">Jam Selesai</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Body --}}
                    <tbody>
                        @forelse ($jadwalPeriksas as $index => $jadwalPeriksa)
                        <tr class="hover:bg-slate-50 transition">

                            <td class="px-6 py-4 text-slate-500">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $jadwalPeriksa->hari }}
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ \Carbon\Carbon::parse($jadwalPeriksa->jam_mulai)->format('H:i') }}
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ \Carbon\Carbon::parse($jadwalPeriksa->jam_selesai)->format('H:i') }}
                            </td>

                            <td class="px-6 py-4">
                                @if ($jadwalPeriksa->status === 'Aktif')
                                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-600">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-600">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">

                                    {{-- Edit --}}
                                    <a href="{{ route('jadwal-periksa.edit', $jadwalPeriksa->id) }}" class="btn btn-sm bg-amber-500 hover:bg-amber-600 
                                                  text-white border-none rounded-lg px-4">
                                        <i class="fas fa-pen-to-square"></i>
                                        Edit
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('jadwal-periksa.destroy', $jadwalPeriksa->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Yakin ingin menghapus Data Jadwal Periksa ini?')" class="btn btn-sm bg-red-500 hover:bg-red-600 
                                                       text-white border-none rounded-lg px-4">
                                            <i class="fas fa-trash"></i>
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-14 text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i class="fas fa-inbox text-3xl"></i>
                                    <span>Belum ada Jadwal Periksa</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    <script>
        // Notifikasi hilang otomatis setelah 5 detik
        setTimeout(() => {
            const alertBox = document.getElementById('flash-alert');
            if (alertBox) {
                alertBox.remove();
            }
        }, 5000);
    </script>

</x-layouts.app>