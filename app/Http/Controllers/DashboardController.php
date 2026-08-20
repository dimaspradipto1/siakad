<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;
use App\Models\Siswa;
use App\Models\Kehadiran;
use App\Models\Nilai;
use App\Models\ProfilSekolah;

class DashboardController extends Controller
{
    use \App\Traits\ResolvesStudentFromUser;
    /**
     * Tampilkan halaman dashboard sesuai role pengguna.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $userRoles = $user->getRolesList();

        // Multi-role check: jika pengguna memiliki lebih dari 1 role dan belum memilih peran aktif
        if (count($userRoles) > 1 && !session()->has('active_role')) {
            $hideNav = true;
            $isMultiRoleSelection = true;
            $activeRole = '';

            $kelasWaliNama = null;
            if (in_array('wali kelas', $userRoles)) {
                $guru = $user->pegawai?->guru ?? $user->guru;
                $guruId = $guru ? $guru->id : 0;
                $waliRecord = \App\Models\WaliKelas::where('guru_id', $guruId)
                    ->whereHas('tahunAjaran', fn($q) => $q->where('status', 'Aktif'))
                    ->first();
                $kelasWaliNama = $waliRecord && $waliRecord->kelas ? $waliRecord->kelas->nama_kelas : 'Perwalian';
            }

            return view('layouts.dashboard.index', compact('user', 'activeRole', 'hideNav', 'isMultiRoleSelection', 'userRoles', 'kelasWaliNama'));
        }

        $activeRole = $user->activeRole();

        if ($activeRole === 'siswa') {
            return redirect()->route('siswa.profile');
        }

        if ($user && $activeRole === 'kepala sekolah') {
            $totalPegawai = \App\Models\Pegawai::count();

            // Dynamic pegawai breakdown by role / jabatan
            $pegawaiRoleCounts = \App\Models\Pegawai::with('user')->get()
                ->groupBy(function($p) {
                    return $p->user?->roles ? ucwords($p->user->roles) : ($p->jabatan ?? 'Pegawai');
                })
                ->map(fn($group) => $group->count());

            $totalSiswa = \App\Models\Siswa::count();
            $siswaCountByTingkat = [];
            for ($t = 1; $t <= 6; $t++) {
                $siswaCountByTingkat[$t] = \App\Models\Siswa::whereHas('kelas', function($q) use ($t) {
                    $q->where('tingkat', (string)$t);
                })->count();
            }

            $kehadiranTotal = \App\Models\Kehadiran::count();
            $hadirCount = \App\Models\Kehadiran::whereHas('jenisKehadiran', function($q) {
                $q->where('nama_kehadiran', 'Hadir');
            })->count();
            $kehadiranPercentage = $kehadiranTotal > 0 ? round(($hadirCount / $kehadiranTotal) * 100) : 0;

            $akademikAvg = \App\Models\Nilai::whereNotNull('nilai_raport')->avg('nilai_raport');
            $akademikPercentage = $akademikAvg !== null ? round($akademikAvg) : 0;

            $schoolProfile = \App\Models\ProfilSekolah::first();

            $allKelas = \App\Models\Kelas::orderBy('nama_kelas', 'asc')->get();
            $chartDataRaw = \DB::table('nilais')
                ->join('siswas', 'nilais.siswa_id', '=', 'siswas.id')
                ->join('kelas', 'siswas.kelas_id', '=', 'kelas.id')
                ->whereNotNull('nilais.nilai_raport')
                ->select('kelas.nama_kelas', \DB::raw('ROUND(AVG(nilais.nilai_raport)) as avg_nilai'))
                ->groupBy('kelas.id', 'kelas.nama_kelas')
                ->orderBy('kelas.nama_kelas', 'asc')
                ->pluck('avg_nilai', 'nama_kelas');

            $chartLabels = [];
            $chartValues = [];
            foreach ($allKelas as $k) {
                $chartLabels[] = $k->nama_kelas;
                $chartValues[] = isset($chartDataRaw[$k->nama_kelas]) ? (int)$chartDataRaw[$k->nama_kelas] : 0;
            }

            return view('layouts.dashboard.index', compact(
                'user', 'activeRole', 'totalPegawai', 'pegawaiRoleCounts',
                'totalSiswa', 'siswaCountByTingkat', 'kehadiranPercentage', 'kehadiranTotal', 'hadirCount',
                'akademikPercentage', 'akademikAvg', 'schoolProfile', 'chartLabels', 'chartValues'
            ));
        }

        if ($user && in_array($activeRole, ['guru', 'wali kelas'])) {
            $guru = $user->pegawai?->guru ?? $user->guru ?? \App\Models\Guru::whereHas('pegawai', fn($p) => $p->where('nama_pegawai', 'like', '%' . $user->name . '%'))->first();
            $guruId = $guru ? $guru->id : 0;

            $activeTa = \App\Models\TahunAjaran::where('status', 'Aktif')->first();
            $activeTaId = $activeTa ? $activeTa->id : null;

            if ($activeRole === 'guru') {
                // Data khusus Guru Pengajar (merujuk ke mata pelajaran aktif guru pada tahun ajaran aktif)
                $mapelQuery = \App\Models\MataPelajaran::where('guru_id', $guruId)
                    ->whereNotNull('kelas_id')
                    ->where(function ($q) {
                        $q->where('status', 'Aktif')->orWhereNull('status');
                    })
                    ->when($activeTaId, function ($q) use ($activeTaId) {
                        $q->where('tahun_ajaran_id', $activeTaId);
                    })
                    ->with('kelas');

                $guruMapels = $mapelQuery->get();
                $kelasDiajarList = $guruMapels->pluck('kelas.nama_kelas')->filter()->unique()->values();

                $guruKelasDisplay = $kelasDiajarList->isNotEmpty() ? $kelasDiajarList->implode(', ') : '—';
                $guruKelasCount = $kelasDiajarList->count();

                $guruMapelNames = $guruMapels->pluck('nama_mata_pelajaran')->filter()->unique()->values();
                $guruMapelDisplay = $guruMapelNames->isNotEmpty() ? $guruMapelNames->implode(', ') : '—';
                $guruMapelCount = $guruMapelNames->count();

                return view('layouts.dashboard.index', compact(
                    'user', 'activeRole', 'guruKelasDisplay', 'guruKelasCount', 'guruMapelDisplay', 'guruMapelCount'
                ));
            }

            if ($activeRole === 'wali kelas') {
                // Data khusus Wali Kelas (kelas perwalian & jumlah siswa)
                $waliRecord = \App\Models\WaliKelas::where('guru_id', $guruId)
                    ->when($activeTaId, fn($q) => $q->where('tahun_ajaran_id', $activeTaId))
                    ->with('kelas')
                    ->first();

                $kelasWaliNama = $waliRecord && $waliRecord->kelas ? $waliRecord->kelas->nama_kelas : null;
                $waliKelasDisplay = $kelasWaliNama ? 'Kelas ' . $kelasWaliNama : '—';

                $siswaPerwalianCount = 0;
                if ($waliRecord && $waliRecord->kelas_id) {
                    $siswaPerwalianCount = \App\Models\PembagianKelas::where('kelas_id', $waliRecord->kelas_id)
                        ->when($activeTaId, fn($q) => $q->where('tahun_ajaran_id', $activeTaId))
                        ->count();
                    if ($siswaPerwalianCount === 0) {
                        $siswaPerwalianCount = \App\Models\Siswa::where('kelas_id', $waliRecord->kelas_id)->count();
                    }
                }

                return view('layouts.dashboard.index', compact(
                    'user', 'activeRole', 'waliKelasDisplay', 'kelasWaliNama', 'siswaPerwalianCount'
                ));
            }
        }

        if ($user && $activeRole === 'admin') {
            $totalSiswa = \App\Models\Siswa::count();
            $totalGuru = \App\Models\Guru::count();
            $totalWaliKelas = \App\Models\WaliKelas::whereHas('tahunAjaran', fn($q) => $q->where('status', 'Aktif'))->count();
            if ($totalWaliKelas === 0) {
                $totalWaliKelas = \App\Models\WaliKelas::count();
            }
            $totalKelas = \App\Models\Kelas::count();
            return view('layouts.dashboard.index', compact('user', 'activeRole', 'totalSiswa', 'totalGuru', 'totalWaliKelas', 'totalKelas'));
        }

        if ($user && $activeRole === 'orang tua') {
            $children = $this->getChildrenForParent($user);
            $selectedChild = $this->resolveStudentForCurrentUser();
            $hideNav = true;

            return view('layouts.dashboard.index', compact('user', 'activeRole', 'children', 'selectedChild', 'hideNav'));
        }

        return view('layouts.dashboard.index', compact('user', 'activeRole'));
    }

    public function selectChild(Request $request, $id)
    {
        $siswa = Siswa::find($id);
        if ($siswa) {
            session(['selected_child_id' => (int)$id]);
            alert()->success('Berhasil!', 'Aktif melihat data anak: ' . $siswa->nama_siswa);
        }
        if ($request->filled('redirect')) {
            return redirect($request->get('redirect'));
        }
        return redirect()->route('siswa.profile');
    }
}
