@extends('layouts.app')

@section('title', 'Laporan Penjualan - Matcha Indonesia')

@section('content')
<div class="space-y-6">
    <!-- Header & Tombol Aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Laporan & Analisis Penjualan</h2>
            <p class="mt-1 text-sm text-gray-500">Rekapitulasi komprehensif untuk analisis bisnis 4 cabang outdoor Matcha Indonesia.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('laporan.cetak', request()->all()) }}" target="_blank" class="px-4 py-2 bg-[#2F593E] text-white text-sm font-semibold rounded-lg hover:bg-[#23442f] transition shadow-sm flex items-center gap-2">
                🖨️ Cetak Laporan / PDF Resmi
            </a>
            <a href="{{ route('laporan.export', request()->all()) }}" class="px-4 py-2 bg-green-700 text-white text-sm font-semibold rounded-lg hover:bg-green-800 transition shadow-sm flex items-center gap-2">
                📥 Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('laporan.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label for="tanggal_awal" class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" value="{{ $tanggalAwal }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#2F593E] focus:ring-[#2F593E]">
            </div>

            <div>
                <label for="tanggal_akhir" class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="{{ $tanggalAkhir }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#2F593E] focus:ring-[#2F593E]">
            </div>

            <div>
                <label for="id_cabang" class="block text-xs font-semibold text-gray-600 mb-1">Filter Cabang</label>
                <select name="id_cabang" id="id_cabang" class="w-full rounded-lg border-gray-300 text-sm focus:border-[#2F593E] focus:ring-[#2F593E]">
                    <option value="">Semua Cabang Outdoor</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id_cabang }}" {{ $idCabang == $c->id_cabang ? 'selected' : '' }}>
                            {{ $c->nama_cabang }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-[#2F593E] text-white text-sm font-semibold rounded-lg hover:bg-[#23442f] transition shadow-sm w-full">
                    Terapkan Filter Periode
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Analisis Per Bulan & Per Cabang -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Rekap Per Bulan -->
        <div class="bg-white p-5 rounded-xl shadow-md">
            <h3 class="font-bold text-gray-800 text-md mb-3">📈 Tren Pendapatan Per Bulan</h3>
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-700 text-xs uppercase">
                    <tr>
                        <th class="p-2">Bulan/Tahun</th>
                        <th class="p-2 text-center">Transaksi</th>
                        <th class="p-2 text-right">Total (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm">
                    @foreach($rekapPerBulan as $rb)
                    <tr>
                        <td class="p-2 font-medium">{{ \Carbon\Carbon::parse($rb->bulan . '-01')->translatedFormat('F Y') }}</td>
                        <td class="p-2 text-center">{{ number_format($rb->jumlah_transaksi, 0, ',', '.') }}</td>
                        <td class="p-2 text-right font-bold text-[#2F593E]">Rp {{ number_format($rb->total_pendapatan, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Rekap Per Cabang -->
        <div class="bg-white p-5 rounded-xl shadow-md">
            <h3 class="font-bold text-gray-800 text-md mb-3">🏪 Kinerja Cabang Outdoor</h3>
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-700 text-xs uppercase">
                    <tr>
                        <th class="p-2">Nama Cabang</th>
                        <th class="p-2 text-center">Transaksi</th>
                        <th class="p-2 text-right">Total (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm">
                    @foreach($rekapPerCabang as $rc)
                    <tr>
                        <td class="p-2 font-medium">{{ $rc->nama_cabang }}</td>
                        <td class="p-2 text-center">{{ number_format($rc->jumlah_transaksi, 0, ',', '.') }}</td>
                        <td class="p-2 text-right font-bold text-[#2F593E]">Rp {{ number_format($rc->total_pendapatan, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabel Rincian Detail Transaksi -->
    <section class="overflow-hidden rounded-xl bg-white shadow-md">
        <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
            <h3 class="font-bold text-gray-700 text-sm">Rincian Seluruh Transaksi Penjualan</h3>
            <span class="text-xs text-gray-500">Total Baris: {{ count($transaksi) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#2F593E] text-white">
                    <tr>
                        <th class="px-4 py-3 text-xs uppercase">Waktu</th>
                        <th class="px-4 py-3 text-xs uppercase">Order ID</th>
                        <th class="px-4 py-3 text-xs uppercase">Cabang</th>
                        <th class="px-4 py-3 text-xs uppercase">Barista</th>
                        <th class="px-4 py-3 text-xs uppercase">Metode Bayar</th>
                        <th class="px-4 py-3 text-xs uppercase text-right">Total Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transaksi as $row)
                        <tr class="hover:bg-gray-50 text-sm">
                            <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($row->waktu_transaksi)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $row->order_id ?? '-' }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $row->nama_cabang }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $row->nama_barista }}</td>
                            <td class="px-4 py-3 uppercase font-semibold text-xs text-gray-700">{{ $row->metode_bayar }}</td>
                            <td class="px-4 py-3 text-right font-bold text-[#2F593E]">Rp {{ number_format($row->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Tidak ada data transaksi pada rentang tanggal tersebut.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection