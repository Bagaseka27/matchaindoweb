<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Matcha Latte',  'Matcha', true],
            ['Matcha Frappe', 'Matcha', true],
            ['Cafe Latte',    'Coffee', true],
            ['Espresso',      'Coffee', false],
            ['Croissant',     'Snack',  true],
        ] as [$nama, $kategori, $aktif]) {
            Menu::create(['nama_menu' => $nama, 'kategori' => $kategori, 'is_aktif' => $aktif]);
        }
    }
}