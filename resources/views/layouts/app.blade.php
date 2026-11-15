<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Matcha Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#2F593E] text-white flex flex-col shadow-xl">
        <div class="p-6 text-center border-b border-green-700">
            <h2 class="text-2xl font-bold tracking-wider">
                MATCHA
            </h2>
            <p class="text-xs text-green-200">
                INDONESIA
            </p>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-white text-[#2F593E] font-semibold shadow-sm' : 'text-white hover:bg-green-700' }}">
                <span>🏠</span> Dashboard
            </a>

            <!-- Pengguna -->
            <a href="{{ route('user.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('user.*') ? 'bg-white text-[#2F593E] font-semibold shadow-sm' : 'text-white hover:bg-green-700' }}">
                <span>👤</span> Pengguna
            </a>

            <!-- Kelola Transaksi -->
            <a href="{{ route('transaksi.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('transaksi.*') ? 'bg-white text-[#2F593E] font-semibold shadow-sm' : 'text-white hover:bg-green-700' }}">
                <span>🧾</span> Kelola Transaksi
            </a>

            <!-- Rekonsiliasi QRIS -->
            <a href="{{ route('rekonsiliasi.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('rekonsiliasi.*') ? 'bg-white text-[#2F593E] font-semibold shadow-sm' : 'text-white hover:bg-green-700' }}">
                <span>🔄</span> Rekonsiliasi QRIS
            </a>

            <!-- KELOLA DATA MASTER (Dropdown) -->
            <details class="relative group">
                <summary class="flex items-center justify-between gap-3 px-4 py-2.5 rounded-lg cursor-pointer list-none hover:bg-green-700 transition">
                    <span class="flex items-center gap-3">
                        <span>📦</span> Kelola Data Master
                    </span>
                    <span class="text-xs transition-transform group-open:rotate-180">
                        ▼
                    </span>
                </summary>

                <div class="mt-1 ml-4 space-y-1 border-l-2 border-green-600 pl-3">
                    <a href="{{ route('menus.index') }}"
                       class="block px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs('menus.*') ? 'bg-white text-[#2F593E] font-semibold' : 'text-white hover:bg-green-700' }}">
                        🍵 Kelola Menu
                    </a>

                    <a href="{{ route('kategori.index') }}"
                       class="block px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs('kategori.*') ? 'bg-white text-[#2F593E] font-semibold' : 'text-white hover:bg-green-700' }}">
                        🏷️ Kelola Kategori
                    </a>

                    <a href="{{ route('jenis-cup.index') }}"
                       class="block px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs('jenis-cup.*') ? 'bg-white text-[#2F593E] font-semibold' : 'text-white hover:bg-green-700' }}">
                        🥤 Kelola Jenis Cup
                    </a>
                </div>
            </details>

            <!-- Kelola Stok Bahan -->
            <a href="{{ route('stok.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('stok.*') ? 'bg-white text-[#2F593E] font-semibold shadow-sm' : 'text-white hover:bg-green-700' }}">
                <span>📊</span> Kelola Stok
            </a>

            <!-- Stok Cup & Mutasi -->
            <a href="{{ route('stok-cup.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('stok-cup.*') ? 'bg-white text-[#2F593E] font-semibold shadow-sm' : 'text-white hover:bg-green-700' }}">
                <span>🥤</span> Stok Cup & Mutasi
            </a>

            <!-- Kelola Cabang -->
            <a href="{{ route('cabang.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('cabang.*') ? 'bg-white text-[#2F593E] font-semibold shadow-sm' : 'text-white hover:bg-green-700' }}">
                <span>🏪</span> Kelola Cabang
            </a>

            <!-- Kelola Shift -->
            <a href="{{ route('shift.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('shift.*') ? 'bg-white text-[#2F593E] font-semibold shadow-sm' : 'text-white hover:bg-green-700' }}">
                <span>🕒</span> Kelola Shift
            </a>

            <!-- Laporan -->
            <a href="{{ route('laporan.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition {{ request()->routeIs('laporan.*') ? 'bg-white text-[#2F593E] font-semibold shadow-sm' : 'text-white hover:bg-green-700' }}">
                <span>📈</span> Laporan
            </a>
        </nav>

        <!-- Tombol Logout -->
        <div class="p-4 border-t border-green-700">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full text-left flex items-center gap-3 px-4 py-2.5 hover:bg-green-700 rounded-lg transition text-red-200 font-medium">
                    <span>🚪</span> Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center z-10">
            <h1 class="text-xl font-semibold text-gray-800">
                @yield('title', 'Dashboard')
            </h1>

            <div class="text-sm font-medium text-gray-600">
                Halo, {{ auth()->user()->nama ?? 'Owner' }}
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </div>
    </main>

</body>
</html>