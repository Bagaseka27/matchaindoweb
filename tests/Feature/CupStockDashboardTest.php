<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CupStockDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_restock_updates_stock_and_records_incoming_mutation(): void
    {
        $idCabang = DB::table('cabang')->insertGetId([
            'nama_cabang' => 'Cabang Dashboard',
            'alamat' => 'Alamat',
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $idCup = DB::table('jenis_cup')->insertGetId([
            'nama_cup' => 'Large',
            'volume_ml' => 500,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::create([
            'nama' => 'Pemilik Test',
            'username' => 'pemilik-dashboard-test',
            'password' => 'password',
            'role' => 'pemilik',
            'is_aktif' => true,
        ]);
        $this->actingAs($user);

        $this->post(route('stok-cup.restock'), [
            'id_cabang' => $idCabang,
            'id_cup' => $idCup,
            'jumlah' => 25,
            'stok_minimum' => 5,
        ])->assertRedirect(route('stok-cup.index'));

        $this->assertDatabaseHas('stok_cup', [
            'id_cabang' => $idCabang,
            'id_cup' => $idCup,
            'jumlah_stok' => 25,
            'stok_minimum' => 5,
        ]);
        $this->assertDatabaseHas('mutasi_cup', [
            'id_cabang' => $idCabang,
            'id_cup' => $idCup,
            'id_user' => $user->id_user,
            'jenis' => 'masuk',
            'jumlah' => 25,
        ]);
    }

    public function test_dashboard_shows_branch_stock_and_barista_transactions(): void
    {
        $idCabang = DB::table('cabang')->insertGetId([
            'nama_cabang' => 'Cabang Dashboard',
            'alamat' => 'Alamat',
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $idCup = DB::table('jenis_cup')->insertGetId([
            'nama_cup' => 'Regular',
            'volume_ml' => 350,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $idMenu = DB::table('menu')->insertGetId([
            'nama_menu' => 'Matcha Test',
            'kategori' => 'Matcha',
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $barista = User::create([
            'id_cabang' => $idCabang,
            'nama' => 'Barista Dashboard',
            'username' => 'barista-dashboard-test',
            'password' => 'password',
            'role' => 'barista',
            'is_aktif' => true,
        ]);
        $idTransaksi = DB::table('transaksi')->insertGetId([
            'id_cabang' => $idCabang,
            'id_user' => $barista->id_user,
            'urutan_pelanggan' => 1,
            'waktu_transaksi' => now(),
            'total_bayar' => 18000,
            'metode_bayar' => 'qris',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('detail_transaksi')->insert([
            'id_transaksi' => $idTransaksi,
            'id_menu' => $idMenu,
            'id_cup' => $idCup,
            'jumlah' => 1,
            'harga_satuan' => 18000,
            'subtotal' => 18000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $owner = User::create([
            'nama' => 'Pemilik Test',
            'username' => 'pemilik-transaksi-test',
            'password' => 'password',
            'role' => 'pemilik',
            'is_aktif' => true,
        ]);
        $this->actingAs($owner);

        $this->get(route('stok-cup.index'))
            ->assertOk()
            ->assertSee('Cabang Dashboard')
            ->assertSee('Regular');
        $this->get(route('transaksi.index'))
            ->assertOk()
            ->assertSee('Barista Dashboard')
            ->assertSee('Matcha Test')
            ->assertSee('qris');
    }
}
