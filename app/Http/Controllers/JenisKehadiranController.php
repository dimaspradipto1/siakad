<?php

namespace App\Http\Controllers;

use App\Models\JenisKehadiran;
use App\Http\Requests\JenisKehadiranRequest;

use App\DataTables\JenisKehadiranDataTable;

class JenisKehadiranController extends Controller
{
    use \App\Traits\AuthorizeMasterData;
    public function index(JenisKehadiranDataTable $dataTable)
    {
        return $dataTable->render('pages.jenis-kehadiran.index');
    }

    public function create()
    {
        return view('pages.jenis-kehadiran.create');
    }

    public function store(JenisKehadiranRequest $request)
    {
        $validated = $request->validated();
        $jenis = JenisKehadiran::create($validated);

        alert()->html(
            'Berhasil!',
            'Jenis Kehadiran <strong>' . e($jenis->nama_kehadiran) . '</strong> berhasil ditambahkan.',
            'success'
        );

        return redirect()->route('jeniskehadiran.index');
    }

    public function show(JenisKehadiran $jeniskehadiran)
    {
        return redirect()->route('jeniskehadiran.edit', $jeniskehadiran);
    }

    public function edit(JenisKehadiran $jeniskehadiran)
    {
        return view('pages.jenis-kehadiran.edit', compact('jeniskehadiran'));
    }

    public function update(JenisKehadiranRequest $request, JenisKehadiran $jeniskehadiran)
    {
        $validated = $request->validated();
        $jeniskehadiran->update($validated);

        alert()->html(
            'Diperbarui!',
            'Jenis Kehadiran <strong>' . e($jeniskehadiran->nama_kehadiran) . '</strong> berhasil diperbarui.',
            'success'
        );

        return redirect()->route('jeniskehadiran.index');
    }

    public function destroy(JenisKehadiran $jeniskehadiran)
    {
        $nama = $jeniskehadiran->nama_kehadiran;
        $jeniskehadiran->delete();

        alert()->html(
            'Dihapus!',
            'Jenis Kehadiran <strong>' . e($nama) . '</strong> berhasil dihapus.',
            'success'
        );

        return redirect()->route('jeniskehadiran.index');
    }
}
