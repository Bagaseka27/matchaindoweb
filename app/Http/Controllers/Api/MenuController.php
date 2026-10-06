<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // GET /api/menus?search=latte&kategori=Coffee&aktif=1
    public function index(Request $request)
    {
        $menus = Menu::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama_menu', 'like', "%{$s}%"))
            ->when($request->kategori, fn ($q, $k) => $q->where('kategori', $k))
            ->when($request->boolean('aktif'), fn ($q) => $q->where('is_aktif', true))
            ->orderBy('kategori')
            ->orderBy('nama_menu')
            ->get();

        return MenuResource::collection($menus); // dibungkus {"data": [...]}
    }

    // GET /api/menus/{menu}
    public function show(Menu $menu)
    {
        return new MenuResource($menu);
    }
}