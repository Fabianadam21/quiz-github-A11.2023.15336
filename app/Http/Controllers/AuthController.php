<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'Email atau password salah'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'dokter') {
            return redirect()->route('dokter.dashboard');
        }

        return redirect()->route('pasien.dashboard');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'no_ktp' => ['required', 'numeric', 'unique:users,no_ktp'],
            'no_hp' => ['required', 'numeric'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $lastPasien = User::where('role', 'pasien')->orderByDesc('id')->first();
        $nextPatientId = $lastPasien ? $lastPasien->id + 1 : 1;
        $validated['no_rm'] = now()->format('Ym').'-'.str_pad((string) $nextPatientId, 3, '0', STR_PAD_LEFT);
        $validated['role'] = 'pasien';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
