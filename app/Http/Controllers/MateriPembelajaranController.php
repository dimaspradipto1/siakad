<?php

namespace App\Http\Controllers;

use App\Models\MateriPembelajaran;
use Illuminate\Http\Request;

use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\OrangTua;
use App\Http\Requests\MateriPembelajaranRequest;
use App\DataTables\MateriPembelajaranDataTable;

class MateriPembelajaranController extends Controller
{
    use \App\Traits\AuthorizeTransactionData;

    private function getWaliKelasIds($user): array
    {
        if (!$user) return [];

        $guru = $user->pegawai?->guru ?? $user->guru;
        if (!$guru && $user->pegawai_id) {
            $guru = \App\Models\Guru::where('pegawai_id', $user->pegawai_id)->first();
        }
        if (!$guru) {
            $guru = \App\Models\Guru::where('user_id', $user->id)->first();
        }
        if (!$guru) {
            $pegawai = \App\Models\Pegawai::where('user_id', $user->id)->first();
            if (!$pegawai) {
                $nameBase = trim(explode(',', $user->name)[0]);
                $pegawai = \App\Models\Pegawai::where('nama_pegawai', 'like', '%' . $nameBase . '%')->first();
            }
            if ($pegawai) {
                $guru = \App\Models\Guru::where('pegawai_id', $pegawai->id)->first();
            }
        }

        if ($guru) {
            $ids = \App\Models\WaliKelas::where('guru_id', $guru->id)->pluck('kelas_id')->toArray();
            if (!empty($ids)) {
                return array_unique(array_filter($ids));
            }
        }

        $nameBase = trim(explode(',', $user->name)[0]);
        $matchingWali = \App\Models\WaliKelas::whereHas('guru.pegawai', function($q) use ($nameBase) {
            $q->where('nama_pegawai', 'like', '%' . $nameBase . '%');
        })->pluck('kelas_id')->toArray();

        if (!empty($matchingWali)) {
            return array_unique(array_filter($matchingWali));
        }

        return [];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(MateriPembelajaranDataTable $dataTable)
    {
        $user = auth()->user();
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas');
        $isPersonal = $user && in_array($user->roles, ['siswa', 'orang tua']);
        $mySiswa = null;

        if ($isPersonal) {
            if ($user->roles === 'siswa') {
                $mySiswa = \App\Models\Siswa::where('user_id', $user->id)->first();
            } else {
                $orangTua = \App\Models\OrangTua::where('user_id', $user->id)->first();
                if (!$orangTua) {
                    $orangTua = \App\Models\OrangTua::where('nama_ayah', 'like', '%' . $user->name . '%')
                        ->orWhere('nama_ibu', 'like', '%' . $user->name . '%')
                        ->first();
                }
                if ($orangTua) {
                    $selectedChildId = session('selected_child_id');
                    if ($selectedChildId) {
                        $mySiswa = \App\Models\Siswa::where('id', $selectedChildId)->where('orang_tua_id', $orangTua->id)->first();
                    }
                    if (!$mySiswa) {
                        $mySiswa = \App\Models\Siswa::where('orang_tua_id', $orangTua->id)->first();
                    }
                }
            }
        }

        if ($isPersonal && $mySiswa) {
            $studentKelasIds = \App\Models\PembagianKelas::where('siswa_id', $mySiswa->id)->pluck('kelas_id')->toArray();
            if ($mySiswa->kelas_id) {
                $studentKelasIds[] = $mySiswa->kelas_id;
            }
            $studentKelasIds = array_unique(array_filter($studentKelasIds));
            $kelas = Kelas::whereIn('id', $studentKelasIds)->get();
        } elseif ($isWali) {
            $waliKelasIds = $this->getWaliKelasIds($user);
            $kelas = !empty($waliKelasIds)
                ? Kelas::whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } else {
            $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        }

        $uniqueMapels = MataPelajaran::query()->distinct()->orderBy('nama_mata_pelajaran')->pluck('nama_mata_pelajaran');

        $tahunAjarans = TahunAjaran::all();
        return $dataTable->render('pages.materipembelajaran.index', compact('kelas', 'tahunAjarans', 'uniqueMapels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $tahunAjarans = TahunAjaran::all();
        $uniqueMapels = MataPelajaran::query()->distinct()->orderBy('nama_mata_pelajaran')->pluck('nama_mata_pelajaran');
        return view('pages.materipembelajaran.create', compact('kelas', 'tahunAjarans', 'uniqueMapels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MateriPembelajaranRequest $request)
    {
        $validated = $request->validated();
        $validated['diupload_oleh'] = auth()->id();

        $semester = Semester::query()
            ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
            ->where('nama_semester', $request->semester_name)
            ->first();
        $validated['semester_id'] = $semester ? $semester->id : null;
        unset($validated['semester_name']);

        // Look up the actual mata_pelajaran_id
        $mapel = MataPelajaran::query()
            ->where('kelas_id', $request->kelas_id)
            ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
            ->where('semester_id', $validated['semester_id'])
            ->where('nama_mata_pelajaran', $request->nama_mata_pelajaran)
            ->first();

        if (!$mapel) {
            return back()->withInput()->withErrors(['nama_mata_pelajaran' => 'Mata pelajaran tidak ditemukan untuk kelas, tahun ajaran, dan semester yang dipilih.']);
        }
        $validated['mata_pelajaran_id'] = $mapel->id;
        unset($validated['nama_mata_pelajaran']);

        if ($request->hasFile('file_materi')) {
            $file = $request->file('file_materi');
            $filename = time() . '_' . $file->getClientOriginalName();
            if (!file_exists(public_path('uploads/materi'))) {
                mkdir(public_path('uploads/materi'), 0755, true);
            }
            $file->move(public_path('uploads/materi'), $filename);
            $validated['file_materi'] = 'uploads/materi/' . $filename;

            // Get size
            $sizeInBytes = filesize(public_path($validated['file_materi']));
            if ($sizeInBytes >= 1048576) {
                $validated['file_size'] = number_format($sizeInBytes / 1048576, 2) . ' MB';
            } elseif ($sizeInBytes >= 1024) {
                $validated['file_size'] = number_format($sizeInBytes / 1024, 2) . ' KB';
            } else {
                $validated['file_size'] = $sizeInBytes . ' Bytes';
            }
        }

        $materi = MateriPembelajaran::create($validated);

        alert()->html(
            'Berhasil!',
            'Materi Pembelajaran <strong>' . e($materi->judul_materi) . '</strong> berhasil diunggah.',
            'success'
        );

        return redirect()->route('materipembelajaran.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(MateriPembelajaran $materipembelajaran)
    {
        return redirect()->route('materipembelajaran.download', $materipembelajaran->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MateriPembelajaran $materipembelajaran)
    {
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $tahunAjarans = TahunAjaran::all();
        $uniqueMapels = MataPelajaran::query()->distinct()->orderBy('nama_mata_pelajaran')->pluck('nama_mata_pelajaran');
        return view('pages.materipembelajaran.edit', compact('materipembelajaran', 'kelas', 'tahunAjarans', 'uniqueMapels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MateriPembelajaranRequest $request, MateriPembelajaran $materipembelajaran)
    {
        $validated = $request->validated();

        $semester = Semester::query()
            ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
            ->where('nama_semester', $request->semester_name)
            ->first();
        $validated['semester_id'] = $semester ? $semester->id : null;
        unset($validated['semester_name']);

        // Look up the actual mata_pelajaran_id
        $mapel = MataPelajaran::query()
            ->where('kelas_id', $request->kelas_id)
            ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
            ->where('semester_id', $validated['semester_id'])
            ->where('nama_mata_pelajaran', $request->nama_mata_pelajaran)
            ->first();

        if (!$mapel) {
            return back()->withInput()->withErrors(['nama_mata_pelajaran' => 'Mata pelajaran tidak ditemukan untuk kelas, tahun ajaran, dan semester yang dipilih.']);
        }
        $validated['mata_pelajaran_id'] = $mapel->id;
        unset($validated['nama_mata_pelajaran']);

        if ($request->hasFile('file_materi')) {
            // Delete old file if exists
            if ($materipembelajaran->file_materi && file_exists(public_path($materipembelajaran->file_materi))) {
                @unlink(public_path($materipembelajaran->file_materi));
            }

            $file = $request->file('file_materi');
            $filename = time() . '_' . $file->getClientOriginalName();
            if (!file_exists(public_path('uploads/materi'))) {
                mkdir(public_path('uploads/materi'), 0755, true);
            }
            $file->move(public_path('uploads/materi'), $filename);
            $validated['file_materi'] = 'uploads/materi/' . $filename;

            // Get size
            $sizeInBytes = filesize(public_path($validated['file_materi']));
            if ($sizeInBytes >= 1048576) {
                $validated['file_size'] = number_format($sizeInBytes / 1048576, 2) . ' MB';
            } elseif ($sizeInBytes >= 1024) {
                $validated['file_size'] = number_format($sizeInBytes / 1024, 2) . ' KB';
            } else {
                $validated['file_size'] = $sizeInBytes . ' Bytes';
            }
        }

        $materipembelajaran->update($validated);

        alert()->html(
            'Diperbarui!',
            'Materi Pembelajaran <strong>' . e($materipembelajaran->judul_materi) . '</strong> berhasil diperbarui.',
            'success'
        );

        return redirect()->route('materipembelajaran.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MateriPembelajaran $materipembelajaran)
    {
        $judul = $materipembelajaran->judul_materi;

        if ($materipembelajaran->file_materi && file_exists(public_path($materipembelajaran->file_materi))) {
            @unlink(public_path($materipembelajaran->file_materi));
        }

        $materipembelajaran->delete();

        alert()->html(
            'Dihapus!',
            'Materi Pembelajaran <strong>' . e($judul) . '</strong> berhasil dihapus.',
            'success'
        );

        return redirect()->route('materipembelajaran.index');
    }

    /**
     * Download the uploaded file.
     */
    public function download($id)
    {
        $materi = MateriPembelajaran::findOrFail($id);
        $path = public_path($materi->file_materi);
        if (file_exists($path)) {
            return response()->download($path);
        }

        alert()->html(
            'Error!',
            'File materi tidak ditemukan di server.',
            'error'
        );
        return redirect()->back();
    }
}
