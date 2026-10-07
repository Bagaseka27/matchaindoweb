@extends('layouts.app')

@section('title', 'Kelola Menu')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center">

        <div>

            <h2 class="text-2xl font-bold text-gray-800">
                Kelola Menu
            </h2>

            <p class="text-gray-500 text-sm mt-1">
                Manajemen menu, kategori, variasi cup, dan harga
            </p>

        </div>


        <a
            href="{{ route('menus.create') }}"
            class="bg-[#2F593E] text-white px-5 py-2 rounded-lg hover:bg-green-800 transition"
        >
            + Tambah Menu
        </a>

    </div>


    {{-- Alert Success --}}
    @if(session('success'))

        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg">

            {{ session('success') }}

        </div>

    @endif


    {{-- Alert Error --}}
    @if(session('error'))

        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">

            {{ session('error') }}

        </div>

    @endif


    {{-- Search --}}
    <div class="bg-white rounded-xl shadow-md p-5">

        <form
            method="GET"
            action="{{ route('menus.index') }}"
            class="flex gap-3"
        >

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nama menu atau kategori..."
                class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
            >

            <button
                type="submit"
                class="bg-[#2F593E] text-white px-6 py-3 rounded-lg hover:bg-green-800 transition"
            >
                Cari
            </button>


            @if(request('search'))

                <a
                    href="{{ route('menus.index') }}"
                    class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition"
                >
                    Reset
                </a>

            @endif

        </form>

    </div>


    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-md overflow-hidden">

        {{-- Scroll horizontal jika layar kecil --}}
        <div class="overflow-x-auto">

            <table class="w-full min-w-[1100px] text-left border-collapse table-fixed">

                {{-- Atur lebar masing-masing kolom --}}
                <colgroup>

                    <col class="w-[70px]">

                    <col class="w-[190px]">

                    <col class="w-[190px]">

                    <col class="w-[330px]">

                    <col class="w-[150px]">

                    <col class="w-[230px]">

                </colgroup>


                <thead class="bg-[#2F593E] text-white">

                    <tr>

                        <th class="px-6 py-4 border border-green-700">
                            No
                        </th>

                        <th class="px-6 py-4 border border-green-700">
                            Nama Menu
                        </th>

                        <th class="px-6 py-4 border border-green-700">
                            Kategori
                        </th>

                        <th class="px-6 py-4 border border-green-700">
                            Variasi Cup & Harga
                        </th>

                        <th class="px-6 py-4 border border-green-700">
                            Status
                        </th>

                        <th class="px-6 py-4 border border-green-700">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($menus as $index => $menu)

                        <tr class="hover:bg-gray-50 transition">


                            {{-- No --}}
                            <td class="px-6 py-4 border border-gray-200">

                                {{ $menus->firstItem() + $index }}

                            </td>


                            {{-- Nama Menu --}}
                            <td class="px-6 py-4 border border-gray-200">

                                <div class="font-semibold text-gray-800 break-words">

                                    {{ $menu->nama_menu }}

                                </div>

                            </td>


                            {{-- Kategori --}}
                            <td class="px-6 py-4 border border-gray-200">

                                @if($menu->kategori)

                                    <span
                                        class="inline-block max-w-full px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-700 whitespace-normal break-words"
                                    >

                                        {{ $menu->kategori->nama_kategori }}

                                    </span>

                                @else

                                    <span class="text-gray-400">
                                        Tanpa Kategori
                                    </span>

                                @endif

                            </td>


                            {{-- Harga --}}
                            <td class="px-6 py-4 border border-gray-200">

                                @forelse($menu->hargaMenus as $hargaMenu)

                                    <div class="mb-2 last:mb-0">

                                        <span class="font-semibold text-gray-700">

                                            {{ $hargaMenu->jenisCup->nama_cup ?? '-' }}

                                        </span>

                                        <span class="text-gray-500">
                                            -
                                        </span>

                                        <span class="text-gray-700">

                                            Rp
                                            {{ number_format((float) $hargaMenu->harga, 0, ',', '.') }}

                                        </span>

                                    </div>

                                @empty

                                    <span class="text-gray-400">
                                        Belum ada harga
                                    </span>

                                @endforelse

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4 border border-gray-200">

                                @if($menu->is_aktif)

                                    <span
                                        class="inline-block px-3 py-1 rounded-full text-sm bg-green-100 text-green-700 whitespace-nowrap"
                                    >
                                        Aktif
                                    </span>

                                @else

                                    <span
                                        class="inline-block px-3 py-1 rounded-full text-sm bg-red-100 text-red-700 whitespace-nowrap"
                                    >
                                        Tidak Aktif
                                    </span>

                                @endif

                            </td>


                            {{-- Aksi --}}
                            <td class="px-6 py-4 border border-gray-200">

                                <div class="flex justify-center items-center gap-3">

                                    {{-- Edit --}}
                                    <a
                                        href="{{ route('menus.edit', $menu->id_menu) }}"
                                        class="inline-flex items-center justify-center w-20 py-2 bg-yellow-400 text-white rounded-lg text-sm font-medium hover:bg-yellow-500 transition"
                                    >
                                        Edit
                                    </a>


                                    {{-- Hapus --}}
                                    <form
                                        action="{{ route('menus.destroy', $menu->id_menu) }}"
                                        method="POST"
                                        onsubmit="return confirm('Hapus menu {{ $menu->nama_menu }}?')"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center w-20 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 transition"
                                        >
                                            Hapus
                                        </button>

                                    </form>

                                </div>

                            </td>


                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="text-center py-10 text-gray-400 border border-gray-200"
                            >
                                Belum ada data menu
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- Pagination --}}
    @if($menus->hasPages())

        <div>

            {{ $menus->links() }}

        </div>

    @endif

</div>

@endsection