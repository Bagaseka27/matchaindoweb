<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // pakai provider yang sama dengan login web (model pengguna yang sama)
        $provider = Auth::guard('web')->getProvider();
        $user = $provider->retrieveByCredentials(['username' => $credentials['username']]);

        if (! $user || ! $provider->validateCredentials($user, $credentials)) {
            return response()->json([
                'message' => 'Username atau kata sandi salah',
            ], 401);
        }

        if (! $user->is_aktif) {
            return response()->json([
                'message' => 'Akun tidak aktif',
            ], 403);
        }

        $token = $user->createToken('mobile-pos')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id_user'   => $user->id_user,
                'id_cabang' => $user->id_cabang,
                'nama'      => $user->nama,
                'username'  => $user->username,
                'role'      => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}