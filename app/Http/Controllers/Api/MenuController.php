<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MenuController extends Controller
{
    // GET /api/menus?search=latte&kategori=Coffee&aktif=1
    public function index(Request $request)
    {
        $usesCategoryRelation = Schema::hasColumn('menu', 'id_kategori')
            && Schema::hasTable('kategori');
        $kategori = $request->query('kategori', $request->query('category'));

        $query = Menu::query()
            ->with(['hargaMenus.jenisCup'])
            ->when($request->search, fn ($q, $s) => $q->where('nama_menu', 'like', "%{$s}%"))
            ->when($request->boolean('aktif'), fn ($q) => $q->where('is_aktif', true));

        if ($usesCategoryRelation) {
            $query->with('kategori')
                ->when($kategori, fn ($q, $namaKategori) => $q->whereHas(
                    'kategori',
                    fn ($categoryQuery) => $categoryQuery->where('nama_kategori', $namaKategori)
                ))
                ->leftJoin('kategori as menu_kategori', 'menu_kategori.id_kategori', '=', 'menu.id_kategori')
                ->select('menu.*')
                ->orderBy('menu_kategori.nama_kategori');
        } else {
            $query->when($kategori, fn ($q, $namaKategori) => $q->where('kategori', $namaKategori))
                ->orderBy('kategori');
        }

        $menus = $query->orderBy('menu.nama_menu')->get();

        return MenuResource::collection($menus); // dibungkus {"data": [...]}
    }

    // GET /api/menus/{menu}
    public function show(Menu $menu)
    {
        $menu->load('hargaMenus.jenisCup');
        if (Schema::hasColumn('menu', 'id_kategori') && Schema::hasTable('kategori')) {
            $menu->load('kategori');
        }

        return new MenuResource($menu);
    }
}