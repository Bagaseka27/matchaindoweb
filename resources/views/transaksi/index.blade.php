@extends('layouts.app')

@section('title', 'Transaksi Penjualan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Transaksi Penjualan</h2>
            <p class="mt-1 text-sm text-gray-500">Daftar transaksi yang dicatat Barista melalui aplikasi mobile.</p>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('transaksi.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Filter Cabang -->
            <div>
                <label for="cabang" class="block text-xs font-semibold text-gray-600 mb-1">Filter Cabang</label>
                <select name="cabang" id="cabang" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#2F593E] focus:ring-[#2F593E]">
                    <option value="">Semua Cabang Outdoor</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id_cabang }}" {{ request('cabang') == $c->id_cabang ? 'selected' : '' }}>
                            {{ $c->nama_cabang }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Input Pencarian -->
            <div>
                <label for="search" class="block text-xs font-semibold text-gray-600 mb-1">Cari Transaksi</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari barista, metode bayar..." class="w-full rounded-lg border-gray-300 text-sm focus:border-[#2F593E] focus:ring-[#2F593E]">
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-[#2F593E] text-white text-sm font-semibold rounded-lg hover:bg-[#23442f] transition shadow-sm">
                    Terapkan
                </button>
                <a href="{{ route('transaksi.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-semibold rounded-lg hover:bg-gray-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Transaksi -->
    <section class="overflow-hidden rounded-xl bg-white shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#2F593E] text-white">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Barista</th>
                        <th class="px-4 py-3">Pesanan</th>
                        <th class="px-4 py-3 text-center">Jumlah</th>
                        <th class="px-4 py-3">Pembayaran</th>
                        <th class="px-4 py-3">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $row)
                        <tr class="border-b align-top hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">{{ \Illuminate\Support\Carbon::parse($row->waktu_transaksi)->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $row->nama_cabang }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $row->nama_barista }}</td>
                            
                            <!-- Kolom Pesanan (Hanya Nama Menu & Cup) -->
                            <td class="px-4 py-3 text-sm text-gray-800">
                                @foreach($detail->get($row->id_transaksi, collect()) as $item)
                                    <div class="py-1">{{ $item->nama_menu }} · <span class="text-gray-500 font-normal">{{ $item->nama_cup }}</span></div>
                                @endforeach
                            </td>

                            <!-- Kolom Tersendiri untuk Jumlah (Qty) -->
                            <td class="px-4 py-3 text-sm text-center font-semibold text-gray-700">
                                @foreach($detail->get($row->id_transaksi, collect()) as $item)
                                    <div class="py-1">
                                        <span class="inline-block bg-gray-100 text-gray-800 px-2.5 py-0.5 rounded text-xs">
                                            {{ $item->jumlah }}
                                        </span>
                                    </div>
                                @endforeach
                            </td>

                            <td class="px-4 py-3 text-sm uppercase font-semibold text-gray-700">{{ $row->metode_bayar }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-[#2F593E]">Rp {{ number_format($row->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">Belum ada data transaksi penjualan yang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t px-4 py-3">{{ $transaksi->links() }}</div>
    </section>
</div>
@endsection