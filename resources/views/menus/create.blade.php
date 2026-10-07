@extends('layouts.app')

@section('title', 'Tambah Menu')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div>

        <h2 class="text-2xl font-bold text-gray-800">
            Tambah Menu
        </h2>

        <p class="text-gray-500 text-sm mt-1">
            Tambahkan menu baru beserta kategori, variasi cup, dan harga
        </p>

    </div>


    {{-- Form --}}
    <div class="bg-white rounded-xl shadow-md p-8 w-full max-w-4xl">

        <form
            action="{{ route('menus.store') }}"
            method="POST"
            id="formMenu"
        >

            @csrf


            {{-- Nama Menu --}}
            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Nama Menu
                </label>

                <input
                    type="text"
                    name="nama_menu"
                    value="{{ old('nama_menu') }}"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    placeholder="Contoh: Matcha Latte"
                    required
                >

                @error('nama_menu')

                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            {{-- Kategori --}}
            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Kategori
                </label>

                <select
                    name="id_kategori"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    required
                >

                    <option value="">
                        -- Pilih Kategori --
                    </option>

                    @foreach($kategoris as $kategori)

                        <option
                            value="{{ $kategori->id_kategori }}"
                            {{ old('id_kategori') == $kategori->id_kategori ? 'selected' : '' }}
                        >
                            {{ $kategori->nama_kategori }}
                        </option>

                    @endforeach

                </select>

                @error('id_kategori')

                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>

                @enderror

            </div>


            {{-- Harga --}}
            <div class="mb-6">

                <label class="block mb-3 font-semibold text-gray-700">
                    Harga Berdasarkan Ukuran Cup
                </label>

                <div class="space-y-4">

                    @foreach($cups as $cup)

                        <div class="grid grid-cols-2 gap-4 items-center">

                            {{-- Nama Cup --}}
                            <div>

                                <label class="block text-sm font-semibold text-gray-700">
                                    {{ $cup->nama_cup }}
                                </label>

                                @if($cup->volume_ml)

                                    <p class="text-gray-400 text-sm">
                                        {{ $cup->volume_ml }} ml
                                    </p>

                                @endif

                            </div>


                            {{-- Input Harga --}}
                            <div>

                                <input
                                    type="text"
                                    name="harga[{{ $cup->id_cup }}]"
                                    value="{{ old('harga.' . $cup->id_cup) }}"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    class="harga-input w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                                    placeholder="Contoh: 15.000"
                                >

                            </div>

                        </div>

                    @endforeach

                </div>

                @error('harga')

                    <p class="text-red-500 text-sm mt-2">
                        {{ $message }}
                    </p>

                @enderror

                @foreach($errors->get('harga.*') as $messages)

                    @foreach($messages as $message)

                        <p class="text-red-500 text-sm mt-2">
                            {{ $message }}
                        </p>

                    @endforeach

                @endforeach

            </div>


            {{-- Status --}}
            <div class="mb-6">

                <label class="block mb-2 font-semibold text-gray-700">
                    Status
                </label>

                <select
                    name="is_aktif"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                >

                    <option
                        value="1"
                        {{ old('is_aktif', 1) == 1 ? 'selected' : '' }}
                    >
                        Aktif
                    </option>

                    <option
                        value="0"
                        {{ old('is_aktif') == 0 && old('is_aktif') !== null ? 'selected' : '' }}
                    >
                        Tidak Aktif
                    </option>

                </select>

            </div>


            {{-- Button --}}
            <div class="flex justify-end gap-3">

                <a
                    href="{{ route('menus.index') }}"
                    class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition"
                >
                    Kembali
                </a>

                <button
                    type="submit"
                    class="bg-[#2F593E] text-white px-6 py-3 rounded-lg hover:bg-green-800 transition"
                >
                    Simpan
                </button>

            </div>

        </form>

    </div>

</div>


{{-- Format Harga --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const hargaInputs = document.querySelectorAll('.harga-input');

    hargaInputs.forEach(function (input) {

        if (input.value) {
            input.value = formatRupiah(input.value);
        }


        input.addEventListener('input', function () {

            this.value = formatRupiah(this.value);

        });

    });


    function formatRupiah(value) {

        value = value.replace(/\D/g, '');

        if (value === '') {
            return '';
        }


        return new Intl.NumberFormat('id-ID').format(
            parseInt(value, 10)
        );

    }


    document.getElementById('formMenu').addEventListener('submit', function () {

        hargaInputs.forEach(function (input) {

            input.value = input.value.replace(/\./g, '');

        });

    });

});

</script>

@endsection