
@extends('layouts.app')

@section('title', 'Rekonsiliasi QRIS')

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">
        Rekonsiliasi QRIS
    </h2>
    <p class="text-sm text-gray-500 mt-1">
        Pencocokan total pembayaran QRIS dengan catatan kasir.
    </p>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-300
                text-green-800 p-4 rounded-lg mb-5">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-5">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<!-- FILTER TRANSAKSI -->
<div class="bg-white p-6 rounded-lg shadow-sm mb-6">

    <h3 class="font-bold text-lg mb-4">
        Filter Transaksi QRIS
    </h3>

    <form method="GET"
          action="{{ route('rekonsiliasi.index') }}"
          class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">

        <div>
            <label class="block text-sm mb-2">Cabang</label>
            <select name="id_cabang"
                    class="w-full border rounded-lg p-2">
                <option value="">Semua Cabang</option>
                @foreach($cabang as $item)
                    <option value="{{ $item->id_cabang }}"
                        @selected((string)$idCabang === (string)$item->id_cabang)>
                        {{ $item->nama_cabang }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm mb-2">Tanggal Awal</label>
            <input type="date" name="tanggal_awal"
                   value="{{ $tanggalAwal }}"
                   class="w-full border rounded-lg p-2"
                   required>
        </div>

        <div>
            <label class="block text-sm mb-2">Tanggal Akhir</label>
            <input type="date" name="tanggal_akhir"
                   value="{{ $tanggalAkhir }}"
                   class="w-full border rounded-lg p-2"
                   required>
        </div>

        <button type="submit"
                class="bg-green-800 text-white rounded-lg p-2">
            Tampilkan
        </button>
    </form>
</div>

<!-- RINGKASAN -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">

    <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-green-800">
        <h3 class="text-gray-500 text-sm">Total QRIS Sistem</h3>
        <p class="text-3xl font-bold mt-2">
            Rp {{ number_format($totalSistem, 0, ',', '.') }}
        </p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-green-800">
        <h3 class="text-gray-500 text-sm">Jumlah Transaksi QRIS</h3>
        <p class="text-3xl font-bold mt-2">
            {{ $transaksi->count() }} Transaksi
        </p>
    </div>

</div>

<!-- FORM REKONSILIASI -->
<div class="bg-white p-6 rounded-lg shadow-sm mb-6">

    <h3 class="text-lg font-bold mb-2">
        Pencocokan Pembayaran QRIS
    </h3>

    <p class="text-sm text-gray-500 mb-5">
        Masukkan total pembayaran aktual berdasarkan laporan
        penyedia QRIS untuk cabang dan periode yang sama.
        Jangan gunakan nominal sebelum dikurangi MDR jika
        laporan penyedia menampilkan nilai bersih.
    </p>

    <form action="{{ route('rekonsiliasi.store') }}"
          method="POST" class="space-y-4">

        @csrf

        <div>
            <label class="block text-sm font-semibold mb-2">
                Cabang
            </label>

            <select name="id_cabang"
                    class="w-full border rounded-lg p-3"
                    required>
                <option value="">Pilih Cabang</option>
                @foreach($cabang as $item)
                    <option value="{{ $item->id_cabang }}"
                        @selected((string)old('id_cabang', $idCabang) === (string)$item->id_cabang)>
                        {{ $item->nama_cabang }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">
                    Periode Awal
                </label>
                <input type="date" name="periode_awal"
                       value="{{ old('periode_awal', $tanggalAwal) }}"
                       class="w-full border rounded-lg p-3"
                       required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">
                    Periode Akhir
                </label>
                <input type="date" name="periode_akhir"
                       value="{{ old('periode_akhir', $tanggalAkhir) }}"
                       class="w-full border rounded-lg p-3"
                       required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-2">
                Total QRIS Aktual (Rp)
            </label>

            <input type="number"
                   name="total_aktual"
                   min="0"
                   step="0.01"
                   value="{{ old('total_aktual') }}"
                   placeholder="Contoh: 63000"
                   class="w-full border rounded-lg p-3"
                   required>
        </div>

        <button type="submit"
                class="bg-green-800 hover:bg-green-900
                       text-white px-6 py-3 rounded-lg font-semibold">
            Cek dan Simpan Rekonsiliasi
        </button>
    </form>
</div>

<!-- DAFTAR TRANSAKSI QRIS -->
<div class="bg-white p-6 rounded-lg shadow-sm mb-6">

    <h3 class="font-bold text-lg mb-4">
        Daftar Transaksi QRIS
    </h3>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">ID Transaksi</th>
                    <th class="p-3">Waktu</th>
                    <th class="p-3">Cabang</th>
                    <th class="p-3">Kasir</th>
                    <th class="p-3">Nominal</th>
                </tr>
            </thead>

            <tbody>
                @forelse($transaksi as $row)
                    <tr class="border-b">
                        <td class="p-3">#{{ $row->id_transaksi }}</td>
                        <td class="p-3 whitespace-nowrap">
                            {{ $row->waktu_transaksi }}
                        </td>
                        <td class="p-3">{{ $row->nama_cabang }}</td>
                        <td class="p-3">{{ $row->nama_kasir }}</td>
                        <td class="p-3">
                            Rp {{ number_format($row->total_bayar, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"
                            class="p-6 text-center text-gray-500">
                            Belum ada transaksi QRIS pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- RIWAYAT HASIL REKONSILIASI -->
<div class="bg-white p-6 rounded-lg shadow-sm">

    <h3 class="font-bold text-lg mb-4">
        Riwayat Rekonsiliasi QRIS
    </h3>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">Cabang</th>
                    <th class="p-3">Periode</th>
                    <th class="p-3">Total Sistem</th>
                    <th class="p-3">Total Aktual</th>
                    <th class="p-3">Selisih</th>
                    <th class="p-3">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($riwayat as $row)
                    <tr class="border-b">
                        <td class="p-3">{{ $row->nama_cabang }}</td>
                        <td class="p-3 whitespace-nowrap">
                            {{ $row->periode_awal }} s/d
                            {{ $row->periode_akhir }}
                        </td>
                        <td class="p-3">
                            Rp {{ number_format($row->total_sistem, 0, ',', '.') }}
                        </td>
                        <td class="p-3">
                            Rp {{ number_format($row->total_aktual, 0, ',', '.') }}
                        </td>
                        <td class="p-3">
                            Rp {{ number_format($row->selisih, 0, ',', '.') }}
                        </td>

                        <td class="p-3">
                            @if(abs((float)$row->selisih) < 0.005)
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full font-semibold">
                                    Cocok Total
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full font-semibold">
                                    Selisih
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="p-6 text-center text-gray-500">
                            Belum ada hasil rekonsiliasi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
