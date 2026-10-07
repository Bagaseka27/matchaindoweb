<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return view('user.index', compact('users'));
    }

    public function create()
    {
        return view('user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:pengguna,username',
            'password' => 'required|min:6',
            'role' => 'required',
            'tarif_harian' => 'nullable|numeric',
            'is_aktif' => 'required|boolean',
        ]);

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'tarif_harian' => $request->tarif_harian ?? 0,
            'is_aktif' => $request->is_aktif,
        ]);

        return redirect()
            ->route('user.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:pengguna,username,' . $id . ',id_user',
            'password' => 'nullable|min:6',
            'role' => 'required',
            'tarif_harian' => 'nullable|numeric',
            'is_aktif' => 'required|boolean',
        ]);

        $data = [
            'nama' => $request->nama,
            'username' => $request->username,
            'role' => $request->role,
            'tarif_harian' => $request->tarif_harian ?? 0,
            'is_aktif' => $request->is_aktif,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('user.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return redirect()
            ->route('user.index')
            ->with('success', 'User berhasil dihapus.');
    }
}