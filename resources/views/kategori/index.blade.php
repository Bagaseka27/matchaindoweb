@extends('layouts.app')

@section('title', 'Kelola Kategori')

@section('content')

<div class="space-y-6">

    <div class="flex justify-between items-center">

        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Kelola Kategori
            </h2>

            <p class="text-gray-500 text-sm mt-1">
                Manajemen kategori menu Matcha Indonesia
            </p>
        </div>

        <a href="{{ route('kategori.create') }}"
           class="bg-[#2F593E] text-white px-5 py-2 rounded-lg hover:bg-green-800 transition">

            + Tambah Kategori

        </a>

    </div>


    @if(session('success'))

        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg">

            {{ session('success') }}

        </div>

    @endif


    @if(session('error'))

        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">

            {{ session('error') }}

        </div>

    @endif


    <div class="bg-white rounded-xl shadow-md p-5">

        <form method="GET" action="{{ route('kategori.index') }}">

            <div class="flex gap-3">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama kategori..."
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                >

                <button
                    type="submit"
                    class="bg-[#2F593E] text-white px-6 py-3 rounded-lg hover:bg-green-800 transition">

                    Cari

                </button>

                @if(request('search'))

                    <a
                        href="{{ route('kategori.index') }}"
                        class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition">

                        Reset

                    </a>

                @endif

            </div>

        </form>

    </div>


    <div class="bg-white rounded-xl shadow-md overflow-hidden">

        <table class="w-full text-left border-collapse">

            <thead class="bg-[#2F593E] text-white">

                <tr>

                    <th class="px-6 py-4 border border-green-700 w-20">
                        No
                    </th>

                    <th class="px-6 py-4 border border-green-700">
                        Nama Kategori
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

            @forelse($kategoris as $index => $kategori)

                <tr class="hover:bg-gray-50 transition">

                    <td class="px-6 py-4 border border-gray-200 text-gray-600">
                        {{ $kategoris->firstItem() + $index }}
                    </td>


                    <td class="px-6 py-4 border border-gray-200 font-semibold text-gray-800">

                        {{ $kategori->nama_kategori }}

                    </td>


                    <td class="px-6 py-4 border border-gray-200">

                        @if($kategori->is_aktif == 1)

                            <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700">

                                Aktif

                            </span>

                        @else

                            <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-700">

                                Tidak Aktif

                            </span>

                        @endif

                    </td>


                    <td class="px-6 py-4 border border-gray-200">

                        <div class="flex justify-center gap-3">

                            <a
                                href="{{ route('kategori.edit', $kategori->id_kategori) }}"
                                class="inline-flex items-center justify-center w-20 py-2 bg-yellow-400 text-white rounded-lg text-sm font-medium hover:bg-yellow-500 transition">

                                Edit

                            </a>


                            <form
                                action="{{ route('kategori.destroy', $kategori->id_kategori) }}"
                                method="POST"
                                onsubmit="return confirm('Hapus kategori {{ $kategori->nama_kategori }}?')">

                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center w-20 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 transition">

                                    Hapus

                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

            @empty

                <tr>

                    <td
                        colspan="4"
                        class="text-center py-10 text-gray-400 border border-gray-200">

                        @if(request('search'))

                            Kategori "{{ request('search') }}" tidak ditemukan.

                        @else

                            Belum ada data kategori.

                        @endif

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>


    @if($kategoris->hasPages())

        <div>
            {{ $kategoris->links() }}
        </div>

    @endif

</div>

@endsection