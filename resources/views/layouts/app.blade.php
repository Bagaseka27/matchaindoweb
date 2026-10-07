<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') - Matcha Indonesia</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">

    <aside class="w-64 bg-[#2F593E] text-white flex flex-col shadow-xl">

        <div class="p-6 text-center border-b border-green-700">
            <h2 class="text-2xl font-bold tracking-wider">
                MATCHA
            </h2>

            <p class="text-xs text-green-200">
                INDONESIA
            </p>
        </div>


        <nav class="flex-1 px-4 py-6 space-y-2">

            <a href="{{ route('dashboard') }}"
               class="block px-4 py-2 rounded-lg transition
               {{ request()->routeIs('dashboard')
                    ? 'bg-[#F3F4F6] text-[#2F593E] font-semibold'
                    : 'text-white hover:bg-green-700' }}">

                🏠 Dashboard

            </a>


            <a href="{{ route('user.index') }}"
               class="flex items-center gap-3 px-5 py-3 rounded-lg
               {{ request()->routeIs('user.*')
                    ? 'bg-white text-green-900 font-semibold'
                    : 'text-white hover:bg-green-700' }}">

                👤 Pengguna

            </a>


            <a href="#"
               class="block px-4 py-2 hover:bg-green-700 rounded-lg transition">

                🧾 Kelola Transaksi

            </a>


            <a href="#"
               class="block px-4 py-2 hover:bg-green-700 rounded-lg transition">

                🔄 Rekonsiliasi QRIS

            </a>

            <!-- KELOLA DATA MASTER -->

            <details class="relative group">

                <summary
                    class="flex items-center justify-between gap-3 px-4 py-2 rounded-lg cursor-pointer list-none
                    hover:bg-green-700 transition">

                    <span>
                        📦 Kelola Data Master
                    </span>

                    <span class="text-xs transition-transform group-open:rotate-180">
                        ▼
                    </span>

                </summary>


                <div
                    class="absolute left-full top-0 ml-2 w-52
                           bg-white rounded-xl shadow-2xl
                           border border-gray-200
                           py-2 z-50">

                    <a href="{{ route('menus.index') }}"
                       class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition
                       {{ request()->routeIs('menu.*')
                            ? 'bg-gray-100 font-semibold text-green-900'
                            : '' }}">

                        🍵 Kelola Menu

                    </a>


                    <a href="{{ route('kategori.index') }}"
                       class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition
                       {{ request()->routeIs('kategori.*')
                            ? 'bg-gray-100 font-semibold text-green-900'
                            : '' }}">

                        🏷️ Kelola Kategori

                    </a>


                    <a href="{{ route('jenis-cup.index') }}"
                       class="block px-4 py-3 text-gray-700 hover:bg-gray-100 transition
                       {{ request()->routeIs('variasi.*')
                            ? 'bg-gray-100 font-semibold text-green-900'
                            : '' }}">

                        🥤 Kelola Jenis Cup

                    </a>

                </div>

            </details>


            <a href="#"
               class="block px-4 py-2 hover:bg-green-700 rounded-lg transition">

                📊 Kelola Stok

            </a>


            <a href="{{ route('cabang.index') }}"
               class="block px-4 py-2 rounded-lg transition
               {{ request()->routeIs('cabang.*')
                    ? 'bg-white text-green-900 font-semibold'
                    : 'text-white hover:bg-green-700' }}">

                🏪 Kelola Cabang

            </a>


            <a href="#"
               class="block px-4 py-2 hover:bg-green-700 rounded-lg transition">

                📈 Laporan

            </a>

        </nav>


        <div class="p-4 border-t border-green-700">

            <form action="{{ route('logout') }}" method="POST">

                @csrf

                <button
                    type="submit"
                    class="w-full text-left px-4 py-2 hover:bg-green-700 rounded-lg transition text-red-300">

                    🚪 Keluar

                </button>

            </form>

        </div>

    </aside>


    <main class="flex-1 flex flex-col overflow-hidden">

        <header class="bg-white shadow-sm p-4 flex justify-between items-center">

            <h1 class="text-xl font-semibold text-gray-800">
                @yield('title', 'Dashboard')
            </h1>

            <div class="text-sm text-gray-500">
                Halo, {{ auth()->user()->nama ?? 'Owner' }}
            </div>

        </header>


        <div class="flex-1 overflow-y-auto p-6">

            @yield('content')

        </div>

    </main>

</body>
</html>