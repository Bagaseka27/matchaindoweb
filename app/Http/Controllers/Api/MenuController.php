<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // GET /api/menus?search=latte&category=Coffee&available=1
    public function index(Request $request)
    {
        $menus = Menu::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->boolean('available'), fn ($q) => $q->where('is_available', true))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return MenuResource::collection($menus); // dibungkus {"data": [...]}
    }

    // GET /api/menus/{menu}
    public function show(Menu $menu)
    {
        return new MenuResource($menu);
    }
}

