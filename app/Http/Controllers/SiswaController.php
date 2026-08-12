<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\OrangTua;
use App\Models\Kelas;
use App\Models\Ekstrakurikuler;
use App\Http\Requests\SiswaRequest;

use App\DataTables\SiswaDataTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SiswaExport;
use App\Imports\SiswaImport;
use App\Exports\SiswaTemplateExport;

class SiswaController extends Controller
{
    use \App\Traits\AuthorizeMasterData;
    public function index(SiswaDataTable $dataTable)
    {
        return $dataTable->render('pages.siswa.index');
    }

    public function create()
    {
        $orangTuas = OrangTua::all();
        $kelas = Kelas::all();
        $ekstrakurikulers = Ekstrakurikuler::all();
        return view('pages.siswa.create', compact('orangTuas', 'kelas', 'ekstrakurikulers'));
    }

    public function store(SiswaRequest $request)
    {
        $validated = $request->validated();

        // 1. Process Parent Data (Orang Tua) if filled
        $orangTuaId = $request->orang_tua_id;
        if (!$orangTuaId && ($request->filled('nama_ayah') || $request->filled('nama_ibu') || $request->filled('email_ortu'))) {
            $userOrtu = null;
            if ($request->filled('email_ortu')) {
                $usernameOrtu = 'ortu_' . preg_replace('/[^A-Za-z0-9]/', '', strtolower($request->nisn));
                $userOrtu = User::where('email', $request->email_ortu)->first();
                if (!$userOrtu) {
                    $userOrtu = User::create([
                        'name' => $request->nama_ayah ?: ($request->nama_ibu ?: 'Orang Tua'),
                        'username' => $usernameOrtu,
                        'email' => $request->email_ortu,
                        'password' => Hash::make($request->password_ortu ?: 'password'),
                        'roles' => 'orang tua',
                        'is_active' => true,
                    ]);
                }
            }

            $orangTua = OrangTua::create([
                'user_id' => $userOrtu ? $userOrtu->id : null,
                'nama_ayah' => $request->nama_ayah,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'nomor_wa' => $request->nomor_wa_ayah,
                'nama_ibu' => $request->nama_ibu,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'nomor_wa_ibu' => $request->nomor_wa_ibu,
                'alamat' => $request->alamat_ortu,
                'email' => $request->email_ortu,
            ]);
            $orangTuaId = $orangTua->id;
        }

        // 2. Resolve Kelas ID
        $kelasId = $request->kelas_id;
        if (!$kelasId) {
            $firstKelas = Kelas::first();
            $kelasId = $firstKelas ? $firstKelas->id : 1;
        }

        // 3. Process Student User Account
        $usernameSiswa = preg_replace('/[^A-Za-z0-9]/', '', strtolower($request->nisn));
        $emailSiswa = $request->email_siswa ?: ($usernameSiswa . '@gmail.com');
        $userSiswa = User::where('username', $usernameSiswa)->orWhere('email', $emailSiswa)->first();
        if (!$userSiswa) {
            $userSiswa = User::create([
                'name' => $request->nama_siswa,
                'username' => $usernameSiswa,
                'email' => $emailSiswa,
                'password' => Hash::make($request->password_siswa ?: 'password'),
                'roles' => 'siswa',
                'is_active' => true,
            ]);
        }

        // 4. Save Siswa record
        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'orang_tua_id' => $orangTuaId,
            'kelas_id' => $kelasId,
            'ekstrakurikuler_id' => $request->ekstrakurikuler_id,
            'nisn' => $request->nisn,
            'nama_siswa' => $request->nama_siswa,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tgl_lahir' => $request->tgl_lahir,
            'agama' => $request->agama,
            'nomor_wa' => $request->nomor_wa,
            'alamat' => $request->alamat,
            'tgl_masuk' => $request->tgl_masuk ?: date('Y-m-d'),
            'status' => $request->status ?: 'Aktif',
        ]);

        alert()->html(
            'Berhasil!',
            'Data Siswa <strong>' . e($siswa->nama_siswa) . '</strong> berhasil ditambahkan.',
            'success'
        );

        return redirect()->route('siswa.index');
    }

    public function show(Siswa $siswa)
    {
        return redirect()->route('siswa.edit', $siswa);
    }

    public function edit(Siswa $siswa)
    {
        $siswa->load(['user', 'orangTua.user', 'kelas', 'ekstrakurikuler']);
        $orangTuas = OrangTua::all();
        $kelas = Kelas::all();
        $ekstrakurikulers = Ekstrakurikuler::all();
        return view('pages.siswa.edit', compact('siswa', 'orangTuas', 'kelas', 'ekstrakurikulers'));
    }

    public function update(SiswaRequest $request, Siswa $siswa)
    {
        $validated = $request->validated();

        // 1. Update/Create Student User Account
        if ($siswa->user) {
            $userData = [];
            if ($request->filled('nama_siswa')) {
                $userData['name'] = $request->nama_siswa;
            }
            if ($request->filled('email_siswa')) {
                $userData['email'] = $request->email_siswa;
            }
            if ($request->filled('password_siswa')) {
                $userData['password'] = Hash::make($request->password_siswa);
            }
            if (!empty($userData)) {
                $siswa->user->update($userData);
            }
        } elseif ($request->filled('email_siswa') || $request->filled('password_siswa')) {
            $usernameSiswa = preg_replace('/[^A-Za-z0-9]/', '', strtolower($request->nisn));
            $userSiswa = User::where('username', $usernameSiswa)->orWhere('email', $request->email_siswa)->first();
            if (!$userSiswa) {
                $userSiswa = User::create([
                    'name' => $request->nama_siswa,
                    'username' => $usernameSiswa,
                    'email' => $request->email_siswa ?: ($usernameSiswa . '@gmail.com'),
                    'password' => Hash::make($request->password_siswa ?: 'password'),
                    'roles' => 'siswa',
                    'is_active' => true,
                ]);
            }
            $siswa->user_id = $userSiswa->id;
        }

        // 2. Update/Create Parent Data (Orang Tua)
        $orangTua = $siswa->orangTua;
        if ($orangTua) {
            $orangTua->update([
                'nama_ayah' => $request->nama_ayah,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'nomor_wa' => $request->nomor_wa_ayah,
                'nama_ibu' => $request->nama_ibu,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'nomor_wa_ibu' => $request->nomor_wa_ibu,
                'alamat' => $request->alamat_ortu,
                'email' => $request->email_ortu,
            ]);
            if ($orangTua->user) {
                $parentUserData = [];
                if ($request->filled('email_ortu')) {
                    $parentUserData['email'] = $request->email_ortu;
                }
                if ($request->filled('password_ortu')) {
                    $parentUserData['password'] = Hash::make($request->password_ortu);
                }
                if (!empty($parentUserData)) {
                    $orangTua->user->update($parentUserData);
                }
            }
        } elseif ($request->filled('nama_ayah') || $request->filled('nama_ibu') || $request->filled('email_ortu')) {
            $userOrtu = null;
            if ($request->filled('email_ortu')) {
                $usernameOrtu = 'ortu_' . preg_replace('/[^A-Za-z0-9]/', '', strtolower($request->nisn));
                $userOrtu = User::where('email', $request->email_ortu)->first();
                if (!$userOrtu) {
                    $userOrtu = User::create([
                        'name' => $request->nama_ayah ?: ($request->nama_ibu ?: 'Orang Tua'),
                        'username' => $usernameOrtu,
                        'email' => $request->email_ortu,
                        'password' => Hash::make($request->password_ortu ?: 'password'),
                        'roles' => 'orang tua',
                        'is_active' => true,
                    ]);
                }
            }

            $newOrangTua = OrangTua::create([
                'user_id' => $userOrtu ? $userOrtu->id : null,
                'nama_ayah' => $request->nama_ayah,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'nomor_wa' => $request->nomor_wa_ayah,
                'nama_ibu' => $request->nama_ibu,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'nomor_wa_ibu' => $request->nomor_wa_ibu,
                'alamat' => $request->alamat_ortu,
                'email' => $request->email_ortu,
            ]);
            $siswa->orang_tua_id = $newOrangTua->id;
        }

        // 3. Update Siswa record
        $siswa->update([
            'nisn' => $request->nisn,
            'nama_siswa' => $request->nama_siswa,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tgl_lahir' => $request->tgl_lahir,
            'agama' => $request->agama,
            'nomor_wa' => $request->nomor_wa,
            'alamat' => $request->alamat,
            'tgl_masuk' => $request->tgl_masuk,
            'status' => $request->status,
            'kelas_id' => $request->kelas_id ?: $siswa->kelas_id,
            'ekstrakurikuler_id' => $request->ekstrakurikuler_id,
        ]);

        alert()->html(
            'Diperbarui!',
            'Data Siswa <strong>' . e($siswa->nama_siswa) . '</strong> berhasil diperbarui.',
            'success'
        );

        return redirect()->route('siswa.index');
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SiswaExport, 'Data_Siswa_'.date('Ymd').'.xlsx');
    }

    public function import(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\SiswaImport, $request->file('file'));

        alert()->success('Berhasil!', 'Data Siswa berhasil diimport.');
        return redirect()->route('siswa.index');
    }

    public function template()
    {
        $headers = [['NISN', 'Nama Lengkap', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'No Whatsapp', 'Alamat', 'Tanggal Masuk', 'Status', 'Email Siswa', 'Nama Ayah', 'Pekerjaan Ayah', 'No Whatsapp Ayah', 'Nama Ibu', 'Pekerjaan Ibu', 'No Whatsapp Ibu', 'Alamat Orang Tua', 'Email Orang Tua']];
        $export = new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            public function __construct(array $data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        };
        return \Maatwebsite\Excel\Facades\Excel::download($export, 'Template_Import_Siswa.xlsx');
    }

    public function destroy(Siswa $siswa)
    {
        $nama = $siswa->nama_siswa;
        $siswa->delete();

        alert()->html(
            'Dihapus!',
            'Data Siswa <strong>' . e($nama) . '</strong> berhasil dihapus.',
            'success'
        );

        return redirect()->route('siswa.index');
    }

    private function resolveSiswaAndOrangTua($user)
    {
        $siswa = null;
        $orangTua = null;

        if ($user->roles === 'siswa') {
            $siswa = Siswa::where('user_id', $user->id)->first();
            if (!$siswa) {
                $siswa = Siswa::where('nama_siswa', 'like', '%' . $user->name . '%')->first();
                if (!$siswa) {
                    $siswa = Siswa::whereNull('user_id')->first();
                }
                if ($siswa) {
                    $siswa->update(['user_id' => $user->id]);
                }
            }
            if ($siswa) {
                $orangTua = $siswa->orangTua;
            }
        } elseif ($user->roles === 'orang tua') {
            $selectedChildId = session('selected_child_id');
            if ($selectedChildId) {
                $siswa = Siswa::find($selectedChildId);
            }
            if (!$siswa) {
                $orangTuaIds = OrangTua::where('user_id', $user->id)->pluck('id')->toArray();
                if ($user->email) {
                    $extraIds = OrangTua::where('email', $user->email)->pluck('id')->toArray();
                    $orangTuaIds = array_unique(array_merge($orangTuaIds, $extraIds));
                }
                if ($user->name) {
                    $nameBase = trim(explode(',', $user->name)[0]);
                    $extraIds = OrangTua::where('nama_ayah', 'like', '%' . $nameBase . '%')
                        ->orWhere('nama_ibu', 'like', '%' . $nameBase . '%')
                        ->pluck('id')->toArray();
                    $orangTuaIds = array_unique(array_merge($orangTuaIds, $extraIds));
                }
                if (!empty($orangTuaIds)) {
                    OrangTua::whereIn('id', $orangTuaIds)->whereNull('user_id')->update(['user_id' => $user->id]);
                    $siswa = Siswa::whereIn('orang_tua_id', $orangTuaIds)->first();
                }
            }
            if ($siswa) {
                $orangTua = $siswa->orangTua;
            }
        }

        // Auto-create User account for Student if not linked
        if ($siswa && !$siswa->user_id) {
            $username = preg_replace('/[^A-Za-z0-9]/', '', strtolower($siswa->nisn));
            $studentUser = User::where('username', $username)->first();
            if (!$studentUser) {
                $studentUser = User::create([
                    'name' => $siswa->nama_siswa,
                    'username' => $username,
                    'email' => $username . '@gmail.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'roles' => 'siswa',
                    'is_active' => true,
                ]);
            }
            $siswa->update(['user_id' => $studentUser->id]);
            $siswa->load('user');
        }

        // Auto-create User account for Parent if not linked
        if ($orangTua && !$orangTua->user_id) {
            $username = 'ortu_' . ($siswa ? $siswa->nisn : rand(10000000, 99999999));
            $parentUser = User::where('username', $username)->first();
            if (!$parentUser) {
                $parentUser = User::create([
                    'name' => $orangTua->nama_ayah,
                    'username' => $username,
                    'email' => $username . '@gmail.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                    'roles' => 'orang tua',
                    'is_active' => true,
                ]);
            }
            $orangTua->update(['user_id' => $parentUser->id]);
            $orangTua->load('user');
        }

        return [$siswa, $orangTua];
    }

    public function profile()
    {
        $user = auth()->user();
        list($siswa, $orangTua) = $this->resolveSiswaAndOrangTua($user);

        if (!$siswa) {
            alert()->error('Error', 'Data profil siswa tidak ditemukan.');
            return redirect()->route('dashboard');
        }

        return view('pages.siswa.profile', compact('siswa', 'orangTua'));
    }

    public function profileUpdate(Request $request)
    {
        $user = auth()->user();
        list($siswa, $orangTua) = $this->resolveSiswaAndOrangTua($user);

        if (!$siswa) {
            alert()->error('Error', 'Data profil siswa tidak ditemukan.');
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            // Data Pribadi
            'nama_siswa' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'required|string|max:255',
            'tgl_lahir' => 'required|date',
            'agama' => 'required|string|max:255',
            'nomor_wa' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tgl_masuk' => 'nullable|date',
            'email_siswa' => 'required|email|unique:users,email,' . ($siswa->user_id ?? 0),

            // Data Orang Tua
            'nama_ayah' => 'required|string|max:255',
            'pekerjaan_ayah' => 'nullable|string|max:255',
            'nomor_wa_ayah' => 'nullable|string|max:20',
            'nama_ibu' => 'required|string|max:255',
            'pekerjaan_ibu' => 'nullable|string|max:255',
            'nomor_wa_ibu' => 'nullable|string|max:20',
            'alamat_ortu' => 'nullable|string',
            'email_ortu' => 'nullable|email',
        ]);

        // 1. Update Student model
        $siswa->update([
            'nama_siswa' => $validated['nama_siswa'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'tgl_lahir' => $validated['tgl_lahir'],
            'agama' => $validated['agama'],
            'nomor_wa' => $validated['nomor_wa'],
            'alamat' => $validated['alamat'],
            'tgl_masuk' => $validated['tgl_masuk'],
        ]);

        // Update Student User Email
        if ($siswa->user) {
            $siswa->user->update(['email' => $validated['email_siswa']]);
        }

        // 2. Update parent
        if ($orangTua) {
            $orangTua->update([
                'nama_ayah' => $validated['nama_ayah'],
                'pekerjaan_ayah' => $validated['pekerjaan_ayah'],
                'nomor_wa' => $validated['nomor_wa_ayah'],
                'nama_ibu' => $validated['nama_ibu'],
                'pekerjaan_ibu' => $validated['pekerjaan_ibu'],
                'nomor_wa_ibu' => $validated['nomor_wa_ibu'],
                'alamat' => $validated['alamat_ortu'],
                'email' => $validated['email_ortu'],
            ]);
            // If parent has user record, update email
            if ($orangTua->user) {
                $orangTua->user->update(['email' => $validated['email_ortu']]);
            }
        }

        alert()->success('Berhasil!', 'Data profil berhasil diperbarui.');
        return redirect()->back();
    }
}
