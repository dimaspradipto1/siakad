<?php

namespace App\Http\Controllers;

use App\Models\Panduan;
use App\Http\Requests\PanduanRequest;
use App\DataTables\PanduanDataTable;
use App\Traits\AuthorizeMasterData;
use Illuminate\Http\Request;

class PanduanController extends Controller
{
    use AuthorizeMasterData;

    /**
     * Tampilkan daftar link panduan (DataTables).
     */
    public function index(PanduanDataTable $dataTable)
    {
        return $dataTable->render('pages.panduan.index');
    }

    /**
     * Tampilkan form tambah link panduan.
     */
    public function create()
    {
        $defaultRoles = Panduan::ROLES_DEFAULT;
        return view('pages.panduan.create', compact('defaultRoles'));
    }

    /**
     * Simpan link panduan baru ke database.
     */
    public function store(PanduanRequest $request)
    {
        $validated = $request->validated();

        $defaultRoles = Panduan::ROLES_DEFAULT;
        $icon = $request->filled('icon') 
            ? $request->input('icon') 
            : ($defaultRoles[$validated['role']] ?? 'bi bi-journal-bookmark');

        $judul = $request->filled('judul')
            ? $request->input('judul')
            : 'Buku Panduan ' . $validated['role'];

        $maxUrutan = Panduan::max('urutan') ?? 0;
        $urutan = $request->filled('urutan') ? $request->input('urutan') : ($maxUrutan + 1);

        Panduan::create([
            'role'        => $validated['role'],
            'judul'       => $judul,
            'icon'        => $icon,
            'link_gdrive' => $validated['link_gdrive'],
            'urutan'      => $urutan,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        alert()->html(
            'Berhasil!',
            'Link Panduan Google Drive untuk <strong>' . e($validated['role']) . '</strong> berhasil ditambahkan.',
            'success'
        );

        return redirect()->route('panduan.index');
    }

    /**
     * Tampilkan form edit link panduan.
     */
    public function edit(Panduan $panduan)
    {
        $defaultRoles = Panduan::ROLES_DEFAULT;
        return view('pages.panduan.edit', compact('panduan', 'defaultRoles'));
    }

    /**
     * Update link panduan di database.
     */
    public function update(PanduanRequest $request, Panduan $panduan)
    {
        $validated = $request->validated();

        $defaultRoles = Panduan::ROLES_DEFAULT;
        $icon = $request->filled('icon') 
            ? $request->input('icon') 
            : ($panduan->icon ?: ($defaultRoles[$validated['role']] ?? 'bi bi-journal-bookmark'));

        $judul = $request->filled('judul')
            ? $request->input('judul')
            : ($panduan->judul ?: 'Buku Panduan ' . $validated['role']);

        $panduan->update([
            'role'        => $validated['role'],
            'judul'       => $judul,
            'icon'        => $icon,
            'link_gdrive' => $validated['link_gdrive'],
            'urutan'      => $request->input('urutan', $panduan->urutan),
            'is_active'   => $request->boolean('is_active'),
        ]);

        alert()->html(
            'Diperbarui!',
            'Link Panduan Google Drive untuk <strong>' . e($panduan->role) . '</strong> berhasil diperbarui.',
            'success'
        );

        return redirect()->route('panduan.index');
    }

    /**
     * Hapus link panduan dari database.
     */
    public function destroy(Panduan $panduan)
    {
        $roleName = $panduan->role;
        $panduan->delete();

        alert()->html('Dihapus!', 'Panduan untuk <strong>' . e($roleName) . '</strong> berhasil dihapus.', 'success');

        return redirect()->route('panduan.index');
    }

    /**
     * Show method redirect ke edit.
     */
    public function show(Panduan $panduan)
    {
        return redirect()->route('panduan.edit', $panduan->id);
    }
}
