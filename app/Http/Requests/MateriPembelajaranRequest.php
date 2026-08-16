<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MateriPembelajaranRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'judul_materi'      => ['required', 'string', 'max:255'],
            'deskripsi_materi'  => ['nullable', 'string'],
            'tahun_ajaran_id'   => ['required', 'exists:tahun_ajarans,id'],
            'semester_name'     => ['required', 'string', 'max:50'],
            'kelas_id'          => ['required', 'exists:kelas,id'],
            'nama_mata_pelajaran' => ['required', 'string'],
            'file_materi'       => $this->isMethod('POST') 
                                    ? ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png', 'max:10240'] 
                                    : ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'judul_materi.required'      => 'Judul Materi wajib diisi.',
            'judul_materi.max'           => 'Judul Materi maksimal 255 karakter.',
            'tahun_ajaran_id.required'   => 'Tahun Ajaran wajib dipilih.',
            'tahun_ajaran_id.exists'     => 'Tahun Ajaran tidak valid.',
            'semester_name.required'     => 'Semester wajib dipilih.',
            'semester_name.in'           => 'Semester tidak valid.',
            'kelas_id.required'          => 'Kelas wajib dipilih.',
            'kelas_id.exists'            => 'Kelas tidak valid.',
            'nama_mata_pelajaran.required' => 'Mata Pelajaran wajib dipilih.',
            'file_materi.required'       => 'File Materi wajib diunggah.',
            'file_materi.file'           => 'Unggahan harus berupa file.',
            'file_materi.mimes'          => 'Tipe file tidak didukung. Gunakan format pdf, doc, docx, xls, xlsx, ppt, pptx, zip, rar, atau gambar.',
            'file_materi.max'            => 'Ukuran file maksimal adalah 10 MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'judul_materi'      => 'Judul Materi',
            'deskripsi_materi'  => 'Deskripsi Materi',
            'tahun_ajaran_id'   => 'Tahun Ajaran',
            'semester_name'     => 'Semester',
            'kelas_id'          => 'Kelas',
            'nama_mata_pelajaran' => 'Mata Pelajaran',
            'file_materi'       => 'File Materi',
        ];
    }
}
