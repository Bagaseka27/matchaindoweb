<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Cabang;
use App\Models\StokBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StokController extends Controller
{
    private const SATUAN = ['pcs', 'gram', 'kg', 'ml', 'liter'];

    public function index(Request $request)
    {
        $daftarCabang = Cabang::orderBy('nama_cabang')->get();

        // Cabang dipilih dari ?cabang=, fallback ke cabang aktif pertama
        $cabangAktif = $daftarCabang->firstWhere('id_cabang', (int) $request->query('cabang'))
            ?? $daftarCabang->firstWhere('is_aktif', true)
            ?? $daftarCabang->first();

        $keyword      = trim((string) $request->query('q', ''));
        $inventaris   = collect();
        $jumlahKritis = 0;

        if ($cabangAktif) {
            $inventaris = StokBahan::query()
                ->select('stok_bahan.*')
                ->join('bahan_baku', 'bahan_baku.id_bahan', '=', 'stok_bahan.id_bahan')
                ->where('stok_bahan.id_cabang', $cabangAktif->id_cabang)
                ->when($keyword !== '', fn ($q) => $q->where('bahan_baku.nama_bahan', 'like', "%{$keyword}%"))
                ->with('bahan')
                ->orderBy('bahan_baku.nama_bahan')
                ->get();

            // Aturan sama dengan status "kritis" di model (sisa <= minimum)
            $jumlahKritis = StokBahan::where('id_cabang', $cabangAktif->id_cabang)
                ->whereColumn('jumlah_stok', '<=', 'stok_minimum')
                ->count();
        }

        return view('stok.index', compact('daftarCabang', 'cabangAktif', 'inventaris', 'jumlahKritis', 'keyword'));
    }

    public function create(Request $request)
    {
        $daftarCabang   = Cabang::orderBy('nama_cabang')->get();
        $cabangTerpilih = (int) $request->query('cabang')
            ?: $daftarCabang->firstWhere('is_aktif', true)?->id_cabang;

        return view('stok.create', [
            'daftarCabang'   => $daftarCabang,
            'cabangTerpilih' => $cabangTerpilih,
            'daftarSatuan'   => self::SATUAN,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_cabang'    => ['required', Rule::exists('cabang', 'id_cabang')],
            'nama_bahan'   => ['required', 'string', 'max:100'],
            'satuan'       => ['required', 'string', 'max:20'],
            'jumlah_stok'  => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'stok_minimum' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ], [], [
            'id_cabang'    => 'cabang',
            'nama_bahan'   => 'nama bahan',
            'jumlah_stok'  => 'sisa stok',
            'stok_minimum' => 'batas minimal',
        ]);

        $nama = trim($data['nama_bahan']);

        return DB::transaction(function () use ($data, $nama) {
            // Bahan sudah terdaftar (mis. dari cabang lain) -> pakai ulang data master-nya
            $bahan = BahanBaku::where('nama_bahan', $nama)->first();

            if ($bahan) {
                if (strcasecmp($bahan->satuan, $data['satuan']) !== 0) {
                    return back()->withInput()->withErrors([
                        'satuan' => "Bahan \"{$bahan->nama_bahan}\" sudah terdaftar dengan satuan {$bahan->satuan}.",
                    ]);
                }

                $sudahAda = StokBahan::where('id_cabang', $data['id_cabang'])
                    ->where('id_bahan', $bahan->id_bahan)
                    ->exists();

                if ($sudahAda) {
                    return back()->withInput()->withErrors([
                        'nama_bahan' => 'Bahan ini sudah ada di cabang tersebut. Gunakan menu edit untuk mengubah stoknya.',
                    ]);
                }
            } else {
                $bahan = BahanBaku::create(['nama_bahan' => $nama, 'satuan' => $data['satuan']]);
            }

            StokBahan::create([
                'id_cabang'       => $data['id_cabang'],
                'id_bahan'        => $bahan->id_bahan,
                'jumlah_stok'     => $data['jumlah_stok'],
                'stok_minimum'    => $data['stok_minimum'],
                'diperbarui_pada' => now(),
            ]);

            if ((float) $data['jumlah_stok'] > 0) {
                $this->catatRiwayat((int) $data['id_cabang'], $bahan->id_bahan, 'masuk', (float) $data['jumlah_stok']);
            }

            return redirect()
                ->route('stok.index', ['cabang' => $data['id_cabang']])
                ->with('success', "Bahan \"{$bahan->nama_bahan}\" berhasil ditambahkan.");
        });
    }

    public function edit(int $cabang, int $bahan)
    {
        $stok = StokBahan::with(['bahan', 'cabang'])
            ->where('id_cabang', $cabang)
            ->where('id_bahan', $bahan)
            ->firstOrFail();

        return view('stok.edit', [
            'stok'         => $stok,
            'daftarSatuan' => array_values(array_unique(array_merge(self::SATUAN, [$stok->bahan->satuan]))),
        ]);
    }

    public function update(Request $request, int $cabang, int $bahan)
    {
        $data = $request->validate([
            'nama_bahan'   => ['required', 'string', 'max:100', Rule::unique('bahan_baku', 'nama_bahan')->ignore($bahan, 'id_bahan')],
            'satuan'       => ['required', 'string', 'max:20'],
            'jumlah_stok'  => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'stok_minimum' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ], [], [
            'nama_bahan'   => 'nama bahan',
            'jumlah_stok'  => 'sisa stok',
            'stok_minimum' => 'batas minimal',
        ]);

        DB::transaction(function () use ($data, $cabang, $bahan) {
            // Kunci baris agar tidak bentrok dengan pengurangan stok dari POS
            $stok = StokBahan::where('id_cabang', $cabang)
                ->where('id_bahan', $bahan)
                ->lockForUpdate()
                ->firstOrFail();

            BahanBaku::findOrFail($bahan)->update([
                'nama_bahan' => trim($data['nama_bahan']),
                'satuan'     => $data['satuan'],
            ]);

            $selisih = round((float) $data['jumlah_stok'] - (float) $stok->jumlah_stok, 2);

            StokBahan::where('id_cabang', $cabang)
                ->where('id_bahan', $bahan)
                ->update([
                    'jumlah_stok'     => $data['jumlah_stok'],
                    'stok_minimum'    => $data['stok_minimum'],
                    'diperbarui_pada' => now(),
                ]);

            if (abs($selisih) > 0.0001) {
                $this->catatRiwayat($cabang, $bahan, 'koreksi', $selisih);
            }
        });

        return redirect()
            ->route('stok.index', ['cabang' => $cabang])
            ->with('success', 'Data bahan berhasil diperbarui.');
    }

    public function destroy(int $cabang, int $bahan)
    {
        // Hanya menghapus stok di cabang ini; data master bahan_baku & riwayat tetap aman
        $terhapus = StokBahan::where('id_cabang', $cabang)->where('id_bahan', $bahan)->delete();

        return redirect()
            ->route('stok.index', ['cabang' => $cabang])
            ->with($terhapus ? 'success' : 'error', $terhapus ? 'Bahan dihapus dari stok cabang.' : 'Data tidak ditemukan.');
    }

    private function catatRiwayat(int $idCabang, int $idBahan, string $jenis, float $jumlah): void
    {
        DB::table('riwayat_stok')->insert([
            'id_cabang'    => $idCabang,
            'id_bahan'     => $idBahan,
            'id_user'      => Auth::id(),
            'id_transaksi' => null,
            'jenis'        => $jenis,
            'jumlah'       => $jumlah,
            'waktu'        => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }
}