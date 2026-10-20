<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CupStockController extends Controller
{
    public function index()
    {
        $stokCup = DB::table('cabang')
            ->crossJoin('jenis_cup')
            ->leftJoin('stok_cup', function ($join) {
                $join->on('stok_cup.id_cabang', '=', 'cabang.id_cabang')
                    ->on('stok_cup.id_cup', '=', 'jenis_cup.id_cup');
            })
            ->select(
                'cabang.id_cabang',
                'cabang.nama_cabang',
                'cabang.is_aktif',
                'jenis_cup.id_cup',
                'jenis_cup.nama_cup',
                'jenis_cup.volume_ml',
                DB::raw('COALESCE(stok_cup.jumlah_stok, 0) as jumlah_stok'),
                DB::raw('COALESCE(stok_cup.stok_minimum, 0) as stok_minimum')
            )
            ->orderBy('cabang.nama_cabang')
            ->orderBy('jenis_cup.nama_cup')
            ->get();

        $mutasi = DB::table('mutasi_cup')
            ->join('cabang', 'cabang.id_cabang', '=', 'mutasi_cup.id_cabang')
            ->join('jenis_cup', 'jenis_cup.id_cup', '=', 'mutasi_cup.id_cup')
            ->join('pengguna', 'pengguna.id_user', '=', 'mutasi_cup.id_user')
            ->leftJoin('transaksi', 'transaksi.id_transaksi', '=', 'mutasi_cup.id_transaksi')
            ->select(
                'mutasi_cup.*',
                'cabang.nama_cabang',
                'jenis_cup.nama_cup',
                'pengguna.nama as nama_user'
            )
            ->orderByDesc('mutasi_cup.waktu')
            ->limit(100)
            ->get();

        return view('stok_cup.index', compact('stokCup', 'mutasi'));
    }

    public function restock(Request $request)
    {
        $data = $request->validate([
            'id_cabang' => ['required', 'integer', Rule::exists('cabang', 'id_cabang')],
            'id_cup' => ['required', 'integer', Rule::exists('jenis_cup', 'id_cup')],
            'jumlah' => ['required', 'integer', 'min:1', 'max:999999999'],
            'stok_minimum' => ['nullable', 'integer', 'min:0', 'max:999999999'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $waktu = now();
            DB::table('stok_cup')->insertOrIgnore([
                'id_cabang' => $data['id_cabang'],
                'id_cup' => $data['id_cup'],
                'jumlah_stok' => 0,
                'stok_minimum' => $data['stok_minimum'] ?? 0,
                'diperbarui_pada' => $waktu,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ]);

            DB::table('stok_cup')
                ->where('id_cabang', $data['id_cabang'])
                ->where('id_cup', $data['id_cup'])
                ->lockForUpdate()
                ->increment('jumlah_stok', $data['jumlah'], [
                    'diperbarui_pada' => $waktu,
                    'updated_at' => $waktu,
                    ... (array_key_exists('stok_minimum', $data)
                        ? ['stok_minimum' => $data['stok_minimum']]
                        : []),
                ]);

            DB::table('mutasi_cup')->insert([
                'id_cabang' => $data['id_cabang'],
                'id_cup' => $data['id_cup'],
                'id_user' => $request->user()->id_user,
                'id_transaksi' => null,
                'jenis' => 'masuk',
                'jumlah' => $data['jumlah'],
                'waktu' => $waktu,
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ]);
        });

        return redirect()->route('stok-cup.index')->with('success', 'Stok cup berhasil ditambahkan.');
    }

    public function transactions()
    {
        $transaksi = DB::table('transaksi')
            ->join('cabang', 'cabang.id_cabang', '=', 'transaksi.id_cabang')
            ->join('pengguna', 'pengguna.id_user', '=', 'transaksi.id_user')
            ->select('transaksi.*', 'cabang.nama_cabang', 'pengguna.nama as nama_barista')
            ->orderByDesc('waktu_transaksi')
            ->paginate(30);

        $detail = DB::table('detail_transaksi')
            ->join('menu', 'menu.id_menu', '=', 'detail_transaksi.id_menu')
            ->join('jenis_cup', 'jenis_cup.id_cup', '=', 'detail_transaksi.id_cup')
            ->whereIn('id_transaksi', $transaksi->pluck('id_transaksi'))
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

        return view('transaksi.index', compact('transaksi', 'detail'));
    }
}
