@extends('layouts.app')

@section('title', 'Kelola User')

@section('content')

<div class="space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">

        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Kelola User
            </h2>

            <p class="text-gray-500 text-sm mt-1">
                Manajemen akun pemilik dan barista Matcha Indonesia
            </p>
        </div>

        <a href="{{ route('user.create') }}"
           class="bg-[#2F593E] text-white px-5 py-2 rounded-lg hover:bg-green-800 transition">
            + Tambah User
        </a>

    </div>


    <!-- Alert -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif


    <!-- Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-left border-collapse">

                <thead class="bg-[#2F593E] text-white">
                    <tr>

                        <th class="px-5 py-4 border border-green-700">
                            Nama
                        </th>

                        <th class="px-5 py-4 border border-green-700">
                            Username
                        </th>

                        <th class="px-5 py-4 border border-green-700">
                            Cabang
                        </th>

                        <th class="px-5 py-4 border border-green-700">
                            Role
                        </th>

                        <th class="px-5 py-4 border border-green-700">
                            Tarif Harian
                        </th>

                        <th class="px-5 py-4 border border-green-700">
                            Status
                        </th>

                        <th class="px-5 py-4 border border-green-700 text-center">
                            Aksi
                        </th>

                    </tr>
                </thead>


                <tbody>

                    @forelse($users as $user)

                        <tr class="hover:bg-gray-50 transition">

                            <td class="px-5 py-4 border border-gray-200 font-semibold text-gray-800">
                                {{ $user->nama }}
                            </td>

                            <td class="px-5 py-4 border border-gray-200 text-gray-600">
                                {{ $user->username }}
                            </td>

                            <td class="px-5 py-4 border border-gray-200 text-gray-600">

                                @if($user->cabang)
                                    {{ $user->cabang->nama_cabang }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif

                            </td>

                            <td class="px-5 py-4 border border-gray-200">

                                @if($user->role == 'pemilik')

                                    <span class="px-3 py-1 rounded-full text-sm bg-purple-100 text-purple-700">
                                        Pemilik
                                    </span>

                                @else

                                    <span class="px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-700">
                                        Barista
                                    </span>

                                @endif

                            </td>

                            <td class="px-5 py-4 border border-gray-200 text-gray-600">

                                Rp {{ number_format($user->tarif_harian, 0, ',', '.') }}

                            </td>

                            <td class="px-5 py-4 border border-gray-200">

                                @if($user->is_aktif == 1)

                                    <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-700">
                                        Aktif
                                    </span>

                                @else

                                    <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-700">
                                        Tidak Aktif
                                    </span>

                                @endif

                            </td>

                            <td class="px-5 py-4 border border-gray-200">

                                <div class="flex justify-center gap-2">

                                    <a href="{{ route('user.edit', $user->id_user) }}"
                                       class="inline-flex items-center justify-center px-4 py-2 bg-yellow-400 text-white rounded-lg text-sm font-medium hover:bg-yellow-500 transition">
                                        Edit
                                    </a>


                                    <form action="{{ route('user.destroy', $user->id_user) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus user ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="inline-flex items-center justify-center px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 transition">
                                            Hapus
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7"
                                class="text-center py-10 text-gray-400 border border-gray-200">
                                Belum ada data user
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection