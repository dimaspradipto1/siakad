<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Pegawai;
use App\Http\Requests\GuruRequest;
use App\DataTables\GuruDataTable;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    use \App\Traits\AuthorizeMasterData;

    /**
     * Tampilkan daftar guru (DataTables).
     */
    public function index(GuruDataTable $dataTable)
    {
        return $dataTable->render('pages.guru.index');
    }

    /**
     * Tampilkan form tambah guru.
     */
    public function create()
    {
        // Menampilkan pegawai dengan role Guru atau Wali Kelas yang belum terdaftar di tabel guru
        $pegawais = Pegawai::where(function ($query) {
            $query->whereHas('user', function ($u) {
                $u->where(function ($q) {
                    $q->whereRaw('LOWER(roles) LIKE ?', ['%guru%'])
                      ->orWhereRaw('LOWER(roles) LIKE ?', ['%wali kelas%']);
                });
            })->orWhereRaw('LOWER(jabatan) LIKE ?', ['%guru%'])
              ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%wali kelas%']);
        })
        ->whereDoesntHave('guru')
        ->orderBy('nama_pegawai')
        ->get();

        return view('pages.guru.create', compact('pegawais'));
    }

    /**
     * Simpan data guru baru ke database.
     */
    public function store(GuruRequest $request)
    {
        $validated = $request->validated();

        $guru = Guru::create($validated);
        
        $nama = $guru->pegawai ? $guru->pegawai->nama_pegawai : $guru->nip_guru;

        alert()->html(
            'Berhasil!',
            'Guru <strong>' . e($nama) . '</strong> berhasil ditambahkan.',
            'success'
        );

        return redirect()->route('guru.index');
    }

    /**
     * Tampilkan detail guru (redirect ke edit).
     */
    public function show(Guru $guru)
    {
        return redirect()->route('guru.edit', $guru);
    }

    /**
     * Tampilkan form edit guru.
     */
    public function edit(Guru $guru)
    {
        // Ambil pegawai ber-role Guru/Wali Kelas yang belum menjadi guru, ATAU yang sedang di-edit saat ini
        $pegawais = Pegawai::where(function ($query) {
            $query->whereHas('user', function ($u) {
                $u->where(function ($q) {
                    $q->whereRaw('LOWER(roles) LIKE ?', ['%guru%'])
                      ->orWhereRaw('LOWER(roles) LIKE ?', ['%wali kelas%']);
                });
            })->orWhereRaw('LOWER(jabatan) LIKE ?', ['%guru%'])
              ->orWhereRaw('LOWER(jabatan) LIKE ?', ['%wali kelas%']);
        })
        ->where(function ($q) use ($guru) {
            $q->whereDoesntHave('guru')
              ->orWhere('id', $guru->pegawai_id);
        })
        ->orderBy('nama_pegawai')
        ->get();

        return view('pages.guru.edit', compact('guru', 'pegawais'));
    }

    /**
     * Update data guru di database.
     */
    public function update(GuruRequest $request, Guru $guru)
    {
        $validated = $request->validated();

        $guru->update($validated);

        $nama = $guru->pegawai ? $guru->pegawai->nama_pegawai : $guru->nip_guru;

        alert()->html(
            'Diperbarui!',
            'Data guru <strong>' . e($nama) . '</strong> berhasil diperbarui.',
            'success'
        );

        return redirect()->route('guru.index');
    }

    /**
     * Hapus data guru dari database.
     */
    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GuruExport, 'Data_Guru_'.date('Ymd').'.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\GuruImport, $request->file('file'));

        alert()->success('Berhasil!', 'Data Guru berhasil diimport.');
        return redirect()->route('guru.index');
    }

    public function template()
    {
        $headers = [['NIP Pegawai', 'NIP Guru', 'Golongan', 'Pendidikan Terakhir']];
        $export = new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            public function __construct(array $data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        };
        return \Maatwebsite\Excel\Facades\Excel::download($export, 'Template_Import_Guru.xlsx');
    }

    public function destroy(Guru $guru)
    {
        $nama = $guru->pegawai ? $guru->pegawai->nama_pegawai : $guru->nip_guru;
        
        $guru->delete();

        alert()->html(
            'Dihapus!',
            'Data guru <strong>' . e($nama) . '</strong> berhasil dihapus.',
            'success'
        );

        return redirect()->route('guru.index');
    }
}
