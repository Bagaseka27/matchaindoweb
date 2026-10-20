@extends('layouts.app')

@section('title', 'Transaksi Penjualan')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Transaksi Penjualan</h2>
        <p class="mt-1 text-sm text-gray-500">Daftar transaksi yang dicatat Barista melalui aplikasi mobile.</p>
    </div>

    <section class="overflow-hidden rounded-xl bg-white shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#2F593E] text-white">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Barista</th>
                        <th class="px-4 py-3">Pesanan</th>
                        <th class="px-4 py-3">Pembayaran</th>
                        <th class="px-4 py-3">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $row)
                        <tr class="border-b align-top hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3">{{ \Illuminate\Support\Carbon::parse($row->waktu_transaksi)->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3">{{ $row->nama_cabang }}</td>
                            <td class="px-4 py-3">{{ $row->nama_barista }}</td>
                            <td class="px-4 py-3">
                                @foreach($detail->get($row->id_transaksi, collect()) as $item)
                                    <div>{{ $item->nama_menu }} · {{ $item->nama_cup }} × {{ $item->jumlah }}</div>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 uppercase">{{ $row->metode_bayar }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-semibold">Rp {{ number_format($row->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada transaksi penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t px-4 py-3">{{ $transaksi->links() }}</div>
    </section>
</div>
@endsection
