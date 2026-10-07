@extends('layouts.app')

@section('title', 'Edit User')

@section('content')

<div class="space-y-6">

    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Edit User
        </h2>

        <p class="text-gray-500 text-sm mt-1">
            Perbarui informasi akun pengguna
        </p>
    </div>


    <!-- Form -->
    <div class="bg-white rounded-xl shadow-md p-8 w-full max-w-4xl">

        <form action="{{ route('user.update', $user->id_user) }}" method="POST">

            @csrf
            @method('PUT')


            <!-- Nama -->
            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Nama
                </label>

                <input
                    type="text"
                    name="nama"
                    value="{{ old('nama', $user->nama) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    placeholder="Masukkan nama"
                    required>

                @error('nama')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- Username -->
            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    value="{{ old('username', $user->username) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    placeholder="Masukkan username"
                    required>

                @error('username')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- Password -->
            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Password Baru
                </label>

                <input
                    type="password"
                    name="password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    placeholder="Kosongkan jika password tidak diubah">

                <p class="text-gray-400 text-sm mt-1">
                    Kosongkan apabila password tetap.
                </p>

                @error('password')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- Cabang -->
            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Cabang
                </label>

                <select
                    name="id_cabang"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    required>

                    <option value="">
                        Pilih Cabang
                    </option>

                    @foreach($cabangs as $cabang)

                        <option
                            value="{{ $cabang->id_cabang }}"
                            {{ old('id_cabang', $user->id_cabang) == $cabang->id_cabang ? 'selected' : '' }}>

                            {{ $cabang->nama_cabang }}

                        </option>

                    @endforeach

                </select>

                @error('id_cabang')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- Role -->
            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Role
                </label>

                <select
                    name="role"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    required>

                    <option value="">
                        Pilih Role
                    </option>

                    <option
                        value="pemilik"
                        {{ old('role', $user->role) == 'pemilik' ? 'selected' : '' }}>
                        Pemilik
                    </option>

                    <option
                        value="barista"
                        {{ old('role', $user->role) == 'barista' ? 'selected' : '' }}>
                        Barista
                    </option>

                </select>

                @error('role')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- Tarif Harian -->
            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Tarif Harian
                </label>

                <input
                    type="number"
                    name="tarif_harian"
                    value="{{ old('tarif_harian', $user->tarif_harian) }}"
                    min="0"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    placeholder="Contoh: 100000">

                @error('tarif_harian')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- Status -->
            <div class="mb-6">

                <label class="block mb-2 font-semibold text-gray-700">
                    Status
                </label>

                <select
                    name="is_aktif"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    required>

                    <option
                        value="1"
                        {{ old('is_aktif', $user->is_aktif) == 1 ? 'selected' : '' }}>
                        Aktif
                    </option>

                    <option
                        value="0"
                        {{ old('is_aktif', $user->is_aktif) == 0 ? 'selected' : '' }}>
                        Tidak Aktif
                    </option>

                </select>

                @error('is_aktif')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            <!-- Button -->
            <div class="flex justify-end gap-3">

                <a
                    href="{{ route('user.index') }}"
                    class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition">
                    Kembali
                </a>

                <button
                    type="submit"
                    class="bg-[#2F593E] text-white px-6 py-3 rounded-lg hover:bg-green-800 transition">
                    Update
                </button>

            </div>

        </form>

    </div>

</div>

@endsection