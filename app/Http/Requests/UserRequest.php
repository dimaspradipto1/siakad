<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Semua pengguna boleh mengakses form ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk form.
     */
    public function rules(): array
    {

        $user = $this->route('user');
        $userId = $user instanceof \App\Models\User ? $user->id : $user;
        return [
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($userId)],
            'password'  => $userId ? ['nullable', 'string', 'min:6', 'confirmed'] : ['required', 'string', 'min:6', 'confirmed'],
            'roles'     => ['required'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Pesan error kustom.
     */
    public function messages(): array
    {

        return [
            'name.required'      => 'Nama wajib diisi.',
            'name.string'        => 'Nama harus berupa teks.',
            'name.max'           => 'Nama maksimal :max karakter.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah digunakan.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'roles.required'     => 'Role wajib dipilih.',
            'roles.in'           => 'Role yang dipilih tidak valid.',
        ];
    }

}
