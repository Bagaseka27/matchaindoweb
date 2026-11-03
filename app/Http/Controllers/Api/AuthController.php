<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Mengacu ke tabel pengguna
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // API Login untuk Mobile
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        // Cek apakah username ada dan password cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Username atau password salah!'
            ], 401);
        }

        // Cek apakah akun aktif
        if (!$user->is_aktif) {
            return response()->json([
                'status' => false,
                'message' => 'Akun Anda nonaktif. Hubungi Pemilik.'
            ], 403);
        }

        // Buat token akses Sanctum
        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login Berhasil',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ], 200);
    }

    // API Logout untuk Mobile
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout Berhasil'
        ], 200);
    }
}