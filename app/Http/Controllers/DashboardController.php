<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $idCabang = $request->input('cabang');

        // Base query transaksi hari ini
        $transaksiHariIni = DB::table('transaksi')
            ->whereDate('waktu_transaksi', today());
        
        if ($idCabang) {
            $transaksiHariIni->where('id_cabang', $idCabang);
        }

        // 1. Total pendapatan
        $totalPendapatan = (clone $transaksiHariIni)->sum('total_bayar');

        // 2. Jumlah transaksi
        $totalTransaksi = (clone $transaksiHariIni)->count();

        // 3. Cabang teramai hari ini (hanya muncul jika tidak sedang memfilter cabang tertentu)
        $cabangTeramai = null;
        if (!$idCabang) {
            $cabangTeramai = DB::table('transaksi')
                ->join('cabang', 'transaksi.id_cabang', '=', 'cabang.id_cabang')
                ->whereDate('transaksi.waktu_transaksi', today())
                ->select('cabang.nama_cabang')
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->groupBy('cabang.id_cabang', 'cabang.nama_cabang')
                ->orderByDesc('jumlah_transaksi')
                ->first();
        }

        // 4. Rincian pendapatan per cabang (untuk perbandingan 4 cabang outdoor)
        $pendapatanPerCabang = DB::table('cabang')
            ->leftJoin('transaksi', function($join) {
                $join->on('cabang.id_cabang', '=', 'transaksi.id_cabang')
                     ->whereDate('transaksi.waktu_transaksi', today());
            })
            ->select('cabang.id_cabang', 'cabang.nama_cabang')
            ->selectRaw('COALESCE(SUM(transaksi.total_bayar), 0) as total_pendapatan')
            ->selectRaw('COUNT(transaksi.id_transaksi) as total_transaksi')
            ->groupBy('cabang.id_cabang', 'cabang.nama_cabang')
            ->get();

        // 5. Grafik penjualan 7 hari terakhir (bisa difilter per cabang)
        $grafikQuery = DB::table('transaksi')
            ->selectRaw('DATE(waktu_transaksi) as tanggal')
            ->selectRaw('SUM(total_bayar) as total_pendapatan')
            ->where('waktu_transaksi', '>=', now()->subDays(6)->startOfDay());
        
        if ($idCabang) {
            $grafikQuery->where('id_cabang', $idCabang);
        }

        $grafikPenjualan = $grafikQuery->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get();

        // 6. Menu Terlaris (Top 5) per Cabang / Global
        $menuTerlarisQuery = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('menu', 'detail_transaksi.id_menu', '=', 'menu.id_menu')
            ->select('menu.nama_menu')
            ->selectRaw('SUM(detail_transaksi.jumlah) as total_terjual');

        if ($idCabang) {
            $menuTerlarisQuery->where('transaksi.id_cabang', $idCabang);
        }

        $menuTerlaris = $menuTerlarisQuery->groupBy('menu.id_menu', 'menu.nama_menu')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        // 7. Menu Paling Sepi (Bottom 5) per Cabang / Global
        $menuSepi = DB::table('menu')
            ->select('menu.id_menu', 'menu.nama_menu')
            ->get()
            ->map(function($m) use ($idCabang) {
                $total = DB::table('detail_transaksi')
                    ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                    ->where('detail_transaksi.id_menu', $m->id_menu)
                    ->when($idCabang, function($q) use ($idCabang) {
                        $q->where('transaksi.id_cabang', $idCabang);
                    })
                    ->sum('detail_transaksi.jumlah');
                $m->total_terjual = $total;
                return $m;
            })
            ->sortBy('total_terjual')
            ->take(5)
            ->values();

        // Ambil daftar cabang untuk dropdown filter
        $cabangs = DB::table('cabang')->where('is_aktif', true)->orderBy('nama_cabang')->get();

        return view('dashboard', compact(
            'totalPendapatan',
            'totalTransaksi',
            'cabangTeramai',
            'pendapatanPerCabang',
            'grafikPenjualan',
            'menuTerlaris',
            'menuSepi',
            'cabangs',
            'idCabang'
        ));
    }
}