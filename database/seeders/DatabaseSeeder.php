<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil seeder yang sudah kita sesuaikan dengan ERD
        $this->call([
            PenggunaSeeder::class,
        ]);
    }
}