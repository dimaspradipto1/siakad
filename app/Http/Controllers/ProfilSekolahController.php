<?php

namespace App\Http\Controllers;

use App\Models\ProfilSekolah;
use App\Models\TahunAjaran;
use App\Http\Requests\ProfilSekolahRequest;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ProfilSekolahController extends Controller
{
    use \App\Traits\AuthorizeMasterData;

    /**
     * Display a listing of the resource (Renders View Profile).
     */
    public function index(Request $request = null)
    {
        $profil = ProfilSekolah::with('tahunAjaran')->first();

        if (!$profil) {
            $user = auth()->user();
            if ($user && $user->roles === 'admin') {
                return redirect()->route('profil-sekolah.create');
            } else {
                abort(404, 'Profil sekolah belum dibuat.');
            }
        }

        return view('pages.profil-sekolah.index', compact('profil'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request = null)
    {
        $profil = ProfilSekolah::first();
        if ($profil) {
            return redirect()->route('profil-sekolah.index');
        }

        $tahunAjarans = TahunAjaran::all();
        return view('pages.profil-sekolah.create', compact('tahunAjarans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProfilSekolahRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('logo_sekolah')) {
            $file = $request->file('logo_sekolah');
            $filename = time() . '_' . $file->getClientOriginalName();
            if (!file_exists(public_path('uploads/logo'))) {
                mkdir(public_path('uploads/logo'), 0755, true);
            }
            $file->move(public_path('uploads/logo'), $filename);
            $validated['logo_sekolah'] = 'uploads/logo/' . $filename;
        }

        $profil = ProfilSekolah::create($validated);

        Alert::success('Berhasil', 'Profil sekolah berhasil ditambahkan.');

        return redirect()->route('profil-sekolah.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id = null)
    {
        return redirect()->route('profil-sekolah.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProfilSekolah $profil_sekolah)
    {
        $tahunAjarans = TahunAjaran::all();
        return view('pages.profil-sekolah.edit', compact('profil_sekolah', 'tahunAjarans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProfilSekolahRequest $request, ProfilSekolah $profil_sekolah)
    {
        $validated = $request->validated();

        if ($request->hasFile('logo_sekolah')) {
            if ($profil_sekolah->logo_sekolah && file_exists(public_path($profil_sekolah->logo_sekolah))) {
                @unlink(public_path($profil_sekolah->logo_sekolah));
            }

            $file = $request->file('logo_sekolah');
            $filename = time() . '_' . $file->getClientOriginalName();
            if (!file_exists(public_path('uploads/logo'))) {
                mkdir(public_path('uploads/logo'), 0755, true);
            }
            $file->move(public_path('uploads/logo'), $filename);
            $validated['logo_sekolah'] = 'uploads/logo/' . $filename;
        }

        $profil_sekolah->update($validated);

        Alert::success('Berhasil', 'Profil sekolah berhasil diperbarui.');

        return redirect()->route('profil-sekolah.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id = null)
    {
        $profil_sekolah = ProfilSekolah::find($id);

        if ($profil_sekolah) {
            if ($profil_sekolah->logo_sekolah && file_exists(public_path($profil_sekolah->logo_sekolah))) {
                @unlink(public_path($profil_sekolah->logo_sekolah));
            }
            $profil_sekolah->delete();
        }

        Alert::success('Berhasil', 'Profil sekolah berhasil dihapus.');

        return redirect()->route('profil-sekolah.index');
    }
}
