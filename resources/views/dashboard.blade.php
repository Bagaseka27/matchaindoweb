@extends('layouts.app')

@section('title', 'Dashboard Konsolidasi Pendapatan')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Card 1 -->
    <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-[#2F593E]">
        <h3 class="text-gray-500 text-sm font-semibold mb-2">Total Pendapatan Hari Ini</h3>
        <p class="text-3xl font-bold text-gray-800">Rp 0</p>
    </div>
    
    <!-- Card 2 -->
    <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-[#2F593E]">
        <h3 class="text-gray-500 text-sm font-semibold mb-2">Total Transaksi Hari Ini</h3>
        <p class="text-3xl font-bold text-gray-800">0 Transaksi</p>
    </div>

    <!-- Card 3 -->
    <div class="bg-white p-6 rounded-lg shadow-sm border-t-4 border-[#2F593E]">
        <h3 class="text-gray-500 text-sm font-semibold mb-2">Cabang Teramai Hari Ini</h3>
        <p class="text-xl font-bold text-gray-800">-</p>
    </div>
</div>

<!-- Placeholder untuk Grafik & Tabel Stok Kritis -->
<div class="bg-white p-6 rounded-lg shadow-sm">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Stok Kritis Bahan Baku & Cup</h3>
    <p class="text-sm text-gray-500">Belum ada data stok yang menipis.</p>
</div>
@endsection