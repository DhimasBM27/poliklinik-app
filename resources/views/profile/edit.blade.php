<x-layouts.app title="Profile">

    <div class="max-w-xl mx-auto space-y-6">

        {{-- Informasi akun --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
            <div class="card-body p-8">
                <h2 class="text-xl font-bold text-slate-800 mb-4">
                    <i class="fas fa-user text-indigo-600 mr-2"></i>
                    Profile
                </h2>

                <table class="text-sm w-full">
                    <tbody>
                        <tr>
                            <td class="py-1.5 w-36 text-slate-500">Nama</td>
                            <td class="py-1.5 font-semibold text-slate-800">{{ $user->nama }}</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 text-slate-500">Email</td>
                            <td class="py-1.5 text-slate-800">{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 text-slate-500">Role</td>
                            <td class="py-1.5 text-slate-800 capitalize">{{ $user->role }}</td>
                        </tr>
                        @if ($user->role === 'pasien')
                        <tr>
                            <td class="py-1.5 text-slate-500">No. Rekam Medis</td>
                            <td class="py-1.5 text-slate-800">{{ $user->no_rm ?? '-' }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Form ganti password --}}
        <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
            <div class="card-body p-8">
                <h3 class="text-lg font-bold text-slate-800 mb-4">
                    <i class="fas fa-key text-indigo-600 mr-2"></i>
                    Ganti Password
                </h3>

                <form action="{{ route('profile.password.update') }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    {{-- Password lama --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Password Lama <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="current_password" autocomplete="current-password"
                            class="w-full px-4 py-2 border-2 rounded-lg focus:border-primary focus:outline-none
                                   @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password baru --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" autocomplete="new-password"
                            class="w-full px-4 py-2 border-2 rounded-lg focus:border-primary focus:outline-none
                                   @error('password') border-red-500 @enderror">
                        @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-[11px] text-slate-400 mt-1">Minimal 8 karakter.</p>
                    </div>

                    {{-- Konfirmasi password baru --}}
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                            class="w-full px-4 py-2 border-2 rounded-lg focus:border-primary focus:outline-none">
                    </div>

                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-sm transition">
                        <i class="fas fa-save mr-1"></i>
                        Simpan Password
                    </button>
                </form>
            </div>
        </div>

    </div>

</x-layouts.app>
