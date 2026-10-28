<x-layouts.app title="Verifikasi Pembayaran">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Verifikasi Pembayaran
        </h2>

        @php $menunggu = $pembayarans->where('status', \App\Models\Pembayaran::MENUNGGU_VERIFIKASI)->count(); @endphp
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-100 text-amber-700 text-sm font-semibold">
            <i class="fas fa-hourglass-half"></i>
            {{ $menunggu }} menunggu verifikasi
        </span>
    </div>

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">

                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Pasien</th>
                            <th class="px-6 py-4">Poli / Dokter</th>
                            <th class="px-6 py-4">Tanggal Periksa</th>
                            <th class="px-6 py-4">Tagihan</th>
                            <th class="px-6 py-4">Bukti</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="text-sm text-slate-700">
                        @forelse ($pembayarans as $index => $pembayaran)
                        @php
                            $daftarPoli = $pembayaran->periksa->daftarPoli;
                            $jadwal     = $daftarPoli->jadwalPeriksa;
                        @endphp
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-slate-500">{{ $index + 1 }}</td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $daftarPoli->pasien->nama ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $jadwal->dokter->poli->nama_poli ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $jadwal->dokter->nama ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4">
                                {{ \Carbon\Carbon::parse($pembayaran->periksa->tgl_periksa)->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4">
                                @if ($pembayaran->bukti_bayar)
                                    <a href="{{ asset('storage/' . $pembayaran->bukti_bayar) }}" target="_blank" rel="noopener"
                                       title="Klik untuk memperbesar">
                                        <img src="{{ asset('storage/' . $pembayaran->bukti_bayar) }}" alt="Bukti pembayaran"
                                             class="w-16 h-16 object-cover rounded-lg border border-slate-200">
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">Belum ada</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                @if ($pembayaran->status === \App\Models\Pembayaran::LUNAS)
                                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-600">Lunas</span>
                                @elseif ($pembayaran->status === \App\Models\Pembayaran::MENUNGGU_VERIFIKASI)
                                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-600">Menunggu Verifikasi</span>
                                @else
                                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-600">Belum Dibayar</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                @if ($pembayaran->status === \App\Models\Pembayaran::MENUNGGU_VERIFIKASI)
                                    <form action="{{ route('admin.pembayaran.verifikasi', $pembayaran->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            onclick="return confirm('Konfirmasi pembayaran ini sebagai LUNAS?')"
                                            class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white border-none rounded-lg px-4">
                                            <i class="fas fa-check"></i>
                                            Konfirmasi
                                        </button>
                                    </form>
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
                                    <span>Belum ada tagihan</span>
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
