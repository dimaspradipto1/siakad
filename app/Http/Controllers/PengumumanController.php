<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\User;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\MataPelajaran;
use App\Http\Requests\PengumumanRequest;

use App\DataTables\PengumumanDataTable;

class PengumumanController extends Controller
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

    public function index(PengumumanDataTable $dataTable)
    {
        $user = auth()->user();
        $activeRole = $user?->activeRole() ?? $user?->roles;
        $isWali = $user && ($user->roles === 'wali kelas' || $activeRole === 'wali kelas');

        if ($user && in_array($user->roles, ['siswa', 'orang tua'])) {
            $siswa = null;
            if ($user->roles === 'siswa') {
                $siswa = \App\Models\Siswa::where('user_id', $user->id)->first();
            } else {
                $ortu = \App\Models\OrangTua::where('user_id', $user->id)->first();
                if (!$ortu) {
                    $ortu = \App\Models\OrangTua::where('nama_ayah', 'like', '%' . $user->name . '%')
                        ->orWhere('nama_ibu', 'like', '%' . $user->name . '%')
                        ->first();
                }
                if ($ortu) {
                    $selectedChildId = session('selected_child_id');
                    if ($selectedChildId) {
                        $siswa = \App\Models\Siswa::where('id', $selectedChildId)->where('orang_tua_id', $ortu->id)->first();
                    }
                    if (!$siswa) {
                        $siswa = \App\Models\Siswa::where('orang_tua_id', $ortu->id)->first();
                    }
                }
            }

            if ($siswa) {
                $studentKelasIds = \App\Models\PembagianKelas::where('siswa_id', $siswa->id)
                    ->pluck('kelas_id')
                    ->toArray();
                if ($siswa->kelas_id) {
                    $studentKelasIds[] = $siswa->kelas_id;
                }
                $studentKelasIds = array_unique(array_filter($studentKelasIds));

                $kelas = \App\Models\Kelas::query()
                    ->whereIn('id', $studentKelasIds)
                    ->orderBy('nama_kelas', 'asc')
                    ->get();
            } else {
                $kelas = collect();
            }
        } elseif ($isWali) {
            $waliKelasIds = $this->getWaliKelasIds($user);
            $kelas = !empty($waliKelasIds)
                ? \App\Models\Kelas::query()->whereIn('id', $waliKelasIds)->orderBy('nama_kelas', 'asc')->get()
                : collect();
        } else {
            $kelas = \App\Models\Kelas::query()->orderBy('nama_kelas', 'asc')->get();
        }

        $tahunAjarans = \App\Models\TahunAjaran::query()->get();
        $mapels = \App\Models\MataPelajaran::query()->distinct()->orderBy('nama_mata_pelajaran', 'asc')->get(['nama_mata_pelajaran']);
        
        return $dataTable->render('pages.pengumuman.index', compact('kelas', 'tahunAjarans', 'mapels'));
    }

    public function create()
    {
        $kelas = \App\Models\Kelas::orderBy('nama_kelas', 'asc')->get();
        $tahunAjarans = \App\Models\TahunAjaran::all();
        $semesters = \App\Models\Semester::all();
        $matapelajarans = \App\Models\MataPelajaran::query()
            ->orderBy('nama_mata_pelajaran', 'asc')
            ->get()
            ->unique('nama_mata_pelajaran')
            ->values();
        return view('pages.pengumuman.create', compact('kelas', 'tahunAjarans', 'semesters', 'matapelajarans'));
    }

    public function store(PengumumanRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id(); // assign to current user
        $pengumuman = Pengumuman::create($validated);

        alert()->html(
            'Berhasil!',
            'Pengumuman <strong>' . e($pengumuman->judul) . '</strong> berhasil dipublikasikan.',
            'success'
        );

        return redirect()->route('pengumuman.index');
    }

    public function show(Pengumuman $pengumuman)
    {
        return view('pages.pengumuman.show', compact('pengumuman'));
    }

    public function edit(Pengumuman $pengumuman)
    {
        $kelas = \App\Models\Kelas::orderBy('nama_kelas', 'asc')->get();
        $tahunAjarans = \App\Models\TahunAjaran::all();
        $semesters = \App\Models\Semester::all();
        $matapelajarans = \App\Models\MataPelajaran::query()
            ->orderBy('nama_mata_pelajaran', 'asc')
            ->get()
            ->unique('nama_mata_pelajaran')
            ->values();
        return view('pages.pengumuman.edit', compact('pengumuman', 'kelas', 'tahunAjarans', 'semesters', 'matapelajarans'));
    }

    public function update(PengumumanRequest $request, Pengumuman $pengumuman)
    {
        $validated = $request->validated();
        $pengumuman->update($validated);

        alert()->html(
            'Diperbarui!',
            'Pengumuman <strong>' . e($pengumuman->judul) . '</strong> berhasil diperbarui.',
            'success'
        );

        return redirect()->route('pengumuman.index');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $judul = $pengumuman->judul;
        $pengumuman->delete();

        alert()->html(
            'Dihapus!',
            'Pengumuman <strong>' . e($judul) . '</strong> berhasil dihapus.',
            'success'
        );

        return redirect()->route('pengumuman.index');
    }
}
