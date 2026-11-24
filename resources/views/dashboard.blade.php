@extends('layouts.app')

@section('title', 'Dashboard Konsolidasi Pendapatan - Matcha Indonesia')

@section('content')
<!-- Header, Filter Periode (Hari/Bulan/Tahun) & Filter Cabang -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Dashboard Konsolidasi</h2>
        <p class="text-sm text-gray-500">
            <span id="subjudulCabang">
                @if($idCabang)
                    Menampilkan data spesifik untuk cabang terpilih
                @else
                    Menampilkan rekapitulasi keseluruhan dari cabang outdoor Matcha Indonesia
                @endif
            </span>
            — <span id="teksPeriodeAktif" class="font-semibold text-[#2F593E]">{{ $labelPeriode }}</span>
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <!-- Filter Periode: Hari Ini, Bulan Ini, Tahun Ini -->
        <div class="inline-flex rounded-lg border border-[#2F593E] overflow-hidden bg-white">
            <button type="button" data-periode="hari"
                class="btn-filter-periode px-3.5 py-2 text-xs font-semibold transition {{ $periode === 'hari' ? 'bg-[#2F593E] text-white' : 'text-[#2F593E] hover:bg-green-50' }}">
                Hari Ini
            </button>
            <button type="button" data-periode="bulan"
                class="btn-filter-periode px-3.5 py-2 text-xs font-semibold border-l border-[#2F593E] transition {{ $periode === 'bulan' ? 'bg-[#2F593E] text-white' : 'text-[#2F593E] hover:bg-green-50' }}">
                Bulan Ini
            </button>
            <button type="button" data-periode="tahun"
                class="btn-filter-periode px-3.5 py-2 text-xs font-semibold border-l border-[#2F593E] transition {{ $periode === 'tahun' ? 'bg-[#2F593E] text-white' : 'text-[#2F593E] hover:bg-green-50' }}">
                Tahun Ini
            </button>
        </div>

        <!-- Dropdown Filter Cabang -->
        <select id="selectCabang" name="cabang" class="rounded-lg border-gray-300 text-sm focus:border-[#2F593E] focus:ring-[#2F593E]">
            <option value="">Semua Cabang (Konsolidasi)</option>
            @foreach($cabangs as $c)
                <option value="{{ $c->id_cabang }}" {{ $idCabang == $c->id_cabang ? 'selected' : '' }}>
                    {{ $c->nama_cabang }}
                </option>
            @endforeach
        </select>

        <button type="button" id="btnResetFilter"
            class="px-3 py-2 bg-gray-100 text-gray-600 text-xs font-semibold rounded-lg hover:bg-gray-200 transition {{ ($idCabang || $periode !== 'hari') ? '' : 'hidden' }}">
            Reset
        </button>
    </div>
</div>

<!-- 1. Ringkasan Utama (Total Pendapatan, Jumlah Transaksi, Pendapatan Cash, Pendapatan QRIS) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <!-- Total Pendapatan -->
    <div class="bg-white p-5 rounded-lg shadow-sm border-t-4 border-[#2F593E]">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-gray-500 text-sm font-semibold">Total Pendapatan</h3>
            <span id="badgePeriode" class="bg-green-50 text-[#2F593E] text-[11px] font-bold px-2 py-0.5 rounded uppercase">
                {{ $periode }}
            </span>
        </div>
        <p id="nilaiTotalPendapatan" class="text-2xl font-bold text-gray-800">
            Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
        </p>
        <span class="text-xs text-gray-500 mt-1.5 block">
            Teramai:
            <strong id="namaCabangTeramai" class="text-gray-700">
                {{ $cabangTeramai->nama_cabang ?? ($idCabang ? 'Cabang Terpilih' : 'Belum ada transaksi') }}
            </strong>
        </span>
    </div>

    <!-- Jumlah Transaksi -->
    <div class="bg-white p-5 rounded-lg shadow-sm border-t-4 border-[#2F593E]">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-gray-500 text-sm font-semibold">Jumlah Transaksi</h3>
            <span class="bg-gray-100 text-gray-600 text-[11px] font-semibold px-2 py-0.5 rounded">TOTAL</span>
        </div>
        <p id="nilaiTotalTransaksi" class="text-2xl font-bold text-gray-800">
            {{ number_format($totalTransaksi, 0, ',', '.') }} Transaksi
        </p>
        <span class="text-xs text-gray-500 mt-1.5 block">Sesuai periode & cabang terpilih</span>
    </div>

    <!-- Pendapatan Tunai (Cash) -->
    <div class="bg-white p-5 rounded-lg shadow-sm border-t-4 border-[#2F593E]">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-gray-500 text-sm font-semibold">Pendapatan Tunai (Cash)</h3>
            <span class="bg-amber-50 text-amber-700 text-[11px] font-bold px-2 py-0.5 rounded">CASH</span>
        </div>
        <p id="nilaiPendapatanCash" class="text-2xl font-bold text-gray-800">
            Rp {{ number_format($pendapatanCash, 0, ',', '.') }}
        </p>
        <span class="text-xs text-gray-500 mt-1.5 block">
            <strong id="jumlahTransaksiCash" class="text-gray-700">{{ $transaksiCash }}</strong> transaksi tunai
        </span>
    </div>

    <!-- Pendapatan QRIS -->
    <div class="bg-white p-5 rounded-lg shadow-sm border-t-4 border-[#2F593E]">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-gray-500 text-sm font-semibold">Pendapatan QRIS</h3>
            <span class="bg-sky-50 text-sky-700 text-[11px] font-bold px-2 py-0.5 rounded">QRIS</span>
        </div>
        <p id="nilaiPendapatanQris" class="text-2xl font-bold text-gray-800">
            Rp {{ number_format($pendapatanQris, 0, ',', '.') }}
        </p>
        <span class="text-xs text-gray-500 mt-1.5 block">
            <strong id="jumlahTransaksiQris" class="text-gray-700">{{ $transaksiQris }}</strong> transaksi QRIS
        </span>
    </div>
</div>

<!-- 2. Tren Pendapatan per Hari, Bulan, Tahun -->
<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-3">Tren Pendapatan (Per Hari, Bulan, Tahun)</h3>
    <div id="containerTren" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach(['hari', 'bulan', 'tahun'] as $k)
            @php
                $t = $trenPendapatan[$k];
                $naik = $t['persen'] >= 0;
            @endphp
            <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold text-gray-500">{{ $t['label'] }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded {{ $naik ? 'bg-green-50 text-[#2F593E]' : 'bg-red-50 text-red-600' }}">
                        {{ $naik ? '▲ +' : '▼ ' }}{{ $t['persen'] }}%
                    </span>
                </div>
                <p class="text-xl font-extrabold text-gray-800 mb-1">
                    Rp {{ number_format($t['nilai'], 0, ',', '.') }}
                </p>
                <span class="text-xs text-gray-500">
                    {{ $t['ket'] }}: Rp {{ number_format($t['pembanding'], 0, ',', '.') }}
                </span>
            </div>
        @endforeach
    </div>
</div>

<!-- 3. Kinerja Cabang Outdoor -->
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 gap-1">
        <h3 class="text-lg font-semibold text-gray-800">Kinerja Cabang Outdoor</h3>
        <span class="text-xs text-gray-500">Perbandingan omzet, kontribusi, transaksi, Cash & QRIS per cabang</span>
    </div>
    <div id="containerKinerjaCabang" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($pendapatanPerCabang as $cabang)
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-1">
                <h4 class="text-md font-bold text-[#2F593E]">{{ $cabang->nama_cabang }}</h4>
                <span class="bg-green-50 text-[#2F593E] text-xs font-bold px-2 py-0.5 rounded">
                    {{ $cabang->kontribusi }}%
                </span>
            </div>
            <p class="text-xl font-extrabold text-gray-800 mb-2">
                Rp {{ number_format($cabang->total_pendapatan, 0, ',', '.') }}
            </p>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mb-3">
                <div class="bg-[#2F593E] h-1.5 rounded-full" style="width: {{ $cabang->kontribusi }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Transaksi:</span>
                <span class="font-semibold text-gray-700">{{ $cabang->total_transaksi }}</span>
            </div>
            <div class="flex justify-between text-xs text-gray-500 pt-1 border-t border-gray-100">
                <span>Cash: <strong class="text-gray-700">Rp {{ number_format($cabang->pendapatan_cash, 0, ',', '.') }}</strong></span>
                <span>QRIS: <strong class="text-gray-700">Rp {{ number_format($cabang->pendapatan_qris, 0, ',', '.') }}</strong></span>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- 4. Grafik Pendapatan (Filter Hari/Bulan/Tahun) & Grafik 2 Line (Terlaris & Kurang Diminati) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Grafik Pendapatan -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h3 id="judulGrafikPendapatan" class="text-lg font-semibold text-gray-800">
                {{ $grafikPendapatan['judul'] }}
            </h3>
            <div class="inline-flex rounded-md border border-[#2F593E] overflow-hidden">
                <button type="button" data-periode="hari"
                    class="btn-filter-periode px-2.5 py-1 text-xs font-semibold transition {{ $periode === 'hari' ? 'bg-[#2F593E] text-white' : 'text-[#2F593E] hover:bg-green-50' }}">
                    Hari
                </button>
                <button type="button" data-periode="bulan"
                    class="btn-filter-periode px-2.5 py-1 text-xs font-semibold border-l border-[#2F593E] transition {{ $periode === 'bulan' ? 'bg-[#2F593E] text-white' : 'text-[#2F593E] hover:bg-green-50' }}">
                    Bulan
                </button>
                <button type="button" data-periode="tahun"
                    class="btn-filter-periode px-2.5 py-1 text-xs font-semibold border-l border-[#2F593E] transition {{ $periode === 'tahun' ? 'bg-[#2F593E] text-white' : 'text-[#2F593E] hover:bg-green-50' }}">
                    Tahun
                </button>
            </div>
        </div>
        <div class="relative h-72">
            <canvas id="grafikPenjualanCanvas"></canvas>
        </div>
    </div>

    <!-- Grafik 2 Line: Menu Terlaris & Kurang Diminati -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Grafik Menu Terlaris & Kurang Diminati</h3>
            <p class="text-xs text-gray-500">Perbandingan 2 line: Menu Terlaris (Hijau) vs Menu Kurang Diminati (Merah)</p>
        </div>
        <div class="relative h-72">
            <canvas id="grafikMenu2LineCanvas"></canvas>
        </div>
    </div>
</div>

<!-- 5. Top 5 Menu Terlaris, Menu Kurang Diminati & Stok Kritis -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Top 5 Menu Terlaris -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 5 Menu Terlaris</h3>
        <div id="listMenuTerlaris">
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

    <!-- Menu Kurang Diminati -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Menu Kurang Diminati (Perlu Evaluasi)</h3>
        <div id="listMenuSepi">
            @if(count($menuSepi) > 0)
                <ul class="divide-y divide-gray-100">
                    @foreach($menuSepi as $index => $menu)
                    <li class="py-2.5 flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-700">{{ $index + 1 }}. {{ $menu->nama_menu }}</span>
                        <span class="bg-red-50 text-red-600 font-semibold px-2.5 py-1 rounded text-xs">
                            {{ $menu->total_terjual }} terjual
                        </span>
                    </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">Belum ada data menu.</p>
            @endif
        </div>
    </div>

    <!-- Stok Kritis Bahan Baku & Cup -->
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Stok Kritis Bahan Baku & Cup</h3>
        <p class="text-sm text-gray-500">Belum ada data stok bahan baku yang menipis di sistem.</p>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let activePeriode = @json($periode);
    let activeCabang  = @json($idCabang ?? '');

    const initGrafikPendapatan = @json($grafikPendapatan);
    const initGrafikMenu       = @json($grafikMenu2Line);

    let namaTerlarisArr = initGrafikMenu.namaTerlaris;
    let namaSepiArr     = initGrafikMenu.namaSepi;

    const formatRp = (val) => 'Rp ' + Number(val || 0).toLocaleString('id-ID');

    // 1. Chart Pendapatan
    const ctxPendapatan = document.getElementById('grafikPenjualanCanvas').getContext('2d');
    const chartPendapatan = new Chart(ctxPendapatan, {
        type: 'line',
        data: {
            labels: initGrafikPendapatan.labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: initGrafikPendapatan.data,
                borderColor: '#2F593E',
                backgroundColor: 'rgba(47, 89, 62, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#2F593E',
                pointRadius: 4,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + formatRp(ctx.parsed.y)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatRp(value)
                    }
                }
            }
        }
    });

    // 2. Chart 2 Line (Menu Terlaris vs Kurang Diminati)
    const ctxMenu2Line = document.getElementById('grafikMenu2LineCanvas').getContext('2d');
    const chartMenu2Line = new Chart(ctxMenu2Line, {
        type: 'line',
        data: {
            labels: initGrafikMenu.labels,
            datasets: [
                {
                    label: 'Menu Terlaris',
                    data: initGrafikMenu.dataTerlaris,
                    borderColor: '#2F593E',
                    backgroundColor: 'rgba(47, 89, 62, 0.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#2F593E',
                    pointRadius: 5,
                    tension: 0.25
                },
                {
                    label: 'Menu Kurang Diminati',
                    data: initGrafikMenu.dataSepi,
                    borderColor: '#DC2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#DC2626',
                    pointRadius: 5,
                    tension: 0.25
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            const i = ctx.dataIndex;
                            const menuName = ctx.datasetIndex === 0
                                ? (namaTerlarisArr[i] || '-')
                                : (namaSepiArr[i] || '-');
                            return ` ${ctx.dataset.label} (${menuName}): ${ctx.parsed.y} terjual`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });

    // 3. Update Dashboard Dinamis saat Filter Periode / Cabang diubah
    function updateButtonStates() {
        document.querySelectorAll('.btn-filter-periode').forEach(btn => {
            const isSelected = btn.dataset.periode === activePeriode;
            btn.classList.toggle('bg-[#2F593E]', isSelected);
            btn.classList.toggle('text-white', isSelected);
            btn.classList.toggle('text-[#2F593E]', !isSelected);
            btn.classList.toggle('hover:bg-green-50', !isSelected);
        });

        const btnReset = document.getElementById('btnResetFilter');
        if (activeCabang || activePeriode !== 'hari') {
            btnReset.classList.remove('hidden');
        } else {
            btnReset.classList.add('hidden');
        }
    }

    function refreshDashboard() {
        updateButtonStates();

        const params = new URLSearchParams();
        if (activePeriode) params.set('periode', activePeriode);
        if (activeCabang) params.set('cabang', activeCabang);

        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.replaceState({}, '', newUrl);

        fetch(`{{ route('dashboard') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(res => {
            document.getElementById('subjudulCabang').textContent = activeCabang
                ? 'Menampilkan data spesifik untuk cabang terpilih'
                : 'Menampilkan rekapitulasi keseluruhan dari cabang outdoor Matcha Indonesia';
            document.getElementById('teksPeriodeAktif').textContent = res.labelPeriode;
            document.getElementById('badgePeriode').textContent = res.periode;

            document.getElementById('nilaiTotalPendapatan').textContent = formatRp(res.totalPendapatan);
            document.getElementById('namaCabangTeramai').textContent = res.cabangTeramai
                ? res.cabangTeramai.nama_cabang
                : (activeCabang ? 'Cabang Terpilih' : 'Belum ada transaksi');

            document.getElementById('nilaiTotalTransaksi').textContent = `${Number(res.totalTransaksi).toLocaleString('id-ID')} Transaksi`;
            document.getElementById('nilaiPendapatanCash').textContent = formatRp(res.pendapatanCash);
            document.getElementById('jumlahTransaksiCash').textContent = res.transaksiCash;
            document.getElementById('nilaiPendapatanQris').textContent = formatRp(res.pendapatanQris);
            document.getElementById('jumlahTransaksiQris').textContent = res.transaksiQris;

            // Update Tren Pendapatan
            let trenHtml = '';
            ['hari', 'bulan', 'tahun'].forEach(k => {
                const t = res.trenPendapatan[k];
                const naik = t.persen >= 0;
                const badgeClass = naik ? 'bg-green-50 text-[#2F593E]' : 'bg-red-50 text-red-600';
                trenHtml += `
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-gray-500">${t.label}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded ${badgeClass}">
                                ${naik ? '▲ +' : '▼ '}${t.persen}%
                            </span>
                        </div>
                        <p class="text-xl font-extrabold text-gray-800 mb-1">${formatRp(t.nilai)}</p>
                        <span class="text-xs text-gray-500">${t.ket}: ${formatRp(t.pembanding)}</span>
                    </div>`;
            });
            document.getElementById('containerTren').innerHTML = trenHtml;

            // Update Kinerja Cabang Outdoor
            let cabangHtml = '';
            res.pendapatanPerCabang.forEach(cab => {
                cabangHtml += `
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-md font-bold text-[#2F593E]">${cab.nama_cabang}</h4>
                            <span class="bg-green-50 text-[#2F593E] text-xs font-bold px-2 py-0.5 rounded">
                                ${cab.kontribusi}%
                            </span>
                        </div>
                        <p class="text-xl font-extrabold text-gray-800 mb-2">${formatRp(cab.total_pendapatan)}</p>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 mb-3">
                            <div class="bg-[#2F593E] h-1.5 rounded-full" style="width: ${cab.kontribusi}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Transaksi:</span>
                            <span class="font-semibold text-gray-700">${cab.total_transaksi}</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 pt-1 border-t border-gray-100">
                            <span>Cash: <strong class="text-gray-700">${formatRp(cab.pendapatan_cash)}</strong></span>
                            <span>QRIS: <strong class="text-gray-700">${formatRp(cab.pendapatan_qris)}</strong></span>
                        </div>
                    </div>`;
            });
            document.getElementById('containerKinerjaCabang').innerHTML = cabangHtml;

            // Update Grafik Pendapatan
            document.getElementById('judulGrafikPendapatan').textContent = res.grafikPendapatan.judul;
            chartPendapatan.data.labels = res.grafikPendapatan.labels;
            chartPendapatan.data.datasets[0].data = res.grafikPendapatan.data;
            chartPendapatan.update();

            // Update Grafik 2 Line
            namaTerlarisArr = res.grafikMenu2Line.namaTerlaris;
            namaSepiArr     = res.grafikMenu2Line.namaSepi;
            chartMenu2Line.data.labels = res.grafikMenu2Line.labels;
            chartMenu2Line.data.datasets[0].data = res.grafikMenu2Line.dataTerlaris;
            chartMenu2Line.data.datasets[1].data = res.grafikMenu2Line.dataSepi;
            chartMenu2Line.update();

            // Update List Menu Terlaris
            if (res.menuTerlaris.length > 0) {
                document.getElementById('listMenuTerlaris').innerHTML = `<ul class="divide-y divide-gray-100">` +
                    res.menuTerlaris.map((m, idx) => `
                        <li class="py-2.5 flex justify-between items-center text-sm">
                            <span class="font-medium text-gray-700">
                                <span class="text-[#2F593E] font-bold mr-2">${idx + 1}.</span> ${m.nama_menu}
                            </span>
                            <span class="bg-green-50 text-[#2F593E] font-semibold px-2.5 py-1 rounded text-xs">
                                ${m.total_terjual} terjual
                            </span>
                        </li>
                    `).join('') + `</ul>`;
            } else {
                document.getElementById('listMenuTerlaris').innerHTML = `<p class="text-sm text-gray-500">Belum ada data penjualan menu.</p>`;
            }

            // Update List Menu Kurang Diminati
            if (res.menuSepi.length > 0) {
                document.getElementById('listMenuSepi').innerHTML = `<ul class="divide-y divide-gray-100">` +
                    res.menuSepi.map((m, idx) => `
                        <li class="py-2.5 flex justify-between items-center text-sm">
                            <span class="font-medium text-gray-700">${idx + 1}. ${m.nama_menu}</span>
                            <span class="bg-red-50 text-red-600 font-semibold px-2.5 py-1 rounded text-xs">
                                ${m.total_terjual} terjual
                            </span>
                        </li>
                    `).join('') + `</ul>`;
            } else {
                document.getElementById('listMenuSepi').innerHTML = `<p class="text-sm text-gray-500">Belum ada data menu.</p>`;
            }
        });
    }

    document.querySelectorAll('.btn-filter-periode').forEach(btn => {
        btn.addEventListener('click', function () {
            activePeriode = this.dataset.periode;
            refreshDashboard();
        });
    });

    document.getElementById('selectCabang').addEventListener('change', function () {
        activeCabang = this.value;
        refreshDashboard();
    });

    document.getElementById('btnResetFilter').addEventListener('click', function () {
        activePeriode = 'hari';
        activeCabang = '';
        document.getElementById('selectCabang').value = '';
        refreshDashboard();
    });
</script>
@endpush
@endsection