<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;

use App\DataTables\UserDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use \App\Traits\AuthorizeMasterData;
    /**
     * Tampilkan daftar pengguna (DataTables).
     */
    public function index(UserDataTable $dataTable)
    {
        return $dataTable->render('pages.user.index');
    }

    /**
     * Tampilkan form tambah pengguna.
     */
    public function create()
    {
        $roles = User::ROLES;

        return view('pages.user.create', compact('roles'));
    }

    /**
     * Simpan pengguna baru ke database.
     */
    public function store(UserRequest $request)
    {
        $validated = $request->validated();

        $rolesInput = $request->input('roles');
        $rolesStr = is_array($rolesInput) ? implode(',', array_filter($rolesInput)) : ($rolesInput ?: 'pegawai');

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'roles'     => $rolesStr,
            'is_active' => $request->boolean('is_active', true),
        ]);

        alert()->html(
            'Berhasil!',
            'Pengguna <strong>' . e($validated['name']) . '</strong> berhasil ditambahkan.',
            'success'
        );

        return redirect()->route('user.index');
    }

    /**
     * Tampilkan form edit pengguna.
     */
    public function edit(User $user)
    {
        $roles = User::ROLES;

        return view('pages.user.edit', compact('user', 'roles'));
    }

    /**
     * Update data pengguna.
     */
    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();

        $rolesInput = $request->input('roles');
        $rolesStr = is_array($rolesInput) ? implode(',', array_filter($rolesInput)) : ($rolesInput ?: 'pegawai');

        $data = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'roles'     => $rolesStr,
            'is_active' => $request->boolean('is_active'),
        ];

        // Hanya update password jika diisi
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        alert()->html(
            'Diperbarui!',
            'Data pengguna <strong>' . e($user->name) . '</strong> berhasil diperbarui.',
            'success'
        );

        return redirect()->route('user.index');
    }

    /**
     * Hapus pengguna dari database.
     */
    public function destroy(User $user)
    {
        $nama = $user->name;
        $user->delete();

        alert()->html('Dihapus!', 'Pengguna <strong>' . e($nama) . '</strong> berhasil dihapus.', 'success');

        return redirect()->route('user.index');
    }

    /**
     * show — redirect ke edit.
     */
    public function show(User $user)
    {
        return redirect()->route('user.edit', $user);
    }
}
