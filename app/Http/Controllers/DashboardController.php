<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total pendapatan semua cabang hari ini
        $totalPendapatan = DB::table('transaksi')
            ->whereDate('waktu_transaksi', today())
            ->sum('total_bayar');

        // Jumlah transaksi semua cabang hari ini
        $totalTransaksi = DB::table('transaksi')
            ->whereDate('waktu_transaksi', today())
            ->count();

        $cabangTeramai = DB::table('transaksi')
            ->join('cabang', 'transaksi.id_cabang', '=', 'cabang.id_cabang')
            ->whereDate('transaksi.waktu_transaksi', today())
            ->select(
                'cabang.nama_cabang'
            )
            ->selectRaw('COUNT(*) as jumlah_transaksi')
            ->groupBy('cabang.id_cabang', 'cabang.nama_cabang')
            ->orderByDesc('jumlah_transaksi')
            ->first();

        return view('dashboard', compact(
            'totalPendapatan',
            'totalTransaksi',
            'cabangTeramai'
        ));
    }
}
