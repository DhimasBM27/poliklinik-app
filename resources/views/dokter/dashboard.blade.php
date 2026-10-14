<x-layouts.app title="Dokter Dashboard">
    <div class="px-6 py-6">
        <h1 class="text-2xl font-bold mb-4">Selamat Datang, Dokter!</h1>

        <div class="bg-base-100 p-6 rounded-xl shadow-sm border border-base-300">
            <p class="text-base-content/70">
                Halo <span class="font-semibold text-primary">{{ auth()->user()->nama }}</span>, Anda telah masuk sebagai Dokter. Gunakan menu di sebelah kiri untuk mengelola Jadwal Periksa.
            </p>
        </div>
    </div>
</x-layouts.app>