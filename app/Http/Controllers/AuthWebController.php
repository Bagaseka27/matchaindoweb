<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthWebController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // Cek login dan pastikan hanya role 'pemilik' yang bisa masuk ke web
        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'role' => 'pemilik', 'is_aktif' => true])) {
            $request->session()->regenerate();
            return redirect()->route('booting.dashboard');
        }

        return back()->withErrors([
            'username' => 'Kredensial tidak cocok atau Anda bukan pemilik.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}