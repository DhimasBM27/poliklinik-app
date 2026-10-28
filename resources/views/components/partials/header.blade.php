@php
    // Hanya notifikasi milik pengguna yang login (relasi notifications() milik user).
    $penggunaLogin     = auth()->user();
    $notifikasi        = $penggunaLogin->notifications()->latest()->limit(10)->get();
    $jumlahBelumDibaca = $penggunaLogin->unreadNotifications()->count();
@endphp

<header class="bg-base-100 border-b border-base-300 h-16 flex items-center px-6 gap-4 sticky top-0 z-30 shadow-sm">

    {{-- Mobile Hamburger --}}
    <button onclick="toggleSidebar()" class="btn btn-square btn-ghost lg:hidden">
        <i data-lucide="menu" class="w-5 h-5"></i>
    </button>

    {{-- Breadcrumb --}}
    <div class="flex-1">
        <div class="flex items-center gap-2 text-sm">
            <span class="text-base-content/50">Poliklinik</span>
            <i data-lucide="chevron-right" class="w-4 h-4 text-base-content/30"></i>
            <span class="font-semibold text-base-content">
                {{ $title ?? 'Dashboard' }}
            </span>
        </div>
    </div>

    {{-- Notifikasi (lonceng) --}}
    <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost btn-circle relative" aria-label="Notifikasi">
            <i class="fas fa-bell text-lg"></i>

            @if ($jumlahBelumDibaca > 0)
            <span class="absolute top-0.5 right-0.5 min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                {{ $jumlahBelumDibaca > 99 ? '99+' : $jumlahBelumDibaca }}
            </span>
            @endif
        </div>

        <div tabindex="0" class="dropdown-content z-50 mt-3 w-96 max-w-[90vw] bg-base-100 rounded-2xl shadow-xl border border-slate-200 overflow-hidden">

            {{-- Judul dropdown --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-slate-50">
                <span class="text-sm font-bold text-slate-800">
                    Notifikasi
                    @if ($jumlahBelumDibaca > 0)
                        <span class="text-xs font-semibold text-red-500">({{ $jumlahBelumDibaca }} baru)</span>
                    @endif
                </span>

                @if ($jumlahBelumDibaca > 0)
                <form method="POST" action="{{ route('notifikasi.bacaSemua') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-indigo-600 hover:underline">
                        Tandai semua dibaca
                    </button>
                </form>
                @endif
            </div>

            {{-- Daftar notifikasi (terbaru di atas) --}}
            <div class="max-h-96 overflow-y-auto">
                @forelse ($notifikasi as $item)
                <div class="px-4 py-3 border-b border-slate-100 {{ $item->read_at ? 'bg-white' : 'bg-indigo-50 border-l-4 border-l-indigo-500' }}">

                    <a href="{{ route('notifikasi.buka', $item->id) }}" class="block">
                        <p class="text-sm {{ $item->read_at ? 'text-slate-500' : 'font-bold text-slate-800' }}">
                            {{ $item->data['judul'] ?? 'Notifikasi' }}
                        </p>
                        <p class="text-xs mt-0.5 {{ $item->read_at ? 'text-slate-400' : 'text-slate-600' }}">
                            {{ $item->data['pesan'] ?? '' }}
                        </p>
                    </a>

                    <div class="flex items-center justify-between mt-2">
                        <span class="text-[11px] text-slate-400">
                            {{ $item->created_at->format('d/m/Y H:i') }}
                        </span>

                        @if (!$item->read_at)
                        <form method="POST" action="{{ route('notifikasi.baca', $item->id) }}">
                            @csrf
                            <button type="submit" class="text-[11px] font-semibold text-indigo-600 hover:underline">
                                Tandai dibaca
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-4 py-10 text-center text-sm text-slate-400">
                    <i class="fas fa-bell-slash text-2xl mb-2 block"></i>
                    Belum ada notifikasi
                </div>
                @endforelse
            </div>

        </div>
    </div>

    {{-- Fullscreen --}}
    <button onclick="toggleFullscreen()" class="btn btn-square btn-ghost">
        <i id="fsIcon" class="fas fa-expand w-5 h-5"></i>
    </button>

    {{-- User Info --}}
    <div class="flex items-center gap-3">

        <div class="text-right hidden sm:block">
            <div class="text-sm font-semibold leading-tight">
                {{ auth()->user()->nama ?? 'Pengguna' }}
            </div>
            <div class="text-xs text-base-content/50 leading-tight">
                {{ auth()->user()->role ?? 'Admin Sistem' }}
            </div>
        </div>

        <div class="avatar">
            <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center">
                <span class="text-sm font-semibold leading-none">
                    {{ strtoupper(substr(auth()->user()->nama ?? 'U', 0, 1)) }}
                </span>
            </div>
        </div>
    </div>

</header>

<script>
    function toggleFullscreen() {
        const icon = document.getElementById('fsIcon');

        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            icon.classList.remove('fa-expand');
            icon.classList.add('fa-compress');
        } else {
            document.exitFullscreen();
            icon.classList.remove('fa-compress');
            icon.classList.add('fa-expand');
        }
    }
</script>
