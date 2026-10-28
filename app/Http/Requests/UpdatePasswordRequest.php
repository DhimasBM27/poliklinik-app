<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya pengguna yang sedang login yang boleh mengganti passwordnya sendiri.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // 'current_password' = rule bawaan Laravel: cocokkan dengan password akun yang login.
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'         => 'Password lama wajib diisi.',
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.required'                 => 'Password baru wajib diisi.',
            'password.min'                      => 'Password baru minimal 8 karakter.',
            'password.confirmed'                => 'Konfirmasi password baru tidak sama.',
            'password.different'                => 'Password baru tidak boleh sama dengan password lama.',
        ];
    }
}
