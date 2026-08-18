<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\OrangTua;
use App\Models\PembagianKelas;
use App\Http\Requests\MataPelajaranRequest;
use App\DataTables\MataPelajaranDataTable;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MataPelajaranController extends Controller
{
    use \App\Traits\AuthorizeMasterData;
    use \App\Traits\ResolvesStudentFromUser;
    public function index(MataPelajaranDataTable $dataTable)
    {
        return $dataTable->render('pages.mata-pelajaran.index');
    }

    public function create()
    {
        $kelas = \App\Models\Kelas::orderBy('nama_kelas', 'asc')->get();
        $tahunAjarans = \App\Models\TahunAjaran::all();
        $semesters = \App\Models\Semester::all();
        $gurus = \App\Models\Guru::with('pegawai')->get();
        return view('pages.mata-pelajaran.create', compact('kelas', 'tahunAjarans', 'semesters', 'gurus'));
    }

    public function store(MataPelajaranRequest $request)
    {
        $validated = $request->validated();

        // Filter item TP yang kosong
        $validated['tp_optimal']     = array_values(array_filter($validated['tp_optimal'] ?? [], fn($v) => trim($v) !== ''));
        $validated['tp_peningkatan'] = array_values(array_filter($validated['tp_peningkatan'] ?? [], fn($v) => trim($v) !== ''));

        $mapel = MataPelajaran::create($validated);

        alert()->html(
            'Berhasil!',
            'Mata Pelajaran <strong>' . e($mapel->nama_mata_pelajaran) . '</strong> berhasil ditambahkan.',
            'success'
        );

        return redirect()->route('matapelajaran.index');
    }

    public function show(MataPelajaran $matapelajaran)
    {
        $matapelajaran->load(['kelas', 'tahunAjaran', 'semester', 'guru.pegawai']);
        return view('pages.mata-pelajaran.show', compact('matapelajaran'));
    }

    public function edit(MataPelajaran $matapelajaran)
    {
        $kelas = \App\Models\Kelas::orderBy('nama_kelas', 'asc')->get();
        $tahunAjarans = \App\Models\TahunAjaran::all();
        $semesters = \App\Models\Semester::all();
        $gurus = \App\Models\Guru::with('pegawai')->get();
        return view('pages.mata-pelajaran.edit', compact('matapelajaran', 'kelas', 'tahunAjarans', 'semesters', 'gurus'));
    }

    public function update(MataPelajaranRequest $request, MataPelajaran $matapelajaran)
    {
        $validated = $request->validated();

        // Filter item TP yang kosong
        $validated['tp_optimal']     = array_values(array_filter($validated['tp_optimal'] ?? [], fn($v) => trim($v) !== ''));
        $validated['tp_peningkatan'] = array_values(array_filter($validated['tp_peningkatan'] ?? [], fn($v) => trim($v) !== ''));

        $matapelajaran->update($validated);

        alert()->html(
            'Diperbarui!',
            'Mata Pelajaran <strong>' . e($matapelajaran->nama_mata_pelajaran) . '</strong> berhasil diperbarui.',
            'success'
        );

        return redirect()->route('matapelajaran.index');
    }

    public function export()
    {
        return Excel::download(new \App\Exports\MataPelajaranExport, 'Data_Mata_Pelajaran_'.date('Ymd').'.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        Excel::import(new \App\Imports\MataPelajaranImport, $request->file('file'));

        alert()->success('Berhasil!', 'Data Mata Pelajaran berhasil diimport.');
        return redirect()->route('matapelajaran.index');
    }

    public function template()
    {
        $headers = [['Kode Mapel', 'Nama Mapel', 'KKM', 'TP yang Optimal', 'TP Yang Perlu Peningkatan']];
        $export = new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;
            public function __construct(array $data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        };
        return Excel::download($export, 'Template_Import_MataPelajaran.xlsx');
    }

    public function destroy(MataPelajaran $matapelajaran)
    {
        $nama = $matapelajaran->nama_mata_pelajaran;
        $matapelajaran->delete();

        alert()->html(
            'Dihapus!',
            'Mata Pelajaran <strong>' . e($nama) . '</strong> berhasil dihapus.',
            'success'
        );

        return redirect()->route('matapelajaran.index');
    }

    private function getWaliKelasIds($user): array
    {
        if (!$user) return [];

        $guru = $user->pegawai?->guru ?? $user->guru;
        if (!$guru && $user->pegawai_id) {
            $guru = \App\Models\Guru::where('pegawai_id', $user->pegawai_id)->first();
        }
        if (!$guru) {
            $guru = \App\Models\Guru::whereHas('pegawai', function($q) use ($user) {
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

    public function jadwal(Request $request)
    {
        $user = auth()->user();
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas');
        $isPersonal = $user && in_array($user->roles, ['siswa', 'orang tua']);
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

        if ($isPersonal && $mySiswa) {
            $pk = \App\Models\PembagianKelas::where('siswa_id', $mySiswa->id)
                ->where('tahun_ajaran_id', $selectedTa)
                ->first();
            if (!$pk) {
                $pk = \App\Models\PembagianKelas::where('siswa_id', $mySiswa->id)->latest('id')->first();
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
        } else {
            $kelas = Kelas::query()->orderBy('nama_kelas', 'asc')->get();
            $selectedKelas = $request->get('kelas_id');
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

        $matapelajaran = [];
        if ($selectedTa && $selectedSem && $selectedKelas) {
            $matapelajaran = MataPelajaran::with(['kelas', 'tahunAjaran', 'semester', 'guru.pegawai'])
                ->where('kelas_id', $selectedKelas)
                ->where('tahun_ajaran_id', $selectedTa)
                ->where('semester_id', $selectedSem)
                ->orderBy('hari_mengajar', 'asc')
                ->orderBy('jam_mengajar', 'asc')
                ->get();
        }

        $children = $this->getChildrenForParent();

        return view('pages.mata-pelajaran.jadwal', compact(
            'kelas', 'tahunAjarans', 'semesters',
            'selectedTa', 'selectedSemName', 'selectedSem', 'selectedKelas',
            'matapelajaran', 'children', 'mySiswa'
        ));
    }
}
