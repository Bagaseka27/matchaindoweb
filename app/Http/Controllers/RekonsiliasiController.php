<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RekonsiliasiController extends Controller
{
    // ======================================
    // MENAMPILKAN HALAMAN REKONSILIASI QRIS
    // ======================================
    public function index(Request $request)
    {
        // Hanya pemilik yang boleh mengakses
        abort_unless(
            $request->user() &&
            $request->user()->role === 'pemilik',
            403
        );

        // Ambil seluruh cabang
        $cabang = DB::table('cabang')
            ->orderBy('nama_cabang')
            ->get();

        // Default tanggal hari ini
        $tanggalAwal = $request->input(
            'tanggal_awal',
            now()->toDateString()
        );

        $tanggalAkhir = $request->input(
            'tanggal_akhir',
            now()->toDateString()
        );

        $idCabang = $request->input('id_cabang');

        // Validasi nilai termasuk tanggal default
        Validator::make([
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'id_cabang' => $idCabang,
        ], [
            'tanggal_awal' => [
                'required',
                'date_format:Y-m-d'
            ],
            'tanggal_akhir' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:tanggal_awal'
            ],
            'id_cabang' => [
                'nullable',
                'integer',
                Rule::exists('cabang', 'id_cabang')
            ],
        ])->validate();

        // Ambil transaksi khusus QRIS
        $query = DB::table('transaksi')
            ->join(
                'cabang',
                'transaksi.id_cabang',
                '=',
                'cabang.id_cabang'
            )
            ->join(
                'pengguna',
                'transaksi.id_user',
                '=',
                'pengguna.id_user'
            )
            ->where('transaksi.metode_bayar', 'qris')
            ->whereDate(
                'transaksi.waktu_transaksi',
                '>=',
                $tanggalAwal
            )
            ->whereDate(
                'transaksi.waktu_transaksi',
                '<=',
                $tanggalAkhir
            );

        // Filter cabang jika dipilih
        if (!empty($idCabang)) {
            $query->where(
                'transaksi.id_cabang',
                $idCabang
            );
        }

        $transaksi = $query
            ->select(
                'transaksi.id_transaksi',
                'transaksi.waktu_transaksi',
                'transaksi.total_bayar',
                'cabang.nama_cabang',
                'pengguna.nama as nama_kasir'
            )
            ->orderByDesc(
                'transaksi.waktu_transaksi'
            )
            ->orderByDesc(
                'transaksi.id_transaksi'
            )
            ->get();

        // Hitung total pembayaran QRIS tercatat
        $totalSistem = $transaksi->sum('total_bayar');

        // Ambil riwayat rekonsiliasi
        $riwayat = DB::table('rekonsiliasi_qris')
            ->join(
                'cabang',
                'rekonsiliasi_qris.id_cabang',
                '=',
                'cabang.id_cabang'
            )
            ->join(
                'pengguna',
                'rekonsiliasi_qris.id_user',
                '=',
                'pengguna.id_user'
            )
            ->select(
                'rekonsiliasi_qris.*',
                'cabang.nama_cabang',
                'pengguna.nama as nama_pemeriksa'
            )
            ->orderByDesc(
                'rekonsiliasi_qris.dibuat_pada'
            )
            ->orderByDesc(
                'rekonsiliasi_qris.id_rekon'
            )
            ->limit(50)
            ->get();

        // Tampilkan halaman
        return view('rekonsiliasi.index', compact(
            'cabang',
            'tanggalAwal',
            'tanggalAkhir',
            'idCabang',
            'transaksi',
            'totalSistem',
            'riwayat'
        ));
    }

    // ======================================
    // SIMPAN HASIL REKONSILIASI QRIS
    // ======================================
    public function store(Request $request)
    {
        // Hanya pemilik yang boleh menyimpan
        abort_unless(
            $request->user() &&
            $request->user()->role === 'pemilik',
            403
        );

        // Validasi input
        $data = $request->validate([
            'id_cabang' => [
                'required',
                'integer',
                Rule::exists('cabang', 'id_cabang')
            ],
            'periode_awal' => [
                'required',
                'date_format:Y-m-d'
            ],
            'periode_akhir' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:periode_awal'
            ],
            'total_aktual' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999999.99'
            ]
        ]);

        // Hitung nominal QRIS dari transaksi kasir
        $totalSistem = DB::table('transaksi')
            ->where('id_cabang', $data['id_cabang'])
            ->where('metode_bayar', 'qris')
            ->whereDate(
                'waktu_transaksi',
                '>=',
                $data['periode_awal']
            )
            ->whereDate(
                'waktu_transaksi',
                '<=',
                $data['periode_akhir']
            )
            ->sum('total_bayar');

        // Perhitungan selisih
        $sistemSen = (int) round(
            (float) $totalSistem * 100
        );

        $aktualSen = (int) round(
            (float) $data['total_aktual'] * 100
        );

        $selisihSen = $aktualSen - $sistemSen;

        // Simpan hasil rekonsiliasi
        DB::table('rekonsiliasi_qris')->insert([
            'id_cabang' => $data['id_cabang'],
            'id_user' => $request->user()->id_user,

            'periode_awal' => $data['periode_awal'],
            'periode_akhir' => $data['periode_akhir'],

            'total_sistem' => $sistemSen / 100,
            'total_aktual' => $aktualSen / 100,
            'selisih' => $selisihSen / 100,

            'dibuat_pada' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Kembali ke halaman rekonsiliasi
        return redirect()
            ->route('rekonsiliasi.index', [
                'id_cabang' => $data['id_cabang'],
                'tanggal_awal' => $data['periode_awal'],
                'tanggal_akhir' => $data['periode_akhir']
            ])
            ->with(
                'success',
                $selisihSen === 0
                    ? 'Total QRIS cocok dengan catatan kasir.'
                    : 'Rekonsiliasi tersimpan. Terdapat selisih pembayaran.'
            );
    }
}
