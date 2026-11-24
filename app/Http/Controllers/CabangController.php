<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
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
    public function destroy($id)
    {
    $cabang = Cabang::where('id_cabang', $id)->firstOrFail();

    try {
        $cabang->delete();
        return redirect()->route('cabang.index')
            ->with('success', 'Cabang berhasil dihapus.');
    } catch (\Illuminate\Database\QueryException $e) {
        // Biasanya karena cabang masih dipakai tabel lain (foreign key)
        return redirect()->route('cabang.index')
            ->with('error', 'Cabang tidak bisa dihapus karena masih dipakai data lain.');
    }
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

    public function indexShift(Request $request)
    {
        $today = Carbon::today();

        // Tentukan awal minggu (Senin). Default: Senin minggu ini, atau dari request week_start
        if ($request->filled('week_start')) {
            try {
                $startOfWeek = Carbon::parse($request->week_start)->startOfWeek(Carbon::MONDAY);
            } catch (\Exception $e) {
                $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
            }
        } elseif ($request->get('period') === 'next') {
            $startOfWeek = $today->copy()->addWeek()->startOfWeek(Carbon::MONDAY);
        } else {
            $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        }

        $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

        // Buat array 7 hari untuk periode 1 minggu ini (Senin - Minggu)
        $namaHari = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dayNum = $date->dayOfWeekIso; // 1 = Senin, 7 = Minggu
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'day_num' => $dayNum,
                'day_name' => $namaHari[$dayNum],
                'date_formatted' => $date->format('d M Y'),
                'short_date' => $date->format('d/m'),
                'is_today' => $date->isToday(),
                'is_past' => $date->isPast() && !$date->isToday(),
            ];
        }

        $prevWeek = $startOfWeek->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $startOfWeek->copy()->addWeek()->format('Y-m-d');
        $currentWeekMonday = $today->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $nextWeekMonday = $today->copy()->addWeek()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $isThisWeek = $startOfWeek->format('Y-m-d') === $currentWeekMonday;
        $isNextWeek = $startOfWeek->format('Y-m-d') === $nextWeekMonday;

        // Filter Cabang
        $cabangId = $request->get('cabang_id');
        $cabangs = Cabang::orderBy('nama_cabang')->get();

        // Ambil data master shift (beserta cabang)
        $shiftsQuery = Shift::leftJoin(
            'cabang',
            'shift.id_cabang',
            '=',
            'cabang.id_cabang'
        )
            ->select('shift.*', 'cabang.nama_cabang')
            ->orderBy('shift.id_cabang')
            ->orderBy('shift.jam_mulai');

        if ($cabangId) {
            $shiftsQuery->where('shift.id_cabang', $cabangId);
        }

        $shifts = $shiftsQuery->get();

        // Ambil barista aktif
        $baristasQuery = User::where('role', 'barista')
            ->where('is_aktif', 1)
            ->orderBy('nama');

        if ($cabangId) {
            $baristasQuery->where('id_cabang', $cabangId);
        }

        $baristas = $baristasQuery->get();
        $allBaristas = User::where('role', 'barista')->where('is_aktif', 1)->orderBy('nama')->get();

        // Ambil jadwal barista HANYA untuk 1 minggu yang dipilih
        $jadwals = DB::table('jadwal_shift')
            ->join('pengguna', 'jadwal_shift.id_user', '=', 'pengguna.id_user')
            ->join('shift', 'jadwal_shift.id_shift', '=', 'shift.id_shift')
            ->join('cabang', 'shift.id_cabang', '=', 'cabang.id_cabang')
            ->select(
                'jadwal_shift.*',
                'pengguna.nama as nama_barista',
                'shift.nama_shift',
                'shift.jam_mulai',
                'shift.jam_selesai',
                'shift.id_cabang',
                'cabang.nama_cabang'
            )
            ->whereBetween('jadwal_shift.tanggal', [
                $startOfWeek->format('Y-m-d'),
                $endOfWeek->format('Y-m-d')
            ])
            ->when($cabangId, function ($query) use ($cabangId) {
                $query->where('shift.id_cabang', $cabangId);
            })
            ->orderBy('jadwal_shift.tanggal')
            ->orderBy('shift.jam_mulai')
            ->get();

        // Cek apakah ada jadwal minggu sebelumnya yang dapat disalin
        $prevWeekStart = $startOfWeek->copy()->subWeek()->format('Y-m-d');
        $prevWeekEnd = $startOfWeek->copy()->subDays(1)->format('Y-m-d');
        $hasPrevWeekSchedule = DB::table('jadwal_shift')
            ->whereBetween('tanggal', [$prevWeekStart, $prevWeekEnd])
            ->exists();

        $totalPenugasan = $jadwals->count();

        return view('shift.index', compact(
            'shifts',
            'cabangs',
            'baristas',
            'allBaristas',
            'jadwals',
            'days',
            'startOfWeek',
            'endOfWeek',
            'prevWeek',
            'nextWeek',
            'currentWeekMonday',
            'nextWeekMonday',
            'isThisWeek',
            'isNextWeek',
            'cabangId',
            'hasPrevWeekSchedule',
            'totalPenugasan'
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
    // TAMBAH JADWAL BARISTA (HARIAN)
    // =====================================

    public function storeJadwalShift(Request $request)
    {
        $data = $request->validate([
            'id_user' => 'required|exists:pengguna,id_user',
            'id_shift' => 'required|exists:shift,id_shift',
            'tanggal' => 'required|date_format:Y-m-d',
            'week_start' => 'nullable|date_format:Y-m-d',
            'cabang_id' => 'nullable',
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
            ->route('shift.index', array_filter([
                'week_start' => $request->week_start,
                'cabang_id' => $request->cabang_id,
            ]))
            ->with('success', 'Jadwal barista berhasil ditambahkan');
    }

    // =====================================
    // TAMBAH JADWAL BARISTA (MINGGUAN / 1 MINGGU KE DEPAN)
    // =====================================

    public function storeJadwalMingguan(Request $request)
    {
        $data = $request->validate([
            'id_user' => 'required|exists:pengguna,id_user',
            'id_shift' => 'required|exists:shift,id_shift',
            'tanggal' => 'required|array|min:1',
            'tanggal.*' => 'required|date_format:Y-m-d',
            'week_start' => 'nullable|date_format:Y-m-d',
            'cabang_id' => 'nullable',
        ]);

        $shift = Shift::findOrFail($data['id_shift']);
        $barista = User::findOrFail($data['id_user']);

        // Pastikan barista aktif dan sesuai cabang shift
        if (
            $barista->role !== 'barista' ||
            !$barista->is_aktif ||
            (string) $barista->id_cabang !== (string) $shift->id_cabang
        ) {
            throw ValidationException::withMessages([
                'id_user' => 'Pilih barista aktif dari cabang yang sama dengan shift.',
            ]);
        }

        $berhasil = 0;
        $dilewati = 0;

        foreach ($data['tanggal'] as $tgl) {
            // Mencegah jadwal ganda di tanggal sama
            $duplikat = DB::table('jadwal_shift')
                ->where('id_user', $data['id_user'])
                ->where('tanggal', $tgl)
                ->exists();

            if ($duplikat) {
                $dilewati++;
                continue;
            }

            DB::table('jadwal_shift')->insert([
                'id_user' => $data['id_user'],
                'id_shift' => $data['id_shift'],
                'tanggal' => $tgl,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $berhasil++;
        }

        $redirectParams = array_filter([
            'week_start' => $request->week_start,
            'cabang_id' => $request->cabang_id,
        ]);

        if ($berhasil > 0 && $dilewati === 0) {
            return redirect()
                ->route('shift.index', $redirectParams)
                ->with('success', "Penugasan mingguan berhasil disimpan ({$berhasil} hari kerja).");
        } elseif ($berhasil > 0 && $dilewati > 0) {
            return redirect()
                ->route('shift.index', $redirectParams)
                ->with('warning', "{$berhasil} hari berhasil ditugaskan. {$dilewati} hari dilewati karena barista sudah punya jadwal di tanggal tersebut.");
        } else {
            return redirect()
                ->route('shift.index', $redirectParams)
                ->withErrors([
                    'jadwal' => 'Semua hari yang dipilih dilewati karena barista sudah memiliki jadwal di tanggal tersebut.',
                ]);
        }
    }

    // =====================================
    // SALIN JADWAL DARI MINGGU SEBELUMNYA
    // =====================================

    public function salinJadwalMingguLalu(Request $request)
    {
        $request->validate([
            'target_week_start' => 'required|date_format:Y-m-d',
            'cabang_id' => 'nullable',
        ]);

        $targetStart = Carbon::parse($request->target_week_start)->startOfWeek(Carbon::MONDAY);
        $prevStart = $targetStart->copy()->subWeek();
        $prevEnd = $prevStart->copy()->endOfWeek(Carbon::SUNDAY);

        // Ambil jadwal minggu lalu
        $query = DB::table('jadwal_shift')
            ->join('shift', 'jadwal_shift.id_shift', '=', 'shift.id_shift')
            ->select('jadwal_shift.*', 'shift.id_cabang')
            ->whereBetween('jadwal_shift.tanggal', [
                $prevStart->format('Y-m-d'),
                $prevEnd->format('Y-m-d')
            ]);

        if ($request->filled('cabang_id')) {
            $query->where('shift.id_cabang', $request->cabang_id);
        }

        $jadwalLalu = $query->get();

        $redirectParams = array_filter([
            'week_start' => $targetStart->format('Y-m-d'),
            'cabang_id' => $request->cabang_id,
        ]);

        if ($jadwalLalu->isEmpty()) {
            return redirect()
                ->route('shift.index', $redirectParams)
                ->withErrors(['salin' => 'Tidak ditemukan jadwal pada minggu sebelumnya untuk disalin.']);
        }

        $disalin = 0;
        $dilewati = 0;

        foreach ($jadwalLalu as $row) {
            $tanggalLama = Carbon::parse($row->tanggal);
            $tanggalBaru = $tanggalLama->copy()->addWeek()->format('Y-m-d');

            // Cek keaktifan barista
            $barista = DB::table('pengguna')
                ->where('id_user', $row->id_user)
                ->where('is_aktif', 1)
                ->first();

            if (!$barista) {
                $dilewati++;
                continue;
            }

            // Cek apakah tanggal baru sudah punya penugasan untuk barista ini
            $adaJadwal = DB::table('jadwal_shift')
                ->where('id_user', $row->id_user)
                ->where('tanggal', $tanggalBaru)
                ->exists();

            if ($adaJadwal) {
                $dilewati++;
                continue;
            }

            DB::table('jadwal_shift')->insert([
                'id_user' => $row->id_user,
                'id_shift' => $row->id_shift,
                'tanggal' => $tanggalBaru,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $disalin++;
        }

        return redirect()
            ->route('shift.index', $redirectParams)
            ->with('success', "Berhasil menyalin {$disalin} jadwal dari minggu sebelumnya. ({$dilewati} dilewati karena sudah ada jadwal / barista nonaktif).");
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
            'week_start' => 'nullable|date_format:Y-m-d',
            'cabang_id' => 'nullable',
        ]);

        $jadwal = DB::table('jadwal_shift')
            ->where('id_jadwal', $id)
            ->first();

        abort_if(!$jadwal, 404);

        $redirectParams = array_filter([
            'week_start' => $request->week_start,
            'cabang_id' => $request->cabang_id,
        ]);

        // Lindungi data yang sudah dipakai penggajian
        $digunakan = DB::table('penggajian')
            ->where('id_jadwal', $id)
            ->exists();

        if ($digunakan) {
            return redirect()
                ->route('shift.index', $redirectParams)
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
            ->route('shift.index', $redirectParams)
            ->with('success', 'Jadwal barista berhasil diperbarui');
    }

    public function swapJadwalShift(Request $request, $id)
    {
        $data = $request->validate([
            'id_jadwal_lawan' => ['required', 'integer', Rule::notIn([$id])],
            'week_start' => 'nullable|date_format:Y-m-d',
            'cabang_id' => 'nullable',
        ]);

        $redirectParams = array_filter([
            'week_start' => $request->week_start,
            'cabang_id' => $request->cabang_id,
        ]);

        DB::transaction(function () use ($id, $data) {
            $jadwals = DB::table('jadwal_shift')
                ->whereIn('id_jadwal', [$id, $data['id_jadwal_lawan']])
                ->orderBy('id_jadwal')
                ->lockForUpdate()
                ->get()
                ->keyBy('id_jadwal');

            if ($jadwals->count() !== 2) {
                abort(404);
            }

            $jadwal = $jadwals->get($id);
            $jadwalLawan = $jadwals->get($data['id_jadwal_lawan']);

            if ((string) $jadwal->id_user === (string) $jadwalLawan->id_user) {
                throw ValidationException::withMessages([
                    'id_jadwal_lawan' => 'Pilih jadwal milik barista yang berbeda.',
                ]);
            }

            $digunakanPenggajian = DB::table('penggajian')
                ->whereIn('id_jadwal', [$jadwal->id_jadwal, $jadwalLawan->id_jadwal])
                ->exists();

            if ($digunakanPenggajian) {
                throw ValidationException::withMessages([
                    'id_jadwal_lawan' => 'Jadwal yang sudah digunakan dalam penggajian tidak dapat ditukar.',
                ]);
            }

            $shift = Shift::whereIn('id_shift', [$jadwal->id_shift, $jadwalLawan->id_shift])
                ->get()
                ->keyBy('id_shift');
            $barista = User::whereIn('id_user', [$jadwal->id_user, $jadwalLawan->id_user])
                ->get()
                ->keyBy('id_user');

            $baristaPertama = $barista->get($jadwal->id_user);
            $baristaKedua = $barista->get($jadwalLawan->id_user);
            $shiftPertama = $shift->get($jadwal->id_shift);
            $shiftKedua = $shift->get($jadwalLawan->id_shift);

            if (
                !$baristaPertama ||
                !$baristaKedua ||
                !$shiftPertama ||
                !$shiftKedua ||
                $baristaPertama->role !== 'barista' ||
                $baristaKedua->role !== 'barista' ||
                !$baristaPertama->is_aktif ||
                !$baristaKedua->is_aktif ||
                (string) $baristaKedua->id_cabang !== (string) $shiftPertama->id_cabang ||
                (string) $baristaPertama->id_cabang !== (string) $shiftKedua->id_cabang
            ) {
                throw ValidationException::withMessages([
                    'id_jadwal_lawan' => 'Pertukaran hanya dapat dilakukan antarbarista aktif di cabang yang sama.',
                ]);
            }

            $bentrokPertama = DB::table('jadwal_shift')
                ->where('id_user', $jadwalLawan->id_user)
                ->where('tanggal', $jadwal->tanggal)
                ->whereNotIn('id_jadwal', [$jadwal->id_jadwal, $jadwalLawan->id_jadwal])
                ->exists();
            $bentrokKedua = DB::table('jadwal_shift')
                ->where('id_user', $jadwal->id_user)
                ->where('tanggal', $jadwalLawan->tanggal)
                ->whereNotIn('id_jadwal', [$jadwal->id_jadwal, $jadwalLawan->id_jadwal])
                ->exists();

            if ($bentrokPertama || $bentrokKedua) {
                throw ValidationException::withMessages([
                    'id_jadwal_lawan' => 'Pertukaran menyebabkan barista memiliki lebih dari satu jadwal pada tanggal yang sama.',
                ]);
            }

            DB::table('jadwal_shift')
                ->where('id_jadwal', $jadwal->id_jadwal)
                ->update([
                    'id_user' => $jadwalLawan->id_user,
                    'updated_at' => now(),
                ]);

            DB::table('jadwal_shift')
                ->where('id_jadwal', $jadwalLawan->id_jadwal)
                ->update([
                    'id_user' => $jadwal->id_user,
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('shift.index', $redirectParams)
            ->with('success', 'Jadwal kedua barista berhasil ditukar tanpa menambah penugasan.');
    }


    // =====================================
    // HAPUS JADWAL BARISTA
    // =====================================

    public function deleteJadwalShift(Request $request, $id)
    {
        $jadwal = DB::table('jadwal_shift')
            ->where('id_jadwal', $id)
            ->first();

        abort_if(!$jadwal, 404);

        $redirectParams = array_filter([
            'week_start' => $request->week_start,
            'cabang_id' => $request->cabang_id,
        ]);

        // Periksa relasi penggajian
        $digunakan = DB::table('penggajian')
            ->where('id_jadwal', $id)
            ->exists();

        if ($digunakan) {
            return redirect()
                ->route('shift.index', $redirectParams)
                ->withErrors([
                    'jadwal' => 'Jadwal tidak dapat dihapus karena sudah digunakan dalam penggajian.',
                ]);
        }

        DB::table('jadwal_shift')
            ->where('id_jadwal', $id)
            ->delete();

        return redirect()
            ->route('shift.index', $redirectParams)
            ->with('success', 'Jadwal barista berhasil dihapus');
    }
}
