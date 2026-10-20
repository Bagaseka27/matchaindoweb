<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiCupStockCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_decreases_cup_stock_and_records_mutation(): void
    {
        [$user, $idCabang, $idMenu, $idCup] = $this->createFixture(8);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/transaksi/checkout', [
            'items' => [
                ['id_menu' => $idMenu, 'id_cup' => $idCup, 'jumlah' => 2],
            ],
            'metode_bayar' => 'tunai',
            'uang_diterima' => 50000,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.total_bayar', 36000)
            ->assertJsonPath('data.kembalian', 14000);

        $this->assertDatabaseHas('stok_cup', [
            'id_cabang' => $idCabang,
            'id_cup' => $idCup,
            'jumlah_stok' => 6,
        ]);
        $this->assertDatabaseHas('mutasi_cup', [
            'id_cabang' => $idCabang,
            'id_cup' => $idCup,
            'id_transaksi' => $response->json('data.id_transaksi'),
            'jenis' => 'pemakaian',
            'jumlah' => 2,
        ]);
    }

    public function test_checkout_with_insufficient_cup_stock_rolls_back_transaction(): void
    {
        [$user, $idCabang, $idMenu, $idCup] = $this->createFixture(1);
        Sanctum::actingAs($user);

        $this->postJson('/api/transaksi/checkout', [
            'items' => [
                ['id_menu' => $idMenu, 'id_cup' => $idCup, 'jumlah' => 2],
            ],
            'metode_bayar' => 'qris',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('items');

        $this->assertDatabaseHas('stok_cup', [
            'id_cabang' => $idCabang,
            'id_cup' => $idCup,
            'jumlah_stok' => 1,
        ]);
        $this->assertDatabaseCount('transaksi', 0);
        $this->assertDatabaseCount('detail_transaksi', 0);
        $this->assertDatabaseCount('mutasi_cup', 0);
    }

    public function test_checkout_rejects_cash_payment_below_total(): void
    {
        [$user, $idCabang, $idMenu, $idCup] = $this->createFixture(8);
        Sanctum::actingAs($user);

        $this->postJson('/api/transaksi/checkout', [
            'items' => [
                ['id_menu' => $idMenu, 'id_cup' => $idCup, 'jumlah' => 1],
            ],
            'metode_bayar' => 'tunai',
            'uang_diterima' => 10000,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('uang_diterima');

        $this->assertDatabaseHas('stok_cup', [
            'id_cabang' => $idCabang,
            'id_cup' => $idCup,
            'jumlah_stok' => 8,
        ]);
        $this->assertDatabaseCount('transaksi', 0);
    }

    public function test_non_cup_menu_does_not_consume_cup_stock(): void
    {
        [$user, , $idMenu] = $this->createFixture(0);
        $idCup = DB::table('jenis_cup')->insertGetId([
            'nama_cup' => 'Tanpa Cup',
            'volume_ml' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('harga_menu')->insert([
            'id_menu' => $idMenu,
            'id_cup' => $idCup,
            'harga' => 15000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->postJson('/api/transaksi/checkout', [
            'items' => [
                ['id_menu' => $idMenu, 'id_cup' => $idCup, 'jumlah' => 1],
            ],
            'metode_bayar' => 'qris',
        ])->assertCreated()
            ->assertJsonPath('data.total_bayar', 15000);

        $this->assertDatabaseCount('mutasi_cup', 0);
    }

    public function test_barista_cup_stock_endpoint_is_limited_to_their_branch(): void
    {
        [$user, $idCabang, , $idCup] = $this->createFixture(8);
        $idCabangLain = DB::table('cabang')->insertGetId([
            'nama_cabang' => 'Cabang Lain',
            'alamat' => 'Alamat',
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('stok_cup')->insert([
            'id_cabang' => $idCabangLain,
            'id_cup' => $idCup,
            'jumlah_stok' => 100,
            'stok_minimum' => 0,
            'diperbarui_pada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/stok/cup?cabang={$idCabangLain}")
            ->assertOk()
            ->assertJsonPath('data.0.jumlah_stok', 8);
    }

    public function test_barista_without_branch_cannot_select_another_branch_for_stock(): void
    {
        [$user, $idCabang] = $this->createFixture(8);
        $user->id_cabang = null;
        $user->save();
        Sanctum::actingAs($user);

        $this->getJson("/api/stok/cup?cabang={$idCabang}")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Cabang belum dipilih.');
    }

    public function test_menu_endpoint_includes_cup_prices_for_mobile_checkout(): void
    {
        [$user, , $idMenu, $idCup] = $this->createFixture(8);
        Sanctum::actingAs($user);

        $this->getJson('/api/menus')
            ->assertOk()
            ->assertJsonPath('data.0.id_menu', $idMenu)
            ->assertJsonPath('data.0.harga.0.id_cup', $idCup)
            ->assertJsonPath('data.0.harga.0.harga', 18000);
    }

    private function createFixture(int $jumlahStok): array
    {
        $idCabang = DB::table('cabang')->insertGetId([
            'nama_cabang' => 'Cabang Test',
            'alamat' => 'Alamat',
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::create([
            'id_cabang' => $idCabang,
            'nama' => 'Barista Test',
            'username' => 'barista-test',
            'password' => 'password',
            'role' => 'barista',
            'is_aktif' => true,
        ]);

        $idMenu = DB::table('menu')->insertGetId([
            'nama_menu' => 'Matcha Latte',
            'kategori' => 'Matcha',
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $idCup = DB::table('jenis_cup')->insertGetId([
            'nama_cup' => 'Medium',
            'volume_ml' => 350,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('harga_menu')->insert([
            'id_menu' => $idMenu,
            'id_cup' => $idCup,
            'harga' => 18000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('stok_cup')->insert([
            'id_cabang' => $idCabang,
            'id_cup' => $idCup,
            'jumlah_stok' => $jumlahStok,
            'stok_minimum' => 0,
            'diperbarui_pada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$user, $idCabang, $idMenu, $idCup];
    }
}
