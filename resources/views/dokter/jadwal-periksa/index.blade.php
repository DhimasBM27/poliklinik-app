<x-layouts.app title="Jadwal Periksa">
    <div class="px-6 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Jadwal Periksa Dokter</h1>
            <a href="{{ route('jadwal-periksa.create') }}" class="btn btn-primary btn-sm gap-2">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </a>
        </div>

        <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-base-200 text-base-content">
                            <th>No</th>
                            <th>Hari</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwals as $index => $jadwal)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-semibold">{{ $jadwal->hari }}</td>
                                <td>{{ $jadwal->jam_mulai }}</td>
                                <td>{{ $jadwal->jam_selesai }}</td>
                                <td>
                                    @if(strtolower($jadwal->status) == 'aktif' || $jadwal->status == 'Y' || $jadwal->status == 1)
                                        <span class="badge badge-success text-white">Aktif</span>
                                    @else
                                        <span class="badge badge-error text-white">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center space-x-2">
                                    <a href="{{ route('jadwal-periksa.edit', $jadwal->id) }}" class="btn btn-warning btn-xs text-white">
                                        <i class="fas fa-pen"></i> Edit
                                    </a>
                                    <form action="{{ route('jadwal-periksa.destroy', $jadwal->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-error btn-xs text-white">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-base-content/50">Belum ada jadwal periksa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>