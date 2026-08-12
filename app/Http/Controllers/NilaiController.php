<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Pegawai;
use App\Models\MataPelajaran;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\PembagianKelas;
use App\Models\ProfilSekolah;
use App\Models\Kehadiran;
use App\Models\CatatanSiswa;
use App\Models\WaliKelas;
use App\Models\SiswaEkstrakurikuler;
use App\Models\OrangTua;
use App\Http\Requests\NilaiRequest;

use App\DataTables\NilaiDataTable;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NilaiExport;
use App\Imports\NilaiImport;
use App\Exports\NilaiTemplateExport;

class NilaiController extends Controller
{
    use \App\Traits\AuthorizeTransactionData;
    use \App\Traits\ResolvesStudentFromUser;

    public function index(NilaiDataTable $dataTable, Request $request)
    {
        $user = auth()->user();
        if (auth()->check() && in_array($user->roles, ['admin', 'kepala sekolah', 'wali kelas'])) {
            return redirect()->route('nilai.rekap-raport');
        }

        if (auth()->check() && in_array($user->roles, ['siswa', 'orang tua'])) {
            $siswa = $this->resolveStudentForCurrentUser();

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

            $selectedKelas = $siswa->kelas_id;
            $kelas = Kelas::query()->where('id', $selectedKelas)->get();

            $mapels = collect();
            if ($selectedTa && $selectedSem) {
                $activeMapels = MataPelajaran::query()
                    ->where('kelas_id', $selectedKelas)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->where('semester_id', $selectedSem)
                    ->get();

                $masterMapels = MataPelajaran::query()
                    ->whereNull('kelas_id')
                    ->get();

                $mapels = $activeMapels->concat($masterMapels)
                    ->unique('nama_mata_pelajaran')
                    ->sortBy('nama_mata_pelajaran')
                    ->values();
            }

            $selectedMapel = $request->get('mata_pelajaran_id');
            $gradeRecord = null;
            if ($selectedTa && $selectedSem && $selectedMapel) {
                $targetMapel = MataPelajaran::find($selectedMapel);
                if ($targetMapel) {
                    $gradeRecord = Nilai::query()
                        ->where('siswa_id', $siswa->id)
                        ->where(function($q) use ($targetMapel) {
                            $q->where('mata_pelajaran_id', $targetMapel->id)
                              ->orWhereHas('mataPelajaran', function($subQ) use ($targetMapel) {
                                  $subQ->where('nama_mata_pelajaran', $targetMapel->nama_mata_pelajaran);
                              });
                        })
                        ->where('semester_id', $selectedSem)
                        ->where('tahun_ajaran_id', $selectedTa)
                        ->first();
                }
            }

            return view('pages.nilai.rekap_personal', compact('siswa', 'tahunAjarans', 'kelas', 'mapels', 'selectedTa', 'selectedSemName', 'selectedSem', 'selectedKelas', 'selectedMapel', 'gradeRecord'));
        }

        return $dataTable->render('pages.nilai.index');
    }

    public function create()
    {
        $siswas = Siswa::query()->with('kelas')->orderBy('nama_siswa', 'asc')->get();
        $mapels = MataPelajaran::query()->get();
        $semesters = Semester::query()->with('tahunAjaran')->get();
        $tahunAjarans = TahunAjaran::query()->get();
        return view('pages.nilai.create', compact('siswas', 'mapels', 'semesters', 'tahunAjarans'));
    }

    public function store(NilaiRequest $request)
    {
        $validated = $request->validated();
        Nilai::query()->create($validated);

        alert()->html(
            'Berhasil!',
            'Data nilai berhasil ditambahkan.',
            'success'
        );

        return redirect()->route('nilai.index');
    }

    public function show(Nilai $nilai)
    {
        return redirect()->route('nilai.edit', $nilai);
    }

    public function edit(Nilai $nilai)
    {
        $siswas = Siswa::query()->with('kelas')->orderBy('nama_siswa', 'asc')->get();
        $mapels = MataPelajaran::query()->get();
        $semesters = Semester::query()->with('tahunAjaran')->get();
        $tahunAjarans = TahunAjaran::query()->get();
        return view('pages.nilai.edit', compact('nilai', 'siswas', 'mapels', 'semesters', 'tahunAjarans'));
    }

    public function update(NilaiRequest $request, Nilai $nilai)
    {
        $validated = $request->validated();
        $nilai->update($validated);

        alert()->html(
            'Diperbarui!',
            'Data nilai berhasil diperbarui.',
            'success'
        );

        return redirect()->route('nilai.index');
    }

    public function destroy(Nilai $nilai)
    {
        $nilai->delete();

        alert()->html(
            'Dihapus!',
            'Data nilai berhasil dihapus.',
            'success'
        );

        return redirect()->route('nilai.index');
    }

    /**
     * Helper to resolve filters with smart fallbacks to active school year and semester.
     */
    private function resolveFilters(Request $request)
    {
        $selectedTa = $request->get('tahun_ajaran_id');
        $selectedSem = $request->get('semester_id');
        $selectedKelas = $request->get('kelas_id');
        $selectedMapel = $request->get('mata_pelajaran_id');

        if (!$selectedTa) {
            $activeTa = TahunAjaran::query()->where('status', 'Aktif')->first() ?? TahunAjaran::query()->first();
            $selectedTa = $activeTa ? $activeTa->id : null;
        }
        if (!$selectedSem && $selectedTa) {
            $activeSem = Semester::query()->where('tahun_ajaran_id', $selectedTa)->first();
            $selectedSem = $activeSem ? $activeSem->id : null;
        }

        return [$selectedTa, $selectedSem, $selectedKelas, $selectedMapel];
    }

    private function getGuruId($user)
    {
        if (!$user) return null;

        $guru = $user->pegawai?->guru ?? $user->guru;
        if (!$guru && $user->pegawai_id) {
            $guru = Guru::where('pegawai_id', $user->pegawai_id)->first();
        }
        if (!$guru) {
            $guru = Guru::whereHas('pegawai', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();
        }
        if (!$guru) {
            $pegawai = Pegawai::where('user_id', $user->id)->first();
            if (!$pegawai) {
                $nameBase = trim(explode(',', $user->name)[0]);
                $pegawai = Pegawai::where('nama_pegawai', 'like', '%' . $nameBase . '%')->first();
            }
            if ($pegawai) {
                $guru = Guru::where('pegawai_id', $pegawai->id)->first();
            }
        }
        if (!$guru) {
            $nameBase = trim(explode(',', $user->name)[0]);
            $guru = Guru::whereHas('pegawai', function($q) use ($nameBase) {
                $q->where('nama_pegawai', 'like', '%' . $nameBase . '%');
            })->first();
        }

        return $guru ? $guru->id : null;
    }

    /**
     * Helper to retrieve available Mata Pelajaran options for grading.
     * Restricts strictly to subjects taught by the teacher when $isGuru is true.
     */
    private function getMapelOptions($selectedTa, $selectedSem, $selectedKelas, $isGuru, $guruId = null)
    {
        $query = MataPelajaran::query();

        if ($isGuru && $guruId) {
            $query->where('guru_id', $guruId);
        }

        if ($selectedKelas) {
            $query->where('kelas_id', $selectedKelas);
        }

        if ($selectedTa) {
            $query->where(function($q) use ($selectedTa) {
                $q->where('tahun_ajaran_id', $selectedTa)->orWhereNull('tahun_ajaran_id');
            });
        }

        $results = $query->orderBy('nama_mata_pelajaran', 'asc')
            ->get()
            ->unique('nama_mata_pelajaran')
            ->values();

        if ($isGuru && $guruId && $results->isEmpty()) {
            $results = MataPelajaran::query()
                ->where('guru_id', $guruId)
                ->orderBy('nama_mata_pelajaran', 'asc')
                ->get()
                ->unique('nama_mata_pelajaran')
                ->values();
        }

        return $results;
    }
    private function getWaliKelasIds($user): array
    {
        if (!$user) return [];

        $guru = $user->pegawai?->guru ?? $user->guru;
        if (!$guru && $user->pegawai_id) {
            $guru = Guru::where('pegawai_id', $user->pegawai_id)->first();
        }
        if (!$guru) {
            $guru = Guru::whereHas('pegawai', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();
        }
        if (!$guru) {
            $pegawai = Pegawai::where('user_id', $user->id)->first();
            if (!$pegawai) {
                $nameBase = trim(explode(',', $user->name)[0]);
                $pegawai = Pegawai::where('nama_pegawai', 'like', '%' . $nameBase . '%')->first();
            }
            if ($pegawai) {
                $guru = Guru::where('pegawai_id', $pegawai->id)->first();
            }
        }

        if ($guru) {
            $ids = WaliKelas::where('guru_id', $guru->id)->pluck('kelas_id')->toArray();
            if (!empty($ids)) {
                return array_values(array_unique(array_filter($ids)));
            }
        }

        $nameBase = trim(explode(',', $user->name)[0]);
        $matchingWali = WaliKelas::whereHas('guru.pegawai', function($q) use ($nameBase) {
            $q->where('nama_pegawai', 'like', '%' . $nameBase . '%');
        })->pluck('kelas_id')->toArray();

        if (!empty($matchingWali)) {
            return array_values(array_unique(array_filter($matchingWali)));
        }

        $matchingGuruIds = Guru::whereHas('pegawai', function($q) use ($nameBase) {
            $q->where('nama_pegawai', 'like', '%' . $nameBase . '%');
        })->pluck('id')->toArray();

        if (!empty($matchingGuruIds)) {
            $ids = WaliKelas::whereIn('guru_id', $matchingGuruIds)->pluck('kelas_id')->toArray();
            if (!empty($ids)) {
                return array_values(array_unique(array_filter($ids)));
            }
        }

        return [];
    }

    // ----------------------------------------------------
    // BULK GRADING: NILAI HARIAN
    // ----------------------------------------------------
    public function harian(Request $request)
    {
        $user = auth()->user();
        $guruId = $this->getGuruId($user);
        $isAdminOrKepsek = $user && in_array($user->roles, ['admin', 'kepala sekolah']);
        $isGuru = !$isAdminOrKepsek && !empty($guruId);
        
        list($selectedTa, $selectedSem, $selectedKelas, $selectedMapel) = $this->resolveFilters($request);

        $mapels = $this->getMapelOptions($selectedTa, $selectedSem, $selectedKelas, $isGuru, $guruId);

        $waliKelasIds = $this->getWaliKelasIds($user);
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas' || !empty($waliKelasIds));

        if ($isWali) {
            $kelas = !empty($waliKelasIds)
                ? Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } elseif ($isGuru) {
            $kelasIds = MataPelajaran::query()->where('guru_id', $guruId)->whereNotNull('kelas_id')->pluck('kelas_id');
            $kelas = !empty($kelasIds)
                ? Kelas::query()->whereIn('id', $kelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } else {
            $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
        }

        $tahunAjarans = TahunAjaran::query()->get();
        $semesters = $selectedTa
            ? Semester::query()->where('tahun_ajaran_id', $selectedTa)->get()
            : Semester::query()->get();

        $students = [];
        if ($selectedTa && $selectedSem && $selectedKelas && $selectedMapel) {
            $siswaIds = PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');
            
            $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();

            foreach ($studentsList as $siswa) {
                $nilaiRecord = Nilai::query()->where('siswa_id', $siswa->id)
                    ->where('mata_pelajaran_id', $selectedMapel)
                    ->where('semester_id', $selectedSem)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->first();
                
                $siswa->nilai_record = $nilaiRecord;
                $students[] = $siswa;
            }
        }

        return view('pages.nilai.harian', compact('mapels', 'kelas', 'semesters', 'tahunAjarans', 'selectedTa', 'selectedSem', 'selectedKelas', 'selectedMapel', 'students'));
    }

    public function harianSave(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'semester_id' => 'required|exists:semesters,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'nilai' => 'required|array',
        ]);

        $taId = $request->tahun_ajaran_id;
        $semId = $request->semester_id;
        $mapelId = $request->mata_pelajaran_id;
        $gradesInput = $request->nilai;

        foreach ($gradesInput as $siswaId => $data) {
            $lms = [];
            for ($lm = 1; $lm <= 5; $lm++) {
                $tps = [];
                for ($tp = 1; $tp <= 4; $tp++) {
                    $val = $data["lm{$lm}_tp{$tp}"];
                    if ($val !== '' && $val !== null) {
                        $tps[] = floatval($val);
                    }
                }
                $lms[$lm] = count($tps) > 0 ? array_sum($tps) / count($tps) : null;
            }

            $nilaiHarian = null;
            if ($lms[1] !== null && $lms[2] !== null && $lms[3] !== null && $lms[4] !== null && $lms[5] !== null) {
                $nilaiHarian = ($lms[1] + $lms[2] + $lms[3] + $lms[4] + $lms[5]) / 5;
            }

            $predikat = $nilaiHarian !== null ? Nilai::hitungPredikat($nilaiHarian) : null;

            Nilai::query()->updateOrCreate(
                [
                    'siswa_id' => $siswaId,
                    'mata_pelajaran_id' => $mapelId,
                    'semester_id' => $semId,
                    'tahun_ajaran_id' => $taId,
                ],
                [
                    'lm1_tp1' => $data['lm1_tp1'] !== '' ? $data['lm1_tp1'] : null,
                    'lm1_tp2' => $data['lm1_tp2'] !== '' ? $data['lm1_tp2'] : null,
                    'lm1_tp3' => $data['lm1_tp3'] !== '' ? $data['lm1_tp3'] : null,
                    'lm1_tp4' => $data['lm1_tp4'] !== '' ? $data['lm1_tp4'] : null,
                    'lm1' => $lms[1],

                    'lm2_tp1' => $data['lm2_tp1'] !== '' ? $data['lm2_tp1'] : null,
                    'lm2_tp2' => $data['lm2_tp2'] !== '' ? $data['lm2_tp2'] : null,
                    'lm2_tp3' => $data['lm2_tp3'] !== '' ? $data['lm2_tp3'] : null,
                    'lm2_tp4' => $data['lm2_tp4'] !== '' ? $data['lm2_tp4'] : null,
                    'lm2' => $lms[2],

                    'lm3_tp1' => $data['lm3_tp1'] !== '' ? $data['lm3_tp1'] : null,
                    'lm3_tp2' => $data['lm3_tp2'] !== '' ? $data['lm3_tp2'] : null,
                    'lm3_tp3' => $data['lm3_tp3'] !== '' ? $data['lm3_tp3'] : null,
                    'lm3_tp4' => $data['lm3_tp4'] !== '' ? $data['lm3_tp4'] : null,
                    'lm3' => $lms[3],

                    'lm4_tp1' => $data['lm4_tp1'] !== '' ? $data['lm4_tp1'] : null,
                    'lm4_tp2' => $data['lm4_tp2'] !== '' ? $data['lm4_tp2'] : null,
                    'lm4_tp3' => $data['lm4_tp3'] !== '' ? $data['lm4_tp3'] : null,
                    'lm4_tp4' => $data['lm4_tp4'] !== '' ? $data['lm4_tp4'] : null,
                    'lm4' => $lms[4],

                    'lm5_tp1' => $data['lm5_tp1'] !== '' ? $data['lm5_tp1'] : null,
                    'lm5_tp2' => $data['lm5_tp2'] !== '' ? $data['lm5_tp2'] : null,
                    'lm5_tp3' => $data['lm5_tp3'] !== '' ? $data['lm5_tp3'] : null,
                    'lm5_tp4' => $data['lm5_tp4'] !== '' ? $data['lm5_tp4'] : null,
                    'lm5' => $lms[5],

                    'nilai_harian' => $nilaiHarian,
                    'nilai' => $nilaiHarian, // fallback main column
                    'predikat' => $predikat,
                ]
            );
        }

        alert()->html('Berhasil!', 'Data Nilai Harian berhasil disimpan.', 'success');
        return back();
    }

    // ----------------------------------------------------
    // BULK GRADING: NILAI MID (UTS)
    // ----------------------------------------------------
    public function mid(Request $request)
    {
        $user = auth()->user();
        $guruId = $this->getGuruId($user);
        $isAdminOrKepsek = $user && in_array($user->roles, ['admin', 'kepala sekolah']);
        $isGuru = !$isAdminOrKepsek && !empty($guruId);
        
        list($selectedTa, $selectedSem, $selectedKelas, $selectedMapel) = $this->resolveFilters($request);

        $mapels = $this->getMapelOptions($selectedTa, $selectedSem, $selectedKelas, $isGuru, $guruId);

        $waliKelasIds = $this->getWaliKelasIds($user);
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas' || !empty($waliKelasIds));

        if ($isWali) {
            $kelas = !empty($waliKelasIds)
                ? Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } elseif ($isGuru) {
            $kelasIds = MataPelajaran::query()->where('guru_id', $guruId)->whereNotNull('kelas_id')->pluck('kelas_id');
            $kelas = !empty($kelasIds)
                ? Kelas::query()->whereIn('id', $kelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } else {
            $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
        }

        $tahunAjarans = TahunAjaran::query()->get();
        $semesters = $selectedTa
            ? Semester::query()->where('tahun_ajaran_id', $selectedTa)->get()
            : Semester::query()->get();

        $students = [];
        if ($selectedTa && $selectedSem && $selectedKelas && $selectedMapel) {
            $siswaIds = PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');
            
            $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();

            foreach ($studentsList as $siswa) {
                $nilaiRecord = Nilai::query()->where('siswa_id', $siswa->id)
                    ->where('mata_pelajaran_id', $selectedMapel)
                    ->where('semester_id', $selectedSem)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->first();
                
                $siswa->nilai_record = $nilaiRecord;
                $students[] = $siswa;
            }
        }

        return view('pages.nilai.mid', compact('mapels', 'kelas', 'semesters', 'tahunAjarans', 'selectedTa', 'selectedSem', 'selectedKelas', 'selectedMapel', 'students'));
    }

    public function midSave(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'semester_id' => 'required|exists:semesters,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'nilai' => 'required|array',
        ]);

        $taId = $request->tahun_ajaran_id;
        $semId = $request->semester_id;
        $mapelId = $request->mata_pelajaran_id;
        $gradesInput = $request->nilai;

        foreach ($gradesInput as $siswaId => $data) {
            $nilaiMid = $data['nilai_mid'] !== '' ? floatval($data['nilai_mid']) : null;
            $nilaiMidPlus = isset($data['nilai_mid_plus']) && $data['nilai_mid_plus'] !== '' ? floatval($data['nilai_mid_plus']) : null;

            Nilai::query()->updateOrCreate(
                [
                    'siswa_id' => $siswaId,
                    'mata_pelajaran_id' => $mapelId,
                    'semester_id' => $semId,
                    'tahun_ajaran_id' => $taId,
                ],
                [
                    'nilai_mid' => $nilaiMid,
                    'nilai_mid_plus' => $nilaiMidPlus,
                ]
            );
        }

        alert()->html('Berhasil!', 'Data Nilai MID berhasil disimpan.', 'success');
        return back();
    }

    // ----------------------------------------------------
    // BULK GRADING: NILAI PAS (UAS)
    // ----------------------------------------------------
    public function pas(Request $request)
    {
        $user = auth()->user();
        $guruId = $this->getGuruId($user);
        $isAdminOrKepsek = $user && in_array($user->roles, ['admin', 'kepala sekolah']);
        $isGuru = !$isAdminOrKepsek && !empty($guruId);
        
        list($selectedTa, $selectedSem, $selectedKelas, $selectedMapel) = $this->resolveFilters($request);

        $mapels = $this->getMapelOptions($selectedTa, $selectedSem, $selectedKelas, $isGuru, $guruId);

        $waliKelasIds = $this->getWaliKelasIds($user);
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas' || !empty($waliKelasIds));

        if ($isWali) {
            $kelas = !empty($waliKelasIds)
                ? Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } elseif ($isGuru) {
            $kelasIds = MataPelajaran::query()->where('guru_id', $guruId)->whereNotNull('kelas_id')->pluck('kelas_id');
            $kelas = !empty($kelasIds)
                ? Kelas::query()->whereIn('id', $kelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } else {
            $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
        }

        $tahunAjarans = TahunAjaran::query()->get();
        $semesters = $selectedTa
            ? Semester::query()->where('tahun_ajaran_id', $selectedTa)->get()
            : Semester::query()->get();

        $students = [];
        if ($selectedTa && $selectedSem && $selectedKelas && $selectedMapel) {
            $siswaIds = PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');
            
            $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();

            foreach ($studentsList as $siswa) {
                $nilaiRecord = Nilai::query()->where('siswa_id', $siswa->id)
                    ->where('mata_pelajaran_id', $selectedMapel)
                    ->where('semester_id', $selectedSem)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->first();
                
                $siswa->nilai_record = $nilaiRecord;
                $students[] = $siswa;
            }
        }

        return view('pages.nilai.pas', compact('mapels', 'kelas', 'semesters', 'tahunAjarans', 'selectedTa', 'selectedSem', 'selectedKelas', 'selectedMapel', 'students'));
    }

    public function pasSave(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'semester_id' => 'required|exists:semesters,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'nilai' => 'required|array',
        ]);

        $taId = $request->tahun_ajaran_id;
        $semId = $request->semester_id;
        $mapelId = $request->mata_pelajaran_id;
        $gradesInput = $request->nilai;

        foreach ($gradesInput as $siswaId => $data) {
            $nilaiPas = $data['nilai_pas'] !== '' ? floatval($data['nilai_pas']) : null;
            $nilaiPasPlus = isset($data['nilai_pas_plus']) && $data['nilai_pas_plus'] !== '' ? floatval($data['nilai_pas_plus']) : null;

            Nilai::query()->updateOrCreate(
                [
                    'siswa_id' => $siswaId,
                    'mata_pelajaran_id' => $mapelId,
                    'semester_id' => $semId,
                    'tahun_ajaran_id' => $taId,
                ],
                [
                    'nilai_pas' => $nilaiPas,
                    'nilai_pas_plus' => $nilaiPasPlus,
                ]
            );
        }

        alert()->html('Berhasil!', 'Data Nilai PAS berhasil disimpan.', 'success');
        return back();
    }

    // ----------------------------------------------------
    // BULK GRADING: NILAI RAPORT -> Nilai Rata2 = (Harian + MID+ + PAS+) / 3
    // ----------------------------------------------------
    public function raportInput(Request $request)
    {
        $user = auth()->user();
        $guruId = $this->getGuruId($user);
        $isAdminOrKepsek = $user && in_array($user->roles, ['admin', 'kepala sekolah']);
        $isGuru = !$isAdminOrKepsek && !empty($guruId);

        list($selectedTa, $selectedSem, $selectedKelas, $selectedMapel) = $this->resolveFilters($request);

        $mapels = $this->getMapelOptions($selectedTa, $selectedSem, $selectedKelas, $isGuru, $guruId);

        $waliKelasIds = $this->getWaliKelasIds($user);
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas' || !empty($waliKelasIds));

        if ($isWali) {
            $kelas = !empty($waliKelasIds)
                ? Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } elseif ($isGuru) {
            $kelasIds = MataPelajaran::query()->where('guru_id', $guruId)->whereNotNull('kelas_id')->pluck('kelas_id');
            $kelas = !empty($kelasIds)
                ? Kelas::query()->whereIn('id', $kelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } else {
            $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
        }

        $tahunAjarans = TahunAjaran::query()->get();
        $semesters = $selectedTa
            ? Semester::query()->where('tahun_ajaran_id', $selectedTa)->get()
            : Semester::query()->get();

        // Master TP (Tujuan Pembelajaran) diambil dari Mata Pelajaran terpilih & Master Mapel
        $tpOptimalOptions    = [];
        $tpPeningkatanOptions = [];
        if ($selectedMapel) {
            $mapelTerpilih = MataPelajaran::query()->find($selectedMapel);
            if ($mapelTerpilih) {
                $rawOptimal     = $mapelTerpilih->tp_optimal;
                $rawPeningkatan = $mapelTerpilih->tp_peningkatan;

                // Selalu cek ke master mapel (kelas_id null / tahun_ajaran_id null) jika master mapel punya TP yang diinput admin
                $masterMapel = MataPelajaran::query()
                    ->where('nama_mata_pelajaran', $mapelTerpilih->nama_mata_pelajaran)
                    ->where(function($q) {
                        $q->whereNull('kelas_id')->orWhereNull('tahun_ajaran_id');
                    })
                    ->first();

                if ($masterMapel) {
                    if (!empty($masterMapel->tp_optimal)) {
                        $rawOptimal = $masterMapel->tp_optimal;
                    }
                    if (!empty($masterMapel->tp_peningkatan)) {
                        $rawPeningkatan = $masterMapel->tp_peningkatan;
                    }
                }

                // Parse: support array (JSON cast baru) dan string lama
                $parseTp = function($raw): array {
                    if (is_array($raw)) return array_values(array_filter($raw));
                    if (is_string($raw) && $raw !== '') {
                        $dec = json_decode($raw, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) {
                            return array_values(array_filter($dec));
                        }
                        // string lama: pisah per baris
                        return array_values(array_filter(preg_split('/\r\n|\r|\n/', $raw)));
                    }
                    return [];
                };

                $tpOptimalOptions    = $parseTp($rawOptimal);
                $tpPeningkatanOptions = $parseTp($rawPeningkatan);
            }
        }

        $students = [];
        if ($selectedTa && $selectedSem && $selectedKelas && $selectedMapel) {
            $siswaIds = PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');

            $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();

            foreach ($studentsList as $siswa) {
                $nilaiRecord = Nilai::query()->where('siswa_id', $siswa->id)
                    ->where('mata_pelajaran_id', $selectedMapel)
                    ->where('semester_id', $selectedSem)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->first();

                $rata2 = null;
                if ($nilaiRecord && $nilaiRecord->nilai_harian !== null && $nilaiRecord->nilai_mid_plus !== null && $nilaiRecord->nilai_pas_plus !== null) {
                    $rata2 = round(($nilaiRecord->nilai_harian + $nilaiRecord->nilai_mid_plus + $nilaiRecord->nilai_pas_plus) / 3, 1);
                }

                $siswa->nilai_record = $nilaiRecord;
                $siswa->nilai_rata2_calc = $rata2;
                $students[] = $siswa;
            }
        }

        return view('pages.nilai.raport_input', compact('mapels', 'kelas', 'semesters', 'tahunAjarans', 'selectedTa', 'selectedSem', 'selectedKelas', 'selectedMapel', 'students', 'tpOptimalOptions', 'tpPeningkatanOptions'));
    }

    public function raportInputSave(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'semester_id' => 'required|exists:semesters,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'nilai' => 'required|array',
        ]);

        $taId = $request->tahun_ajaran_id;
        $semId = $request->semester_id;
        $mapelId = $request->mata_pelajaran_id;
        $gradesInput = $request->nilai;

        foreach ($gradesInput as $siswaId => $data) {
            $nilaiRaport = isset($data['nilai_raport']) && $data['nilai_raport'] !== ''
                ? round(floatval($data['nilai_raport'])) : null;
            $predikat = $nilaiRaport !== null ? Nilai::hitungPredikat($nilaiRaport) : null;

            // TP dipilih sebagai array checkbox (bisa lebih dari 1)
            $tpOptimal     = isset($data['tp_optimal']) && is_array($data['tp_optimal'])
                ? array_values(array_filter($data['tp_optimal']))
                : null;
            $tpPeningkatan = isset($data['tp_perlu_peningkatan']) && is_array($data['tp_perlu_peningkatan'])
                ? array_values(array_filter($data['tp_perlu_peningkatan']))
                : null;

            $existing = Nilai::query()->where([
                'siswa_id'         => $siswaId,
                'mata_pelajaran_id'=> $mapelId,
                'semester_id'      => $semId,
                'tahun_ajaran_id'  => $taId,
            ])->first();

            $rata2 = null;
            if ($existing && $existing->nilai_harian !== null && $existing->nilai_mid_plus !== null && $existing->nilai_pas_plus !== null) {
                $rata2 = round(($existing->nilai_harian + $existing->nilai_mid_plus + $existing->nilai_pas_plus) / 3, 1);
            }

            Nilai::query()->updateOrCreate(
                [
                    'siswa_id'          => $siswaId,
                    'mata_pelajaran_id' => $mapelId,
                    'semester_id'       => $semId,
                    'tahun_ajaran_id'   => $taId,
                ],
                [
                    'nilai_rata2'          => $rata2,
                    'nilai_raport'         => $nilaiRaport,
                    'tp_optimal'           => !empty($tpOptimal) ? $tpOptimal : null,
                    'tp_perlu_peningkatan' => !empty($tpPeningkatan) ? $tpPeningkatan : null,
                    'nilai'                => $nilaiRaport,
                    'predikat'             => $predikat,
                ]
            );
        }

        alert()->html('Berhasil!', 'Data Nilai Raport berhasil disimpan.', 'success');
        return back();
    }

    // ----------------------------------------------------
    // REKAP: REKAP NILAI PER MATA PELAJARAN
    // ----------------------------------------------------
    public function rekapMapel(Request $request)
    {
        $user = auth()->user();
        $isGuru = $user->roles === 'guru';
        
        list($selectedTa, $selectedSem, $selectedKelas, $selectedMapel) = $this->resolveFilters($request);

        $selectedSemName = $request->get('semester_name');
        if (!$selectedSemName) {
            $selectedSemName = 'Semester 1 (Ganjil)';
        }

        if ($selectedTa && $selectedSemName) {
            $semModel = Semester::query()
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('nama_semester', $selectedSemName)
                ->first();
            if ($semModel) {
                $selectedSem = $semModel->id;
            }
        }

        $user = auth()->user();
        $guruId = $this->getGuruId($user);
        $isAdminOrKepsek = $user && in_array($user->roles, ['admin', 'kepala sekolah']);
        $isGuru = !$isAdminOrKepsek && !empty($guruId);

        list($selectedTa, $selectedSem, $selectedKelas, $selectedMapel) = $this->resolveFilters($request);

        $mapels = $this->getMapelOptions($selectedTa, $selectedSem, $selectedKelas, $isGuru, $guruId);

        $waliKelasIds = $this->getWaliKelasIds($user);
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas' || !empty($waliKelasIds));

        if ($isWali) {
            $kelas = !empty($waliKelasIds)
                ? Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } elseif ($isGuru) {
            $kelasIds = MataPelajaran::query()->where('guru_id', $guruId)->whereNotNull('kelas_id')->pluck('kelas_id');
            $kelas = !empty($kelasIds)
                ? Kelas::query()->whereIn('id', $kelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } else {
            $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
        }

        $tahunAjarans = TahunAjaran::query()->get();
        $semesters = $selectedTa
            ? Semester::query()->where('tahun_ajaran_id', $selectedTa)->get()
            : Semester::query()->get();

        $students = [];
        $selectedMapelModel = null;
        if ($selectedTa && $selectedSem && $selectedKelas && $selectedMapel) {
            $selectedMapelModel = MataPelajaran::query()->find($selectedMapel);
            $siswaIds = PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');
            
            $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();

            foreach ($studentsList as $siswa) {
                $nilaiRecord = Nilai::query()->where('siswa_id', $siswa->id)
                    ->where('mata_pelajaran_id', $selectedMapel)
                    ->where('semester_id', $selectedSem)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->first();

                $rata2 = null;
                if ($nilaiRecord && $nilaiRecord->nilai_harian !== null && $nilaiRecord->nilai_mid_plus !== null && $nilaiRecord->nilai_pas_plus !== null) {
                    $rata2 = round(($nilaiRecord->nilai_harian + $nilaiRecord->nilai_mid_plus + $nilaiRecord->nilai_pas_plus) / 3, 1);
                }

                $siswa->nilai_record = $nilaiRecord;
                $siswa->nilai_rata2_calc = $rata2;
                $students[] = $siswa;
            }
        }

        return view('pages.nilai.rekap_mapel', compact('mapels', 'kelas', 'semesters', 'tahunAjarans', 'selectedTa', 'selectedSem', 'selectedSemName', 'selectedKelas', 'selectedMapel', 'students', 'selectedMapelModel'));
    }

    public function rekapRaport(Request $request)
    {
        $user = auth()->user();
        $guruId = $this->getGuruId($user);
        $isAdminOrKepsek = $user && in_array($user->roles, ['admin', 'kepala sekolah']);
        $waliKelasIds = $this->getWaliKelasIds($user);
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas' || !empty($waliKelasIds));
        $isGuru = !$isAdminOrKepsek && !$isWali && !empty($guruId);

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

        if ($isWali) {
            $kelas = !empty($waliKelasIds)
                ? Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
            $selectedKelas = $request->get('kelas_id');
            if (!$selectedKelas || !in_array($selectedKelas, $waliKelasIds)) {
                $selectedKelas = $kelas->first()?->id;
            }
        } elseif ($isGuru) {
            $kelasIds = MataPelajaran::query()->where('guru_id', $guruId)->whereNotNull('kelas_id')->pluck('kelas_id');
            $kelas = !empty($kelasIds)
                ? Kelas::query()->whereIn('id', $kelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
            $selectedKelas = $request->get('kelas_id');
            if (!$selectedKelas || !$kelas->contains('id', (int) $selectedKelas)) {
                $selectedKelas = $kelas->first()?->id;
            }
        } else {
            $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
            $selectedKelas = $request->get('kelas_id');
        }

        $selectedSiswa = $request->get('siswa_id');

        $semester = null;
        if ($selectedTa && $selectedSemName) {
            $semester = Semester::query()
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('nama_semester', $selectedSemName)
                ->first();
        }
        $selectedSem = $semester ? $semester->id : null;

        $students = [];
        $classMapels = [];
        $siswaOptions = [];

        if ($selectedTa && $selectedKelas) {
            $siswaIds = PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');

            $siswaOptions = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();
        }

        if ($selectedTa && $selectedSem && $selectedKelas) {
            $siswaIds = PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');

            $mapelIdsFromGrades = Nilai::query()->whereIn('siswa_id', $siswaIds)
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->pluck('mata_pelajaran_id');

            $classMapels = MataPelajaran::query()
                ->where(function($q) use ($selectedKelas, $selectedTa, $selectedSem, $mapelIdsFromGrades) {
                    $q->where(function($sub) use ($selectedKelas, $selectedTa, $selectedSem) {
                        $sub->where('kelas_id', $selectedKelas)
                            ->where('tahun_ajaran_id', $selectedTa)
                            ->where('semester_id', $selectedSem);
                    })
                    ->orWhereIn('id', $mapelIdsFromGrades);
                })
                ->orderBy('nama_mata_pelajaran', 'asc')
                ->get()
                ->unique('nama_mata_pelajaran');

            $studentsList = $siswaOptions;
            if ($selectedSiswa) {
                $studentsList = $studentsList->where('id', $selectedSiswa);
            }

            foreach ($studentsList as $siswa) {
                $mapelGrades = [];
                $total = 0;
                $count = 0;
                
                foreach ($classMapels as $mp) {
                    $matchingMapelIds = MataPelajaran::where('nama_mata_pelajaran', $mp->nama_mata_pelajaran)->pluck('id')->toArray();
                    $nilaiRecord = Nilai::query()->where('siswa_id', $siswa->id)
                        ->whereIn('mata_pelajaran_id', $matchingMapelIds)
                        ->where('semester_id', $selectedSem)
                        ->where('tahun_ajaran_id', $selectedTa)
                        ->first();
                    
                    $grade = null;
                    if ($nilaiRecord) {
                        $grade = $nilaiRecord->nilai_raport !== null ? floatval($nilaiRecord->nilai_raport) : ($nilaiRecord->nilai !== null ? floatval($nilaiRecord->nilai) : null);
                    }
                    
                    $mapelGrades[$mp->id] = $grade;
                    if ($grade !== null) {
                        $total += floatval($grade);
                        $count++;
                    }
                }
                
                $siswa->grades = $mapelGrades;
                $siswa->average = $count > 0 ? $total / $count : null;
                $siswa->predikat = $siswa->average !== null ? Nilai::hitungPredikat($siswa->average) : '-';
                $students[] = $siswa;
            }
        }

        return view('pages.nilai.rekap_raport', compact('kelas', 'tahunAjarans', 'selectedTa', 'selectedSemName', 'selectedSem', 'selectedKelas', 'selectedSiswa', 'siswaOptions', 'students', 'classMapels'));
    }

    public function cetakRaportList(Request $request)
    {
        $user = auth()->user();
        $waliKelasIds = $this->getWaliKelasIds($user);
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas' || !empty($waliKelasIds));

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

        if ($isWali) {
            $kelas = !empty($waliKelasIds)
                ? Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
            $selectedKelas = $request->get('kelas_id');
            if (!$selectedKelas || !in_array($selectedKelas, $waliKelasIds)) {
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
            $siswaIds = PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');
            
            $students = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();
        }

        return view('pages.nilai.cetak_raport_list', compact('kelas', 'tahunAjarans', 'selectedTa', 'selectedSemName', 'selectedSem', 'selectedKelas', 'students'));
    }

    public function cetakRaportPrint($siswa_id, $tahun_ajaran_id, $semester_id)
    {
        $user = auth()->user();
        if ($user && $user->roles === 'wali kelas') {
            $guru = $user->pegawai?->guru ?? $user->guru;
            $guruId = $guru ? $guru->id : 0;
            $waliRecord = WaliKelas::where('guru_id', $guruId)->where('tahun_ajaran_id', $tahun_ajaran_id)->first() ?? WaliKelas::where('guru_id', $guruId)->first();
            if ($waliRecord) {
                $pembagian = PembagianKelas::where('siswa_id', $siswa_id)->where('tahun_ajaran_id', $tahun_ajaran_id)->first();
                $siswaKelasId = $pembagian ? $pembagian->kelas_id : Siswa::find($siswa_id)?->kelas_id;
                if ($siswaKelasId != $waliRecord->kelas_id) {
                    alert()->error('Akses Ditolak', 'Anda hanya dapat mencetak raport siswa dari kelas perwalian Anda.');
                    return redirect()->route('nilai.cetak-raport');
                }
            }
        }

        $siswa = Siswa::query()->findOrFail($siswa_id);
        $tahunAjaran = TahunAjaran::query()->findOrFail($tahun_ajaran_id);
        $semester = Semester::query()->findOrFail($semester_id);
        
        $pembagianKelas = PembagianKelas::query()
            ->where('siswa_id', $siswa_id)
            ->where('tahun_ajaran_id', $tahun_ajaran_id)
            ->first();
        $kelasId = $pembagianKelas ? $pembagianKelas->kelas_id : $siswa->kelas_id;
        $kelasModel = Kelas::query()->find($kelasId);

        $school = \App\Models\ProfilSekolah::query()->first();

        $classMapels = MataPelajaran::query()->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $tahun_ajaran_id)
            ->where('semester_id', $semester_id)
            ->orderBy('nama_mata_pelajaran', 'asc')
            ->get();

        $grades = [];
        foreach ($classMapels as $mp) {
            $nilaiRecord = Nilai::query()->where('siswa_id', $siswa_id)
                ->where('mata_pelajaran_id', $mp->id)
                ->where('semester_id', $semester_id)
                ->where('tahun_ajaran_id', $tahun_ajaran_id)
                ->first();
            $grades[$mp->id] = $nilaiRecord;
        }

        $attendance = [
            'Sakit' => \App\Models\Kehadiran::query()->where('siswa_id', $siswa_id)
                ->whereHas('jenisKehadiran', function($q) { $q->where('nama_kehadiran', 'Sakit'); })
                ->count(),
            'Izin' => \App\Models\Kehadiran::query()->where('siswa_id', $siswa_id)
                ->whereHas('jenisKehadiran', function($q) { $q->where('nama_kehadiran', 'Izin'); })
                ->count(),
            'Alpa' => \App\Models\Kehadiran::query()->where('siswa_id', $siswa_id)
                ->whereHas('jenisKehadiran', function($q) { $q->where('nama_kehadiran', 'Alpa')->orWhere('nama_kehadiran', 'Tanpa Keterangan'); })
                ->count(),
        ];

        $catatan = \App\Models\CatatanSiswa::query()->where('siswa_id', $siswa_id)
            ->where('semester_id', $semester_id)
            ->where('tahun_ajaran_id', $tahun_ajaran_id)
            ->first();

        $waliKelas = WaliKelas::query()
            ->with(['guru.pegawai'])
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $tahun_ajaran_id)
            ->first();

        if (!$waliKelas) {
            $waliKelas = WaliKelas::query()
                ->with(['guru.pegawai'])
                ->where('kelas_id', $kelasId)
                ->first();
        }

        if (!$waliKelas) {
            $guru = \App\Models\Guru::with('pegawai')->first();
            if ($guru) {
                $waliKelas = new WaliKelas();
                $waliKelas->guru = $guru;
            }
        }

        $ortuNama = $siswa->orangTua ? ($siswa->orangTua->nama_ayah ?: ($siswa->orangTua->nama_ibu ?: '..........................................')) : '..........................................';

        $kepsek = \App\Models\Pegawai::where('jabatan', 'Kepala Sekolah')->first();
        $kepsekNama = $school && $school->nama_kepala_sekolah ? $school->nama_kepala_sekolah : ($kepsek ? $kepsek->nama_pegawai : 'Yusal, S.Pd.');
        $kepsekNip = $school && $school->nip_kepala_sekolah ? $school->nip_kepala_sekolah : ($kepsek ? $kepsek->nip : '196805121990031005');

        $ekskuls = \App\Models\SiswaEkstrakurikuler::query()
            ->with('ekstrakurikuler')
            ->where('siswa_id', $siswa_id)
            ->where('tahun_ajaran_id', $tahun_ajaran_id)
            ->where('semester_id', $semester_id)
            ->get()
            ->pluck('ekstrakurikuler');

        // Calculate ranking with high precision
        $siswaIdsInClass = PembagianKelas::query()
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $tahun_ajaran_id)
            ->pluck('siswa_id');

        $studentAverages = [];
        foreach ($siswaIdsInClass as $sId) {
            $gradesList = Nilai::query()
                ->where('siswa_id', $sId)
                ->where('tahun_ajaran_id', $tahun_ajaran_id)
                ->where('semester_id', $semester_id)
                ->whereIn('mata_pelajaran_id', $classMapels->pluck('id'))
                ->pluck('nilai_raport');
            
            $validGrades = $gradesList->filter(fn($val) => $val !== null && $val !== '')->map(fn($val) => (float)$val);
            $avg = $validGrades->count() > 0 ? $validGrades->sum() / $validGrades->count() : 0;
            $studentAverages[$sId] = $avg;
        }

        arsort($studentAverages);

        $rank = 1;
        $prevAvg = null;
        $actualRank = 1;
        $studentRanks = [];

        foreach ($studentAverages as $sId => $avg) {
            if ($prevAvg !== null && $avg < $prevAvg) {
                $actualRank = $rank;
            }
            $studentRanks[$sId] = $actualRank;
            $prevAvg = $avg;
            $rank++;
        }

        $ranking = $studentRanks[$siswa_id] ?? 1;
        $totalStudents = count($siswaIdsInClass) > 0 ? count($siswaIdsInClass) : count($studentAverages);

        $ekskulText = $ekskuls->map(fn($e) => $e->nama_ekskul)->implode(', ');
        if (empty($ekskulText)) {
            $ekskulText = '-';
        }

        return view('pages.nilai.cetak_raport_print', compact('siswa', 'tahunAjaran', 'semester', 'kelasModel', 'school', 'classMapels', 'grades', 'attendance', 'catatan', 'waliKelas', 'ekskuls', 'ortuNama', 'kepsekNama', 'kepsekNip', 'ranking', 'totalStudents', 'ekskulText'));
    }

    public function cetakRaportPersonal(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $siswa = null;
        if ($user->roles === 'siswa') {
            $siswa = Siswa::where('user_id', $user->id)->first();
        } elseif ($user->roles === 'orang tua') {
            $orangTua = \App\Models\OrangTua::where('user_id', $user->id)->first();
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

        $kelasId = $siswa->kelas_id;
        $kelasModel = Kelas::query()->find($kelasId);
        $school = \App\Models\ProfilSekolah::query()->first();

        $classMapels = [];
        $grades = [];
        $ekskuls = [];
        $attendance = [];
        $catatan = null;
        $waliKelas = null;
        $ortuNama = '';
        $kepsekNama = '';
        $kepsekNip = '';
        $ranking = 1;
        $totalStudents = 1;
        $ekskulText = '';

        if ($selectedTa && $selectedSem) {
            $classMapels = MataPelajaran::query()->where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->orderBy('nama_mata_pelajaran', 'asc')
                ->get();

            foreach ($classMapels as $mp) {
                $nilaiRecord = Nilai::query()->where('siswa_id', $siswa->id)
                    ->where('mata_pelajaran_id', $mp->id)
                    ->where('semester_id', $selectedSem)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->first();
                $grades[$mp->id] = $nilaiRecord;
            }

            $attendance = [
                'Sakit' => \App\Models\Kehadiran::query()->where('siswa_id', $siswa->id)
                    ->whereHas('jenisKehadiran', function($q) { $q->where('nama_kehadiran', 'Sakit'); })
                    ->count(),
                'Izin' => \App\Models\Kehadiran::query()->where('siswa_id', $siswa->id)
                    ->whereHas('jenisKehadiran', function($q) { $q->where('nama_kehadiran', 'Izin'); })
                    ->count(),
                'Alpa' => \App\Models\Kehadiran::query()->where('siswa_id', $siswa->id)
                    ->whereHas('jenisKehadiran', function($q) { $q->where('nama_kehadiran', 'Alpa')->orWhere('nama_kehadiran', 'Tanpa Keterangan'); })
                    ->count(),
            ];

            $catatan = \App\Models\CatatanSiswa::query()->where('siswa_id', $siswa->id)
                ->where('semester_id', $selectedSem)
                ->where('tahun_ajaran_id', $selectedTa)
                ->first();

            $waliKelas = WaliKelas::query()
                ->with(['guru.pegawai'])
                ->where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $selectedTa)
                ->first();

            if (!$waliKelas) {
                $waliKelas = WaliKelas::query()
                    ->with(['guru.pegawai'])
                    ->where('kelas_id', $kelasId)
                    ->first();
            }

            if (!$waliKelas) {
                $guru = \App\Models\Guru::with('pegawai')->first();
                if ($guru) {
                    $waliKelas = new WaliKelas();
                    $waliKelas->guru = $guru;
                }
            }

            $ortuNama = $siswa->orangTua ? ($siswa->orangTua->nama_ayah ?: ($siswa->orangTua->nama_ibu ?: '..........................................')) : '..........................................';

            $kepsek = \App\Models\Pegawai::where('jabatan', 'Kepala Sekolah')->first();
            $kepsekNama = $school && $school->nama_kepala_sekolah ? $school->nama_kepala_sekolah : ($kepsek ? $kepsek->nama_pegawai : 'Yusal, S.Pd.');
            $kepsekNip = $school && $school->nip_kepala_sekolah ? $school->nip_kepala_sekolah : ($kepsek ? $kepsek->nip : '196805121990031005');

            $ekskuls = \App\Models\SiswaEkstrakurikuler::query()
                ->with('ekstrakurikuler')
                ->where('siswa_id', $siswa->id)
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->get()
                ->pluck('ekstrakurikuler');

            // Calculate ranking with high precision
            $siswaIdsInClass = PembagianKelas::query()
                ->where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');

            $studentAverages = [];
            foreach ($siswaIdsInClass as $sId) {
                $gradesList = Nilai::query()
                    ->where('siswa_id', $sId)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->where('semester_id', $selectedSem)
                    ->whereIn('mata_pelajaran_id', $classMapels->pluck('id'))
                    ->pluck('nilai_raport');
                
                $validGrades = $gradesList->filter(fn($val) => $val !== null && $val !== '')->map(fn($val) => (float)$val);
                $avg = $validGrades->count() > 0 ? $validGrades->sum() / $validGrades->count() : 0;
                $studentAverages[$sId] = $avg;
            }

            arsort($studentAverages);

            $rank = 1;
            $prevAvg = null;
            $actualRank = 1;
            $studentRanks = [];

            foreach ($studentAverages as $sId => $avg) {
                if ($prevAvg !== null && $avg < $prevAvg) {
                    $actualRank = $rank;
                }
                $studentRanks[$sId] = $actualRank;
                $prevAvg = $avg;
                $rank++;
            }

            $ranking = $studentRanks[$siswa->id] ?? 1;
            $totalStudents = count($siswaIdsInClass) > 0 ? count($siswaIdsInClass) : count($studentAverages);

            $ekskulText = $ekskuls->map(fn($e) => $e->nama_ekskul)->implode(', ');
            if (empty($ekskulText)) {
                $ekskulText = '-';
            }
        }

        return view('pages.nilai.cetak_raport_personal', compact(
            'siswa', 'tahunAjarans', 'selectedTa', 'selectedSemName', 'selectedSem', 'kelasModel', 'school',
            'classMapels', 'grades', 'attendance', 'catatan', 'waliKelas', 'ekskuls', 'ortuNama', 'kepsekNama', 'kepsekNip', 'ranking', 'totalStudents', 'ekskulText'
        ));
    }

    public function rekapRaportExport(Request $request)
    {
        $selectedTa = $request->get('tahun_ajaran_id');
        $selectedSemName = $request->get('semester_name');
        $selectedKelas = $request->get('kelas_id');
        $selectedSiswa = $request->get('siswa_id');

        if (!$selectedTa || !$selectedSemName || !$selectedKelas) {
            alert()->warning('Peringatan', 'Silakan pilih Tahun Ajaran, Semester, dan Kelas terlebih dahulu.');
            return back();
        }

        $kelas = Kelas::find($selectedKelas);
        $semester = Semester::query()
            ->where('tahun_ajaran_id', $selectedTa)
            ->where('nama_semester', $selectedSemName)
            ->first();
        $selectedSem = $semester ? $semester->id : null;

        $siswaIds = PembagianKelas::query()->where('kelas_id', $selectedKelas)
            ->where('tahun_ajaran_id', $selectedTa)
            ->pluck('siswa_id');

        $mapelIdsFromGrades = Nilai::query()->whereIn('siswa_id', $siswaIds)
            ->where('tahun_ajaran_id', $selectedTa)
            ->where('semester_id', $selectedSem)
            ->pluck('mata_pelajaran_id');

        $classMapels = MataPelajaran::query()
            ->where(function($q) use ($selectedKelas, $selectedTa, $selectedSem, $mapelIdsFromGrades) {
                $q->where(function($sub) use ($selectedKelas, $selectedTa, $selectedSem) {
                    $sub->where('kelas_id', $selectedKelas)
                        ->where('tahun_ajaran_id', $selectedTa)
                        ->where('semester_id', $selectedSem);
                })
                ->orWhereIn('id', $mapelIdsFromGrades);
            })
            ->orderBy('nama_mata_pelajaran', 'asc')
            ->get()
            ->unique('nama_mata_pelajaran');

        $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();
        if ($selectedSiswa) {
            $studentsList = $studentsList->where('id', $selectedSiswa);
        }

        $headings = ['No', 'NISN', 'Nama Siswa'];
        foreach ($classMapels as $mp) {
            $headings[] = $mp->nama_mata_pelajaran;
        }
        $headings[] = 'Rata-Rata';
        $headings[] = 'Predikat';

        $dataRows = [];
        $index = 1;
        foreach ($studentsList as $siswa) {
            $row = [$index++, $siswa->nisn, $siswa->nama_siswa];
            $total = 0;
            $count = 0;

            foreach ($classMapels as $mp) {
                $nilaiRecord = Nilai::query()->where('siswa_id', $siswa->id)
                    ->where('mata_pelajaran_id', $mp->id)
                    ->where('semester_id', $selectedSem)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->first();

                $grade = null;
                if ($nilaiRecord) {
                    $grade = $nilaiRecord->nilai_raport !== null ? floatval($nilaiRecord->nilai_raport) : ($nilaiRecord->nilai !== null ? floatval($nilaiRecord->nilai) : null);
                }

                $row[] = $grade !== null ? $grade : '-';
                if ($grade !== null) {
                    $total += $grade;
                    $count++;
                }
            }

            $avg = $count > 0 ? round($total / $count, 1) : null;
            $predikat = $avg !== null ? Nilai::hitungPredikat($avg) : '-';

            $row[] = $avg !== null ? $avg : '-';
            $row[] = $predikat;

            $dataRows[] = $row;
        }

        $export = new class($headings, $dataRows) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            protected $headings;
            protected $rows;

            public function __construct(array $headings, array $rows) {
                $this->headings = $headings;
                $this->rows = $rows;
            }

            public function headings(): array {
                return $this->headings;
            }

            public function array(): array {
                return $this->rows;
            }
        };

        $fileName = 'Rekap_Nilai_Raport_Kelas_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $kelas->nama_kelas ?? 'Kelas') . '_' . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download($export, $fileName);
    }

    public function rekapMapelExport(Request $request)
    {
        $selectedTa = $request->get('tahun_ajaran_id');
        $selectedSemName = $request->get('semester_name');
        $selectedKelas = $request->get('kelas_id');
        $selectedMapel = $request->get('mata_pelajaran_id');

        if (!$selectedTa || !$selectedSemName || !$selectedKelas || !$selectedMapel) {
            alert()->warning('Peringatan', 'Silakan pilih Filter terlebih dahulu.');
            return back();
        }

        $kelas = Kelas::find($selectedKelas);
        $mapel = MataPelajaran::find($selectedMapel);
        $semester = Semester::query()
            ->where('tahun_ajaran_id', $selectedTa)
            ->where('nama_semester', $selectedSemName)
            ->first();
        $selectedSem = $semester ? $semester->id : null;

        $siswaIds = PembagianKelas::query()->where('kelas_id', $selectedKelas)
            ->where('tahun_ajaran_id', $selectedTa)
            ->pluck('siswa_id');

        $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();

        $headings = ['No', 'NISN', 'Nama Siswa', 'Nilai Harian', 'Nilai MID+', 'Nilai PAS+', 'Rata-Rata', 'Predikat'];
        $dataRows = [];
        $index = 1;

        foreach ($studentsList as $siswa) {
            $nilaiRecord = Nilai::query()->where('siswa_id', $siswa->id)
                ->where('mata_pelajaran_id', $selectedMapel)
                ->where('semester_id', $selectedSem)
                ->where('tahun_ajaran_id', $selectedTa)
                ->first();

            $harian = $nilaiRecord?->nilai_harian !== null ? floatval($nilaiRecord->nilai_harian) : '-';
            $midPlus = $nilaiRecord?->nilai_mid_plus !== null ? floatval($nilaiRecord->nilai_mid_plus) : '-';
            $pasPlus = $nilaiRecord?->nilai_pas_plus !== null ? floatval($nilaiRecord->nilai_pas_plus) : '-';

            $rata2 = null;
            if ($nilaiRecord && $nilaiRecord->nilai_harian !== null && $nilaiRecord->nilai_mid_plus !== null && $nilaiRecord->nilai_pas_plus !== null) {
                $rata2 = round(($nilaiRecord->nilai_harian + $nilaiRecord->nilai_mid_plus + $nilaiRecord->nilai_pas_plus) / 3, 1);
            }

            $predikat = $rata2 !== null ? Nilai::hitungPredikat($rata2) : '-';

            $dataRows[] = [
                $index++,
                $siswa->nisn,
                $siswa->nama_siswa,
                $harian,
                $midPlus,
                $pasPlus,
                $rata2 !== null ? $rata2 : '-',
                $predikat
            ];
        }

        $export = new class($headings, $dataRows) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            protected $headings;
            protected $rows;

            public function __construct(array $headings, array $rows) {
                $this->headings = $headings;
                $this->rows = $rows;
            }

            public function headings(): array {
                return $this->headings;
            }

            public function array(): array {
                return $this->rows;
            }
        };

        $fileName = 'Rekap_Nilai_Mapel_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $mapel->nama_mata_pelajaran ?? 'Mapel') . '_' . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download($export, $fileName);
    }
}
