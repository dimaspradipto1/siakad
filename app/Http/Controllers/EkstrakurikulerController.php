<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Http\Requests\EkstrakurikulerRequest;

use App\DataTables\EkstrakurikulerDataTable;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\PembagianKelas;
use App\Models\SiswaEkstrakurikuler;
use App\Models\OrangTua;

class EkstrakurikulerController extends Controller
{
    use \App\Traits\AuthorizeMasterData;
    public function index(EkstrakurikulerDataTable $dataTable)
    {
        if (auth()->check() && auth()->user()->roles === 'wali kelas') {
            return redirect()->route('ekstrakurikuler.siswa');
        }
        return $dataTable->render('pages.ekstrakurikuler.index');
    }

    public function create()
    {
        return view('pages.ekstrakurikuler.create');
    }

    public function store(EkstrakurikulerRequest $request)
    {
        $validated = $request->validated();
        $ekskul = Ekstrakurikuler::create($validated);

        alert()->success(
            'Berhasil!',
            'Ekstrakurikuler <strong>' . e($ekskul->nama_ekskul) . '</strong> berhasil ditambahkan.'
        )->html();

        return redirect()->route('ekstrakurikuler.index');
    }

    public function show(Ekstrakurikuler $ekstrakurikuler)
    {
        return redirect()->route('ekstrakurikuler.edit', $ekstrakurikuler);
    }

    public function edit(Ekstrakurikuler $ekstrakurikuler)
    {
        return view('pages.ekstrakurikuler.edit', compact('ekstrakurikuler'));
    }

    public function update(EkstrakurikulerRequest $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $validated = $request->validated();
        $ekstrakurikuler->update($validated);

        alert()->success(
            'Diperbarui!',
            'Ekstrakurikuler <strong>' . e($ekstrakurikuler->nama_ekskul) . '</strong> berhasil diperbarui.'
        )->html();

        return redirect()->route('ekstrakurikuler.index');
    }

    public function destroy(Ekstrakurikuler $ekstrakurikuler)
    {
        $nama = $ekstrakurikuler->nama_ekskul;
        $ekstrakurikuler->delete();

        alert()->success(
            'Dihapus!',
            'Ekstrakurikuler <strong>' . e($nama) . '</strong> berhasil dihapus.'
        )->html();

        return redirect()->route('ekstrakurikuler.index');
    }

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
                $pegawai = \App\Models\Pegawai::where('nama_pegawai', 'like', '%' . $user->name . '%')->first();
            }
            if ($pegawai) {
                $guru = \App\Models\Guru::where('pegawai_id', $pegawai->id)->first();
            }
        }

        if ($guru) {
            return \App\Models\WaliKelas::where('guru_id', $guru->id)->pluck('kelas_id')->toArray();
        }

        return [];
    }

    public function ekskulSiswa(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas');

        if ($isWali) {
            $waliKelasIds = $this->getWaliKelasIds($user);
            $kelas = !empty($waliKelasIds)
                ? \App\Models\Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } else {
            $kelas = \App\Models\Kelas::query()->orderBy('nama_kelas', 'asc')->get();
        }
        $tahunAjarans = \App\Models\TahunAjaran::query()->get();
        $ekskuls = Ekstrakurikuler::query()->orderBy('nama_ekskul', 'asc')->get();

        $selectedTa = $request->get('tahun_ajaran_id');
        $selectedSemName = $request->get('semester_name');
        $selectedKelas = $request->get('kelas_id');

        if (!$selectedTa) {
            $activeTa = \App\Models\TahunAjaran::query()->where('status', 'Aktif')->first() ?? \App\Models\TahunAjaran::query()->first();
            $selectedTa = $activeTa ? $activeTa->id : null;
        }
        if (!$selectedSemName) {
            $selectedSemName = 'Semester 1 (Ganjil)';
        }

        $semester = null;
        if ($selectedTa && $selectedSemName) {
            $semester = \App\Models\Semester::query()
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('nama_semester', $selectedSemName)
                ->first();
        }
        $selectedSem = $semester ? $semester->id : null;

        $students = [];
        if ($selectedTa && $selectedSem && $selectedKelas) {
            $siswaIds = \App\Models\PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');
            
            $students = \App\Models\Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();

            // Load currently assigned extracurriculars for each student in this semester/year
            foreach ($students as $siswa) {
                $siswa->assigned_ekskuls = \App\Models\SiswaEkstrakurikuler::query()
                    ->where('siswa_id', $siswa->id)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->where('semester_id', $selectedSem)
                    ->pluck('ekstrakurikuler_id')
                    ->toArray();
            }
        }

        return view('pages.ekstrakurikuler.siswa', compact('kelas', 'tahunAjarans', 'ekskuls', 'selectedTa', 'selectedSemName', 'selectedSem', 'selectedKelas', 'students'));
    }

    public function ekskulSiswaSave(\Illuminate\Http\Request $request)
    {
        $selectedTa = $request->input('tahun_ajaran_id');
        $selectedSem = $request->input('semester_id');
        $ekskulAssignments = $request->input('ekskul', []); // map of student_id => array of ekskul_ids

        if (!$selectedTa || !$selectedSem) {
            alert()->error('Error', 'Tahun Ajaran dan Semester wajib diisi.');
            return redirect()->back();
        }

        foreach ($ekskulAssignments as $siswaId => $ekskulIds) {
            // Delete existing assignments for this student in this semester/year
            \App\Models\SiswaEkstrakurikuler::query()
                ->where('siswa_id', $siswaId)
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->delete();

            // Save new assignments
            if (is_array($ekskulIds)) {
                foreach ($ekskulIds as $ekskulId) {
                    if ($ekskulId) {
                        \App\Models\SiswaEkstrakurikuler::query()->create([
                            'siswa_id' => $siswaId,
                            'ekstrakurikuler_id' => $ekskulId,
                            'tahun_ajaran_id' => $selectedTa,
                            'semester_id' => $selectedSem,
                        ]);
                    }
                }
            }
        }

        alert()->success('Berhasil!', 'Data ekstrakurikuler siswa berhasil disimpan.')->html();
        return redirect()->back();
    }

    public function rekapEkskulPersonal(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $siswa = null;
        if ($user->roles === 'siswa') {
            $siswa = Siswa::where('user_id', $user->id)->first();
        } elseif ($user->roles === 'orang tua') {
            $orangTua = OrangTua::where('user_id', $user->id)->first();
            if ($orangTua) {
                $siswa = Siswa::where('orang_tua_id', $orangTua->id)->first();
            }
        }

        if (!$siswa) {
            alert()->error('Error', 'Data siswa tidak ditemukan.');
            return redirect()->route('dashboard');
        }

        $tahunAjarans = TahunAjaran::query()->get();

        $selectedTa = $request->get('tahun_ajaran_id');
        $selectedSemName = $request->get('semester_name');

        if (!$selectedTa) {
            $activeTa = TahunAjaran::query()->where('status', 'Aktif')->first() ?? TahunAjaran::query()->first();
            $selectedTa = $activeTa ? $activeTa->id : null;
        }
        if (!$selectedSemName) {
            $selectedSemName = 'Semester 1 (Ganjil)';
        }

        $semester = null;
        if ($selectedTa && $selectedSemName) {
            $semester = Semester::query()
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('nama_semester', $selectedSemName)
                ->first();
        }
        $selectedSem = $semester ? $semester->id : null;

        $ekskuls = [];
        if ($selectedTa && $selectedSem) {
            $ekskuls = SiswaEkstrakurikuler::query()
                ->with('ekstrakurikuler')
                ->where('siswa_id', $siswa->id)
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->get()
                ->pluck('ekstrakurikuler');
        }

        return view('pages.ekstrakurikuler.rekap_personal', compact('siswa', 'tahunAjarans', 'selectedTa', 'selectedSemName', 'selectedSem', 'ekskuls'));
    }

    public function rekapEkskul(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $isPersonal = $user && in_array($user->roles, ['siswa', 'orang tua']);
        $mySiswa = null;

        if ($isPersonal) {
            if ($user->roles === 'siswa') {
                $mySiswa = Siswa::where('user_id', $user->id)->first();
            } else {
                $orangTua = OrangTua::where('user_id', $user->id)->first();
                if ($orangTua) {
                    $mySiswa = Siswa::where('orang_tua_id', $orangTua->id)->first();
                }
            }
        }

        $tahunAjarans = TahunAjaran::query()->get();

        $selectedTa = $request->get('tahun_ajaran_id');
        $selectedSemName = $request->get('semester_name');

        if (!$selectedTa) {
            $activeTa = TahunAjaran::query()->where('status', 'Aktif')->first() ?? TahunAjaran::query()->first();
            $selectedTa = $activeTa ? $activeTa->id : null;
        }
        if (!$selectedSemName) {
            $selectedSemName = 'Semester 1 (Ganjil)';
        }

        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas');

        if ($isPersonal && $mySiswa) {
            $pk = PembagianKelas::where('siswa_id', $mySiswa->id)
                ->where('tahun_ajaran_id', $selectedTa)
                ->first();
            $selectedKelas = $pk ? $pk->kelas_id : $mySiswa->kelas_id;
            $kelas = Kelas::query()->where('id', $selectedKelas)->get();
        } elseif ($isWali) {
            $waliKelasIds = $this->getWaliKelasIds($user);
            if (!empty($waliKelasIds)) {
                $kelas = Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get();
            } else {
                $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
            }
            $selectedKelas = $request->get('kelas_id');
            if (!$selectedKelas || (!empty($waliKelasIds) && !in_array($selectedKelas, $waliKelasIds))) {
                $selectedKelas = $kelas->first()?->id;
            }
        } else {
            $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
            $selectedKelas = $request->get('kelas_id');
        }

        $semester = null;
        if ($selectedTa && $selectedSemName) {
            $semester = Semester::query()
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('nama_semester', $selectedSemName)
                ->first();
        }
        $selectedSem = $semester ? $semester->id : null;

        $students = [];
        if ($selectedTa && $selectedSem && $selectedKelas) {
            $siswaIdsQuery = PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa);

            if ($isPersonal && $mySiswa) {
                $siswaIdsQuery->where('siswa_id', $mySiswa->id);
            }

            $siswaIds = $siswaIdsQuery->pluck('siswa_id');
            $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();

            foreach ($studentsList as $siswa) {
                $ekskulNames = SiswaEkstrakurikuler::query()
                    ->with('ekstrakurikuler')
                    ->where('siswa_id', $siswa->id)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->where('semester_id', $selectedSem)
                    ->get()
                    ->pluck('ekstrakurikuler.nama_ekskul')
                    ->filter()
                    ->implode(', ');

                $siswa->ekskul_list = $ekskulNames ?: '-';
                $students[] = $siswa;
            }
        }

        return view('pages.ekstrakurikuler.rekap', compact(
            'kelas', 'tahunAjarans',
            'selectedTa', 'selectedSemName', 'selectedSem', 'selectedKelas',
            'students'
        ));
    }
}
