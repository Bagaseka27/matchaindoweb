@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')

<div class="space-y-6">

    <div>
        <h2 class="text-2xl font-bold text-gray-800">
            Tambah User
        </h2>

        <p class="text-gray-500 text-sm mt-1">
            Tambahkan akun pemilik atau barista baru
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-8 w-full max-w-4xl">

        <form action="{{ route('user.store') }}" method="POST">

            @csrf

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">
                    Nama
                </label>

                <input
                    type="text"
                    name="nama"
                    value="{{ old('nama') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3"
                    placeholder="Masukkan nama"
                    required>

                @error('nama')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3"
                    placeholder="Masukkan username"
                    required>

                @error('username')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3"
                    placeholder="Masukkan password"
                    required>

                @error('password')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">
                    Role
                </label>

                <select
                    name="role"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3"
                    required>

                    <option value="">Pilih Role</option>

                    <option value="pemilik"
                        {{ old('role') == 'pemilik' ? 'selected' : '' }}>
                        Pemilik
                    </option>

                    <option value="barista"
                        {{ old('role') == 'barista' ? 'selected' : '' }}>
                        Barista
                    </option>

                </select>

                @error('role')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="mb-5">
                <label class="block mb-2 font-semibold text-gray-700">
                    Tarif Harian
                </label>

                <input
                    type="number"
                    name="tarif_harian"
                    value="{{ old('tarif_harian', 0) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3"
                    placeholder="Contoh: 100000">
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold text-gray-700">
                    Status
                </label>

                <select
                    name="is_aktif"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3"
                    required>

                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>

                </select>
            </div>

            <div class="flex justify-end gap-3">

                <a href="{{ route('user.index') }}"
                   class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg">
                    Kembali
                </a>

                <button
                    type="submit"
                    class="bg-[#2F593E] text-white px-6 py-3 rounded-lg hover:bg-green-800">
                    Simpan
                </button>

            </div>

        </form>

    </div>

</div>

@endsection