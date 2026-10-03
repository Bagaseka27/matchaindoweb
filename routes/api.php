<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    private function rules(): array
    {
        return [
            'nama_menu'         => 'required|string|max:100',
            'kategori'     => 'required|string|max:50',
            'harga'        => 'required|integer|min:0',
            'image'        => 'nullable|image|max:2048',
            'is_available' => 'nullable|boolean',
        ];
    }

    public function index(Request $request)
    {
        $menus = Menu::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama_menu', 'like', "%{$s}%"))
            ->latest()
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
        $data['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menus', 'public');
        }

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
        $data['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $data['image'] = $request->file('image')->store('menus', 'public');
        }

        $menu->update($data);

        return redirect()->route('menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }
        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu berhasil dihapus.');
    }
}
