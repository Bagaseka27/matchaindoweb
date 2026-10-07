<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisCup;

class JenisCupController extends Controller
{
    public function index(Request $request)
    {
    $query = JenisCup::query();

    if ($request->filled('search')) {

        $search = $request->search;

        $query->where(function ($q) use ($search) {

            $q->where('nama_cup', 'like', "%{$search}%")
              ->orWhere('volume_ml', 'like', "%{$search}%");

        });
    }

    $jenisCups = $query
        ->orderBy('id_cup', 'desc')
        ->paginate(10)
        ->withQueryString();

    return view('jenis_cup.index', compact('jenisCups'));
    }

    public function create()
    {
        return view('jenis_cup.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_cup' => 'required|string|max:255|unique:jenis_cup,nama_cup',
            'volume_ml' => 'nullable|numeric|min:0',
        ], [
            'nama_cup.required' => 'Nama ukuran/cup wajib diisi.',
            'nama_cup.unique' => 'Nama ukuran/cup sudah ada.',
            'volume_ml.numeric' => 'Volume harus berupa angka.',
        ]);

        JenisCup::create([
            'nama_cup' => $request->nama_cup,
            'volume_ml' => $request->volume_ml,
        ]);

        return redirect()->route('jenis-cup.index')->with('success', 'Ukuran/Cup berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $jenisCup = JenisCup::findOrFail($id);
        return view('jenis_cup.edit', compact('jenisCup'));
    }

    public function update(Request $request, $id)
    {
    $jenisCup = JenisCup::findOrFail($id);

    $request->validate([
        'nama_cup' => 'required|string|max:255|unique:jenis_cup,nama_cup,' . $id . ',id_cup',
        'volume_ml' => 'nullable|numeric|min:0',
    ]);

    $jenisCup->update([
        'nama_cup' => $request->nama_cup,
        'volume_ml' => $request->volume_ml,
    ]);

    return redirect()->route('jenis-cup.index')
        ->with('success', 'Ukuran/Cup berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $jenisCup = JenisCup::findOrFail($id);

        // Proteksi: cegah hapus jika ukuran cup masih digunakan di penetapan harga menu
        if ($jenisCup->hargaMenus()->exists()) {
            return redirect()->back()->with('error', 'Ukuran/Cup tidak dapat dihapus karena masih digunakan pada penetapan harga menu!');
        }

        $jenisCup->delete();

        return redirect()->route('jenis-cup.index')->with('success', 'Ukuran/Cup berhasil dihapus!');
    }
}