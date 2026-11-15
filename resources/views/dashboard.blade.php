@extends('layouts.app')

@section('title', 'Dashboard Konsolidasi Pendapatan - Matcha Indonesia')

@section('content')
<!-- Header & Filter Cabang -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Dashboard Konsolidasi</h2>
        <p class="text-sm text-gray-500">
            @if(request('cabang'))
                Menampilkan data spesifik untuk cabang terpilih.
            @else
                Menampilkan rekapitulasi keseluruhan dari 4 cabang outdoor Matcha Indonesia.
            @endif
        </p>
    </div>

    <!-- Dropdown Filter Cabang -->
    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
        <select name="cabang" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-[#2F593E] focus:ring-[#2F593E]">
            <option value="">Semua Cabang (Konsolidasi)</option>
            @foreach($cabangs as $c)
                <option value="{{ $c->id_cabang }}" {{ $idCabang == $c->id_cabang ? 'selected' : '' }}>
                    {{ $c->nama_cabang }}
                </option>
            @endforeach
        </select>
        @if($idCabang)
            <a href="{{ route('dashboard') }}" class="px-3 py-2 bg-gray-100 text-gray-600 text-xs font-semibold rounded-lg hover:bg-gray-200 transition">
                Reset
            </a>
        @endif
    </form>
</div>

<!-- Ringkasan Utama Hari Ini -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-[#2F593E]">
        <h3 class="text-gray-500 text-sm font-semibold mb-2">Total Pendapatan Hari Ini</h3>
        <p class="text-3xl font-bold text-gray-800">
            Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
        </p>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-[#2F593E]">
        <h3 class="text-gray-500 text-sm font-semibold mb-2">Total Transaksi Hari Ini</h3>
        <p class="text-3xl font-bold text-gray-800">
            {{ number_format($totalTransaksi, 0, ',', '.') }} Transaksi
        </p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-[#2F593E]">
        <h3 class="text-gray-500 text-sm font-semibold mb-2">Cabang Teramai Hari Ini</h3>
        <p class="text-xl font-bold text-gray-800">
            {{ $cabangTeramai->nama_cabang ?? ($idCabang ? 'Cabang Terpilih' : 'Belum ada transaksi') }}
        </p>
        @if(isset($cabangTeramai))
            <span class="text-xs text-gray-500 mt-1 block">{{ $cabangTeramai->jumlah_transaksi }} transaksi tercatat</span>
        @endif
    </div>
</div>

<!-- Rincian Pendapatan 4 Cabang Outdoor (Hanya tampil jika tidak sedang memfilter cabang tertentu) -->
@if(!$idCabang)
<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Performansi 4 Cabang Outdoor Hari Ini</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($pendapatanPerCabang as $cabang)
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
            <h4 class="text-md font-bold text-[#2F593E] mb-1">{{ $cabang->nama_cabang }}</h4>
            <p class="text-xl font-extrabold text-gray-800 mb-2">
                Rp {{ number_format($cabang->total_pendapatan, 0, ',', '.') }}
            </p>
            <div class="flex justify-between text-xs text-gray-500">
                <span>Transaksi:</span>
                <span class="font-semibold text-gray-700">{{ $cabang->total_transaksi }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Grafik Penjualan & Analisis Menu -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Grafik Pendapatan (7 Hari Terakhir) -->
    <div class="bg-white p-6 rounded-lg shadow-sm lg:col-span-2">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Grafik Pendapatan (7 Hari Terakhir)</h3>
        <div class="relative h-72">
            <canvas id="grafikPenjualanCanvas"></canvas>
        </div>
    </div>

    <!-- Top 5 Menu Terlaris -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 5 Menu Terlaris</h3>
        @if(count($menuTerlaris) > 0)
            <ul class="divide-y divide-gray-100">
                @foreach($menuTerlaris as $index => $menu)
                <li class="py-2.5 flex justify-between items-center text-sm">
                    <span class="font-medium text-gray-700">
                        <span class="text-[#2F593E] font-bold mr-2">{{ $index + 1 }}.</span> {{ $menu->nama_menu }}
                    </span>
                    <span class="bg-green-50 text-[#2F593E] font-semibold px-2.5 py-1 rounded text-xs">
                        {{ $menu->total_terjual }} terjual
                    </span>
                </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Belum ada data penjualan menu.</p>
        @endif
    </div>
</div>

<!-- Menu Kurang Diminati & Stok Kritis -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Menu Kurang Diminati (Perlu Evaluasi)</h3>
        @if(count($menuSepi) > 0)
            <ul class="divide-y divide-gray-100">
                @foreach($menuSepi as $index => $menu)
                <li class="py-2.5 flex justify-between items-center text-sm">
                    <span class="font-medium text-gray-700">{{ $index + 1 }}. {{ $menu->nama_menu }}</span>
                    <span class="bg-gray-100 text-gray-600 font-semibold px-2.5 py-1 rounded text-xs">
                        {{ $menu->total_terjual }} terjual
                    </span>
                </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">Belum ada data menu.</p>
        @endif
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Stok Kritis Bahan Baku & Cup</h3>
        <p class="text-sm text-gray-500">Belum ada data stok bahan baku yang menipis di sistem.</p>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('grafikPenjualanCanvas').getContext('2d');
    const salesData = @json($grafikPenjualan);
    
    const labels = salesData.map(item => item.tanggal);
    const dataValues = salesData.map(item => item.total_pendapatan);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: dataValues,
                borderColor: '#2F593E',
                backgroundColor: 'rgba(47, 89, 62, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection