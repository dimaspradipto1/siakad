<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PanduanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()?->roles === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'role'        => ['required', 'string', 'max:100'],
            'judul'       => ['nullable', 'string', 'max:255'],
            'icon'        => ['nullable', 'string', 'max:100'],
            'link_gdrive' => ['required', 'url', 'max:1000'],
            'urutan'      => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom message validation.
     */
    public function messages(): array
    {
        return [
            'role.required'        => 'Role / Kategori wajib dipilih atau diisi.',
            'link_gdrive.required' => 'Link Google Drive wajib diisi.',
            'link_gdrive.url'      => 'Format Link Google Drive harus berupa URL yang valid (misal: https://drive.google.com/...).',
        ];
    }
}
