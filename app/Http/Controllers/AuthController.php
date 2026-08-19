<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function index()
    {
        if (Auth::check()) {
            if (Auth::user()->roles === 'siswa') {
                return redirect()->route('siswa.profile');
            }
            return redirect()->route('dashboard');
        }

        return view('layouts.auth.login');
    }

    /**
     * Proses login pengguna.
     * Validasi ditangani otomatis oleh LoginRequest.
     */
    public function loginProses(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $login, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            $request->session()->forget('active_role');

            $user = Auth::user();
            $roles = $user->getRolesList();

            if (count($roles) === 1) {
                $onlyRole = $roles[0];
                $request->session()->put('active_role', $onlyRole);
                toast('Selamat datang, ' . $user->name . '! Anda login sebagai ' . ucwords($onlyRole) . '.', 'success');

                if ($onlyRole === 'siswa') {
                    return redirect()->route('siswa.profile');
                }
                return redirect()->intended(route('dashboard'));
            }

            // Jika memiliki lebih dari 1 role, arahkan ke dashboard untuk memilih mode peran
            toast('Selamat datang, ' . $user->name . '! Silakan pilih peran yang ingin Anda gunakan.', 'info');
            return redirect()->route('dashboard');
        }

        return back()
            ->withInput($request->only('login'))
            ->withErrors([
                'login' => 'Email/Username atau password yang Anda masukkan salah.',
            ]);
    }

    /**
     * Beralih tampilan menu / mode peran pengguna yang memiliki lebih dari satu role.
     */
    public function switchRole(Request $request, string $role)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($role === 'reset') {
            $request->session()->forget('active_role');
            return redirect()->route('dashboard');
        }

        $target = str_replace('-', ' ', strtolower(urldecode($role)));
        $userRoles = $user->getRolesList();

        if (!in_array($target, $userRoles)) {
            abort(403, 'Anda tidak memiliki akses untuk peran: ' . ucwords($target));
        }

        $request->session()->put('active_role', $target);

        toast('Anda sekarang masuk sebagai ' . ucwords($target) . '.', 'success');

        if ($target === 'siswa') {
            return redirect()->route('siswa.profile');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        toast('Anda berhasil keluar dari sistem. Sampai jumpa!', 'info');

        return redirect()->route('login');
    }
}
