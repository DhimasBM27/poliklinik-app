<x-layouts.app title="Dashboard Pasien">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Dashboard Pasien</h2>
        <p class="text-sm text-slate-500">Selamat datang, {{ auth()->user()->nama }}.</p>
    </div>

    {{-- ================= BANNER ANTRIAN AKTIF ================= --}}
    @if ($antrianAktif)
        @php $jadwalAktif = $antrianAktif->jadwalPeriksa; @endphp

        <div class="rounded-2xl p-6 mb-8 text-white shadow-md bg-gradient-to-r from-indigo-600 to-blue-500
                    flex flex-col md:flex-row md:items-center md:justify-between gap-6">

            <div class="space-y-3">
                <p class="text-xs font-bold uppercase tracking-widest text-white/80">Antrian Aktif Anda</p>

                <div>
                    <p class="text-[11px] text-white/70">Poliklinik</p>
                    <p class="text-xl font-bold">{{ $jadwalAktif->dokter->poli->nama_poli ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-[11px] text-white/70">Dokter</p>
                    <p class="font-semibold">{{ $jadwalAktif->dokter->nama ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-[11px] text-white/70">Jadwal Periksa</p>
                    <p class="font-semibold">
                        {{ $jadwalAktif->hari }}
                        ({{ \Carbon\Carbon::parse($jadwalAktif->jam_mulai)->format('H:i') }}
                        - {{ \Carbon\Carbon::parse($jadwalAktif->jam_selesai)->format('H:i') }})
                    </p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="bg-white/20 rounded-2xl px-8 py-4 text-center min-w-32">
                    <p class="text-[11px] font-semibold uppercase text-white/80">Nomor Anda</p>
                    <p class="text-5xl font-extrabold">{{ $antrianAktif->no_antrian }}</p>
                </div>

                <div class="bg-white text-indigo-700 rounded-2xl px-8 py-4 text-center min-w-36">
                    <p class="text-[11px] font-semibold uppercase">Sedang Dilayani</p>
                    <p class="text-5xl font-extrabold" data-nomor-jadwal="{{ $jadwalAktif->id }}">{{ $dilayani[$jadwalAktif->id] ?? '-' }}</p>
                    <p class="text-[10px] mt-1 text-slate-500">
                        <span id="live-dot" class="inline-block w-2 h-2 rounded-full bg-slate-300 mr-1"></span>
                        <span id="live-text">Live update</span>
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 mb-8 text-center text-slate-500">
            <i class="fas fa-ticket text-3xl mb-3 block text-slate-300"></i>
            <p class="font-semibold text-slate-600">Anda belum memiliki antrian aktif.</p>
            <p class="text-sm mt-1">
                Silakan
                <a href="{{ route('pasien.daftar') }}" class="text-indigo-600 font-semibold hover:underline">daftar ke poli</a>
                untuk mendapatkan nomor antrian.
            </p>
        </div>
    @endif

    {{-- ================= TABEL JADWAL & ANTRIAN ================= --}}
    <h3 class="text-lg font-bold text-slate-800 mb-3">Jadwal Poliklinik</h3>

    <div class="card bg-base-100 shadow-md rounded-2xl border">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">

                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Poli</th>
                            <th class="px-6 py-4">Dokter</th>
                            <th class="px-6 py-4">Hari</th>
                            <th class="px-6 py-4">Jam Periksa</th>
                            <th class="px-6 py-4 text-center">Sedang Dilayani</th>
                        </tr>
                    </thead>

                    <tbody class="text-sm text-slate-700">
                        @forelse ($jadwals as $index => $jadwal)
                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition
                                   {{ $antrianAktif && $antrianAktif->id_jadwal === $jadwal->id ? 'bg-indigo-50' : '' }}">
                            <td class="px-6 py-4 text-slate-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $jadwal->dokter->poli->nama_poli ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $jadwal->dokter->nama ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $jadwal->hari }}</td>
                            <td class="px-6 py-4">
                                {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}
                                -
                                {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block min-w-10 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 font-bold"
                                      data-nomor-jadwal="{{ $jadwal->id }}">{{ $dilayani[$jadwal->id] ?? '-' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fas fa-inbox text-3xl mb-3 block"></i>
                                Belum ada jadwal poliklinik yang aktif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    {{-- ================= LIVE UPDATE (Laravel Echo + Reverb) ================= --}}
    @push('scripts')
    <script>
        // Module script (app.js -> echo.js) dijalankan sebelum DOMContentLoaded,
        // jadi window.Echo sudah tersedia di sini.
        document.addEventListener('DOMContentLoaded', function () {
            var dot  = document.getElementById('live-dot');
            var teks = document.getElementById('live-text');

            function setStatus(terhubung) {
                if (!dot || !teks) return;
                dot.className  = 'inline-block w-2 h-2 rounded-full mr-1 ' + (terhubung ? 'bg-green-500' : 'bg-slate-300');
                teks.textContent = terhubung ? 'Live update' : 'Menghubungkan...';
            }

            if (!window.Echo) {
                setStatus(false);
                return;
            }

            // Dengarkan event AntrianDiperbarui di channel publik "antrian".
            window.Echo.channel('antrian').listen('.antrian.diperbarui', function (e) {
                var nomor = (e.sedang_dilayani === null || e.sedang_dilayani === undefined) ? '-' : e.sedang_dilayani;

                document.querySelectorAll('[data-nomor-jadwal="' + e.id_jadwal + '"]').forEach(function (el) {
                    el.textContent = nomor;
                    el.classList.add('animate-pulse');
                    setTimeout(function () { el.classList.remove('animate-pulse'); }, 1500);
                });
            });

            // Indikator koneksi WebSocket.
            try {
                var koneksi = window.Echo.connector.pusher.connection;
                setStatus(koneksi.state === 'connected');
                koneksi.bind('connected', function () { setStatus(true); });
                koneksi.bind('disconnected', function () { setStatus(false); });
                koneksi.bind('unavailable', function () { setStatus(false); });
            } catch (err) {
                setStatus(false);
            }
        });
    </script>
    @endpush

</x-layouts.app>
