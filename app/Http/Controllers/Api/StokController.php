<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisCup;
use App\Models\StokBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function index(Request $request)
    {
        // Barista terkunci ke cabangnya; pemilik bisa memilih lewat ?cabang=
        $idCabang = $this->cabangId($request);

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
                'id_bahan' => $s->id_bahan,
                'nama_bahan' => $s->bahan->nama_bahan,
                'satuan' => $s->bahan->satuan,
                'jumlah_stok' => (int) $s->jumlah_stok,
                'stok_minimum' => (int) $s->stok_minimum,
                'status' => $s->status, // kritis | hampir_habis | aman
            ]);

        return response()->json(['data' => $data]);
    }

    public function cup(Request $request)
    {
        $idCabang = $this->cabangId($request);

        if (! $idCabang) {
            return response()->json(['message' => 'Cabang belum dipilih.'], 422);
        }

        $data = JenisCup::query()
            ->leftJoin('stok_cup', function ($join) use ($idCabang) {
                $join->on('stok_cup.id_cup', '=', 'jenis_cup.id_cup')
                    ->where('stok_cup.id_cabang', '=', $idCabang);
            })
            ->orderBy('jenis_cup.nama_cup')
            ->get([
                'jenis_cup.id_cup',
                'jenis_cup.nama_cup',
                'jenis_cup.volume_ml',
                DB::raw('COALESCE(stok_cup.jumlah_stok, 0) as jumlah_stok'),
                DB::raw('COALESCE(stok_cup.stok_minimum, 0) as stok_minimum'),
            ])
            ->map(fn ($cup) => [
                'id_cup' => $cup->id_cup,
                'nama_cup' => $cup->nama_cup,
                'volume_ml' => $cup->volume_ml,
                'jumlah_stok' => (int) $cup->jumlah_stok,
                'stok_minimum' => (int) $cup->stok_minimum,
                'status' => $cup->jumlah_stok <= $cup->stok_minimum ? 'kritis' : 'aman',
            ]);

        return response()->json(['data' => $data]);
    }

    private function cabangId(Request $request): ?int
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['barista', 'pemilik'], true), 403);

        if ($user->role === 'barista' && ! $user->id_cabang) {
            return null;
        }

        return $user->id_cabang ?? ($request->query('cabang') ? (int) $request->query('cabang') : null);
    }
}
