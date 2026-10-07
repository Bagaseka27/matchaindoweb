@extends('layouts.app')

@section('title', 'Kelola Jenis Cup')

@section('content')

<div class="space-y-6">

    <div class="flex justify-between items-center">

        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Kelola Jenis Cup
            </h2>

            <p class="text-gray-500 text-sm mt-1">
                Manajemen ukuran dan jenis cup produk
            </p>
        </div>

        <a href="{{ route('jenis-cup.create') }}"
           class="bg-[#2F593E] text-white px-5 py-2 rounded-lg hover:bg-green-800 transition">

            + Tambah Jenis Cup

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

        <form method="GET" action="{{ route('jenis-cup.index') }}">

            <div class="flex gap-3">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama cup atau volume..."
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">

                <button
                    type="submit"
                    class="bg-gray-700 text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition">

                    Cari

                </button>

            </div>

        </form>

    </div>


    <div class="bg-white rounded-xl shadow-md overflow-hidden">

        <table class="w-full text-left border-collapse">

            <thead class="bg-[#2F593E] text-white">

                <tr>

                    <th class="px-6 py-4 border border-green-700">
                        No
                    </th>

                    <th class="px-6 py-4 border border-green-700">
                        Nama Ukuran / Cup
                    </th>

                    <th class="px-6 py-4 border border-green-700">
                        Volume
                    </th>

                    <th class="px-6 py-4 border border-green-700 text-center">
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody>

            @forelse($jenisCups as $index => $cup)

                <tr class="hover:bg-gray-50 transition">

                    <td class="px-6 py-4 border border-gray-200 text-gray-600">

                        {{ $jenisCups->firstItem() + $index }}

                    </td>


                    <td class="px-6 py-4 border border-gray-200 font-semibold text-gray-800">

                        {{ $cup->nama_cup }}

                    </td>


                    <td class="px-6 py-4 border border-gray-200">

                        @if($cup->volume_ml)

                            <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700">

                                {{ $cup->volume_ml }} ml

                            </span>

                        @else

                            <span class="text-gray-400">
                                -
                            </span>

                        @endif

                    </td>


                    <td class="px-6 py-4 border border-gray-200">

                        <div class="flex justify-center gap-3">

                            <a
                                href="{{ route('jenis-cup.edit', $cup->id_cup) }}"
                                class="inline-flex items-center justify-center w-20 py-2 bg-yellow-400 text-white rounded-lg text-sm font-medium hover:bg-yellow-500 transition">

                                Edit

                            </a>


                            <form
                                action="{{ route('jenis-cup.destroy', $cup->id_cup) }}"
                                method="POST"
                                onsubmit="return confirm('Hapus ukuran {{ $cup->nama_cup }}?')">

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

                        Belum ada data jenis cup

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>


    @if($jenisCups->hasPages())

        <div>
            {{ $jenisCups->links() }}
        </div>

    @endif

</div>

@endsection