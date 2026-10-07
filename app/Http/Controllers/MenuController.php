<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Kategori;
use App\Models\JenisCup;
use App\Models\HargaMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Menampilkan daftar menu.
     */
    public function index(Request $request)
    {
        $menus = Menu::with([
                'kategori',
                'hargaMenus.jenisCup'
            ])
            ->when($request->filled('search'), function ($query) use ($request) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->where(
                        'nama_menu',
                        'like',
                        "%{$search}%"
                    );

                    $q->orWhereHas('kategori', function ($kategori) use ($search) {

                        $kategori->where(
                            'nama_kategori',
                            'like',
                            "%{$search}%"
                        );

                    });

                });

            })
            ->orderBy('id_kategori')
            ->orderBy('nama_menu')
            ->paginate(10)
            ->withQueryString();

        return view(
            'menus.index',
            compact('menus')
        );
    }


    /**
     * Form tambah menu.
     */
    public function create()
    {
        $kategoris = Kategori::where(
                'is_aktif',
                true
            )
            ->orderBy('nama_kategori')
            ->get();

        $cups = JenisCup::orderBy(
                'id_cup'
            )
            ->get();

        return view(
            'menus.create',
            compact(
                'kategoris',
                'cups'
            )
        );
    }


    /**
     * Menyimpan menu baru.
     */
    public function store(Request $request)
    {
 

        $harga = $request->input(
            'harga',
            []
        );

        foreach ($harga as $idCup => $nilaiHarga) {

            if (
                $nilaiHarga !== null &&
                $nilaiHarga !== ''
            ) {

                $harga[$idCup] = str_replace(
                    '.',
                    '',
                    $nilaiHarga
                );

            }
        }

        $request->merge([
            'harga' => $harga
        ]);


        $request->validate([

            'nama_menu' => [
                'required',
                'string',
                'max:100',
            ],

            'id_kategori' => [
                'required',
                'exists:kategori,id_kategori',
            ],

            'is_aktif' => [
                'nullable',
                'boolean',
            ],

            'harga' => [
                'required',
                'array',
            ],

            'harga.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

        ], [

            'nama_menu.required' =>
                'Nama menu wajib diisi.',

            'id_kategori.required' =>
                'Kategori wajib dipilih.',

            'id_kategori.exists' =>
                'Kategori yang dipilih tidak valid.',

            'harga.required' =>
                'Harga menu wajib diisi.',

            'harga.*.numeric' =>
                'Harga harus berupa angka.',

            'harga.*.min' =>
                'Harga tidak boleh kurang dari 0.',

        ]);


        DB::transaction(function () use ($request) {

            /*
            |--------------------------------------------------------------------------
            | 1. Simpan menu utama
            |--------------------------------------------------------------------------
            */

            $menu = Menu::create([

                'nama_menu' =>
                    $request->nama_menu,

                'id_kategori' =>
                    $request->id_kategori,

                'is_aktif' =>
                    $request->boolean('is_aktif'),

            ]);


            /*
            |--------------------------------------------------------------------------
            | 2. Simpan harga berdasarkan cup
            |--------------------------------------------------------------------------
            */

            foreach ($request->harga as $idCup => $harga) {

                if (
                    $harga !== null &&
                    $harga !== ''
                ) {

                    HargaMenu::create([

                        'id_menu' =>
                            $menu->id_menu,

                        'id_cup' =>
                            $idCup,

                        'harga' =>
                            $harga,

                    ]);

                }

            }

        });


        return redirect()
            ->route('menus.index')
            ->with(
                'success',
                'Menu berhasil ditambahkan.'
            );
    }


    /**
     * Form edit menu.
     */
    public function edit($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Ambil menu beserta harga
        |--------------------------------------------------------------------------
        */

        $menu = Menu::with(
                'hargaMenus'
            )
            ->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Ambil kategori aktif
        |--------------------------------------------------------------------------
        */

        $kategoris = Kategori::where(
                'is_aktif',
                true
            )
            ->orderBy('nama_kategori')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Ambil semua jenis cup
        |--------------------------------------------------------------------------
        */

        $cups = JenisCup::orderBy(
                'id_cup'
            )
            ->get();



        $hargaMap = $menu->hargaMenus
            ->pluck(
                'harga',
                'id_cup'
            )
            ->toArray();


        return view(
            'menus.edit',
            compact(
                'menu',
                'kategoris',
                'cups',
                'hargaMap'
            )
        );
    }


    /**
     * Update menu.
     */
    public function update(
        Request $request,
        $id
    ) {

        $menu = Menu::findOrFail($id);

        $harga = $request->input(
            'harga',
            []
        );

        foreach ($harga as $idCup => $nilaiHarga) {

            if (
                $nilaiHarga !== null &&
                $nilaiHarga !== ''
            ) {

                $harga[$idCup] = str_replace(
                    '.',
                    '',
                    $nilaiHarga
                );

            }

        }

        $request->merge([
            'harga' => $harga
        ]);


        $request->validate([

            'nama_menu' => [
                'required',
                'string',
                'max:100',
            ],

            'id_kategori' => [
                'required',
                'exists:kategori,id_kategori',
            ],

            'is_aktif' => [
                'nullable',
                'boolean',
            ],

            'harga' => [
                'required',
                'array',
            ],

            'harga.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

        ], [

            'nama_menu.required' =>
                'Nama menu wajib diisi.',

            'id_kategori.required' =>
                'Kategori wajib dipilih.',

            'id_kategori.exists' =>
                'Kategori yang dipilih tidak valid.',

            'harga.required' =>
                'Harga menu wajib diisi.',

            'harga.*.numeric' =>
                'Harga harus berupa angka.',

            'harga.*.min' =>
                'Harga tidak boleh kurang dari 0.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Update Menu + Harga
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $request,
            $menu
        ) {

            $menu->update([

                'nama_menu' =>
                    $request->nama_menu,

                'id_kategori' =>
                    $request->id_kategori,

                'is_aktif' =>
                    $request->boolean('is_aktif'),

            ]);


            foreach (
                $request->harga as $idCup => $harga
            ) {


                if (
                    $harga !== null &&
                    $harga !== ''
                ) {


                    $sudahAda = DB::table('harga_menu')
                        ->where(
                            'id_menu',
                            $menu->id_menu
                        )
                        ->where(
                            'id_cup',
                            $idCup
                        )
                        ->exists();

                    if ($sudahAda) {

                        DB::table('harga_menu')
                            ->where(
                                'id_menu',
                                $menu->id_menu
                            )
                            ->where(
                                'id_cup',
                                $idCup
                            )
                            ->update([

                                'harga' =>
                                    $harga,

                                'updated_at' =>
                                    now(),

                            ]);

                    }

                    else {

                        DB::table('harga_menu')
                            ->insert([

                                'id_menu' =>
                                    $menu->id_menu,

                                'id_cup' =>
                                    $idCup,

                                'harga' =>
                                    $harga,

                                'created_at' =>
                                    now(),

                                'updated_at' =>
                                    now(),

                            ]);

                    }

                }


                else {

                    DB::table('harga_menu')
                        ->where(
                            'id_menu',
                            $menu->id_menu
                        )
                        ->where(
                            'id_cup',
                            $idCup
                        )
                        ->delete();

                }

            }

        });


        return redirect()
            ->route('menus.index')
            ->with(
                'success',
                'Menu berhasil diperbarui.'
            );
    }

    /**
     * Hapus menu.
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        DB::transaction(function () use ($menu) {

            DB::table('harga_menu')
                ->where(
                    'id_menu',
                    $menu->id_menu
                )
                ->delete();


            $menu->delete();

        });

        return redirect()
            ->route('menus.index')
            ->with(
                'success',
                'Menu berhasil dihapus.'
            );
    }
}