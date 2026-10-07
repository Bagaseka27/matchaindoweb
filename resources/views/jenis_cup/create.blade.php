@extends('layouts.app')

@section('title', 'Tambah Jenis Cup')

@section('content')

<div class="space-y-6">

    <div>

        <h2 class="text-2xl font-bold text-gray-800">
            Tambah Jenis Cup
        </h2>

        <p class="text-gray-500 text-sm mt-1">
            Tambahkan ukuran atau jenis cup baru
        </p>

    </div>


    <div class="bg-white rounded-xl shadow-md p-8 w-full max-w-4xl">

        <form action="{{ route('jenis-cup.store') }}" method="POST">

            @csrf

            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Nama Ukuran / Cup
                </label>

                <input
                    type="text"
                    name="nama_cup"
                    value="{{ old('nama_cup') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    placeholder="Contoh: Regular, Medium, Large"
                    required>

                @error('nama_cup')

                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            <div class="mb-6">

                <label class="block mb-2 font-semibold text-gray-700">
                    Volume (ml)
                </label>

                <input
                    type="number"
                    name="volume_ml"
                    value="{{ old('volume_ml') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    placeholder="Contoh: 350">

                <p class="text-gray-400 text-sm mt-1">
                    Boleh dikosongkan jika tidak menggunakan ukuran mililiter.
                </p>

                @error('volume_ml')

                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            <div class="flex justify-end gap-3">

                <a
                    href="{{ route('jenis-cup.index') }}"
                    class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition">

                    Kembali

                </a>


                <button
                    type="submit"
                    class="bg-[#2F593E] text-white px-6 py-3 rounded-lg hover:bg-green-800 transition">

                    Simpan

                </button>

            </div>

        </form>

    </div>

</div>

@endsection