<x-layouts.app title="Pembayaran">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Pembayaran
        </h2>
    </div>

    {{-- Pesan error upload (format/ukuran file) --}}
    @if ($errors->has('bukti_bayar'))
    <div class="alert alert-error mb-4 rounded-xl shadow-sm">
        <i class="fas fa-circle-xmark"></i>
        <span>{{ $errors->first('bukti_bayar') }}</span>
    </div>
    @endif

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">

                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Tanggal Periksa</th>
                            <th class="px-6 py-4">Poli / Dokter</th>
                            <th class="px-6 py-4">Tagihan</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Bukti / Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="text-sm text-slate-700">
                        @forelse ($pembayarans as $index => $pembayaran)
                        @php
                            $jadwal = $pembayaran->periksa->daftarPoli->jadwalPeriksa;
                        @endphp
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition align-top">
                            <td class="px-6 py-4 text-slate-500">{{ $index + 1 }}</td>

                            <td class="px-6 py-4">
                                {{ \Carbon\Carbon::parse($pembayaran->periksa->tgl_periksa)->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $jadwal->dokter->poli->nama_poli ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $jadwal->dokter->nama ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
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

                            <td class="px-6 py-4">
                                @if ($pembayaran->status === \App\Models\Pembayaran::BELUM_BAYAR)
                                    {{-- Form upload bukti bayar --}}
                                    <form action="{{ route('pasien.pembayaran.upload', $pembayaran->id) }}" method="POST"
                                          enctype="multipart/form-data" class="flex flex-col gap-2 max-w-xs">
                                        @csrf
                                        <input type="file" name="bukti_bayar" accept="image/png,image/jpeg"
                                               class="file-input file-input-bordered file-input-sm w-full" required>
                                        <button type="submit"
                                            class="btn btn-sm bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg">
                                            <i class="fas fa-upload"></i>
                                            Upload Bukti
                                        </button>
                                        <span class="text-[11px] text-slate-400">JPG/PNG, maksimal 2 MB</span>
                                    </form>
                                @else
                                    <a href="{{ asset('storage/' . $pembayaran->bukti_bayar) }}" target="_blank" rel="noopener">
                                        <img src="{{ asset('storage/' . $pembayaran->bukti_bayar) }}" alt="Bukti pembayaran"
                                             class="w-20 h-20 object-cover rounded-lg border border-slate-200">
                                    </a>
                                    <div class="text-[11px] text-slate-400 mt-1">
                                        Diunggah {{ $pembayaran->tgl_upload?->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-14 text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i class="fas fa-file-invoice-dollar text-3xl"></i>
                                    <span>Belum ada tagihan. Tagihan muncul setelah Anda selesai diperiksa dokter.</span>
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
