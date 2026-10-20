@extends('layouts.app')

@section('title', 'Monitoring Stok Cup')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Monitoring Stok Cup</h2>
        <p class="mt-1 text-sm text-gray-500">Pantau persediaan semua cabang, lakukan restock, dan lihat rekap mutasi terbaru.</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-300 bg-red-100 px-4 py-3 text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <section class="overflow-hidden rounded-xl bg-white shadow-md">
        <div class="border-b px-6 py-4">
            <h3 class="text-lg font-bold text-gray-800">Stok per Cabang dan Ukuran Cup</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#2F593E] text-white">
                    <tr>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Ukuran Cup</th>
                        <th class="px-4 py-3">Stok</th>
                        <th class="px-4 py-3">Minimum</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Restock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stokCup as $row)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $row->nama_cabang }}
                                @unless($row->is_aktif)<span class="text-xs text-gray-500">(Nonaktif)</span>@endunless
                            </td>
                            <td class="px-4 py-3">{{ $row->nama_cup }} <span class="text-sm text-gray-500">({{ $row->volume_ml }} ml)</span></td>
                            <td class="px-4 py-3 font-bold {{ $row->jumlah_stok <= $row->stok_minimum ? 'text-red-600' : 'text-green-700' }}">{{ $row->jumlah_stok }}</td>
                            <td class="px-4 py-3">{{ $row->stok_minimum }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-xs {{ $row->jumlah_stok <= $row->stok_minimum ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $row->jumlah_stok <= $row->stok_minimum ? 'Kritis' : 'Aman' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <form action="{{ route('stok-cup.restock') }}" method="POST" class="flex min-w-[330px] items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="id_cabang" value="{{ $row->id_cabang }}">
                                    <input type="hidden" name="id_cup" value="{{ $row->id_cup }}">
                                    <label class="sr-only" for="jumlah-{{ $row->id_cabang }}-{{ $row->id_cup }}">Jumlah restock {{ $row->nama_cup }} di {{ $row->nama_cabang }}</label>
                                    <input id="jumlah-{{ $row->id_cabang }}-{{ $row->id_cup }}" type="number" name="jumlah" min="1" required placeholder="Jumlah" class="w-24 rounded-lg border px-3 py-2">
                                    <label class="sr-only" for="minimum-{{ $row->id_cabang }}-{{ $row->id_cup }}">Stok minimum</label>
                                    <input id="minimum-{{ $row->id_cabang }}-{{ $row->id_cup }}" type="number" name="stok_minimum" min="0" value="{{ $row->stok_minimum }}" class="w-24 rounded-lg border px-3 py-2">
                                    <button type="submit" class="rounded-lg bg-[#2F593E] px-3 py-2 text-sm text-white hover:bg-green-800">Restock</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada data cabang atau jenis cup.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl bg-white shadow-md">
        <div class="flex items-center justify-between border-b px-6 py-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Rekap Mutasi Cup</h3>
                <p class="text-sm text-gray-500">Maksimal 100 mutasi terbaru, termasuk pemakaian dari transaksi mobile.</p>
            </div>
            <a href="{{ route('transaksi.index') }}" class="text-sm font-semibold text-green-800 hover:underline">Lihat transaksi →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Cup</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Jumlah</th>
                        <th class="px-4 py-3">Petugas</th>
                        <th class="px-4 py-3">Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasi as $row)
                        <tr class="border-b">
                            <td class="whitespace-nowrap px-4 py-3">{{ \Illuminate\Support\Carbon::parse($row->waktu)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $row->nama_cabang }}</td>
                            <td class="px-4 py-3">{{ $row->nama_cup }}</td>
                            <td class="px-4 py-3">{{ ucfirst($row->jenis) }}</td>
                            <td class="px-4 py-3">{{ $row->jenis === 'pemakaian' ? '-' : '+' }}{{ $row->jumlah }}</td>
                            <td class="px-4 py-3">{{ $row->nama_user }}</td>
                            <td class="px-4 py-3">{{ $row->id_transaksi ? '#'.$row->id_transaksi : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">Belum ada mutasi cup.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
