<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $query = Kategori::query();

        if ($request->filled('search')) {
            $query->where(
                'nama_kategori',
                'like',
                '%' . $request->search . '%'
            );
        }

        $kategoris = $query
            ->orderBy('id_kategori', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('kategori.index', compact('kategoris'));
    }


    public function create()
    {
        return view('kategori.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
            'is_aktif' => 'required|boolean',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Nama kategori sudah ada.',
            'is_aktif.required' => 'Status kategori wajib dipilih.',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
            'is_aktif' => $request->is_aktif,
        ]);

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan!');
    }


    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);

        return view('kategori.edit', compact('kategori'));
    }


    public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id . ',id_kategori',
            'is_aktif' => 'required|boolean',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Nama kategori sudah ada.',
            'is_aktif.required' => 'Status kategori wajib dipilih.',
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
            'is_aktif' => $request->is_aktif,
        ]);

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori berhasil diperbarui!');
    }


    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->menus()->exists()) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih digunakan oleh beberapa menu!'
                );
        }

        $kategori->delete();

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori berhasil dihapus!');
    }
}