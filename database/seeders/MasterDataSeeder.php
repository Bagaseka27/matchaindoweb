<?php

namespace Database\Seeders;

use App\Models\JenisCup;
use App\Models\Kategori;
use App\Models\Menu;
use App\Models\HargaMenu;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Isi Data Jenis Cup
        $cupRegular = JenisCup::create(['nama_cup' => 'Regular / Non-Cup', 'volume_ml' => 0]);
        $cupMedium  = JenisCup::create(['nama_cup' => 'Medium', 'volume_ml' => 350]);
        $cupLarge   = JenisCup::create(['nama_cup' => 'Large', 'volume_ml' => 500]);

        // 2. Isi Data Kategori
        $katMatcha = Kategori::create(['nama_kategori' => 'Matcha Series', 'is_aktif' => true]);
        $katCoffee = Kategori::create(['nama_kategori' => 'Coffee Series', 'is_aktif' => true]);
        $katSnack  = Kategori::create(['nama_kategori' => 'Snack & Food', 'is_aktif' => true]);

        // 3. Isi Data Menu & Harganya
        $menu1 = Menu::create([
            'nama_menu'   => 'Matcha Latte',
            'id_kategori' => $katMatcha->id_kategori,
            'is_aktif'    => true,
        ]);
        HargaMenu::create(['id_menu' => $menu1->id_menu, 'id_cup' => $cupMedium->id_cup, 'harga' => 18000]);
        HargaMenu::create(['id_menu' => $menu1->id_menu, 'id_cup' => $cupLarge->id_cup, 'harga' => 22000]);

        $menu2 = Menu::create([
            'nama_menu'   => 'Croissant',
            'id_kategori' => $katSnack->id_kategori,
            'is_aktif'    => true,
        ]);
        HargaMenu::create(['id_menu' => $menu2->id_menu, 'id_cup' => $cupRegular->id_cup, 'harga' => 15000]);
    }
}