@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')

<div class="space-y-6">

    <div>

        <h2 class="text-2xl font-bold text-gray-800">
            Edit Kategori
        </h2>

        <p class="text-gray-500 text-sm mt-1">
            Perbarui informasi kategori menu
        </p>

    </div>


    <div class="bg-white rounded-xl shadow-md p-8 w-full max-w-4xl">

        <form
            action="{{ route('kategori.update', $kategori->id_kategori) }}"
            method="POST">

            @csrf
            @method('PUT')


            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Nama Kategori
                </label>

                <input
                    type="text"
                    name="nama_kategori"
                    value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    placeholder="Contoh: Beverage, Pastry, Dessert"
                    required
                >

                @error('nama_kategori')

                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            <div class="mb-6">

                <label class="block mb-2 font-semibold text-gray-700">
                    Status
                </label>

                <select
                    name="is_aktif"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">

                    <option value="1"
                        {{ old('is_aktif', $kategori->is_aktif) == 1 ? 'selected' : '' }}>
                        Aktif
                    </option>

                    <option value="0"
                        {{ old('is_aktif', $kategori->is_aktif) == 0 ? 'selected' : '' }}>
                        Tidak Aktif
                    </option>

                </select>

                @error('is_aktif')

                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            <div class="flex justify-end gap-3">

                <a
                    href="{{ route('kategori.index') }}"
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