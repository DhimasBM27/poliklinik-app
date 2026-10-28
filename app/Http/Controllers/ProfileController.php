<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Halaman profile + form ganti password (semua role).
     */
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    /**
     * Ganti password akun yang sedang login.
     * Validasi ada di UpdatePasswordRequest (Form Request).
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        // Selalu memakai pengguna yang login, bukan id dari request.
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Password berhasil diganti.');
    }
}
