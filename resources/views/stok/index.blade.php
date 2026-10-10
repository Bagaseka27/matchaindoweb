@extends('layouts.app')

@section('title', 'Kelola Stok')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Stok Terpusat</h2>
            <p class="text-gray-500 text-sm mt-1">Pantau stok kritis, sisa bahan baku bubuk, susu, sirup, dan stok cup di setiap cabang</p>
        </div>
        @if($cabangAktif)
            <a href="{{ route('stok.create', ['cabang' => $cabangAktif->id_cabang]) }}"
               class="bg-[#2F593E] text-white px-5 py-2 rounded-lg hover:bg-green-800 transition">+ Tambah Bahan Baru</a>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Filter cabang + pencarian --}}
    <div class="bg-white rounded-xl shadow-md p-5">
        <form method="GET" action="{{ route('stok.index') }}">
            <div class="flex flex-wrap gap-3 items-center">
                <label for="cabang" class="text-sm font-semibold text-gray-600">Filter Cabang:</label>
                <select id="cabang" name="cabang" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">
                    @forelse($daftarCabang as $cabang)
                        <option value="{{ $cabang->id_cabang }}" @selected($cabangAktif && $cabangAktif->id_cabang === $cabang->id_cabang)>
                            {{ $cabang->nama_cabang }} ({{ $cabang->is_aktif ? 'Aktif' : 'Nonaktif' }})
                        </option>
                    @empty
                        <option>Belum ada cabang</option>
                    @endforelse
                </select>
                <input type="text" name="q" value="{{ $keyword }}" placeholder="Cari bahan/cup..."
                       class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">
                <button type="submit" class="bg-[#2F593E] text-white px-6 py-3 rounded-lg hover:bg-green-800 transition">Cari</button>
                @if($keyword !== '')
                    <a href="{{ route('stok.index', ['cabang' => $cabangAktif?->id_cabang]) }}"
                       class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel inventaris --}}
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        @if($cabangAktif)
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800">Inventaris {{ $cabangAktif->nama_cabang }}</h3>
                @if($jumlahKritis > 0)
                    <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-700">{{ $jumlahKritis }} Kritis</span>
                @endif
            </div>

            <table class="w-full text-left border-collapse">
                <thead class="bg-[#2F593E] text-white">
                    <tr>
                        <th class="px-6 py-4 border border-green-700">Nama Bahan / Cup</th>
                        <th class="px-6 py-4 border border-green-700">Sisa Stok</th>
                        <th class="px-6 py-4 border border-green-700">Satuan</th>
                        <th class="px-6 py-4 border border-green-700">Batas Minimal</th>
                        <th class="px-6 py-4 border border-green-700">Status</th>
                        <th class="px-6 py-4 border border-green-700">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventaris as $row)
                        @php
                            $ui = match ($row->status) {
                                'kritis'       => ['label' => 'Kritis',       'angka' => 'text-red-600',    'badge' => 'bg-red-100 text-red-700'],
                                'hampir_habis' => ['label' => 'Hampir Habis', 'angka' => 'text-yellow-600', 'badge' => 'bg-yellow-100 text-yellow-700'],
                                default        => ['label' => 'Aman',         'angka' => 'text-green-700',  'badge' => 'bg-green-100 text-green-700'],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 border border-gray-200 font-semibold text-gray-800">{{ $row->bahan->nama_bahan }}</td>
                            <td class="px-6 py-4 border border-gray-200 font-bold {{ $ui['angka'] }}">{{ $row->jumlah_stok_fmt }}</td>
                            <td class="px-6 py-4 border border-gray-200 text-gray-600">{{ $row->bahan->satuan }}</td>
                            <td class="px-6 py-4 border border-gray-200 text-gray-600">{{ $row->stok_minimum_fmt }} {{ $row->bahan->satuan }}</td>
                            <td class="px-6 py-4 border border-gray-200">
                                <span class="px-3 py-1 rounded-full text-sm {{ $ui['badge'] }}">{{ $ui['label'] }}</span>
                            </td>
                            <td class="px-6 py-4 border border-gray-200">
                                <div class="flex justify-center gap-3">
                                    <a href="{{ route('stok.edit', [$row->id_cabang, $row->id_bahan]) }}"
                                       class="inline-flex items-center justify-center w-20 py-2 bg-yellow-400 text-white rounded-lg text-sm font-medium hover:bg-yellow-500 transition">Edit</a>
                                    <form action="{{ route('stok.destroy', [$row->id_cabang, $row->id_bahan]) }}" method="POST"
                                          onsubmit="return confirm(@js('Hapus ' . $row->bahan->nama_bahan . ' dari stok ' . $cabangAktif->nama_cabang . '?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center w-20 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-400 border border-gray-200">
                                {{ $keyword !== '' ? 'Bahan "' . $keyword . '" tidak ditemukan.' : 'Belum ada data bahan di cabang ini.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <p class="text-center py-10 text-gray-400">Belum ada cabang. Tambahkan cabang terlebih dahulu di menu Kelola Cabang.</p>
        @endif
    </div>
</div>
@endsection