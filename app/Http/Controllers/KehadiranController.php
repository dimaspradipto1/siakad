<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\JenisKehadiran;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\Kelas;
use App\Models\JenisCatatan;
use App\Models\CatatanSiswa;
use App\Models\PembagianKelas;
use App\Models\Guru;
use App\Models\ProfilSekolah;
use App\Models\WaliKelas;
use App\Models\OrangTua;
use App\Http\Requests\KehadiranRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\DataTables\KehadiranDataTable;

class KehadiranController extends Controller
{
    use \App\Traits\AuthorizeTransactionData;
    use \App\Traits\ResolvesStudentFromUser;

    private const BULAN_LABELS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /**
     * Tahun kalender yang mungkin dicakup oleh sebuah Tahun Ajaran (mis. 2025/2026),
     * dipakai untuk mencocokkan tanggal kehadiran tanpa bergantung pada asumsi
     * semester Ganjil/Genap yang kaku.
     */
    private function candidateYearsForTahunAjaran(?int $tahunAjaranId): array
    {
        if (!$tahunAjaranId) {
            return [];
        }

        $ta = TahunAjaran::find($tahunAjaranId);

        return array_values(array_unique(array_filter([$ta?->tahun_mulai, $ta?->tahun_selesai])));
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
            $pegawai = \App\Models\Pegawai::where('user_id', $user->id)->first();
            if (!$pegawai) {
                $nameBase = trim(explode(',', $user->name)[0]);
                $pegawai = \App\Models\Pegawai::where('nama_pegawai', 'like', '%' . $nameBase . '%')->first();
            }
            if ($pegawai) {
                $guru = Guru::where('pegawai_id', $pegawai->id)->first();
            }
        }

        if ($guru) {
            $ids = WaliKelas::where('guru_id', $guru->id)->pluck('kelas_id')->toArray();
            if (!empty($ids)) {
                return array_unique(array_filter($ids));
            }
        }

        $nameBase = trim(explode(',', $user->name)[0]);
        $matchingWali = WaliKelas::whereHas('guru.pegawai', function($q) use ($nameBase) {
            $q->where('nama_pegawai', 'like', '%' . $nameBase . '%');
        })->pluck('kelas_id')->toArray();

        if (!empty($matchingWali)) {
            return array_unique(array_filter($matchingWali));
        }

        return [];
    }

    public function index(Request $request)
    {
        return redirect()->route('kehadiran.rekap');
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $isGuru = $user->roles === 'guru';

        // 1. Resolve filters
        $selectedTa = $request->get('tahun_ajaran_id');
        $selectedSem = $request->get('semester_id');
        $selectedKelas = $request->get('kelas_id');
        $selectedMapel = $request->get('mata_pelajaran_id');
        $selectedTanggal = $request->get('tanggal', date('Y-m-d'));

        // Fallback for Tahun Ajaran
        if (!$selectedTa) {
            $activeTa = \App\Models\TahunAjaran::query()->where('status', 'Aktif')->first() ?? \App\Models\TahunAjaran::query()->first();
            $selectedTa = $activeTa ? $activeTa->id : null;
        }

        // Fallback for Semester
        if (!$selectedSem && $selectedTa) {
            $activeSem = \App\Models\Semester::query()->where('tahun_ajaran_id', $selectedTa)->first();
            $selectedSem = $activeSem ? $activeSem->id : null;
        }

        // 2. Fetch Tahun Ajaran and Semester lists
        $tahunAjarans = \App\Models\TahunAjaran::query()->get();
        $semesters = $selectedTa
            ? \App\Models\Semester::query()->where('tahun_ajaran_id', $selectedTa)->get()
            : \App\Models\Semester::query()->get();

        // 3. Fetch Mapels and Kelas
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas');

        if ($isWali) {
            $waliKelasIds = $this->getWaliKelasIds($user);
            $kelas = Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get();
            $mapels = MataPelajaran::query()
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->get()
                ->unique('nama_mata_pelajaran')
                ->values();
        } elseif ($isGuru) {
            $guru = $user->pegawai?->guru;
            $guruId = $guru ? $guru->id : 0;
            $mapelsRaw = MataPelajaran::query()->where('guru_id', $guruId)
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->get();
            $kelas = Kelas::query()->whereIn('id', $mapelsRaw->pluck('kelas_id'))->orderBy('nama_kelas', 'asc')->get();
            $mapels = $mapelsRaw->unique('nama_mata_pelajaran')->values();
        } else {
            $mapels = MataPelajaran::query()
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->get()
                ->unique('nama_mata_pelajaran')
                ->values();
            $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
        }

        $jenisKehadirans = JenisKehadiran::all();
        $jenisCatatans = \App\Models\JenisCatatan::all();

        // 4. Fetch students and their current attendance / notes if all filters are set
        $students = [];
        if ($selectedTa && $selectedSem && $selectedKelas && $selectedMapel && $selectedTanggal) {
            $siswaIds = \App\Models\PembagianKelas::query()->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->pluck('siswa_id');
            
            $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();

            $selectedMapelModel = MataPelajaran::find($selectedMapel);
            $matchingMapelIds = $selectedMapelModel
                ? MataPelajaran::where('nama_mata_pelajaran', $selectedMapelModel->nama_mata_pelajaran)->pluck('id')->toArray()
                : ($selectedMapel ? [$selectedMapel] : []);

            foreach ($studentsList as $siswa) {
                // Fetch attendance
                $kehadiranRecord = Kehadiran::query()->where('siswa_id', $siswa->id)
                    ->whereIn('mata_pelajaran_id', $matchingMapelIds)
                    ->where('tanggal', $selectedTanggal)
                    ->first();
                
                // Fetch student note (CatatanSiswa)
                $mapel = $selectedMapelModel;
                $guruId = $mapel ? $mapel->guru_id : null;
                if (!$guruId && $isGuru) {
                    $guruId = $user->pegawai?->guru?->id;
                }

                $catatanRecord = \App\Models\CatatanSiswa::query()->where('siswa_id', $siswa->id)
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->where('semester_id', $selectedSem)
                    ->where('tanggal', $selectedTanggal)
                    ->when($guruId, function($q) use ($guruId) {
                        return $q->where('guru_id', $guruId);
                    })
                    ->first();

                $siswa->kehadiran_record = $kehadiranRecord;
                $siswa->catatan_record = $catatanRecord;
                $students[] = $siswa;
            }
        }

        return view('pages.kehadiran.create', compact(
            'mapels', 'kelas', 'semesters', 'tahunAjarans', 
            'selectedTa', 'selectedSem', 'selectedKelas', 'selectedMapel', 'selectedTanggal',
            'students', 'jenisKehadirans', 'jenisCatatans'
        ));
    }

    public function getKelasDanMapel(Request $request)
    {
        $taId = $request->get('tahun_ajaran_id');
        $semId = $request->get('semester_id');
        $user = auth()->user();
        $isGuru = $user->roles === 'guru';

        if ($isGuru) {
            $guru = $user->pegawai?->guru;
            $guruId = $guru ? $guru->id : 0;
            $mapelsRaw = MataPelajaran::query()->where('guru_id', $guruId)
                ->where('tahun_ajaran_id', $taId)
                ->where('semester_id', $semId)
                ->get();
            
            $kelas = Kelas::query()
                ->whereIn('id', MataPelajaran::query()->where('guru_id', $guruId)
                    ->where('tahun_ajaran_id', $taId)
                    ->where('semester_id', $semId)
                    ->pluck('kelas_id')
                )
                ->orderBy('nama_kelas', 'asc')
                ->get(['id', 'nama_kelas']);

            $mapels = $mapelsRaw->unique('nama_mata_pelajaran')->values();
        } else {
            $mapels = MataPelajaran::query()
                ->where('tahun_ajaran_id', $taId)
                ->where('semester_id', $semId)
                ->get()
                ->unique('nama_mata_pelajaran')
                ->values();
            
            $kelas = Kelas::query()
                ->orderBy('nama_kelas', 'asc')
                ->get(['id', 'nama_kelas']);
        }

        return response()->json([
            'kelas' => $kelas,
            'mapels' => $mapels,
        ]);
    }

    public function bulkSave(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'semester_id' => 'required|exists:semesters,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'tanggal' => 'required|date',
            'kehadiran' => 'required|array',
            'jenis_catatan_id' => 'nullable|array',
            'keterangan' => 'nullable|array',
        ]);

        $taId = $request->tahun_ajaran_id;
        $semId = $request->semester_id;
        $mapelId = $request->mata_pelajaran_id;
        $tanggal = $request->tanggal;

        $kehadiranInput = $request->kehadiran;
        $jenisCatatanInput = $request->jenis_catatan_id ?? [];
        $keteranganInput = $request->keterangan ?? [];

        // Fetch subject's teacher to associate with student notes
        $mapel = MataPelajaran::find($mapelId);
        $guruId = $mapel ? $mapel->guru_id : null;
        if (!$guruId) {
            $user = auth()->user();
            if ($user->roles === 'guru') {
                $guruId = $user->pegawai?->guru?->id;
            }
        }

        if (!$guruId) {
            // Find first guru in DB as a safe fallback
            $fallbackGuru = \App\Models\Guru::first();
            $guruId = $fallbackGuru ? $fallbackGuru->id : null;
        }

        foreach ($kehadiranInput as $siswaId => $jenisKehadiranId) {
            if ($jenisKehadiranId) {
                $keterangan = $keteranganInput[$siswaId] ?? null;

                // Save or update attendance
                Kehadiran::query()->updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'mata_pelajaran_id' => $mapelId,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'jenis_kehadiran_id' => $jenisKehadiranId,
                        'keterangan' => $keterangan,
                    ]
                );

                // Save or update student notes (CatatanSiswa) if a type is selected
                $jenisCatatanId = $jenisCatatanInput[$siswaId] ?? null;
                if ($jenisCatatanId) {
                    \App\Models\CatatanSiswa::query()->updateOrCreate(
                        [
                            'siswa_id' => $siswaId,
                            'tahun_ajaran_id' => $taId,
                            'semester_id' => $semId,
                            'tanggal' => $tanggal,
                            'guru_id' => $guruId,
                        ],
                        [
                            'jenis_catatan_id' => $jenisCatatanId,
                            'isi_catatan' => $keterangan,
                            'status' => 'Aktif',
                        ]
                    );
                }
            }
        }

        alert()->success(
            'Berhasil!',
            'Data kehadiran berhasil disimpan.'
        );

        return redirect()->route('kehadiran.create', [
            'tahun_ajaran_id' => $taId,
            'semester_id' => $semId,
            'kelas_id' => $request->kelas_id,
            'mata_pelajaran_id' => $mapelId,
            'tanggal' => $tanggal,
        ]);
    }

    public function store(KehadiranRequest $request)
    {
        $validated = $request->validated();
        Kehadiran::create($validated);

        alert()->success(
            'Berhasil!',
            'Data kehadiran berhasil ditambahkan.'
        );

        return redirect()->route('kehadiran.index');
    }

    public function show(Kehadiran $kehadiran)
    {
        return redirect()->route('kehadiran.edit', $kehadiran);
    }

    public function edit(Kehadiran $kehadiran)
    {
        $siswas = Siswa::with('kelas')->orderBy('nama_siswa', 'asc')->get();
        $mapels = MataPelajaran::all();
        $jenisKehadirans = JenisKehadiran::all();
        return view('pages.kehadiran.edit', compact('kehadiran', 'siswas', 'mapels', 'jenisKehadirans'));
    }

    public function update(KehadiranRequest $request, Kehadiran $kehadiran)
    {
        $validated = $request->validated();
        $kehadiran->update($validated);

        alert()->success(
            'Diperbarui!',
            'Data kehadiran berhasil diperbarui.'
        );

        return redirect()->route('kehadiran.index');
    }

    public function destroy(Kehadiran $kehadiran)
    {
        $kehadiran->delete();

        alert()->success(
            'Dihapus!',
            'Data kehadiran berhasil dihapus.'
        );

        return redirect()->route('kehadiran.index');
    }

    /**
     * AJAX: Mata Pelajaran yang tersedia untuk kombinasi Tahun Ajaran + Semester + Kelas,
     * dipakai oleh halaman Rekap Kehadiran agar dropdown Mata Pelajaran ter-update
     * tanpa perlu submit ulang form.
     */
    public function rekapGetMapel(Request $request)
    {
        $user = auth()->user();
        $isGuru = $user && $user->roles === 'guru';
        $isPersonal = $user && in_array($user->roles, ['siswa', 'orang tua']);

        $taId = $request->get('tahun_ajaran_id');
        $semName = $request->get('semester_name');
        $kelasId = $request->get('kelas_id');

        if ($isPersonal) {
            $mySiswa = $this->resolveStudentForCurrentUser();
            if ($mySiswa) {
                $pk = PembagianKelas::where('siswa_id', $mySiswa->id)
                    ->where('tahun_ajaran_id', $taId)
                    ->first();
                if (!$pk) {
                    $pk = PembagianKelas::where('siswa_id', $mySiswa->id)->latest('id')->first();
                }
                $kelasId = $pk ? $pk->kelas_id : ($mySiswa->kelas_id ?? $kelasId);
            }
        }

        $semId = Semester::query()
            ->where('tahun_ajaran_id', $taId)
            ->where('nama_semester', $semName)
            ->value('id');

        $mapelQuery = MataPelajaran::query();

        if ($kelasId) {
            $mapelQuery->where(function($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId)->orWhereNull('kelas_id');
            });
        }

        if ($taId) {
            $mapelQuery->where(function($q) use ($taId) {
                $q->where('tahun_ajaran_id', $taId)->orWhereNull('tahun_ajaran_id');
            });
        }

        if ($semId) {
            $mapelQuery->where(function($q) use ($semId) {
                $q->where('semester_id', $semId)->orWhereNull('semester_id');
            });
        }

        if ($isGuru) {
            $guru = $user->pegawai?->guru ?? $user->guru;
            $mapelQuery->where('guru_id', $guru ? $guru->id : 0);
        }

        $mapels = $mapelQuery->orderBy('nama_mata_pelajaran', 'asc')->get(['id', 'nama_mata_pelajaran'])->unique('nama_mata_pelajaran')->values();

        return response()->json(['mapels' => $mapels]);
    }

    public function rekapKehadiran(Request $request)
    {
        $user = auth()->user();
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas');
        $isPersonal = $user && in_array($user->roles, ['siswa', 'orang tua']);
        $isGuru = $user && $user->roles === 'guru';
        $mySiswa = null;

        if ($isPersonal) {
            $mySiswa = $this->resolveStudentForCurrentUser();
        }

        $tahunAjarans = TahunAjaran::query()->get();

        $selectedTa = $request->get('tahun_ajaran_id');
        if (!$selectedTa) {
            $activeTa = TahunAjaran::query()->where('status', 'Aktif')->first() ?? TahunAjaran::query()->first();
            $selectedTa = $activeTa ? $activeTa->id : null;
        }

        $semesters = $selectedTa
            ? Semester::query()->where('tahun_ajaran_id', $selectedTa)->get()
            : Semester::query()->get();

        $selectedSemName = $request->get('semester_name');
        if (!$selectedSemName || !$semesters->contains('nama_semester', $selectedSemName)) {
            $selectedSemName = $semesters->first()?->nama_semester ?? '';
        }

        $semester = null;
        if ($selectedTa && $selectedSemName) {
            $semester = $semesters->firstWhere('nama_semester', $selectedSemName)
                ?? Semester::query()
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->where('nama_semester', $selectedSemName)
                    ->first();
        }
        $selectedSem = $semester ? $semester->id : null;

        if ($isPersonal && $mySiswa) {
            $pk = PembagianKelas::where('siswa_id', $mySiswa->id)
                ->where('tahun_ajaran_id', $selectedTa)
                ->first();
            if (!$pk) {
                $pk = PembagianKelas::where('siswa_id', $mySiswa->id)->latest('id')->first();
            }
            $selectedKelas = $pk ? $pk->kelas_id : $mySiswa->kelas_id;
            $kelas = Kelas::query()->where('id', $selectedKelas)->get();
        } elseif ($isWali) {
            $waliKelasIds = $this->getWaliKelasIds($user);
            $kelas = !empty($waliKelasIds)
                ? Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
            $selectedKelas = $request->get('kelas_id');
            if (!$selectedKelas || (!empty($waliKelasIds) && !in_array($selectedKelas, $waliKelasIds))) {
                $selectedKelas = $kelas->first()?->id;
            }
        } elseif ($isGuru) {
            $guru = $user->pegawai?->guru ?? $user->guru;
            $guruId = $guru ? $guru->id : 0;
            $guruMapelKelasIds = MataPelajaran::query()
                ->where('guru_id', $guruId)
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->pluck('kelas_id');
            $kelas = Kelas::query()->whereIn('id', $guruMapelKelasIds)->orderBy('nama_kelas', 'asc')->get();
            $selectedKelas = $request->get('kelas_id');
            if ($selectedKelas && !in_array($selectedKelas, $kelas->pluck('id')->toArray())) {
                $selectedKelas = null;
            }
        } else {
            $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
            $selectedKelas = $request->get('kelas_id');
        }

        // Mapels query
        $mapelsQuery = MataPelajaran::query();

        if ($isGuru) {
            $guru = $user->pegawai?->guru ?? $user->guru;
            $guruId = $guru ? $guru->id : 0;
            $mapelsQuery->where('guru_id', $guruId);
        }

        if ($isPersonal && $selectedKelas) {
            $mapelsQuery->where(function($q) use ($selectedKelas) {
                $q->where('kelas_id', $selectedKelas)->orWhereNull('kelas_id');
            });
        }

        $mapels = $mapelsQuery->orderByRaw('CASE WHEN kelas_id IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('nama_mata_pelajaran', 'asc')
            ->get()
            ->unique('nama_mata_pelajaran')
            ->values();

        $selectedMapel = $request->get('mata_pelajaran_id');
        $selectedMapelModel = $selectedMapel ? MataPelajaran::find($selectedMapel) : null;
        $matchingMapelIds = $selectedMapelModel
            ? MataPelajaran::where('nama_mata_pelajaran', $selectedMapelModel->nama_mata_pelajaran)->pluck('id')->toArray()
            : ($selectedMapel ? [$selectedMapel] : []);

        $selectedBulan = $request->get('bulan');

        $students = [];
        $dates = [];
        $attendanceMatrix = [];

        if ($selectedTa && $selectedSem && $selectedKelas && $selectedMapel) {
            if ($isPersonal && $mySiswa) {
                $siswaIds = collect([$mySiswa->id]);
                $studentsList = collect([$mySiswa]);
            } else {
                $siswaIdsQuery = PembagianKelas::query()
                    ->where('kelas_id', $selectedKelas)
                    ->where('tahun_ajaran_id', $selectedTa);
                $siswaIds = $siswaIdsQuery->pluck('siswa_id');
                $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();
            }

            // Distinct dates where attendance was recorded
            $datesQuery = Kehadiran::query()
                ->whereIn('mata_pelajaran_id', $matchingMapelIds)
                ->whereIn('siswa_id', $siswaIds);

            if (!empty($selectedBulan)) {
                $datesQuery->whereMonth('tanggal', $selectedBulan);
            }

            $dates = $datesQuery->select('tanggal')
                ->distinct()
                ->orderBy('tanggal', 'asc')
                ->pluck('tanggal')
                ->toArray();

            if (!empty($dates)) {
                $attendanceRecords = Kehadiran::query()
                    ->with('jenisKehadiran')
                    ->whereIn('mata_pelajaran_id', $matchingMapelIds)
                    ->whereIn('siswa_id', $siswaIds)
                    ->whereIn('tanggal', $dates)
                    ->get();

                foreach ($attendanceRecords as $rec) {
                    $attendanceMatrix[$rec->siswa_id][$rec->tanggal] = $rec;
                }
            }

            $students = $studentsList;
        }

        return view('pages.kehadiran.rekap', compact(
            'kelas', 'tahunAjarans', 'semesters', 'mapels',
            'selectedTa', 'selectedSemName', 'selectedSem', 'selectedKelas', 'selectedMapel', 'selectedBulan',
            'students', 'dates', 'attendanceMatrix'
        ));
    }

    public function rekapKehadiranPrint(Request $request)
    {
        $user = auth()->user();
        $isWali = $user && $user->roles === 'wali kelas';
        $isPersonal = $user && in_array($user->roles, ['siswa', 'orang tua']);
        $isGuru = $user && $user->roles === 'guru';
        $mySiswa = null;

        if ($isPersonal) {
            $mySiswa = $this->resolveStudentForCurrentUser();
        }

        $selectedTa = $request->get('tahun_ajaran_id');
        $selectedSemName = $request->get('semester_name');
        $selectedKelas = $request->get('kelas_id');
        $selectedMapel = $request->get('mata_pelajaran_id');
        $selectedBulan = $request->get('bulan');

        $bulanLabels = self::BULAN_LABELS;
        $selectedBulanName = (!empty($selectedBulan) && isset($bulanLabels[(int)$selectedBulan]))
            ? $bulanLabels[(int)$selectedBulan]
            : 'Semua Bulan';

        $tahunAjaran = TahunAjaran::find($selectedTa);
        $semester = Semester::query()
            ->where('tahun_ajaran_id', $selectedTa)
            ->where('nama_semester', $selectedSemName)
            ->first();
        $selectedSem = $semester ? $semester->id : null;

        if ($isPersonal && $mySiswa) {
            $pk = PembagianKelas::where('siswa_id', $mySiswa->id)
                ->where('tahun_ajaran_id', $selectedTa)
                ->first();
            if (!$pk) {
                $pk = PembagianKelas::where('siswa_id', $mySiswa->id)->latest('id')->first();
            }
            $selectedKelas = $pk ? $pk->kelas_id : $mySiswa->kelas_id;
        }

        $kelasModel = Kelas::find($selectedKelas);
        $mapelModel = MataPelajaran::find($selectedMapel);
        $school = \App\Models\ProfilSekolah::query()->first();

        $students = [];
        $dates = [];
        $attendanceMatrix = [];

        if ($selectedTa && $selectedSem && $selectedKelas && $selectedMapel) {
            if ($isPersonal && $mySiswa) {
                $siswaIds = collect([$mySiswa->id]);
                $studentsList = collect([$mySiswa]);
            } else {
                $siswaIdsQuery = PembagianKelas::query()->where('kelas_id', $selectedKelas)
                    ->where('tahun_ajaran_id', $selectedTa);
                $siswaIds = $siswaIdsQuery->pluck('siswa_id');
                $studentsList = Siswa::query()->whereIn('id', $siswaIds)->orderBy('nama_siswa', 'asc')->get();
            }

            $matchingMapelIds = $mapelModel
                ? MataPelajaran::where('nama_mata_pelajaran', $mapelModel->nama_mata_pelajaran)->pluck('id')->toArray()
                : [$selectedMapel];

            $datesQuery = Kehadiran::query()
                ->whereIn('mata_pelajaran_id', $matchingMapelIds)
                ->whereIn('siswa_id', $siswaIds);

            if (!empty($selectedBulan)) {
                $datesQuery->whereMonth('tanggal', $selectedBulan);
            }

            $dates = $datesQuery->select('tanggal')
                ->distinct()
                ->orderBy('tanggal', 'asc')
                ->pluck('tanggal')
                ->toArray();

            if (!empty($dates)) {
                $attendanceRecords = Kehadiran::query()
                    ->with('jenisKehadiran')
                    ->whereIn('mata_pelajaran_id', $matchingMapelIds)
                    ->whereIn('siswa_id', $siswaIds)
                    ->whereIn('tanggal', $dates)
                    ->get();

                foreach ($attendanceRecords as $rec) {
                    $attendanceMatrix[$rec->siswa_id][$rec->tanggal] = $rec;
                }
            }

            $students = $studentsList;
        }

        $waliKelas = \App\Models\WaliKelas::query()->where('kelas_id', $selectedKelas)
            ->where('tahun_ajaran_id', $selectedTa)
            ->first();

        return view('pages.kehadiran.rekap_print', compact(
            'tahunAjaran', 'semester', 'kelasModel', 'mapelModel', 'school',
            'students', 'dates', 'attendanceMatrix', 'waliKelas', 'selectedSemName',
            'selectedBulan', 'selectedBulanName'
        ));
    }

    public function rekapKehadiranCatatan(Request $request)
    {
        $siswaId = $request->get('siswa_id');
        $selectedTa = $request->get('tahun_ajaran_id');
        $selectedSemName = $request->get('semester_name');
        $selectedKelas = $request->get('kelas_id');
        $selectedMapel = $request->get('mata_pelajaran_id');
        $selectedBulan = $request->get('bulan');

        $siswa = Siswa::with('kelas')->findOrFail($siswaId);
        $tahunAjaran = TahunAjaran::find($selectedTa);
        $semester = Semester::query()
            ->where('tahun_ajaran_id', $selectedTa)
            ->where('nama_semester', $selectedSemName)
            ->first();
        
        $kelasModel = Kelas::find($selectedKelas);
        $mapelModel = MataPelajaran::find($selectedMapel);

        $bulanLabels = self::BULAN_LABELS;
        $selectedBulanName = (!empty($selectedBulan) && isset($bulanLabels[(int)$selectedBulan]))
            ? $bulanLabels[(int)$selectedBulan]
            : 'Semua Bulan';

        $matchingMapelIds = $mapelModel
            ? MataPelajaran::where('nama_mata_pelajaran', $mapelModel->nama_mata_pelajaran)->pluck('id')->toArray()
            : ($selectedMapel ? [$selectedMapel] : []);

        // Fetch attendance records for this student
        $kehadiranQuery = Kehadiran::query()
            ->with(['jenisKehadiran', 'mataPelajaran'])
            ->where('siswa_id', $siswaId)
            ->whereIn('mata_pelajaran_id', $matchingMapelIds);

        if (!empty($selectedBulan)) {
            $kehadiranQuery->whereMonth('tanggal', $selectedBulan);
        }

        $kehadirans = $kehadiranQuery->orderBy('tanggal', 'asc')->get();

        // Fetch CatatanSiswa records for this student
        $catatanSiswaQuery = \App\Models\CatatanSiswa::query()
            ->with(['jenisCatatan', 'guru.pegawai'])
            ->where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $selectedTa);

        if ($semester) {
            $catatanSiswaQuery->where('semester_id', $semester->id);
        }

        if (!empty($selectedBulan)) {
            $catatanSiswaQuery->whereMonth('tanggal', $selectedBulan);
        }

        $catatanSiswas = $catatanSiswaQuery->orderBy('tanggal', 'asc')->get();
        $catatanByDate = $catatanSiswas->keyBy(function($item) {
            return $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') : '';
        });

        // Build records list using field keterangan from kehadirans table
        $records = [];
        foreach ($kehadirans as $kh) {
            $tglStr = $kh->tanggal ? \Carbon\Carbon::parse($kh->tanggal)->format('Y-m-d') : '';
            $catatan = $tglStr ? ($catatanByDate[$tglStr] ?? null) : null;

            // Catatan primarily taken from kehadirans.keterangan
            $catatanText = !empty($kh->keterangan) ? $kh->keterangan : ($catatan?->isi_catatan ?? null);

            $records[] = (object) [
                'tanggal' => $kh->tanggal,
                'jenis_kehadiran' => $kh->jenisKehadiran?->nama_kehadiran ?? 'Hadir',
                'kode_kehadiran' => $kh->jenisKehadiran?->kode_kehadiran ?? 'H',
                'keterangan' => $kh->keterangan,
                'jenis_catatan' => $catatan?->jenisCatatan?->nama_jenis_catatan ?? null,
                'isi_catatan' => $catatanText,
                'tindak_lanjut' => $catatan?->tindak_lanjut,
                'status' => $catatan?->status,
                'guru' => $catatan?->guru?->pegawai?->nama_pegawai ?? null,
            ];
        }

        $alpaNames = ['Alpa', 'Tanpa Keterangan', 'Alpha'];
        $counts = [
            'total' => count($records),
            'hadir' => $kehadirans->filter(fn($k) => strtolower($k->jenisKehadiran?->nama_kehadiran ?? '') === 'hadir')->count(),
            'sakit' => $kehadirans->filter(fn($k) => strtolower($k->jenisKehadiran?->nama_kehadiran ?? '') === 'sakit')->count(),
            'izin'  => $kehadirans->filter(fn($k) => strtolower($k->jenisKehadiran?->nama_kehadiran ?? '') === 'izin')->count(),
            'alpa'  => $kehadirans->filter(fn($k) => in_array(strtolower($k->jenisKehadiran?->nama_kehadiran ?? ''), ['alpa', 'alpha', 'tanpa keterangan']))->count(),
            'catatan' => collect($records)->filter(fn($r) => !empty($r->isi_catatan) || !empty($r->keterangan))->count(),
        ];

        return view('pages.kehadiran.catatan', compact(
            'siswa', 'tahunAjaran', 'semester', 'kelasModel', 'mapelModel',
            'selectedTa', 'selectedSemName', 'selectedKelas', 'selectedMapel', 'selectedBulan', 'selectedBulanName',
            'records', 'counts'
        ));
    }

    public function rekapKehadiranPersonal(Request $request)
    {
        $user = auth()->user();
        $siswa = $this->resolveStudentForCurrentUser();

        if (!$siswa) {
            alert()->error('Error', 'Data siswa tidak ditemukan.');
            return redirect()->route('dashboard');
        }

        $tahunAjarans = TahunAjaran::query()->get();

        $selectedTa = $request->get('tahun_ajaran_id');
        if (!$selectedTa) {
            $activeTa = TahunAjaran::query()->where('status', 'Aktif')->first() ?? TahunAjaran::query()->first();
            $selectedTa = $activeTa ? $activeTa->id : null;
        }

        $semesters = $selectedTa
            ? Semester::query()->where('tahun_ajaran_id', $selectedTa)->get()
            : Semester::query()->get();

        $selectedSemName = $request->get('semester_name');
        if (!$selectedSemName || !$semesters->contains('nama_semester', $selectedSemName)) {
            $selectedSemName = $semesters->first()?->nama_semester ?? '';
        }

        $semester = null;
        if ($selectedTa && $selectedSemName) {
            $semester = $semesters->firstWhere('nama_semester', $selectedSemName)
                ?? Semester::query()
                    ->where('tahun_ajaran_id', $selectedTa)
                    ->where('nama_semester', $selectedSemName)
                    ->first();
        }
        $selectedSem = $semester ? $semester->id : null;

        $pk = \App\Models\PembagianKelas::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $selectedTa)
            ->first();
        $kelasId = $pk ? $pk->kelas_id : $siswa->kelas_id;
        $classMapels = [];
        $attendanceCounts = [];

        if ($selectedTa && $selectedSem) {
            $classMapels = MataPelajaran::query()->where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->orderBy('nama_mata_pelajaran', 'asc')
                ->get();

            // Fetch counts of Sakit, Izin, Alpa per subject
            foreach ($classMapels as $mp) {
                $attendanceCounts[$mp->id] = [
                    'Sakit' => Kehadiran::query()->where('siswa_id', $siswa->id)
                        ->where('mata_pelajaran_id', $mp->id)
                        ->whereHas('jenisKehadiran', function($q) { $q->where('nama_kehadiran', 'Sakit'); })
                        ->count(),
                    'Izin' => Kehadiran::query()->where('siswa_id', $siswa->id)
                        ->where('mata_pelajaran_id', $mp->id)
                        ->whereHas('jenisKehadiran', function($q) { $q->where('nama_kehadiran', 'Izin'); })
                        ->count(),
                    'Alpa' => Kehadiran::query()->where('siswa_id', $siswa->id)
                        ->where('mata_pelajaran_id', $mp->id)
                        ->whereHas('jenisKehadiran', function($q) { $q->where('nama_kehadiran', 'Alpa')->orWhere('nama_kehadiran', 'Tanpa Keterangan'); })
                        ->count(),
                ];
            }
        }

        $children = $this->getChildrenForParent();

        return view('pages.kehadiran.rekap_personal', compact('siswa', 'children', 'tahunAjarans', 'semesters', 'selectedTa', 'selectedSemName', 'selectedSem', 'classMapels', 'attendanceCounts'));
    }
}
