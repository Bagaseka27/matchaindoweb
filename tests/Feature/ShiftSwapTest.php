<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShiftSwapTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_baristas_can_swap_existing_assignments_without_adding_rows(): void
    {
        [$jadwalPagi, $jadwalSore, $baristaPagi, $baristaSore, $idShiftPagi, $idShiftSore] =
            $this->createTwoAssignments();

        $this->actingAs($this->createOwner())
            ->post(route('jadwal-shift.swap', $jadwalPagi), [
                'id_jadwal_lawan' => $jadwalSore,
                'week_start' => '2026-11-23',
            ])
            ->assertRedirect(route('shift.index', ['week_start' => '2026-11-23']))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('jadwal_shift', 2);
        $this->assertDatabaseHas('jadwal_shift', [
            'id_jadwal' => $jadwalPagi,
            'id_user' => $baristaSore,
            'id_shift' => $idShiftPagi,
            'tanggal' => '2026-11-23',
        ]);
        $this->assertDatabaseHas('jadwal_shift', [
            'id_jadwal' => $jadwalSore,
            'id_user' => $baristaPagi,
            'id_shift' => $idShiftSore,
            'tanggal' => '2026-11-24',
        ]);
    }

    public function test_swap_is_rejected_if_it_would_give_a_barista_two_shifts_on_one_day(): void
    {
        [$jadwalPagi, $jadwalSore, $baristaPagi, $baristaSore] = $this->createTwoAssignments();
        $idShiftTambahan = $this->createShift();
        DB::table('jadwal_shift')->insert([
            'id_user' => $baristaSore,
            'id_shift' => $idShiftTambahan,
            'tanggal' => '2026-11-23',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->createOwner())
            ->post(route('jadwal-shift.swap', $jadwalPagi), [
                'id_jadwal_lawan' => $jadwalSore,
                'week_start' => '2026-11-23',
            ])
            ->assertSessionHasErrors('id_jadwal_lawan');

        $this->assertDatabaseHas('jadwal_shift', [
            'id_jadwal' => $jadwalPagi,
            'id_user' => $baristaPagi,
        ]);
        $this->assertDatabaseHas('jadwal_shift', [
            'id_jadwal' => $jadwalSore,
            'id_user' => $baristaSore,
        ]);
    }

    public function test_swap_is_rejected_when_either_assignment_is_used_for_payroll(): void
    {
        [$jadwalPagi, $jadwalSore, $baristaPagi] = $this->createTwoAssignments();
        DB::table('penggajian')->insert([
            'id_user' => $baristaPagi,
            'id_jadwal' => $jadwalPagi,
            'tanggal' => '2026-11-23',
            'nominal_gaji' => 100000,
            'status' => 'belum dibayar',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->createOwner())
            ->post(route('jadwal-shift.swap', $jadwalPagi), [
                'id_jadwal_lawan' => $jadwalSore,
                'week_start' => '2026-11-23',
            ])
            ->assertSessionHasErrors('id_jadwal_lawan');

        $this->assertDatabaseHas('jadwal_shift', [
            'id_jadwal' => $jadwalPagi,
            'id_user' => $baristaPagi,
        ]);
    }

    public function test_shift_management_page_displays_the_swap_action(): void
    {
        $this->createTwoAssignments();

        $this->actingAs($this->createOwner())
            ->get(route('shift.index', ['week_start' => '2026-11-23']))
            ->assertOk()
            ->assertSee('Tukar Jadwal Barista')
            ->assertSee('Tukar');
    }

    private function createTwoAssignments(): array
    {
        $idCabang = DB::table('cabang')->insertGetId([
            'nama_cabang' => 'Cabang Swap',
            'alamat' => 'Alamat',
            'is_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $baristaPagi = $this->createBarista($idCabang, 'barista-pagi-test');
        $baristaSore = $this->createBarista($idCabang, 'barista-sore-test');
        $idShiftPagi = $this->createShift($idCabang, 'Pagi');
        $idShiftSore = $this->createShift($idCabang, 'Sore');

        $jadwalPagi = $this->createAssignment($baristaPagi, $idShiftPagi, '2026-11-23');
        $jadwalSore = $this->createAssignment($baristaSore, $idShiftSore, '2026-11-24');

        return [$jadwalPagi, $jadwalSore, $baristaPagi, $baristaSore, $idShiftPagi, $idShiftSore];
    }

    private function createBarista(int $idCabang, string $username): int
    {
        return User::create([
            'id_cabang' => $idCabang,
            'nama' => $username,
            'username' => $username,
            'password' => 'password',
            'role' => 'barista',
            'is_aktif' => true,
        ])->id_user;
    }

    private function createShift(?int $idCabang = null, string $name = 'Shift Tambahan'): int
    {
        if ($idCabang === null) {
            $idCabang = DB::table('cabang')->value('id_cabang');
        }

        return DB::table('shift')->insertGetId([
            'id_cabang' => $idCabang,
            'nama_shift' => $name,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '16:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAssignment(int $idUser, int $idShift, string $date): int
    {
        return DB::table('jadwal_shift')->insertGetId([
            'id_user' => $idUser,
            'id_shift' => $idShift,
            'tanggal' => $date,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createOwner(): User
    {
        return User::create([
            'nama' => 'Pemilik Test',
            'username' => 'pemilik-'.uniqid(),
            'password' => 'password',
            'role' => 'pemilik',
            'is_aktif' => true,
        ]);
    }
}
