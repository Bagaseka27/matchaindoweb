@extends('layouts.app')

@section('title', 'Kelola Shift & Penugasan Barista')

@section('content')

<div class="space-y-6 pb-12">

    {{-- ========================================== --}}
    {{-- HEADER HALAMAN & AKSI UTAMA --}}
    {{-- ========================================== --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-bold text-gray-800">
                    Kelola Penugasan Shift Barista
                </h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    Siklus Mingguan
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                Jadwal kerja barista disusun untuk <strong>1 minggu ke depan</strong> dan diperbarui secara berkala setiap seminggu sekali.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Tombol Buat Shift Baru --}}
            <button type="button"
                onclick="toggleElement('form-master-shift')"
                class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 px-4 py-2 rounded-lg text-sm font-semibold transition">
                <span>⚙️</span> Master Shift
            </button>

            {{-- Tombol Buka Form Penugasan Mingguan --}}
            <button type="button"
                onclick="toggleElement('panel-penugasan-mingguan')"
                class="inline-flex items-center gap-1.5 bg-[#2F593E] text-white hover:bg-green-800 px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
                <span>➕</span> Tugaskan Barista Mingguan
            </button>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- NOTIFIKASI SUKSES / ERROR --}}
    {{-- ========================================== --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-start gap-3 shadow-sm">
            <span class="text-lg">✅</span>
            <div class="flex-1 font-medium text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-xl flex items-start gap-3 shadow-sm">
            <span class="text-lg">⚠️</span>
            <div class="flex-1 font-medium text-sm">
                {{ session('warning') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 font-semibold text-sm mb-1">
                <span>❌</span> Ada beberapa kesalahan:
            </div>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- TOOLBAR NAVIGASI PERIODE 1 MINGGU --}}
    {{-- ========================================== --}}
    <div class="bg-white rounded-xl shadow-md border border-gray-200 p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            {{-- Navigasi Tombol Minggu --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Minggu Lalu --}}
                <a href="{{ route('shift.index', array_filter(['week_start' => $prevWeek, 'cabang_id' => $cabangId])) }}"
                   class="inline-flex items-center gap-1 px-3 py-2 border rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                    &laquo; Minggu Lalu
                </a>

                {{-- Minggu Ini --}}
                <a href="{{ route('shift.index', array_filter(['week_start' => $currentWeekMonday, 'cabang_id' => $cabangId])) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold transition {{ $isThisWeek ? 'bg-[#2F593E] text-white shadow-sm' : 'border text-gray-700 bg-white hover:bg-gray-50' }}">
                    <span>⭐</span> Minggu Ini
                </a>

                {{-- 1 Minggu ke Depan (Highlight Utama) --}}
                <a href="{{ route('shift.index', array_filter(['week_start' => $nextWeekMonday, 'cabang_id' => $cabangId])) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-semibold transition {{ $isNextWeek ? 'bg-blue-600 text-white shadow-sm ring-2 ring-blue-300' : 'border border-blue-300 text-blue-700 bg-blue-50/60 hover:bg-blue-100' }}">
                    <span>🚀</span> 1 Minggu ke Depan
                </a>

                {{-- Minggu Berikutnya --}}
                <a href="{{ route('shift.index', array_filter(['week_start' => $nextWeek, 'cabang_id' => $cabangId])) }}"
                   class="inline-flex items-center gap-1 px-3 py-2 border rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                    Minggu Depan &raquo;
                </a>
            </div>

            {{-- Filter Cabang & Pemilih Tanggal Langsung --}}
            <form method="GET" action="{{ route('shift.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="week_start" value="{{ $startOfWeek->format('Y-m-d') }}">

                {{-- Filter Cabang --}}
                <div class="flex items-center gap-2">
                    <label for="filterCabang" class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Cabang:</label>
                    <select id="filterCabang" name="cabang_id" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 bg-white focus:ring-2 focus:ring-[#2F593E]">
                        <option value="">Semua Cabang</option>
                        @foreach($cabangs as $c)
                            <option value="{{ $c->id_cabang }}" @selected($cabangId == $c->id_cabang)>
                                {{ $c->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lompat ke Tanggal --}}
                <div class="flex items-center gap-2">
                    <label for="jumpDate" class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Mulai:</label>
                    <input type="date" id="jumpDate" name="week_start" value="{{ $startOfWeek->format('Y-m-d') }}"
                        onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm text-gray-700 bg-white focus:ring-2 focus:ring-[#2F593E]">
                </div>
            </form>

        </div>

        {{-- Banner Rangkuman Periode Aktif --}}
        <div class="mt-4 pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/70 p-3 rounded-lg">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">📅</span>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-gray-800">
                            Periode: {{ $days[0]['day_name'] }}, {{ $days[0]['date_formatted'] }} &ndash; {{ $days[6]['day_name'] }}, {{ $days[6]['date_formatted'] }}
                        </span>
                        @if($isNextWeek)
                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                🚀 1 Minggu ke Depan
                            </span>
                        @elseif($isThisWeek)
                            <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                ⭐ Minggu Berjalan
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500">
                        Total <strong>{{ $totalPenugasan }}</strong> jadwal penugasan barista aktif pada minggu ini.
                    </p>
                </div>
            </div>

            {{-- Tombol Salin Jadwal dari Minggu Sebelumnya --}}
            @if($hasPrevWeekSchedule)
                <form action="{{ route('jadwal-shift.salin') }}" method="POST"
                    onsubmit="return confirm('Salin seluruh penugasan shift dari 1 minggu sebelumnya ke periode minggu ini?\n\nJadwal yang sudah terisi di minggu ini tidak akan ditimpa.')">
                    @csrf
                    <input type="hidden" name="target_week_start" value="{{ $startOfWeek->format('Y-m-d') }}">
                    <input type="hidden" name="cabang_id" value="{{ $cabangId }}">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold shadow-sm transition">
                        <span>📋</span> Salin Jadwal Minggu Lalu
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- FORM MASTER SHIFT (TERSEMBUNYI DEFAULT) --}}
    {{-- ========================================== --}}
    <div id="form-master-shift" class="hidden bg-white rounded-xl shadow-md border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4 pb-2 border-b">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    Tambah Master Shift Baru
                </h3>
                <p class="text-xs text-gray-500">
                    Master shift menentukan jam kerja operasional di masing-masing cabang (misal: Pagi, Siang, Sore).
                </p>
            </div>
            <button type="button" onclick="toggleElement('form-master-shift')" class="text-gray-400 hover:text-gray-600 font-bold text-lg">
                &times;
            </button>
        </div>

        <form action="{{ route('shift.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Cabang</label>
                    <select name="id_cabang" required class="w-full border rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-[#2F593E]">
                        <option value="">Pilih Cabang</option>
                        @foreach($cabangs as $cabang)
                            <option value="{{ $cabang->id_cabang }}">
                                {{ $cabang->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nama Shift</label>
                    <input type="text" name="nama_shift" placeholder="Contoh: Pagi, Sore, Full" maxlength="50" required
                        class="w-full border rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-[#2F593E]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Jam Mulai</label>
                    <input type="time" name="jam_mulai" required
                        class="w-full border rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-[#2F593E]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Jam Selesai</label>
                    <input type="time" name="jam_selesai" required
                        class="w-full border rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-[#2F593E]">
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="toggleElement('form-master-shift')"
                    class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-100">
                    Batal
                </button>
                <button type="submit"
                    class="bg-[#2F593E] hover:bg-green-800 text-white font-semibold px-5 py-2 rounded-lg text-sm shadow-sm transition">
                    Simpan Master Shift
                </button>
            </div>
        </form>
    </div>

    {{-- ========================================== --}}
    {{-- FORM PENUGASAN MINGGUAN (1 MINGGU KE DEPAN) --}}
    {{-- ========================================== --}}
    <div id="panel-penugasan-mingguan" class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4 pb-3 border-b">
            <div>
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <span>📋</span> Form Penugasan Barista Mingguan
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Pilih barista, shift cabang, lalu centang hari-hari bertugas dalam minggu aktif
                    (<strong>{{ $days[0]['short_date'] }} s/d {{ $days[6]['short_date'] }}</strong>).
                </p>
            </div>
            <button type="button" onclick="toggleElement('panel-penugasan-mingguan')" class="text-gray-400 hover:text-gray-600 font-bold text-lg">
                &times;
            </button>
        </div>

        <form action="{{ route('jadwal-shift.mingguan') }}" method="POST">
            @csrf
            <input type="hidden" name="week_start" value="{{ $startOfWeek->format('Y-m-d') }}">
            <input type="hidden" name="cabang_id" value="{{ $cabangId }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                {{-- Pilih Shift --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Pilih Shift & Cabang <span class="text-red-500">*</span>
                    </label>
                    <select name="id_shift" id="mingguan_id_shift" required onchange="filterBaristaByShift(this)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#2F593E]">
                        <option value="">-- Pilih Shift & Cabang --</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id_shift }}" data-cabang="{{ $shift->id_cabang }}">
                                {{ $shift->nama_cabang }} &bull; Shift {{ $shift->nama_shift }}
                                ({{ substr((string) $shift->jam_mulai, 0, 5) }} - {{ substr((string) $shift->jam_selesai, 0, 5) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilih Barista --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        Pilih Barista <span class="text-red-500">*</span>
                    </label>
                    <select name="id_user" id="mingguan_id_user" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#2F593E]">
                        <option value="">-- Pilih Barista --</option>
                        @foreach($allBaristas as $barista)
                            <option value="{{ $barista->id_user }}" data-cabang="{{ $barista->id_cabang }}">
                                {{ $barista->nama }}
                                ({{ $cabangs->firstWhere('id_cabang', $barista->id_cabang)?->nama_cabang ?? 'Tanpa Cabang' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        * Barista harus aktif dan terdaftar di cabang yang sama dengan shift.
                    </p>
                </div>
            </div>

            {{-- Checklist Hari dalam Minggu Ini --}}
            <div class="mb-5 bg-gray-50 border border-gray-200 rounded-xl p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Pilih Hari Bertugas (Minggu: {{ $days[0]['short_date'] }} - {{ $days[6]['short_date'] }}) <span class="text-red-500">*</span>
                    </label>

                    {{-- Tombol Cepat Pilihan Hari --}}
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <button type="button" onclick="pilihSemuaHari(true)"
                            class="px-2.5 py-1 bg-white border border-gray-300 hover:bg-gray-100 rounded text-gray-700 font-semibold transition">
                            Semua (7 Hari)
                        </button>
                        <button type="button" onclick="pilihHariKerja()"
                            class="px-2.5 py-1 bg-white border border-gray-300 hover:bg-gray-100 rounded text-gray-700 font-semibold transition">
                            Senin - Jumat
                        </button>
                        <button type="button" onclick="pilihAkhirPekan()"
                            class="px-2.5 py-1 bg-white border border-gray-300 hover:bg-gray-100 rounded text-gray-700 font-semibold transition">
                            Sabtu - Minggu
                        </button>
                        <button type="button" onclick="pilihSemuaHari(false)"
                            class="px-2.5 py-1 bg-white border border-gray-300 hover:bg-gray-100 rounded text-red-600 font-semibold transition">
                            Reset
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                    @foreach($days as $index => $d)
                        <label class="cursor-pointer border rounded-xl p-3 flex flex-col items-center justify-center gap-1 transition select-none {{ $d['is_today'] ? 'border-emerald-500 bg-emerald-50/70 font-semibold' : 'border-gray-200 bg-white hover:bg-gray-100' }}">
                            <input type="checkbox" name="tanggal[]" value="{{ $d['date'] }}"
                                class="day-checkbox w-4 h-4 text-[#2F593E] rounded border-gray-300 focus:ring-[#2F593E]"
                                data-daynum="{{ $d['day_num'] }}">
                            <span class="text-xs font-bold text-gray-800 mt-1">
                                {{ $d['day_name'] }}
                            </span>
                            <span class="text-[11px] text-gray-500">
                                {{ $d['short_date'] }}
                            </span>
                            @if($d['is_today'])
                                <span class="text-[10px] bg-emerald-600 text-white px-1.5 py-0.2 rounded-full">
                                    Hari Ini
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="toggleElement('panel-penugasan-mingguan')"
                    class="px-5 py-2.5 border rounded-lg text-sm text-gray-600 hover:bg-gray-100 font-medium">
                    Tutup
                </button>
                <button type="submit"
                    class="bg-[#2F593E] hover:bg-green-800 text-white font-bold px-6 py-2.5 rounded-lg text-sm shadow-md transition flex items-center gap-2">
                    <span>💾</span> Simpan Penugasan 1 Minggu
                </button>
            </div>
        </form>
    </div>

    {{-- ========================================== --}}
    {{-- MATRIKS PENUGASAN SHIFT MINGGUAN (7 HARI) --}}
    {{-- ========================================== --}}
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <span>🗓️</span> Matriks Jadwal Mingguan (Senin &ndash; Minggu)
                </h3>
                <p class="text-xs text-gray-500">
                    Status penugasan barista di setiap shift selama 7 hari dalam periode aktif.
                </p>
            </div>

            <div class="flex items-center gap-2 text-xs font-medium">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Terisi
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 text-gray-500 border border-gray-200 rounded-lg">
                    <span class="w-2 h-2 rounded-full bg-gray-300"></span> Kosong
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead class="bg-[#2F593E] text-white">
                    <tr>
                        <th class="px-4 py-3.5 text-xs font-bold uppercase tracking-wider w-48 border-r border-green-700">
                            Cabang & Shift
                        </th>
                        @foreach($days as $d)
                            <th class="px-3 py-3 text-center border-r border-green-700 last:border-r-0 {{ $d['is_today'] ? 'bg-green-900/60' : '' }}">
                                <div class="text-xs font-bold uppercase tracking-wider">
                                    {{ $d['day_name'] }}
                                </div>
                                <div class="text-[11px] font-normal text-green-200 mt-0.5">
                                    {{ $d['short_date'] }}
                                </div>
                                @if($d['is_today'])
                                    <div class="text-[9px] bg-emerald-400 text-emerald-950 px-1 rounded font-bold uppercase tracking-tight mt-0.5 inline-block">
                                        Hari Ini
                                    </div>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($shifts as $shift)
                        <tr class="hover:bg-gray-50/60 transition">
                            {{-- Kolom Shift & Cabang --}}
                            <td class="px-4 py-3 bg-gray-50/80 border-r border-gray-200">
                                <div class="font-bold text-gray-800 text-sm">
                                    {{ $shift->nama_shift }}
                                </div>
                                <div class="text-xs text-gray-500 font-medium">
                                    {{ $shift->nama_cabang }}
                                </div>
                                <div class="inline-block mt-1 text-[11px] font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                    {{ substr((string) $shift->jam_mulai, 0, 5) }} - {{ substr((string) $shift->jam_selesai, 0, 5) }}
                                </div>
                            </td>

                            {{-- 7 Kolom Hari (Senin - Minggu) --}}
                            @foreach($days as $d)
                                @php
                                    $penugasanHari = $jadwals->where('id_shift', $shift->id_shift)->where('tanggal', $d['date']);
                                @endphp
                                <td class="px-2.5 py-2.5 border-r border-gray-200 last:border-r-0 align-top {{ $d['is_today'] ? 'bg-emerald-50/20' : '' }}">
                                    @if($penugasanHari->isNotEmpty())
                                        <div class="space-y-1.5">
                                            @foreach($penugasanHari as $j)
                                                <div class="group relative bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-lg p-2 text-xs shadow-xs hover:border-emerald-400 transition">
                                                    <div class="flex items-center justify-between gap-1">
                                                        <div class="font-bold text-emerald-950 truncate flex items-center gap-1">
                                                            <span>☕</span> {{ $j->nama_barista }}
                                                        </div>
                                                    </div>

                                                    {{-- Tombol Aksi Mini (Hover / Selalu ada) --}}
                                                    <div class="mt-1.5 pt-1 border-t border-emerald-200/60 flex items-center justify-end gap-1.5">
                                                        {{-- Edit Jadwal Modal Trigger --}}
                                                        <button type="button"
                                                            onclick="openEditJadwalModal({{ $j->id_jadwal }}, {{ $j->id_user }}, {{ $j->id_shift }}, '{{ $j->tanggal }}')"
                                                            class="text-amber-600 hover:text-amber-800 p-0.5 rounded text-[11px] font-semibold"
                                                            title="Ubah barista">
                                                            ✏️ Edit
                                                        </button>

                                                        {{-- Hapus Jadwal --}}
                                                        <form action="{{ route('jadwal-shift.delete', $j->id_jadwal) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('Hapus penugasan barista {{ $j->nama_barista }} pada tanggal {{ $j->tanggal }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="week_start" value="{{ $startOfWeek->format('Y-m-d') }}">
                                                            <input type="hidden" name="cabang_id" value="{{ $cabangId }}">
                                                            <button type="submit"
                                                                class="text-red-500 hover:text-red-700 p-0.5 rounded text-[11px] font-semibold"
                                                                title="Hapus jadwal ini">
                                                                🗑️ Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        {{-- Tombol Cepat Tugaskan pada Hari Ini --}}
                                        <button type="button"
                                            onclick="openQuickAssignModal({{ $shift->id_shift }}, '{{ $shift->nama_shift }}', '{{ $shift->nama_cabang }}', '{{ $d['date'] }}', '{{ $d['day_name'] }}, {{ $d['short_date'] }}')"
                                            class="w-full h-16 border-2 border-dashed border-gray-200 hover:border-[#2F593E] hover:bg-green-50/50 rounded-lg flex flex-col items-center justify-center text-gray-400 hover:text-[#2F593E] transition group">
                                            <span class="text-sm font-bold group-hover:scale-110 transition">+</span>
                                            <span class="text-[10px] font-medium">Tugaskan</span>
                                        </button>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-gray-400">
                                <span class="text-3xl block mb-2">☕</span>
                                Belum ada master shift yang terdaftar. Silakan buat shift baru terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- TABEL RINCIAN SEMUA JADWAL MINGGU INI --}}
    {{-- ========================================== --}}
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <span>📑</span> Daftar Rincian Penugasan Barista (Minggu Ini)
                </h3>
                <p class="text-xs text-gray-500">
                    Daftar seluruh jadwal barista yang bertugas pada minggu {{ $days[0]['short_date'] }} s/d {{ $days[6]['short_date'] }}.
                </p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-gray-200 text-gray-700 rounded-full">
                {{ $jadwals->count() }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-700 text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Hari & Tanggal</th>
                        <th class="px-5 py-3">Cabang</th>
                        <th class="px-5 py-3">Nama Barista</th>
                        <th class="px-5 py-3">Shift</th>
                        <th class="px-5 py-3">Jam Kerja</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 text-sm">
                    @forelse($jadwals as $jadwal)
                        @php
                            $tglCarbon = \Carbon\Carbon::parse($jadwal->tanggal);
                            $namaHariJadwal = [
                                1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                                4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
                            ][$tglCarbon->dayOfWeekIso] ?? '';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3.5 font-semibold text-gray-800">
                                <div>{{ $namaHariJadwal }}, {{ $tglCarbon->format('d M Y') }}</div>
                                @if($tglCarbon->isToday())
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded">Hari Ini</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-gray-600">
                                {{ $jadwal->nama_cabang }}
                            </td>

                            <td class="px-5 py-3.5 font-bold text-[#2F593E]">
                                ☕ {{ $jadwal->nama_barista }}
                            </td>

                            <td class="px-5 py-3.5 font-medium text-gray-700">
                                {{ $jadwal->nama_shift }}
                            </td>

                            <td class="px-5 py-3.5 font-mono text-xs text-gray-600">
                                {{ substr((string) $jadwal->jam_mulai, 0, 5) }} &ndash; {{ substr((string) $jadwal->jam_selesai, 0, 5) }}
                            </td>

                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                        onclick="openEditJadwalModal({{ $jadwal->id_jadwal }}, {{ $jadwal->id_user }}, {{ $jadwal->id_shift }}, '{{ $jadwal->tanggal }}')"
                                        class="px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-800 hover:bg-amber-200 rounded-md transition">
                                        Edit
                                    </button>

                                    <form action="{{ route('jadwal-shift.delete', $jadwal->id_jadwal) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus jadwal barista {{ $jadwal->nama_barista }} ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="week_start" value="{{ $startOfWeek->format('Y-m-d') }}">
                                        <input type="hidden" name="cabang_id" value="{{ $cabangId }}">
                                        <button type="submit"
                                            class="px-2.5 py-1 text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 rounded-md transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">
                                Belum ada penugasan barista pada minggu ini. Klik tombol <strong>+ Tugaskan Barista Mingguan</strong> di atas untuk membuat jadwal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- TABEL MASTER SHIFT (UNTUK EDIT JAM KERJA) --}}
    {{-- ========================================== --}}
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <span>⏰</span> Data Master Jam Shift Cabang
                </h3>
                <p class="text-xs text-gray-500">
                    Pengaturan jam operasional kerja tiap shift di masing-masing cabang.
                </p>
            </div>
            <button type="button" onclick="toggleElement('form-master-shift')"
                class="text-xs font-semibold text-[#2F593E] hover:underline">
                + Tambah Shift Cabang
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="bg-gray-100 text-gray-700 text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Cabang</th>
                        <th class="px-5 py-3">Nama Shift</th>
                        <th class="px-5 py-3">Jam Mulai</th>
                        <th class="px-5 py-3">Jam Selesai</th>
                        <th class="px-5 py-3 text-center">Aksi Master Shift</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($shifts as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3.5 font-semibold text-gray-800">{{ $s->nama_cabang }}</td>
                            <td class="px-5 py-3.5 font-bold text-gray-700">{{ $s->nama_shift }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs text-gray-600">{{ substr((string) $s->jam_mulai, 0, 5) }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs text-gray-600">{{ substr((string) $s->jam_selesai, 0, 5) }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                        onclick="toggleElement('edit-master-shift-{{ $s->id_shift }}')"
                                        class="px-2.5 py-1 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition">
                                        Edit Jam
                                    </button>

                                    <form action="{{ route('shift.delete', $s->id_shift) }}" method="POST"
                                        onsubmit="return confirm('Hapus master shift {{ $s->nama_shift }} ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-2.5 py-1 text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 rounded-md transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Form Edit Inline Master Shift --}}
                        <tr id="edit-master-shift-{{ $s->id_shift }}" class="hidden bg-gray-50">
                            <td colspan="5" class="p-4 border-b">
                                <form action="{{ route('shift.update', $s->id_shift) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Shift</label>
                                            <input type="text" name="nama_shift" value="{{ $s->nama_shift }}" required
                                                class="w-full border rounded-lg p-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Jam Mulai</label>
                                            <input type="time" name="jam_mulai" value="{{ substr((string) $s->jam_mulai, 0, 5) }}" required
                                                class="w-full border rounded-lg p-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Jam Selesai</label>
                                            <input type="time" name="jam_selesai" value="{{ substr((string) $s->jam_selesai, 0, 5) }}" required
                                                class="w-full border rounded-lg p-2 text-sm">
                                        </div>
                                    </div>
                                    <div class="mt-3 flex justify-end gap-2">
                                        <button type="button" onclick="toggleElement('edit-master-shift-{{ $s->id_shift }}')"
                                            class="px-3 py-1.5 border rounded-lg text-xs text-gray-600">Batal</button>
                                        <button type="submit"
                                            class="px-4 py-1.5 bg-[#2F593E] text-white rounded-lg text-xs font-bold hover:bg-green-800">
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-6 text-gray-400">Belum ada master shift.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ========================================== --}}
{{-- MODAL QUICK ASSIGN (TUGASKAN PER HARI DARI MATRIKS) --}}
{{-- ========================================== --}}
<div id="modal-quick-assign" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-gray-200">
        <div class="flex justify-between items-center pb-3 border-b mb-4">
            <div>
                <h4 class="text-base font-bold text-gray-800">Tugaskan Barista</h4>
                <p id="quick-assign-subtitle" class="text-xs text-gray-500 mt-0.5"></p>
            </div>
            <button type="button" onclick="closeQuickAssignModal()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
        </div>

        <form action="{{ route('jadwal-shift.store') }}" method="POST">
            @csrf
            <input type="hidden" name="week_start" value="{{ $startOfWeek->format('Y-m-d') }}">
            <input type="hidden" name="cabang_id" value="{{ $cabangId }}">
            <input type="hidden" name="id_shift" id="quick_id_shift">
            <input type="hidden" name="tanggal" id="quick_tanggal">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pilih Barista</label>
                    <select name="id_user" id="quick_id_user" required class="w-full border rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-[#2F593E]">
                        <option value="">-- Pilih Barista --</option>
                        @foreach($allBaristas as $b)
                            <option value="{{ $b->id_user }}" data-cabang="{{ $b->id_cabang }}">
                                {{ $b->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" onclick="closeQuickAssignModal()" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-100">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#2F593E] hover:bg-green-800 text-white font-bold rounded-lg text-sm shadow-sm transition">
                    Simpan Penugasan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================== --}}
{{-- MODAL EDIT PENUGASAN BARISTA --}}
{{-- ========================================== --}}
<div id="modal-edit-jadwal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-gray-200">
        <div class="flex justify-between items-center pb-3 border-b mb-4">
            <div>
                <h4 class="text-base font-bold text-gray-800">Ubah Penugasan Barista</h4>
                <p class="text-xs text-gray-500 mt-0.5">Ganti barista atau sesuaikan tanggal penugasan.</p>
            </div>
            <button type="button" onclick="closeEditJadwalModal()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
        </div>

        <form id="form-edit-jadwal" action="" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="week_start" value="{{ $startOfWeek->format('Y-m-d') }}">
            <input type="hidden" name="cabang_id" value="{{ $cabangId }}">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Shift</label>
                    <select name="id_shift" id="edit_id_shift" required class="w-full border rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-[#2F593E]">
                        @foreach($shifts as $s)
                            <option value="{{ $s->id_shift }}">
                                {{ $s->nama_cabang }} &bull; {{ $s->nama_shift }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Barista</label>
                    <select name="id_user" id="edit_id_user" required class="w-full border rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-[#2F593E]">
                        @foreach($allBaristas as $b)
                            <option value="{{ $b->id_user }}">
                                {{ $b->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Tanggal</label>
                    <input type="date" name="tanggal" id="edit_tanggal" required class="w-full border rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-[#2F593E]">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" onclick="closeEditJadwalModal()" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-100">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#2F593E] hover:bg-green-800 text-white font-bold rounded-lg text-sm shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================== --}}
{{-- JAVASCRIPT LOGIC --}}
{{-- ========================================== --}}
<script>
    function toggleElement(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.toggle('hidden');
        }
    }

    // Checklist Hari Cepat
    function pilihSemuaHari(checked) {
        document.querySelectorAll('.day-checkbox').forEach(cb => {
            cb.checked = checked;
        });
    }

    function pilihHariKerja() {
        document.querySelectorAll('.day-checkbox').forEach(cb => {
            const dayNum = parseInt(cb.getAttribute('data-daynum'));
            cb.checked = (dayNum >= 1 && dayNum <= 5); // Senin s/d Jumat
        });
    }

    function pilihAkhirPekan() {
        document.querySelectorAll('.day-checkbox').forEach(cb => {
            const dayNum = parseInt(cb.getAttribute('data-daynum'));
            cb.checked = (dayNum === 6 || dayNum === 7); // Sabtu & Minggu
        });
    }

    // Filter dropdown Barista berdasarkan cabang Shift yang dipilih
    function filterBaristaByShift(shiftSelect) {
        const selectedOption = shiftSelect.options[shiftSelect.selectedIndex];
        const cabangId = selectedOption.getAttribute('data-cabang');
        const baristaSelect = document.getElementById('mingguan_id_user');

        if (!cabangId || !baristaSelect) return;

        let firstMatch = '';
        for (let i = 0; i < baristaSelect.options.length; i++) {
            const opt = baristaSelect.options[i];
            if (!opt.value) continue;
            const bCabang = opt.getAttribute('data-cabang');
            if (bCabang === cabangId) {
                opt.style.display = '';
                if (!firstMatch) firstMatch = opt.value;
            } else {
                opt.style.display = 'none';
            }
        }
        if (firstMatch) {
            baristaSelect.value = firstMatch;
        }
    }

    // Quick Assign Modal
    function openQuickAssignModal(shiftId, shiftName, cabangName, tanggal, dayText) {
        document.getElementById('quick_id_shift').value = shiftId;
        document.getElementById('quick_tanggal').value = tanggal;
        document.getElementById('quick-assign-subtitle').textContent = cabangName + ' • ' + shiftName + ' (' + dayText + ')';
        document.getElementById('modal-quick-assign').classList.remove('hidden');
    }

    function closeQuickAssignModal() {
        document.getElementById('modal-quick-assign').classList.add('hidden');
    }

    // Edit Jadwal Modal
    function openEditJadwalModal(idJadwal, idUser, idShift, tanggal) {
        const form = document.getElementById('form-edit-jadwal');
        form.action = "{{ url('/jadwal-shift') }}/" + idJadwal;
        document.getElementById('edit_id_user').value = idUser;
        document.getElementById('edit_id_shift').value = idShift;
        document.getElementById('edit_tanggal').value = tanggal;
        document.getElementById('modal-edit-jadwal').classList.remove('hidden');
    }

    function closeEditJadwalModal() {
        document.getElementById('modal-edit-jadwal').classList.add('hidden');
    }
</script>

@endsection
