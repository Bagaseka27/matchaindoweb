@extends('layouts.app')

@section('title', 'Kelola Cabang')

@section('content')

<div class="space-y-6">

    <!-- Alert sukses / error -->
    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex justify-between items-center">

        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Kelola Cabang
            </h2>

            <p class="text-gray-500 text-sm mt-1">
                Manajemen data cabang dan operasional toko
            </p>
        </div>

        <a href="{{ route('cabang.create') }}"
           class="bg-[#2F593E] text-white px-5 py-2 rounded-lg hover:bg-green-800 transition">
            + Tambah Cabang
        </a>

    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">

        <table class="w-full text-left border-collapse">

            <thead class="bg-[#2F593E] text-white">
                <tr>
                    <th class="px-6 py-4 border border-green-700">Nama Cabang</th>
                    <th class="px-6 py-4 border border-green-700">Alamat</th>
                    <th class="px-6 py-4 border border-green-700">Status</th>
                    <th class="px-6 py-4 border border-green-700 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>

            @forelse($cabangs as $cabang)

                <tr class="hover:bg-gray-50 transition">

                    <td class="px-6 py-4 border border-gray-200 font-semibold text-gray-800">
                        {{ $cabang->nama_cabang }}
                    </td>

                    <td class="px-6 py-4 border border-gray-200 text-gray-600">
                        {{ $cabang->alamat }}
                    </td>

                    <td class="px-6 py-4 border border-gray-200">
                        @if($cabang->is_aktif == 1)
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

                            <!-- Edit -->
                            <a href="{{ route('cabang.edit', $cabang->id_cabang) }}"
                               class="inline-flex items-center justify-center gap-1 w-20 py-2 bg-yellow-400 text-white rounded-lg text-sm font-medium hover:bg-yellow-500 transition">
                                Edit
                            </a>

                            <!-- Hapus -->
                            <form action="{{ route('cabang.destroy', $cabang->id_cabang) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus cabang {{ $cabang->nama_cabang }}?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="inline-flex items-center justify-center gap-1 w-20 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 transition">
                                    Hapus
                                </button>
                            </form>

                        </div>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="4"
                        class="text-center py-10 text-gray-400 border border-gray-200">
                        Belum ada data cabang
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection