<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StokBahan;
use Illuminate\Http\Request;

class StokController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Barista terkunci ke cabangnya; pemilik bisa memilih lewat ?cabang=
        $idCabang = $user->id_cabang ?? $request->query('cabang');

        if (! $idCabang) {
            return response()->json(['message' => 'Cabang belum dipilih.'], 422);
        }

        $data = StokBahan::query()
            ->select('stok_bahan.*')
            ->join('bahan_baku', 'bahan_baku.id_bahan', '=', 'stok_bahan.id_bahan')
            ->where('stok_bahan.id_cabang', $idCabang)
            ->with('bahan')
            ->orderBy('bahan_baku.nama_bahan')
            ->get()
            ->map(fn ($s) => [
                'id_bahan'     => $s->id_bahan,
                'nama_bahan'   => $s->bahan->nama_bahan,
                'satuan'       => $s->bahan->satuan,
                'jumlah_stok'  => (int) $s->jumlah_stok,
                'stok_minimum' => (int) $s->stok_minimum,
                'status'       => $s->status, // kritis | hampir_habis | aman
            ]);

        return response()->json(['data' => $data]);
    }
}