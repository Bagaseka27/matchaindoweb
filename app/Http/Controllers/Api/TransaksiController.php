<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StokCup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransaksiController extends Controller
{
    public function history(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['barista', 'pemilik'], true), 403);

        $idCabang = $user->role === 'barista'
            ? $user->id_cabang
            : ($user->id_cabang ?? $request->query('cabang'));
        if (! $idCabang) {
            throw ValidationException::withMessages([
                'id_cabang' => 'Cabang harus dipilih untuk melihat riwayat transaksi.',
            ]);
        }

        $transaksi = DB::table('transaksi')
        ->where('id_cabang', $user->id_cabang)
        ->where('id_user', $user->id_user)
        ->orderByDesc('waktu_transaksi')
        ->limit(100)
        ->get();

        $itemsByTransaction = DB::table('detail_transaksi')
            ->join('menu', 'menu.id_menu', '=', 'detail_transaksi.id_menu')
            ->join('jenis_cup', 'jenis_cup.id_cup', '=', 'detail_transaksi.id_cup')
            ->whereIn('detail_transaksi.id_transaksi', $transaksi->pluck('id_transaksi'))
            ->select(
                'detail_transaksi.id_transaksi',
                'menu.nama_menu',
                'jenis_cup.nama_cup',
                'detail_transaksi.jumlah',
                'detail_transaksi.harga_satuan',
                'detail_transaksi.subtotal'
            )
            ->get()
            ->groupBy('id_transaksi');

        return response()->json([
            'data' => $transaksi->map(fn ($row) => [
                'id_transaksi' => (int) $row->id_transaksi,
                'urutan_pelanggan' => (int) $row->urutan_pelanggan,
                'waktu_transaksi' => $row->waktu_transaksi,
                'total_bayar' => (float) $row->total_bayar,
                'metode_bayar' => $row->metode_bayar,
                'items' => $itemsByTransaction->get($row->id_transaksi, collect())
                    ->map(fn ($item) => [
                        'nama_menu' => $item->nama_menu,
                        'nama_cup' => $item->nama_cup,
                        'jumlah' => (int) $item->jumlah,
                        'harga_satuan' => (float) $item->harga_satuan,
                        'subtotal' => (float) $item->subtotal,
                    ])
                    ->values(),
            ])->values(),
        ]);
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'id_cabang' => ['sometimes', 'integer', 'exists:cabang,id_cabang'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.id_menu' => ['required', 'integer', 'exists:menu,id_menu'],
            'items.*.id_cup' => ['required', 'integer', 'exists:jenis_cup,id_cup'],
            'items.*.jumlah' => ['required', 'integer', 'min:1', 'max:9999'],
            'metode_bayar' => ['required', 'in:tunai,qris'],
            'uang_diterima' => ['required_if:metode_bayar,tunai', 'nullable', 'numeric', 'min:0'],
        ]);

        $user = $request->user();
        if (! in_array($user->role, ['barista', 'pemilik'], true)) {
            abort(403, 'Akun tidak diizinkan membuat transaksi.');
        }

        $idCabang = $user->id_cabang ?? ($data['id_cabang'] ?? null);
        if (! $idCabang) {
            throw ValidationException::withMessages([
                'id_cabang' => 'Cabang harus dipilih sebelum checkout.',
            ]);
        }
        if ($user->role === 'barista'
            && isset($data['id_cabang'])
            && (int) $data['id_cabang'] !== (int) $user->id_cabang) {
            abort(403, 'Barista hanya dapat bertransaksi di cabangnya.');
        }

        $idCabang = (int) $idCabang;

        $result = DB::transaction(function () use ($data, $idCabang, $user) {
            $cabang = DB::table('cabang')
                ->where('id_cabang', $idCabang)
                ->lockForUpdate()
                ->first();

            if (! $cabang || ! $cabang->is_aktif) {
                throw ValidationException::withMessages([
                    'id_cabang' => 'Cabang tidak ditemukan atau sedang tidak aktif.',
                ]);
            }

            $idCups = collect($data['items'])->pluck('id_cup')->unique()->all();
            $cups = DB::table('jenis_cup')
                ->whereIn('id_cup', $idCups)
                ->get()
                ->keyBy('id_cup');

            $lines = [];
            $kebutuhanCup = [];
            $totalSen = 0;

            foreach ($data['items'] as $item) {
                $menu = DB::table('menu')
                    ->where('id_menu', $item['id_menu'])
                    ->where('is_aktif', true)
                    ->first();
                if (! $menu) {
                    throw ValidationException::withMessages([
                        'items' => 'Menu tidak ditemukan atau sedang tidak aktif.',
                    ]);
                }

                $cup = $cups->get($item['id_cup']);
                $harga = DB::table('harga_menu')
                    ->where('id_menu', $item['id_menu'])
                    ->where('id_cup', $item['id_cup'])
                    ->value('harga');
                if (! $cup || $harga === null) {
                    throw ValidationException::withMessages([
                        'items' => "Harga menu {$menu->nama_menu} untuk ukuran cup yang dipilih tidak tersedia.",
                    ]);
                }

                $jumlah = (int) $item['jumlah'];
                $hargaSen = (int) round((float) $harga * 100);
                $subtotalSen = $hargaSen * $jumlah;
                $totalSen += $subtotalSen;

                $lines[] = [
                    'id_menu' => (int) $menu->id_menu,
                    'id_cup' => (int) $cup->id_cup,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $hargaSen / 100,
                    'subtotal' => $subtotalSen / 100,
                    'nama_menu' => $menu->nama_menu,
                    'nama_cup' => $cup->nama_cup,
                ];

                if ((int) $cup->volume_ml > 0) {
                    $kebutuhanCup[$cup->id_cup] = ($kebutuhanCup[$cup->id_cup] ?? 0) + $jumlah;
                }
            }

            $uangDiterimaSen = isset($data['uang_diterima'])
                ? (int) round((float) $data['uang_diterima'] * 100)
                : null;

            if ($data['metode_bayar'] === 'tunai' && $uangDiterimaSen < $totalSen) {
                throw ValidationException::withMessages([
                    'uang_diterima' => 'Uang diterima tidak boleh kurang dari total pembayaran.',
                ]);
            }

            $waktu = now();
            $urutan = DB::table('transaksi')
                ->where('id_cabang', $idCabang)
                ->whereDate('waktu_transaksi', $waktu->toDateString())
                ->max('urutan_pelanggan') + 1;

            $idTransaksi = DB::table('transaksi')->insertGetId([
                'id_cabang' => $idCabang,
                'id_user' => $user->id_user,
                'urutan_pelanggan' => $urutan,
                'waktu_transaksi' => $waktu,
                'total_bayar' => $totalSen / 100,
                'metode_bayar' => $data['metode_bayar'],
                'uang_diterima' => $data['metode_bayar'] === 'tunai' ? $uangDiterimaSen / 100 : null,
                'kembalian' => $data['metode_bayar'] === 'tunai' ? ($uangDiterimaSen - $totalSen) / 100 : null,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ]);

            foreach ($lines as $line) {
                DB::table('detail_transaksi')->insert([
                    'id_transaksi' => $idTransaksi,
                    'id_menu' => $line['id_menu'],
                    'id_cup' => $line['id_cup'],
                    'jumlah' => $line['jumlah'],
                    'harga_satuan' => $line['harga_satuan'],
                    'subtotal' => $line['subtotal'],
                    'created_at' => $waktu,
                    'updated_at' => $waktu,
                ]);
            }

            ksort($kebutuhanCup);
            foreach ($kebutuhanCup as $idCup => $jumlah) {
                $stok = StokCup::query()
                    ->where('id_cabang', $idCabang)
                    ->where('id_cup', $idCup)
                    ->lockForUpdate()
                    ->first();

                if (! $stok || $stok->jumlah_stok < $jumlah) {
                    $namaCup = $cups->get($idCup)->nama_cup;
                    throw ValidationException::withMessages([
                        'items' => "Stok cup {$namaCup} tidak mencukupi.",
                    ]);
                }

                DB::table('stok_cup')
                    ->where('id_cabang', $idCabang)
                    ->where('id_cup', $idCup)
                    ->update([
                        'jumlah_stok' => $stok->jumlah_stok - $jumlah,
                        'diperbarui_pada' => $waktu,
                        'updated_at' => $waktu,
                    ]);

                DB::table('mutasi_cup')->insert([
                    'id_cabang' => $idCabang,
                    'id_cup' => $idCup,
                    'id_user' => $user->id_user,
                    'id_transaksi' => $idTransaksi,
                    'jenis' => 'pemakaian',
                    'jumlah' => $jumlah,
                    'waktu' => $waktu,
                    'created_at' => $waktu,
                    'updated_at' => $waktu,
                ]);
            }

            return [
                'id_transaksi' => $idTransaksi,
                'id_cabang' => $idCabang,
                'urutan_pelanggan' => $urutan,
                'waktu_transaksi' => $waktu->toISOString(),
                'total_bayar' => $totalSen / 100,
                'metode_bayar' => $data['metode_bayar'],
                'uang_diterima' => $data['metode_bayar'] === 'tunai' ? $uangDiterimaSen / 100 : null,
                'kembalian' => $data['metode_bayar'] === 'tunai' ? ($uangDiterimaSen - $totalSen) / 100 : null,
                'items' => array_map(fn (array $line) => [
                    'id_menu' => $line['id_menu'],
                    'nama_menu' => $line['nama_menu'],
                    'id_cup' => $line['id_cup'],
                    'nama_cup' => $line['nama_cup'],
                    'jumlah' => $line['jumlah'],
                    'harga_satuan' => $line['harga_satuan'],
                    'subtotal' => $line['subtotal'],
                ], $lines),
            ];
        });

        return response()->json(['data' => $result], 201);
    }
}
