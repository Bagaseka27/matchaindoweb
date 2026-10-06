<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    private function rules(): array
    {
        return [
            'nama_menu' => 'required|string|max:100',
            'kategori'  => 'required|string|max:50',
            'is_aktif'  => 'nullable|boolean',
        ];
    }

    public function index(Request $request)
    {
        $menus = Menu::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama_menu', 'like', "%{$s}%"))
            ->orderBy('kategori')
            ->orderBy('nama_menu')
            ->paginate(10)
            ->withQueryString();

        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        return view('menus.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['is_aktif'] = $request->boolean('is_aktif');

        Menu::create($data);

        return redirect()->route('menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        return view('menus.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate($this->rules());
        $data['is_aktif'] = $request->boolean('is_aktif');

        $menu->update($data);

        return redirect()->route('menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu berhasil dihapus.');
    }
}