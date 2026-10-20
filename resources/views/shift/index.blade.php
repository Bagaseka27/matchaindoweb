
@extends('layouts.app')

@section('title', 'Kelola Shift')

@section('content')

<div class="space-y-6">

    {{-- HEADER --}}
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Kelola Shift
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Kelola jadwal kerja dan penugasan barista setiap cabang.
        </p>
    </div>

    {{-- NOTIFIKASI --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200
                    text-green-800 p-4 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200
                    text-red-700 p-4 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================================= --}}
    {{-- TAMBAH / TUGASKAN BARISTA --}}
    {{-- ================================= --}}

    <div class="bg-white rounded-xl shadow-md p-6">

        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="text-lg font-bold text-gray-800">
                    Tambah Jadwal Shift
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Pilih barista, shift, dan tanggal bertugas.
                </p>
            </div>

            <button type="button"
                onclick="document.getElementById('form-master-shift').classList.toggle('hidden')"
                class="bg-[#2F593E] text-white px-5 py-2
                       rounded-lg hover:bg-green-800">
                + Buat Shift Baru
            </button>
        </div>

        {{-- FORM BUAT SHIFT BARU --}}
        <div id="form-master-shift"
             class="hidden mb-6 p-5 bg-gray-50 rounded-lg border">

            <h4 class="font-bold mb-4">
                Buat Shift Baru
            </h4>

            <form action="{{ route('shift.store') }}"
                  method="POST">

                @csrf

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Cabang
                        </label>
                        <select name="id_cabang" required
                            class="w-full border rounded-lg p-3">
                            <option value="">Pilih Cabang</option>

                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang->id_cabang }}">
                                    {{ $cabang->nama_cabang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Nama Shift
                        </label>
                        <input type="text" name="nama_shift"
                            placeholder="Contoh: Pagi"
                            maxlength="50" required
                            class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Jam Mulai
                        </label>
                        <input type="time" name="jam_mulai"
                            required class="w-full border rounded-lg p-3">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Jam Selesai
                        </label>
                        <input type="time" name="jam_selesai"
                            required class="w-full border rounded-lg p-3">
                    </div>

                </div>

                <button type="submit"
                    class="mt-4 bg-[#2F593E] text-white
                           px-5 py-2 rounded-lg">
                    Simpan Shift Baru
                </button>
            </form>
        </div>

        {{-- FORM TUGASKAN BARISTA --}}
        <form action="{{ route('jadwal-shift.store') }}"
              method="POST">

            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Nama Barista
                    </label>

                    <select name="id_user" required
                        class="w-full border border-gray-300
                               rounded-lg px-4 py-3">

                        <option value="">Pilih Barista</option>

                        @foreach($baristas as $barista)
                            <option value="{{ $barista->id_user }}"
                                @selected(old('id_user') == $barista->id_user)>
                                {{ $barista->nama }}
                                ({{ $cabangs->firstWhere('id_cabang', $barista->id_cabang)?->nama_cabang ?? 'Tanpa cabang' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Shift
                    </label>

                    <select name="id_shift" required
                        class="w-full border border-gray-300
                               rounded-lg px-4 py-3">

                        <option value="">Pilih Shift</option>

                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id_shift }}"
                                @selected(old('id_shift') == $shift->id_shift)>
                                {{ $shift->nama_cabang }} -
                                {{ $shift->nama_shift }}
                                ({{ substr((string) $shift->jam_mulai, 0, 5) }}
                                -
                                {{ substr((string) $shift->jam_selesai, 0, 5) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Tanggal Bertugas
                    </label>

                    <input type="date" name="tanggal"
                        value="{{ old('tanggal') }}"
                        required
                        class="w-full border border-gray-300
                               rounded-lg px-4 py-3">
                </div>

            </div>

            <button type="submit"
                class="mt-5 bg-[#2F593E] text-white
                       px-6 py-3 rounded-lg hover:bg-green-800">
                + Tambah Jadwal Shift
            </button>

        </form>

    </div>

    {{-- ================================= --}}
    {{-- SATU TABEL SHIFT DAN BARISTA --}}
    {{-- ================================= --}}

    <div class="bg-white rounded-xl shadow-md overflow-hidden">

        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-bold text-gray-800">
                Daftar Shift Barista
            </h3>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-left border-collapse">

                <thead class="bg-[#2F593E] text-white">
                    <tr>
                        <th class="px-5 py-4">Cabang</th>
                        <th class="px-5 py-4">Nama Barista</th>
                        <th class="px-5 py-4">Tanggal</th>
                        <th class="px-5 py-4">Shift</th>
                        <th class="px-5 py-4">Jam Mulai</th>
                        <th class="px-5 py-4">Jam Selesai</th>
                        <th class="px-5 py-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($shifts as $shift)

                        @php
                            $penugasan = $jadwals->where(
                                'id_shift',
                                $shift->id_shift
                            );
                        @endphp

                        @forelse($penugasan as $jadwal)

                            <tr class="hover:bg-gray-50 border-b">

                                <td class="px-5 py-4">
                                    {{ $shift->nama_cabang }}
                                </td>

                                <td class="px-5 py-4 font-semibold">
                                    {{ $jadwal->nama_barista }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $jadwal->tanggal }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $shift->nama_shift }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ substr((string) $shift->jam_mulai, 0, 5) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ substr((string) $shift->jam_selesai, 0, 5) }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex justify-center gap-2">

                                        <button type="button"
                                            onclick="document.getElementById('edit-jadwal-{{ $jadwal->id_jadwal }}').classList.toggle('hidden')"
                                            class="bg-yellow-400 text-white
                                                   px-4 py-2 rounded-lg">
                                            Edit
                                        </button>

                                        <form action="{{ route('jadwal-shift.delete', $jadwal->id_jadwal) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus jadwal barista ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="bg-red-600 text-white
                                                       px-4 py-2 rounded-lg">
                                                Hapus
                                            </button>
                                        </form>

                                        <button type="button"
                                            onclick="document.getElementById('edit-shift-{{ $shift->id_shift }}').classList.toggle('hidden')"
                                            class="bg-[#2F593E] text-white px-3 py-2 rounded-lg">
                                            Edit Jam
                                        </button>

                                    </div>
                                </td>
                            </tr>

                            {{-- EDIT PENUGASAN BARISTA --}}
                            <tr id="edit-jadwal-{{ $jadwal->id_jadwal }}"
                                class="hidden bg-gray-50">

                                <td colspan="7" class="p-5">

                                    <form action="{{ route('jadwal-shift.update', $jadwal->id_jadwal) }}"
                                          method="POST">

                                        @csrf
                                        @method('PUT')

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                            <div>
                                                <label class="block text-sm font-semibold mb-2">
                                                    Barista
                                                </label>

                                                <select name="id_user" required
                                                    class="w-full border rounded-lg p-3">

                                                    @foreach($baristas as $barista)
                                                        <option value="{{ $barista->id_user }}"
                                                            @selected($jadwal->id_user == $barista->id_user)>
                                                            {{ $barista->nama }}
                                                        </option>
                                                    @endforeach

                                                    @if(!$baristas->contains('id_user', $jadwal->id_user))
                                                        <option value="{{ $jadwal->id_user }}"
                                                                selected disabled>
                                                            Barista saat ini tidak aktif
                                                        </option>
                                                    @endif

                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold mb-2">
                                                    Shift
                                                </label>

                                                <select name="id_shift" required
                                                    class="w-full border rounded-lg p-3">

                                                    @foreach($shifts as $pilihan)
                                                        <option value="{{ $pilihan->id_shift }}"
                                                            @selected($jadwal->id_shift == $pilihan->id_shift)>
                                                            {{ $pilihan->nama_cabang }} -
                                                            {{ $pilihan->nama_shift }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold mb-2">
                                                    Tanggal
                                                </label>

                                                <input type="date"
                                                    name="tanggal"
                                                    value="{{ $jadwal->tanggal }}"
                                                    required
                                                    class="w-full border rounded-lg p-3">
                                            </div>

                                        </div>

                                        <button type="submit"
                                            class="mt-4 bg-[#2F593E] text-white
                                                   px-5 py-2 rounded-lg">
                                            Simpan Perubahan
                                        </button>
                                    </form>
                                </td>
                            </tr>

                        @empty

                            {{-- SHIFT BELUM PUNYA BARISTA --}}
                            <tr class="hover:bg-gray-50 border-b">

                                <td class="px-5 py-4">
                                    {{ $shift->nama_cabang }}
                                </td>

                                <td class="px-5 py-4 text-gray-400">
                                    Belum ditugaskan
                                </td>

                                <td class="px-5 py-4 text-gray-400">
                                    -
                                </td>

                                <td class="px-5 py-4 font-semibold">
                                    {{ $shift->nama_shift }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ substr((string) $shift->jam_mulai, 0, 5) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ substr((string) $shift->jam_selesai, 0, 5) }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex justify-center gap-2">

                                        <button type="button"
                                            onclick="document.getElementById('edit-shift-{{ $shift->id_shift }}').classList.toggle('hidden')"
                                            class="bg-yellow-400 text-white px-4 py-2 rounded-lg">
                                            Edit
                                        </button>

                                        <form action="{{ route('shift.delete', $shift->id_shift) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus shift ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="bg-red-600 text-white px-4 py-2 rounded-lg">
                                                Hapus
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        @endforelse

                        {{-- EDIT MASTER SHIFT --}}
                        <tr id="edit-shift-{{ $shift->id_shift }}"
                            class="hidden bg-gray-50 border-b">

                            <td colspan="7" class="p-5">

                                <form action="{{ route('shift.update', $shift->id_shift) }}"
                                      method="POST">

                                    @csrf
                                    @method('PUT')

                                    <h4 class="font-bold mb-4">
                                        Edit Jam Shift: {{ $shift->nama_shift }}
                                    </h4>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                        <div>
                                            <label class="block text-sm font-semibold mb-2">
                                                Nama Shift
                                            </label>

                                            <input type="text"
                                                name="nama_shift"
                                                maxlength="50"
                                                value="{{ $shift->nama_shift }}"
                                                required
                                                class="w-full border rounded-lg p-3">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold mb-2">
                                                Jam Mulai
                                            </label>

                                            <input type="time"
                                                name="jam_mulai"
                                                value="{{ substr((string) $shift->jam_mulai, 0, 5) }}"
                                                required
                                                class="w-full border rounded-lg p-3">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold mb-2">
                                                Jam Selesai
                                            </label>

                                            <input type="time"
                                                name="jam_selesai"
                                                value="{{ substr((string) $shift->jam_selesai, 0, 5) }}"
                                                required
                                                class="w-full border rounded-lg p-3">
                                        </div>

                                    </div>

                                    <button type="submit"
                                        class="mt-4 bg-[#2F593E] text-white
                                               px-5 py-2 rounded-lg">
                                        Simpan Jam Shift
                                    </button>
                                </form>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7"
                                class="text-center py-10 text-gray-400">
                                Belum ada data shift.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>

    </div>

</div>

@endsection
