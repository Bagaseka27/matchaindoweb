<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('cabang')->get();

        return view('user.index', compact('users'));
    }

    public function create()
    {
        $cabangs = Cabang::all();

        return view('user.create', compact('cabangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:100',
            'username'  => 'required|string|max:100|unique:pengguna,username',
            'password'  => 'required|min:6',
            'role'      => 'required|in:pemilik,barista',
            'id_cabang' => 'required_if:role,barista|nullable|exists:cabang,id_cabang',
            'is_aktif'  => 'required|boolean',
        ], $this->messages());

        User::create([
            // Pemilik tidak terikat cabang
            'id_cabang'    => $request->role === 'barista' ? $request->id_cabang : null,
            'nama'         => $request->nama,
            'username'     => $request->username,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'tarif_harian' => 0,
            'is_aktif'     => $request->is_aktif,
        ]);

        return redirect()
            ->route('user.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $user    = User::findOrFail($id);
        $cabangs = Cabang::all();

        return view('user.edit', compact('user', 'cabangs'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama'      => 'required|string|max:100',
            'username'  => 'required|string|max:100|unique:pengguna,username,' . $id . ',id_user',
            'password'  => 'nullable|min:6',
            'role'      => 'required|in:pemilik,barista',
            'id_cabang' => 'required_if:role,barista|nullable|exists:cabang,id_cabang',
            'is_aktif'  => 'required|boolean',
        ], $this->messages());

        $data = [
            'id_cabang' => $request->role === 'barista' ? $request->id_cabang : null,
            'nama'      => $request->nama,
            'username'  => $request->username,
            'role'      => $request->role,
            'is_aktif'  => $request->is_aktif,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('user.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() == $user->id_user) {
            return redirect()
                ->route('user.index')
                ->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        try {
            $user->delete();

            return redirect()
                ->route('user.index')
                ->with('success', 'Pengguna berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()
                ->route('user.index')
                ->with('error', 'Pengguna tidak bisa dihapus karena sudah punya data transaksi. Nonaktifkan saja.');
        }
    }

    private function messages(): array
    {
        return [
            'nama.required'          => 'Nama wajib diisi.',
            'username.required'      => 'Username wajib diisi.',
            'username.unique'        => 'Username sudah dipakai.',
            'password.required'      => 'Password wajib diisi.',
            'password.min'           => 'Password minimal 6 karakter.',
            'role.required'          => 'Role wajib dipilih.',
            'id_cabang.required_if'  => 'Cabang wajib dipilih untuk barista.',
            'id_cabang.exists'       => 'Cabang tidak valid.',
        ];
    }
}