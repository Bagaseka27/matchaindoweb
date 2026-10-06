<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PenggunaSeeder extends Seeder
{
    public function run()
    {
        // Buat akun Owner
        User::create([
            'nama' => 'Pak Kurniawan',
            'username' => 'owner',
            'password' => Hash::make('123456'), // Gunakan Hash::make agar aman
            'role' => 'pemilik',
            'is_aktif' => true,
        ]);

        // Buat akun Kasir/Barista untuk dites di Mobile
        User::create([
            'nama' => 'Barista Kasir',
            'username' => 'barista1',
            'password' => Hash::make('123456'),
            'role' => 'barista',
            'is_aktif' => true,
        ]);
    }
}