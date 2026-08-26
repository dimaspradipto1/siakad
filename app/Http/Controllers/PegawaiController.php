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

        $rolesInput = $request->input('roles') ?: $request->input('role');
        if (is_array($rolesInput)) {
            $rolesList = array_values(array_filter($rolesInput));
            $rolesStr = implode(',', $rolesList);
            $primaryRole = $rolesList[0] ?? 'pegawai';
        } elseif (is_string($rolesInput) && !empty($rolesInput)) {
            $rolesList = array_map('trim', explode(',', $rolesInput));
            $rolesStr = $rolesInput;
            $primaryRole = $rolesList[0] ?? 'pegawai';
        } else {
            $rolesList = ['pegawai'];
            $rolesStr = 'pegawai';
            $primaryRole = 'pegawai';
        }

        $jabatan = $request->jabatan ?: ucwords($primaryRole);

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
                'roles' => $rolesStr,
                'is_active' => ($request->status ?? 'Aktif') === 'Aktif',
            ]);
        } else {
            $user->update([
                'roles' => $rolesStr,
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

        // 3. Auto create Guru record hanya jika role mengandung 'guru' secara eksplisit
        //    (role 'wali kelas' saja TIDAK membuat record di tabel gurus)
        if (in_array('guru', array_map('strtolower', $rolesList))) {
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

        $rolesInput = $request->input('roles') ?: $request->input('role');
        if (is_array($rolesInput)) {
            $rolesList = array_values(array_filter($rolesInput));
            $rolesStr = implode(',', $rolesList);
            $primaryRole = $rolesList[0] ?? 'pegawai';
        } elseif (is_string($rolesInput) && !empty($rolesInput)) {
            $rolesList = array_map('trim', explode(',', $rolesInput));
            $rolesStr = $rolesInput;
            $primaryRole = $rolesList[0] ?? 'pegawai';
        } else {
            $rolesList = $pegawai->user ? $pegawai->user->getRolesList() : ['pegawai'];
            $rolesStr = implode(',', $rolesList);
            $primaryRole = $rolesList[0] ?? 'pegawai';
        }

        $jabatan = $request->jabatan ?: ucwords($primaryRole);

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
                    'roles' => $rolesStr,
                    'is_active' => ($request->status ?? 'Aktif') === 'Aktif',
                ]);
            }
            $pegawai->user_id = $user->id;
            $pegawai->saveQuietly();
        }

        if ($user) {
            $userData = [
                'name' => $request->nama_pegawai,
                'roles' => $rolesStr,
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

        $hasGuruRole = in_array('guru', array_map('strtolower', $rolesList));

        if ($hasGuruRole) {
            // Role mengandung 'guru' → buat atau perbarui record Guru
            \App\Models\Guru::updateOrCreate([
                'pegawai_id' => $pegawai->id,
            ], [
                'nip_guru' => $pegawai->nip,
                'golongan' => $pegawai->golongan ?: 'Non-ASN',
                'pendidikan_terakhir' => $pegawai->pendidikan_terakhir ?: 'S1',
            ]);
        } else {
            // Role tidak mengandung 'guru' → hapus record Guru jika ada
            \App\Models\Guru::where('pegawai_id', $pegawai->id)->delete();
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
