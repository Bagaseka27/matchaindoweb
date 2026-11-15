<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analisis & Rekapitulasi - Matcha Indonesia</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 0; padding: 20px; font-size: 12px; }
        .header { text-align: center; border-bottom: 3px double #2F593E; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2F593E; font-size: 22px; letter-spacing: 1px; }
        .header p { margin: 4px 0; color: #555; font-size: 12px; }
        .section-title { font-size: 14px; font-weight: bold; color: #2F593E; margin-top: 20px; margin-bottom: 8px; border-left: 4px solid #2F593E; padding-left: 8px; }
        .summary-box { display: flex; justify-content: space-between; margin-bottom: 15px; gap: 10px; }
        .card { flex: 1; border: 1px solid #ccc; padding: 10px; border-radius: 4px; background: #f9f9f9; }
        .card h4 { margin: 0 0 4px 0; font-size: 10px; text-transform: uppercase; color: #666; }
        .card p { margin: 0; font-size: 14px; font-weight: bold; color: #2F593E; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 15px; }
        th, td { border: 1px solid #bbb; padding: 6px 8px; text-align: left; font-size: 11px; }
        th { background-color: #2F593E; color: white; text-transform: uppercase; }
        tr:nth-child(even) { background-color: #fdfdfd; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; float: right; text-align: center; width: 220px; page-break-inside: avoid; }
        .sign-space { height: 55px; }
        .sign-name { font-weight: bold; border-bottom: 1px solid #111; padding-bottom: 2px; display: inline-block; min-width: 180px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2F593E; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Cetak / Simpan PDF</button>
        <a href="{{ route('laporan.index') }}" style="padding: 8px 16px; background: #666; color: white; text-decoration: none; border-radius: 4px; margin-left: 8px;">Kembali</a>
    </div>

    <!-- Kop Surat -->
    <div class="header">
        <h1>MATCHA INDONESIA</h1>
        <p>Laporan Analisis Keuangan & Rekapitulasi Penjualan Cabang Outdoor</p>
        <p><b>Filter Cabang:</b> {{ $namaCabang }} | <b>Periode:</b> {{ \Carbon\Carbon::parse($tanggalAwal)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') }}</p>
    </div>

    <!-- Kartu Statistik Utama -->
    <div class="summary-box">
        <div class="card">
            <h4>Total Pendapatan</h4>
            <p>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <h4>Total Transaksi</h4>
            <p>{{ number_format($totalTransaksi, 0, ',', '.') }} Transaksi</p>
        </div>
        <div class="card">
            <h4>Pendapatan Tunai</h4>
            <p>Rp {{ number_format($totalTunai, 0, ',', '.') }}</p>
        </div>
        <div class="card">
            <h4>Pendapatan QRIS</h4>
            <p>Rp {{ number_format($totalQris, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- 1. Rekapitulasi Per Bulan / Tahun (Analisis Tren) -->
    <div class="section-title">1. Ringkasan Pendapatan Per Bulan (Analisis Tren)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">No</th>
                <th style="width: 40%;">Bulan / Tahun</th>
                <th style="width: 20%;" class="text-center">Jumlah Transaksi</th>
                <th style="width: 30%;" class="text-right">Total Pendapatan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapPerBulan as $idx => $rb)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><b>{{ \Carbon\Carbon::parse($rb->bulan . '-01')->translatedFormat('F Y') }}</b></td>
                <td class="text-center">{{ number_format($rb->jumlah_transaksi, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($rb->total_pendapatan, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 2. Rekapitulasi Per Cabang -->
    <div class="section-title">2. Kinerja Per Cabang Outdoor</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">No</th>
                <th style="width: 40%;">Nama Cabang</th>
                <th style="width: 20%;" class="text-center">Jumlah Transaksi</th>
                <th style="width: 30%;" class="text-right">Total Pendapatan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapPerCabang as $idx => $rc)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><b>{{ $rc->nama_cabang }}</b></td>
                <td class="text-center">{{ number_format($rc->jumlah_transaksi, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($rc->total_pendapatan, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 3. Rincian Transaksi Lengkap -->
    <div class="section-title">3. Rincian Detail Transaksi Penjualan</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 20%;">Waktu</th>
                <th style="width: 18%;">Order ID</th>
                <th style="width: 22%;">Cabang</th>
                <th style="width: 15%;">Barista</th>
                <th style="width: 10%;" class="text-center">Bayar</th>
                <th style="width: 15%;" class="text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->waktu_transaksi)->format('d/m/Y H:i') }}</td>
                    <td style="font-family: monospace;">{{ $row->order_id ?? '-' }}</td>
                    <td><b>{{ $row->nama_cabang }}</b></td>
                    <td>{{ $row->nama_barista }}</td>
                    <td class="text-center" style="text-transform: uppercase;"><b>{{ $row->metode_bayar }}</b></td>
                    <td class="text-right">Rp {{ number_format($row->total_bayar, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center" style="padding: 15px; color: #666;">Tidak ada data transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Tunggal Owner (Pak Kurniawan) -->
    <div class="footer">
        <p>Kediri, {{ now()->format('d F Y') }}</p>
        <p><b>Pemilik Matcha Indonesia</b></p>
        <div class="sign-space"></div>
        <p class="sign-name">Pak Kurniawan</p>
    </div>

</body>
</html>