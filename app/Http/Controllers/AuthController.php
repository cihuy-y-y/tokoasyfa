<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['username' => 'Username atau password tidak valid.'])
                ->onlyInput('username');
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        // Password benar, tapi akun belum disetujui
        if ($user->status !== 'approved') {
            $pesan = $user->status === 'rejected'
                ? 'Pendaftaran akun Anda ditolak oleh owner.'
                : 'Akun Anda masih menunggu persetujuan owner.';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['username' => $pesan])->onlyInput('username');
        }

        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        // Redirect berdasarkan role
        if ($user->role === 'owner') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'kasir') {
            return redirect()->route('kasir.dashboard');
        }

        // Jika role tidak dikenali
        Auth::logout();

        return redirect()->route('login')
            ->withErrors(['username' => 'Role akun tidak dikenali.']);
        }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}