<?php

namespace App\Http\Controllers;

use App\Models\PembagianKelas;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Http\Requests\PembagianKelasRequest;

use App\DataTables\PembagianKelasDataTable;
use Illuminate\Http\Request;

class PembagianKelasController extends Controller
{
    use \App\Traits\AuthorizeMasterData;

    public function index(PembagianKelasDataTable $dataTable)
    {
        return $dataTable->render('pages.pembagiankelas.index');
    }

    public function create()
    {
        $activeTa = TahunAjaran::where('status', 'Aktif')->first() ?? TahunAjaran::orderBy('tahun_mulai', 'desc')->first();
        $selectedTaId = request('tahun_ajaran_id') ?? ($activeTa ? $activeTa->id : null);

        $siswas = Siswa::whereDoesntHave('pembagianKelas', function($q) use ($selectedTaId) {
            if ($selectedTaId) {
                $q->where('tahun_ajaran_id', $selectedTaId);
            }
        })->orderBy('nama_siswa', 'asc')->get();

        $kelas = Kelas::with(['waliKelas.guru.pegawai'])->get();
        $tahunAjarans = TahunAjaran::orderBy('tahun_mulai', 'desc')->get();
        $waliKelasList = \App\Models\WaliKelas::with(['guru.pegawai'])->get();

        return view('pages.pembagiankelas.create', compact('siswas', 'kelas', 'tahunAjarans', 'waliKelasList', 'selectedTaId'));
    }

    public function getSiswaByTahunAjaran(Request $request)
    {
        $taId = $request->get('tahun_ajaran_id');
        $currentSiswaId = $request->get('current_siswa_id');

        $query = Siswa::query();

        if ($taId) {
            $query->where(function($q) use ($taId, $currentSiswaId) {
                $q->whereDoesntHave('pembagianKelas', function($subQ) use ($taId, $currentSiswaId) {
                    $subQ->where('tahun_ajaran_id', $taId);
                    if ($currentSiswaId) {
                        $subQ->where('siswa_id', '!=', $currentSiswaId);
                    }
                });
                if ($currentSiswaId) {
                    $q->orWhere('id', $currentSiswaId);
                }
            });
        }

        $siswas = $query->orderBy('nama_siswa', 'asc')->get(['id', 'nisn', 'nama_siswa']);

        return response()->json($siswas);
    }

    public function store(PembagianKelasRequest $request)
    {
        $validated = $request->validated();
        $pembagianKelas = PembagianKelas::create($validated);

        $siswa = Siswa::find($pembagianKelas->siswa_id);
        if ($siswa) {
            $siswa->update(['kelas_id' => $pembagianKelas->kelas_id]);
        }

        alert()->html(
            'Berhasil!',
            'Pembagian Kelas untuk Siswa <strong>' . e($siswa->nama_siswa ?? 'Siswa') . '</strong> berhasil ditambahkan.',
            'success'
        );

        return redirect()->route('pembagiankelas.index');
    }

    public function show(PembagianKelas $pembagiankela)
    {
        return redirect()->route('pembagiankelas.edit', $pembagiankela);
    }

    public function edit(PembagianKelas $pembagiankela)
    {
        $pembagiankela->load(['kelas.waliKelas.guru.pegawai', 'siswa']);
        $taId = $pembagiankela->tahun_ajaran_id;
        $currentSiswaId = $pembagiankela->siswa_id;

        $siswas = Siswa::where(function($q) use ($taId, $currentSiswaId) {
            $q->whereDoesntHave('pembagianKelas', function($subQ) use ($taId, $currentSiswaId) {
                $subQ->where('tahun_ajaran_id', $taId)->where('siswa_id', '!=', $currentSiswaId);
            })->orWhere('id', $currentSiswaId);
        })->orderBy('nama_siswa', 'asc')->get();

        $kelas = Kelas::with(['waliKelas.guru.pegawai'])->get();
        $tahunAjarans = TahunAjaran::orderBy('tahun_mulai', 'desc')->get();
        $waliKelasList = \App\Models\WaliKelas::with(['guru.pegawai'])->get();

        return view('pages.pembagiankelas.edit', [
            'pembagianKelas' => $pembagiankela,
            'siswas' => $siswas,
            'kelas' => $kelas,
            'tahunAjarans' => $tahunAjarans,
            'waliKelasList' => $waliKelasList,
        ]);
    }

    public function update(PembagianKelasRequest $request, PembagianKelas $pembagiankela)
    {
        $validated = $request->validated();
        $pembagiankela->update($validated);

        $siswa = Siswa::find($pembagiankela->siswa_id);
        if ($siswa) {
            $siswa->update(['kelas_id' => $pembagiankela->kelas_id]);
        }

        alert()->html(
            'Diperbarui!',
            'Pembagian Kelas untuk Siswa <strong>' . e($siswa->nama_siswa ?? 'Siswa') . '</strong> berhasil diperbarui.',
            'success'
        );

        return redirect()->route('pembagiankelas.index');
    }

    public function destroy(PembagianKelas $pembagiankela)
    {
        $namaSiswa = $pembagiankela->siswa->nama_siswa ?? 'Siswa';
        $pembagiankela->delete();

        alert()->html(
            'Dihapus!',
            'Pembagian Kelas untuk Siswa <strong>' . e($namaSiswa) . '</strong> berhasil dihapus.',
            'success'
        );

        return redirect()->route('pembagiankelas.index');
    }
}
