<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\User;
use App\Http\Requests\PegawaiRequest;
use App\DataTables\PegawaiDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PegawaiController extends Controller
{
    use \App\Traits\AuthorizeMasterData;

    /**
     * Tampilkan daftar pegawai (DataTables).
     */
    public function index(PegawaiDataTable $dataTable)
    {
        return $dataTable->render('pages.pegawai.index');
    }

    /**
     * Tampilkan form tambah pegawai.
     */
    public function create()
    {
        $jabatans = \App\Models\Jabatan::where('status', 'Aktif')->get();
        if ($jabatans->isEmpty()) {
            $jabatans = \App\Models\Jabatan::all();
        }
        return view('pages.pegawai.create', compact('jabatans'));
    }

    /**
     * Simpan data pegawai baru ke database.
     */
    public function store(PegawaiRequest $request)
    {
        $validated = $request->validated();

        $role = $request->role ?: 'pegawai';
        $jabatan = $request->jabatan ?: ucwords($role);

        // 1. Create User Account
        $username = preg_replace('/[^A-Za-z0-9]/', '', strtolower($request->nip));
        $email = $request->email ?: ($username . '@gmail.com');

        $user = User::where('username', $username)->orWhere('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $request->nama_pegawai,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($request->password ?: 'password'),
                'roles' => $role,
                'is_active' => ($request->status ?? 'Aktif') === 'Aktif',
            ]);
        }

        // 2. Create Pegawai record
        $pegawai = Pegawai::create([
            'user_id' => $user->id,
            'nip' => $request->nip,
            'nama_pegawai' => $request->nama_pegawai,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tgl_lahir' => $request->tgl_lahir,
            'jabatan' => $jabatan,
            'golongan' => $request->golongan,
            'pendidikan_terakhir' => $request->pendidikan_terakhir,
            'status' => $request->status ?: 'Aktif',
            'agama' => $request->agama,
            'nomor_wa' => $request->nomor_wa,
            'alamat' => $request->alamat,
        ]);

        // 3. Auto create Guru record if role is guru or wali kelas
        if (in_array(strtolower($role), ['guru', 'wali kelas'])) {
            \App\Models\Guru::firstOrCreate([
                'pegawai_id' => $pegawai->id,
            ], [
                'nip_guru' => $pegawai->nip,
                'golongan' => $pegawai->golongan ?: 'Non-ASN',
                'pendidikan_terakhir' => $pegawai->pendidikan_terakhir ?: 'S1',
            ]);
        }

        alert()->html(
            'Berhasil!',
            'Data pegawai <strong>' . e($pegawai->nama_pegawai) . '</strong> berhasil ditambahkan.',
            'success'
        );

        return redirect()->route('pegawai.index');
    }

    /**
     * Tampilkan detail pegawai (redirect ke edit).
     */
    public function show(Pegawai $pegawai)
    {
        return redirect()->route('pegawai.edit', $pegawai);
    }

    /**
     * Tampilkan form edit pegawai.
     */
    public function edit(Pegawai $pegawai)
    {
        $pegawai->load('user');
        $jabatans = \App\Models\Jabatan::where('status', 'Aktif')->get();
        if ($jabatans->isEmpty()) {
            $jabatans = \App\Models\Jabatan::all();
        }
        return view('pages.pegawai.edit', compact('pegawai', 'jabatans'));
    }

    /**
     * Update data pegawai di database.
     */
    public function update(PegawaiRequest $request, Pegawai $pegawai)
    {
        $validated = $request->validated();

        $role = $request->role ?: ($pegawai->user->roles ?? 'pegawai');
        $jabatan = $request->jabatan ?: ucwords($role);

        // Update linked User Account
        $user = $pegawai->user;
        if (!$user && $pegawai->user_id) {
            $user = User::find($pegawai->user_id);
        }
        if (!$user) {
            $rawUsername = $pegawai->nip ?: 'pegawai_' . $pegawai->id;
            $username = preg_replace('/[^A-Za-z0-9]/', '', strtolower($rawUsername));
            $email = $request->email ?: ($username . '@gmail.com');
            $user = User::where('username', $username)->orWhere('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $request->nama_pegawai,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($request->filled('password') ? $request->password : 'password'),
                    'roles' => $role,
                    'is_active' => ($request->status ?? 'Aktif') === 'Aktif',
                ]);
            }
            $pegawai->user_id = $user->id;
            $pegawai->saveQuietly();
        }

        if ($user) {
            $userData = [
                'name' => $request->nama_pegawai,
                'roles' => $role,
                'is_active' => ($request->status ?? 'Aktif') === 'Aktif',
            ];
            if ($request->filled('email')) {
                $userData['email'] = $request->email;
            }
            // Hanya ganti password jika diisi password baru
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $user->update($userData);
        }

        $pegawai->update([
            'nip' => $request->nip,
            'nama_pegawai' => $request->nama_pegawai,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tgl_lahir' => $request->tgl_lahir,
            'jabatan' => $jabatan,
            'golongan' => $request->golongan,
            'pendidikan_terakhir' => $request->pendidikan_terakhir,
            'status' => $request->status ?: 'Aktif',
            'agama' => $request->agama,
            'nomor_wa' => $request->nomor_wa,
            'alamat' => $request->alamat,
        ]);

        if (in_array(strtolower($role), ['guru', 'wali kelas'])) {
            \App\Models\Guru::updateOrCreate([
                'pegawai_id' => $pegawai->id,
            ], [
                'nip_guru' => $pegawai->nip,
                'golongan' => $pegawai->golongan ?: 'Non-ASN',
                'pendidikan_terakhir' => $pegawai->pendidikan_terakhir ?: 'S1',
            ]);
        }

        alert()->html(
            'Diperbarui!',
            'Data pegawai <strong>' . e($pegawai->nama_pegawai) . '</strong> berhasil diperbarui.',
            'success'
        );

        return redirect()->route('pegawai.index');
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PegawaiExport, 'Data_Pegawai_'.date('Ymd').'.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\PegawaiImport, $request->file('file'));

        alert()->success('Berhasil!', 'Data Pegawai berhasil diimport.');
        return redirect()->route('pegawai.index');
    }

    public function template()
    {
        $headers = [['NIP', 'Nama Lengkap', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Pendidikan Terakhir', 'Golongan', 'Email', 'Alamat Lengkap', 'No Whatsapp', 'Role', 'Status']];
        $export = new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            public function __construct(array $data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        };
        return \Maatwebsite\Excel\Facades\Excel::download($export, 'Template_Import_Pegawai.xlsx');
    }

    public function destroy(Pegawai $pegawai)
    {
        $nama = $pegawai->nama_pegawai;
        $pegawai->delete();

        alert()->html(
            'Dihapus!',
            'Data pegawai <strong>' . e($nama) . '</strong> berhasil dihapus.',
            'success'
        );

        return redirect()->route('pegawai.index');
    }
}
