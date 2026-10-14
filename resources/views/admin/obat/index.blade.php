<x-layouts.app title="Manajemen Obat">
    <div class="px-6 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Data Obat</h1>
            <a href="{{ route('obat.create') }}" class="btn btn-primary btn-sm gap-2">
                <i class="fas fa-plus"></i> Tambah Obat
            </a>
        </div>

        <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-base-200 text-base-content">
                            <th>No</th>
                            <th>Nama Obat</th>
                            <th>Kemasan</th>
                            <th>Harga</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($obats as $index => $obat)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-semibold">{{ $obat->nama_obat }}</td>
                                <td>{{ $obat->kemasan }}</td>
                                <td>Rp {{ number_format($obat->harga, 0, ',', '.') }}</td>
                                <td class="text-center space-x-2">
                                    <a href="{{ route('obat.edit', $obat->id) }}" class="btn btn-warning btn-xs text-white">
                                        <i class="fas fa-pen"></i> Edit
                                    </a>
                                    <form action="{{ route('obat.destroy', $obat->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus obat ini?')">
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
                                <td colspan="5" class="text-center py-4 text-base-content/50">Belum ada data obat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>