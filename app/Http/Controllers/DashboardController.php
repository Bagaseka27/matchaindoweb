<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta');

        // Filter cabang (tetap memakai parameter 'cabang')
        $idCabang = $request->input('cabang');

        // Filter periode: 'hari', 'bulan', 'tahun' (default: 'hari')
        $periode = in_array($request->input('periode'), ['hari', 'bulan', 'tahun'])
            ? $request->input('periode')
            : 'hari';

        // Deteksi otomatis nama kolom metode pembayaran pada tabel transaksi
        $kolomMetode = collect(['metode_pembayaran', 'metode_bayar', 'payment_method', 'jenis_pembayaran'])
            ->first(fn ($col) => Schema::hasColumn('transaksi', $col));

        // Tentukan rentang waktu berdasarkan filter periode aktif
        [$startDate, $endDate, $labelPeriode] = $this->getDateRange($periode, $now);

        // Base query transaksi sesuai filter periode & cabang
        $transaksiQuery = DB::table('transaksi')
            ->whereBetween('waktu_transaksi', [$startDate, $endDate]);

        if ($idCabang) {
            $transaksiQuery->where('id_cabang', $idCabang);
        }

        // 1. KPI UTAMA: Total pendapatan, jumlah transaksi, pendapatan cash, pendapatan QRIS
        $totalPendapatan = (clone $transaksiQuery)->sum('total_bayar');
        $totalTransaksi  = (clone $transaksiQuery)->count();

        if ($kolomMetode) {
            $pendapatanCash = (clone $transaksiQuery)
                ->whereIn(DB::raw("LOWER($kolomMetode)"), ['cash', 'tunai'])
                ->sum('total_bayar');
            $transaksiCash = (clone $transaksiQuery)
                ->whereIn(DB::raw("LOWER($kolomMetode)"), ['cash', 'tunai'])
                ->count();

            $pendapatanQris = (clone $transaksiQuery)
                ->where(DB::raw("LOWER($kolomMetode)"), 'qris')
                ->sum('total_bayar');
            $transaksiQris = (clone $transaksiQuery)
                ->where(DB::raw("LOWER($kolomMetode)"), 'qris')
                ->count();
        } else {
            $pendapatanCash = 0;
            $transaksiCash  = 0;
            $pendapatanQris = 0;
            $transaksiQris  = 0;
        }

        // 2. Cabang teramai sesuai periode aktif
        $cabangTeramai = null;
        if (!$idCabang) {
            $cabangTeramai = DB::table('transaksi')
                ->join('cabang', 'transaksi.id_cabang', '=', 'cabang.id_cabang')
                ->whereBetween('transaksi.waktu_transaksi', [$startDate, $endDate])
                ->select('cabang.nama_cabang')
                ->selectRaw('COUNT(*) as jumlah_transaksi')
                ->groupBy('cabang.id_cabang', 'cabang.nama_cabang')
                ->orderByDesc('jumlah_transaksi')
                ->first();
        }

        // 3. Tren Pendapatan per Hari, Bulan, dan Tahun
        $trenPendapatan = $this->getTrenPendapatan($now, $idCabang);

        // 4. Kinerja Cabang Outdoor (mengikuti filter periode aktif)
        $kinerjaQuery = DB::table('cabang')
            ->leftJoin('transaksi', function ($join) use ($startDate, $endDate) {
                $join->on('cabang.id_cabang', '=', 'transaksi.id_cabang')
                    ->whereBetween('transaksi.waktu_transaksi', [$startDate, $endDate]);
            })
            ->where('cabang.is_aktif', true)
            ->select('cabang.id_cabang', 'cabang.nama_cabang')
            ->selectRaw('COALESCE(SUM(transaksi.total_bayar), 0) as total_pendapatan')
            ->selectRaw('COUNT(transaksi.id_transaksi) as total_transaksi');

        if ($kolomMetode) {
            $kinerjaQuery
                ->selectRaw("COALESCE(SUM(CASE WHEN LOWER(transaksi.$kolomMetode) IN ('cash', 'tunai') THEN transaksi.total_bayar ELSE 0 END), 0) as pendapatan_cash")
                ->selectRaw("COALESCE(SUM(CASE WHEN LOWER(transaksi.$kolomMetode) = 'qris' THEN transaksi.total_bayar ELSE 0 END), 0) as pendapatan_qris");
        } else {
            $kinerjaQuery
                ->selectRaw('0 as pendapatan_cash')
                ->selectRaw('0 as pendapatan_qris');
        }

        $pendapatanPerCabang = $kinerjaQuery
            ->groupBy('cabang.id_cabang', 'cabang.nama_cabang')
            ->orderBy('cabang.id_cabang')
            ->get()
            ->map(function ($cab) use ($totalPendapatan) {
                $cab->kontribusi = $totalPendapatan > 0
                    ? round(($cab->total_pendapatan / $totalPendapatan) * 100, 1)
                    : 0;
                return $cab;
            });

        // 5. Grafik Pendapatan (bisa difilter hari, bulan, tahun)
        $grafikPendapatan = $this->getGrafikPendapatan($periode, $now, $idCabang);

        // 6. Menu Terlaris (Top 5) & Kurang Diminati (Bottom 5)
        $penjualanMenu = DB::table('menu')
            ->leftJoin('detail_transaksi', 'menu.id_menu', '=', 'detail_transaksi.id_menu')
            ->leftJoin('transaksi', function ($join) use ($startDate, $endDate, $idCabang) {
                $join->on('detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                    ->whereBetween('transaksi.waktu_transaksi', [$startDate, $endDate]);

                if ($idCabang) {
                    $join->where('transaksi.id_cabang', '=', $idCabang);
                }
            })
            ->select('menu.id_menu', 'menu.nama_menu')
            ->selectRaw('COALESCE(SUM(CASE WHEN transaksi.id_transaksi IS NOT NULL THEN detail_transaksi.jumlah ELSE 0 END), 0) as total_terjual')
            ->groupBy('menu.id_menu', 'menu.nama_menu')
            ->get();

        $menuTerlaris = $penjualanMenu->sortByDesc('total_terjual')->take(5)->values();
        $menuSepi     = $penjualanMenu->sortBy('total_terjual')->take(5)->values();

        // Data khusus Grafik 2 Line (Terlaris vs Kurang Diminati)
        $grafikMenu2Line = [
            'labels'       => ['Peringkat 1', 'Peringkat 2', 'Peringkat 3', 'Peringkat 4', 'Peringkat 5'],
            'dataTerlaris' => [],
            'namaTerlaris' => [],
            'dataSepi'     => [],
            'namaSepi'     => [],
        ];

        for ($i = 0; $i < 5; $i++) {
            $grafikMenu2Line['dataTerlaris'][] = isset($menuTerlaris[$i]) ? (int) $menuTerlaris[$i]->total_terjual : 0;
            $grafikMenu2Line['namaTerlaris'][] = isset($menuTerlaris[$i]) ? $menuTerlaris[$i]->nama_menu : '-';

            $grafikMenu2Line['dataSepi'][]     = isset($menuSepi[$i]) ? (int) $menuSepi[$i]->total_terjual : 0;
            $grafikMenu2Line['namaSepi'][]     = isset($menuSepi[$i]) ? $menuSepi[$i]->nama_menu : '-';
        }

        // Daftar cabang untuk dropdown filter
        $cabangs = DB::table('cabang')->where('is_aktif', true)->orderBy('nama_cabang')->get();

        $data = compact(
            'periode',
            'labelPeriode',
            'totalPendapatan',
            'totalTransaksi',
            'pendapatanCash',
            'transaksiCash',
            'pendapatanQris',
            'transaksiQris',
            'cabangTeramai',
            'trenPendapatan',
            'pendapatanPerCabang',
            'grafikPendapatan',
            'menuTerlaris',
            'menuSepi',
            'grafikMenu2Line',
            'cabangs',
            'idCabang'
        );

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('dashboard', $data);
    }

    private function getDateRange(string $periode, Carbon $now): array
    {
        return match ($periode) {
            'bulan' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                'Bulan Ini (' . $now->translatedFormat('F Y') . ')',
            ],
            'tahun' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                'Tahun Ini (' . $now->format('Y') . ')',
            ],
            default => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                'Hari Ini (' . $now->translatedFormat('d M Y') . ')',
            ],
        };
    }

    private function getTrenPendapatan(Carbon $now, $idCabang): array
    {
        $queryWithCabang = function () use ($idCabang) {
            $q = DB::table('transaksi');
            if ($idCabang) {
                $q->where('id_cabang', $idCabang);
            }
            return $q;
        };

        // Harian: Hari Ini vs Kemarin
        $hariIni = $queryWithCabang()
            ->whereBetween('waktu_transaksi', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->sum('total_bayar');
        $kemarin = $queryWithCabang()
            ->whereBetween('waktu_transaksi', [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()])
            ->sum('total_bayar');

        // Bulanan: Bulan Ini vs Bulan Lalu
        $bulanIni = $queryWithCabang()
            ->whereBetween('waktu_transaksi', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('total_bayar');
        $bulanLalu = $queryWithCabang()
            ->whereBetween('waktu_transaksi', [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()])
            ->sum('total_bayar');

        // Tahunan: Tahun Ini vs Tahun Lalu
        $tahunIni = $queryWithCabang()
            ->whereBetween('waktu_transaksi', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])
            ->sum('total_bayar');
        $tahunLalu = $queryWithCabang()
            ->whereBetween('waktu_transaksi', [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()])
            ->sum('total_bayar');

        return [
            'hari' => [
                'label'      => 'Tren Pendapatan Hari Ini',
                'nilai'      => $hariIni,
                'pembanding' => $kemarin,
                'ket'        => 'vs Kemarin',
                'persen'     => $this->hitungPersen($hariIni, $kemarin),
            ],
            'bulan' => [
                'label'      => 'Tren Pendapatan Bulan Ini',
                'nilai'      => $bulanIni,
                'pembanding' => $bulanLalu,
                'ket'        => 'vs Bulan Lalu',
                'persen'     => $this->hitungPersen($bulanIni, $bulanLalu),
            ],
            'tahun' => [
                'label'      => 'Tren Pendapatan Tahun Ini',
                'nilai'      => $tahunIni,
                'pembanding' => $tahunLalu,
                'ket'        => 'vs Tahun Lalu',
                'persen'     => $this->hitungPersen($tahunIni, $tahunLalu),
            ],
        ];
    }

    private function hitungPersen($sekarang, $sebelumnya): float
    {
        if ($sebelumnya <= 0) {
            return $sekarang > 0 ? 100.0 : 0.0;
        }
        return round((($sekarang - $sebelumnya) / $sebelumnya) * 100, 1);
    }

    private function getGrafikPendapatan(string $periode, Carbon $now, $idCabang): array
    {
        $labels = [];
        $data   = [];

        if ($periode === 'hari') {
            $judul = 'Grafik Pendapatan Harian (7 Hari Terakhir)';
            $rows  = DB::table('transaksi')
                ->selectRaw('DATE(waktu_transaksi) as tgl')
                ->selectRaw('SUM(total_bayar) as total')
                ->where('waktu_transaksi', '>=', $now->copy()->subDays(6)->startOfDay())
                ->when($idCabang, fn ($q) => $q->where('id_cabang', $idCabang))
                ->groupBy(DB::raw('DATE(waktu_transaksi)'))
                ->pluck('total', 'tgl');

            for ($i = 6; $i >= 0; $i--) {
                $d        = $now->copy()->subDays($i);
                $key      = $d->toDateString();
                $labels[] = $d->format('d M');
                $data[]   = (float) ($rows[$key] ?? 0);
            }
        } elseif ($periode === 'bulan') {
            $judul = 'Grafik Pendapatan Bulanan (' . $now->translatedFormat('F Y') . ')';
            $rows  = DB::table('transaksi')
                ->selectRaw('DAY(waktu_transaksi) as hari_ke')
                ->selectRaw('SUM(total_bayar) as total')
                ->whereYear('waktu_transaksi', $now->year)
                ->whereMonth('waktu_transaksi', $now->month)
                ->when($idCabang, fn ($q) => $q->where('id_cabang', $idCabang))
                ->groupBy(DB::raw('DAY(waktu_transaksi)'))
                ->pluck('total', 'hari_ke');

            for ($day = 1; $day <= $now->daysInMonth; $day++) {
                $labels[] = str_pad($day, 2, '0', STR_PAD_LEFT);
                $data[]   = (float) ($rows[$day] ?? 0);
            }
        } else {
            $judul = 'Grafik Pendapatan Tahunan (' . $now->format('Y') . ')';
            $rows  = DB::table('transaksi')
                ->selectRaw('MONTH(waktu_transaksi) as bulan_ke')
                ->selectRaw('SUM(total_bayar) as total')
                ->whereYear('waktu_transaksi', $now->year)
                ->when($idCabang, fn ($q) => $q->where('id_cabang', $idCabang))
                ->groupBy(DB::raw('MONTH(waktu_transaksi)'))
                ->pluck('total', 'bulan_ke');

            for ($m = 1; $m <= 12; $m++) {
                $labels[] = $now->copy()->month($m)->translatedFormat('M');
                $data[]   = (float) ($rows[$m] ?? 0);
            }
        }

        return [
            'judul'  => $judul,
            'labels' => $labels,
            'data'   => $data,
        ];
    }
}