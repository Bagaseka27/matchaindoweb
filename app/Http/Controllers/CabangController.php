<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Cabang;
use App\Models\Shift;
use App\Models\User;

class CabangController extends Controller
{
    // =====================================
    // KELOLA CABANG
    // =====================================

    public function index()
    {
        $cabangs = Cabang::all();

        return view('cabang.index', compact('cabangs'));
    }

    public function create()
    {
        return view('cabang.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_cabang' => 'required',
            'alamat' => 'required',
            'is_aktif' => 'required',
        ]);

        Cabang::create($data);

        return redirect()
            ->route('cabang.index')
            ->with('success', 'Cabang berhasil ditambahkan');
    }

    public function edit($id)
    {
        $cabang = Cabang::findOrFail($id);

        return view('cabang.edit', compact('cabang'));
    }

    public function update(Request $request, $id)
    {
        $cabang = Cabang::findOrFail($id);

        $data = $request->validate([
            'nama_cabang' => 'required',
            'alamat' => 'required',
            'is_aktif' => 'required',
        ]);

        $cabang->update($data);

        return redirect()
            ->route('cabang.index')
            ->with('success', 'Cabang berhasil diperbarui');
    }


    // =====================================
    // SHIFT LAMA PER CABANG
    // Tetap dipertahankan
    // =====================================

    public function shift($id)
    {
        $cabang = Cabang::findOrFail($id);

        $shift = Shift::where('id_cabang', $id)->get();

        return view('cabang.shift', compact('cabang', 'shift'));
    }

    public function storeShift(Request $request, $id)
    {
        $cabang = Cabang::findOrFail($id);

        $data = $request->validate([
            'nama_shift' => 'required|string|max:50',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|different:jam_mulai',
        ]);

        Shift::create([
            'id_cabang' => $cabang->id_cabang,
            'nama_shift' => $data['nama_shift'],
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
        ]);

        return redirect()
            ->route('cabang.shift', $id)
            ->with('success', 'Shift berhasil ditambahkan');
    }


    // =====================================
    // KELOLA SHIFT TERPISAH
    // =====================================

    public function indexShift()
    {
        // Ambil data shift beserta cabang
        $shifts = Shift::leftJoin(
            'cabang',
            'shift.id_cabang',
            '=',
            'cabang.id_cabang'
        )
            ->select(
                'shift.*',
                'cabang.nama_cabang'
            )
            ->orderBy('shift.id_cabang')
            ->orderBy('shift.jam_mulai')
            ->get();

        // Ambil daftar cabang
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        // Ambil barista aktif
        $baristas = User::where('role', 'barista')
            ->where('is_aktif', 1)
            ->orderBy('nama')
            ->get();

        // Ambil jadwal beserta nama barista
        $jadwals = DB::table('jadwal_shift')
            ->join(
                'pengguna',
                'jadwal_shift.id_user',
                '=',
                'pengguna.id_user'
            )
            ->join(
                'shift',
                'jadwal_shift.id_shift',
                '=',
                'shift.id_shift'
            )
            ->join(
                'cabang',
                'shift.id_cabang',
                '=',
                'cabang.id_cabang'
            )
            ->select(
                'jadwal_shift.*',
                'pengguna.nama as nama_barista',
                'shift.nama_shift',
                'shift.jam_mulai',
                'shift.jam_selesai',
                'cabang.nama_cabang'
            )
            ->orderByDesc('jadwal_shift.tanggal')
            ->orderBy('shift.jam_mulai')
            ->get();

        return view('shift.index', compact(
            'shifts',
            'cabangs',
            'baristas',
            'jadwals'
        ));
    }

    // Tambah shift baru
    public function storeShiftGlobal(Request $request)
    {
        $data = $request->validate([
            'id_cabang' => 'required|exists:cabang,id_cabang',
            'nama_shift' => 'required|string|max:50',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|different:jam_mulai',
        ]);

        Shift::create($data);

        return redirect()
            ->route('shift.index')
            ->with('success', 'Shift berhasil ditambahkan');
    }

    // Edit shift
    public function updateShift(Request $request, $id)
    {
        $data = $request->validate([
            'nama_shift' => 'required|string|max:50',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|different:jam_mulai',
        ]);

        $shift = Shift::findOrFail($id);

        $digunakanPenggajian = DB::table('penggajian')
            ->join(
                'jadwal_shift',
                'penggajian.id_jadwal',
                '=',
                'jadwal_shift.id_jadwal'
            )
            ->where('jadwal_shift.id_shift', $id)
            ->exists();

        if ($digunakanPenggajian) {
            return redirect()
                ->route('shift.index')
                ->withErrors([
                    'shift' => 'Shift sudah digunakan dalam penggajian dan tidak dapat diubah.',
                ]);
        }

        $shift->update($data);

        return redirect()
            ->route('shift.index')
            ->with('success', 'Shift berhasil diperbarui');
    }

    // Hapus shift
    public function deleteShift($id)
    {
        $shift = Shift::findOrFail($id);

        $dipakai = DB::table('jadwal_shift')
            ->where('id_shift', $id)
            ->exists();

        if ($dipakai) {
            return redirect()
                ->route('shift.index')
                ->withErrors([
                    'shift' => 'Shift tidak dapat dihapus karena sudah digunakan pada jadwal barista.',
                ]);
        }

        $shift->delete();

        return redirect()
            ->route('shift.index')
            ->with('success', 'Shift berhasil dihapus');
    }


    // =====================================
    // VALIDASI JADWAL BARISTA
    // =====================================

    private function validasiPenugasan(array $data, $kecualiId = null)
    {
        $shift = Shift::findOrFail($data['id_shift']);
        $barista = User::findOrFail($data['id_user']);

        // Pastikan barista aktif dan sesuai cabang
        if (
            $barista->role !== 'barista' ||
            !$barista->is_aktif ||
            (string) $barista->id_cabang !== (string) $shift->id_cabang
        ) {
            throw ValidationException::withMessages([
                'id_user' => 'Pilih barista aktif dari cabang yang sama dengan shift.',
            ]);
        }

        // Mencegah jadwal ganda di tanggal sama
        $duplikat = DB::table('jadwal_shift')
            ->where('id_user', $data['id_user'])
            ->where('tanggal', $data['tanggal'])
            ->when($kecualiId !== null, function ($query) use ($kecualiId) {
                $query->where('id_jadwal', '!=', $kecualiId);
            })
            ->exists();

        if ($duplikat) {
            throw ValidationException::withMessages([
                'tanggal' => 'Barista sudah memiliki jadwal pada tanggal tersebut.',
            ]);
        }
    }


    // =====================================
    // TAMBAH JADWAL BARISTA
    // =====================================

    public function storeJadwalShift(Request $request)
    {
        $data = $request->validate([
            'id_user' => 'required|exists:pengguna,id_user',
            'id_shift' => 'required|exists:shift,id_shift',
            'tanggal' => 'required|date_format:Y-m-d',
        ]);

        $this->validasiPenugasan($data);

        DB::table('jadwal_shift')->insert([
            'id_user' => $data['id_user'],
            'id_shift' => $data['id_shift'],
            'tanggal' => $data['tanggal'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('shift.index')
            ->with('success', 'Jadwal barista berhasil ditambahkan');
    }


    // =====================================
    // EDIT JADWAL BARISTA
    // =====================================

    public function updateJadwalShift(Request $request, $id)
    {
        $data = $request->validate([
            'id_user' => 'required|exists:pengguna,id_user',
            'id_shift' => 'required|exists:shift,id_shift',
            'tanggal' => 'required|date_format:Y-m-d',
        ]);

        $jadwal = DB::table('jadwal_shift')
            ->where('id_jadwal', $id)
            ->first();

        abort_if(!$jadwal, 404);

        // Lindungi data yang sudah dipakai penggajian
        $digunakan = DB::table('penggajian')
            ->where('id_jadwal', $id)
            ->exists();

        if ($digunakan) {
            return redirect()
                ->route('shift.index')
                ->withErrors([
                    'jadwal' => 'Jadwal sudah digunakan dalam penggajian dan tidak dapat diubah.',
                ]);
        }

        $this->validasiPenugasan($data, $id);

        DB::table('jadwal_shift')
            ->where('id_jadwal', $id)
            ->update([
                'id_user' => $data['id_user'],
                'id_shift' => $data['id_shift'],
                'tanggal' => $data['tanggal'],
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('shift.index')
            ->with('success', 'Jadwal barista berhasil diperbarui');
    }


    // =====================================
    // HAPUS JADWAL BARISTA
    // =====================================

    public function deleteJadwalShift($id)
    {
        $jadwal = DB::table('jadwal_shift')
            ->where('id_jadwal', $id)
            ->first();

        abort_if(!$jadwal, 404);

        // Periksa relasi penggajian
        $digunakan = DB::table('penggajian')
            ->where('id_jadwal', $id)
            ->exists();

        if ($digunakan) {
            return redirect()
                ->route('shift.index')
                ->withErrors([
                    'jadwal' => 'Jadwal tidak dapat dihapus karena sudah digunakan dalam penggajian.',
                ]);
        }

        DB::table('jadwal_shift')
            ->where('id_jadwal', $id)
            ->delete();

        return redirect()
            ->route('shift.index')
            ->with('success', 'Jadwal barista berhasil dihapus');
    }
}
