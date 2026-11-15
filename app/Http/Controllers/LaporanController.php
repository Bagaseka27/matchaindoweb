<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    // ==========================================
    // 1. HALAMAN UTAMA LAPORAN & ANALISIS (WEB PANEL)
    // ==========================================
    public function index(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());
        $idCabang = $request->input('id_cabang');

        // Query Utama Transaksi (Rincian)
        $query = DB::table('transaksi')
            ->join('cabang', 'cabang.id_cabang', '=', 'transaksi.id_cabang')
            ->join('pengguna', 'pengguna.id_user', '=', 'transaksi.id_user')
            ->whereDate('transaksi.waktu_transaksi', '>=', $tanggalAwal)
            ->whereDate('transaksi.waktu_transaksi', '<=', $tanggalAkhir);

        if ($idCabang) {
            $query->where('transaksi.id_cabang', $idCabang);
        }

        $transaksi = (clone $query)
            ->select('transaksi.*', 'cabang.nama_cabang', 'pengguna.nama as nama_barista')
            ->orderByDesc('waktu_transaksi')
            ->get();

        // Statistik Utama Ringkasan
        $totalPendapatan = $transaksi->sum('total_bayar');
        $totalTransaksi = $transaksi->count();
        $totalTunai = $transaksi->where('metode_bayar', 'tunai')->sum('total_bayar');
        $totalQris = $transaksi->where('metode_bayar', 'qris')->sum('total_bayar');

        // Rekapitulasi Pendapatan Per Bulan (Analisis Tren)
        $rekapPerBulan = DB::table('transaksi')
            ->join('cabang', 'cabang.id_cabang', '=', 'transaksi.id_cabang')
            ->whereDate('transaksi.waktu_transaksi', '>=', $tanggalAwal)
            ->whereDate('transaksi.waktu_transaksi', '<=', $tanggalAkhir)
            ->when($idCabang, function($q) use ($idCabang) {
                $q->where('transaksi.id_cabang', $idCabang);
            })
            ->select(
                DB::raw("DATE_FORMAT(transaksi.waktu_transaksi, '%Y-%m') as bulan"),
                DB::raw("COUNT(transaksi.id_transaksi) as jumlah_transaksi"),
                DB::raw("SUM(transaksi.total_bayar) as total_pendapatan")
            )
            ->groupBy('bulan')
            ->orderBy('bulan', 'DESC')
            ->get();

        // Rekapitulasi Per Cabang Outdoor
        $rekapPerCabang = DB::table('transaksi')
            ->join('cabang', 'cabang.id_cabang', '=', 'transaksi.id_cabang')
            ->whereDate('transaksi.waktu_transaksi', '>=', $tanggalAwal)
            ->whereDate('transaksi.waktu_transaksi', '<=', $tanggalAkhir)
            ->when($idCabang, function($q) use ($idCabang) {
                $q->where('transaksi.id_cabang', $idCabang);
            })
            ->select(
                'cabang.nama_cabang',
                DB::raw("COUNT(transaksi.id_transaksi) as jumlah_transaksi"),
                DB::raw("SUM(transaksi.total_bayar) as total_pendapatan")
            )
            ->groupBy('cabang.id_cabang', 'cabang.nama_cabang')
            ->get();

        $cabangs = DB::table('cabang')->where('is_aktif', true)->orderBy('nama_cabang')->get();

        return view('laporan.index', compact(
            'transaksi',
            'totalPendapatan',
            'totalTransaksi',
            'totalTunai',
            'totalQris',
            'rekapPerBulan',
            'rekapPerCabang',
            'tanggalAwal',
            'tanggalAkhir',
            'idCabang',
            'cabangs'
        ));
    }

    // ==========================================
    // 2. HALAMAN CETAK LAPORAN & PDF (RESMI OWNER)
    // ==========================================
    public function cetakPDF(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());
        $idCabang = $request->input('id_cabang');

        $query = DB::table('transaksi')
            ->join('cabang', 'cabang.id_cabang', '=', 'transaksi.id_cabang')
            ->join('pengguna', 'pengguna.id_user', '=', 'transaksi.id_user')
            ->whereDate('transaksi.waktu_transaksi', '>=', $tanggalAwal)
            ->whereDate('transaksi.waktu_transaksi', '<=', $tanggalAkhir);

        if ($idCabang) {
            $query->where('transaksi.id_cabang', $idCabang);
            $namaCabang = DB::table('cabang')->where('id_cabang', $idCabang)->value('nama_cabang');
        } else {
            $namaCabang = 'Semua Cabang Outdoor (Konsolidasi)';
        }

        $transaksi = (clone $query)
            ->select('transaksi.*', 'cabang.nama_cabang', 'pengguna.nama as nama_barista')
            ->orderByDesc('waktu_transaksi')
            ->get();

        $totalPendapatan = $transaksi->sum('total_bayar');
        $totalTransaksi = $transaksi->count();
        $totalTunai = $transaksi->where('metode_bayar', 'tunai')->sum('total_bayar');
        $totalQris = $transaksi->where('metode_bayar', 'qris')->sum('total_bayar');

        $rekapPerBulan = DB::table('transaksi')
            ->join('cabang', 'cabang.id_cabang', '=', 'transaksi.id_cabang')
            ->whereDate('transaksi.waktu_transaksi', '>=', $tanggalAwal)
            ->whereDate('transaksi.waktu_transaksi', '<=', $tanggalAkhir)
            ->when($idCabang, function($q) use ($idCabang) {
                $q->where('transaksi.id_cabang', $idCabang);
            })
            ->select(
                DB::raw("DATE_FORMAT(transaksi.waktu_transaksi, '%Y-%m') as bulan"),
                DB::raw("COUNT(transaksi.id_transaksi) as jumlah_transaksi"),
                DB::raw("SUM(transaksi.total_bayar) as total_pendapatan")
            )
            ->groupBy('bulan')
            ->orderBy('bulan', 'DESC')
            ->get();

        $rekapPerCabang = DB::table('transaksi')
            ->join('cabang', 'cabang.id_cabang', '=', 'transaksi.id_cabang')
            ->whereDate('transaksi.waktu_transaksi', '>=', $tanggalAwal)
            ->whereDate('transaksi.waktu_transaksi', '<=', $tanggalAkhir)
            ->when($idCabang, function($q) use ($idCabang) {
                $q->where('transaksi.id_cabang', $idCabang);
            })
            ->select(
                'cabang.nama_cabang',
                DB::raw("COUNT(transaksi.id_transaksi) as jumlah_transaksi"),
                DB::raw("SUM(transaksi.total_bayar) as total_pendapatan")
            )
            ->groupBy('cabang.id_cabang', 'cabang.nama_cabang')
            ->get();

        return view('laporan.print', compact(
            'transaksi',
            'totalPendapatan',
            'totalTransaksi',
            'totalTunai',
            'totalQris',
            'rekapPerBulan',
            'rekapPerCabang',
            'tanggalAwal',
            'tanggalAkhir',
            'namaCabang'
        ));
    }

    // ==========================================
    // 3. EKSPOR LAPORAN KE FORMAT EXCEL PROFESSIONAL
    // ==========================================
    public function exportExcel(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->input('tanggal_akhir', now()->toDateString());
        $idCabang = $request->input('id_cabang');

        $query = DB::table('transaksi')
            ->join('cabang', 'cabang.id_cabang', '=', 'transaksi.id_cabang')
            ->join('pengguna', 'pengguna.id_user', '=', 'transaksi.id_user')
            ->whereDate('transaksi.waktu_transaksi', '>=', $tanggalAwal)
            ->whereDate('transaksi.waktu_transaksi', '<=', $tanggalAkhir);

        if ($idCabang) {
            $query->where('transaksi.id_cabang', $idCabang);
            $namaCabang = DB::table('cabang')->where('id_cabang', $idCabang)->value('nama_cabang');
        } else {
            $namaCabang = 'Semua Cabang Outdoor (Konsolidasi)';
        }

        $transaksi = $query->select(
            'transaksi.waktu_transaksi',
            'cabang.nama_cabang',
            'pengguna.nama as nama_barista',
            'transaksi.metode_bayar',
            'transaksi.total_bayar'
        )->orderByDesc('waktu_transaksi')->get();

        $totalPendapatan = $transaksi->sum('total_bayar');
        $filename = "Laporan_Penjualan_Matcha_Indonesia_{$tanggalAwal}_sd_{$tanggalAkhir}.xls";

        $html = '<html lang="id">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Calibri, sans-serif; }
                .brand-header {
                    background-color: #2F593E;
                    color: #FFFFFF;
                    font-size: 14px;
                    font-weight: bold;
                    text-align: center;
                    vertical-align: middle;
                    padding: 12px;
                }
                .meta-header {
                    background-color: #E8F0EC;
                    color: #2F593E;
                    font-size: 11px;
                    font-weight: bold;
                    padding: 6px 10px;
                    border: 0.5px solid #A3B899;
                }
                th { 
                    background-color: #3B7A57; 
                    color: #FFFFFF; 
                    font-weight: bold; 
                    text-align: center; 
                    vertical-align: middle; 
                    border: 0.5px solid #2F593E; 
                    padding: 10px 12px; 
                    font-size: 11px;
                    white-space: nowrap;
                }
                td { 
                    border: 0.5px solid #D3D3D3; 
                    padding: 8px 10px; 
                    font-size: 11px; 
                    vertical-align: middle;
                }
                .nowrap { white-space: nowrap; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .total-row { 
                    background-color: #E8F0EC; 
                    font-weight: bold; 
                    border-top: 1.5px solid #2F593E; 
                    border-bottom: double 3px #2F593E; 
                }
                .empty-cell {
                    border: none !important;
                    background: transparent !important;
                }
            </style>
        </head>
        <body>
            <table>
                <!-- PENGATURAN LEBAR KOLOM -->
                <colgroup>
                    <col style="width: 170px;">
                    <col style="width: 220px;">
                    <col style="width: 150px;">
                    <col style="width: 160px;">
                    <col style="width: 180px;">
                </colgroup>

                <!-- KOP HEADER KORPORAT -->
                <tr>
                    <td colspan="5" class="brand-header">MATCHA INDONESIA — LAPORAN REKAPITULASI PENJUALAN</td>
                </tr>
                <tr>
                    <td colspan="5" class="meta-header">Periode Laporan: ' . $tanggalAwal . ' s/d ' . $tanggalAkhir . '</td>
                </tr>
                <tr>
                    <td colspan="5" class="meta-header">Cakupan Cabang: ' . htmlspecialchars($namaCabang) . '</td>
                </tr>
                <tr><td colspan="5" style="border: none;"></td></tr>

                <!-- TABEL HEADER -->
                <thead>
                    <tr>
                        <th>Waktu Transaksi</th>
                        <th>Cabang</th>
                        <th>Barista</th>
                        <th>Metode Pembayaran</th>
                        <th>Total Bayar (Rp)</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($transaksi as $row) {
            $html .= '<tr>
                <td class="text-center nowrap">' . $row->waktu_transaksi . '</td>
                <td>' . htmlspecialchars($row->nama_cabang) . '</td>
                <td class="nowrap">' . htmlspecialchars($row->nama_barista) . '</td>
                <td class="text-center nowrap" style="mso-number-format:\'@\';">' . strtoupper($row->metode_bayar) . '</td>
                <td class="text-right nowrap" style="mso-number-format:\'Rp #,##0\';">' . $row->total_bayar . '</td>
            </tr>';
        }

        // Baris total hanya membungkus kolom 4 dan 5, kolom 1-3 dibiarkan kosong tanpa border agar garis hijau tidak memanjang
        $html .= '<tr>
                    <td colspan="3" class="empty-cell"></td>
                    <td class="text-right total-row">TOTAL KESELURUHAN:</td>
                    <td class="text-right total-row nowrap" style="mso-number-format:\'Rp #,##0\';">' . $totalPendapatan . '</td>
                </tr>
                </tbody>
            </table>
        </body>
        </html>';

        return response($html, 200, [
            "Content-Type" => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Expires" => "0"
        ]);
    }
}