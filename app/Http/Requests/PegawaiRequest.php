<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PegawaiRequest extends FormRequest
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
        $id = $this->route('pegawai') ? (is_object($this->route('pegawai')) ? $this->route('pegawai')->id : $this->route('pegawai')) : null;
        return [
            'nip'                 => ['required', 'string', 'unique:pegawais,nip,' . $id, 'max:50'],
            'nama_pegawai'        => ['required', 'string', 'max:150'],
            'jenis_kelamin'       => ['required', 'in:Laki-laki,Perempuan'],
            'tempat_lahir'        => ['required', 'string', 'max:100'],
            'tgl_lahir'           => ['required', 'date'],
            'jabatan'             => ['nullable', 'string', 'max:100'],
            'golongan'            => ['nullable', 'string', 'max:50'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
            'status'              => ['nullable', 'string', 'max:50'],
            'agama'               => ['required', 'string', 'max:50'],
            'nomor_wa'            => ['nullable', 'string', 'max:20'],
            'alamat'              => ['nullable', 'string'],

            'email'               => ['nullable', 'email'],
            'role'                => ['nullable'],
            'roles'               => ['nullable'],
            'password'            => ['nullable', 'string', 'min:6', 'confirmed'],
        ];
    }

    /**
     * Pesan error kustom.
     */
    public function messages(): array
    {
        return [
            'nip.required'           => 'NIP wajib diisi.',
            'nip.unique'             => 'NIP sudah digunakan oleh pegawai lain.',
            'nama_pegawai.required'  => 'Nama Lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tempat_lahir.required'  => 'Tempat lahir wajib diisi.',
            'tgl_lahir.required'     => 'Tanggal lahir wajib diisi.',
            'agama.required'         => 'Agama wajib dipilih.',
            'password.min'           => 'Password minimal :min karakter.',
            'password.confirmed'     => 'Konfirmasi Password tidak cocok.',
        ];
    }
}
