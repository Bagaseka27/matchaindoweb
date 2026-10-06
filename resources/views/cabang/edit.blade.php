@extends('layouts.app')

@section('title', 'Edit Cabang')

@section('content')

<div class="space-y-6">


    <!-- Header -->
    <div>

        <h2 class="text-2xl font-bold text-gray-800">
            Edit Cabang
        </h2>

        <p class="text-gray-500 text-sm mt-1">
            Perbarui informasi cabang
        </p>

    </div>





    <!-- Form -->
    <div class="bg-white rounded-xl shadow-md p-8 w-full max-w-4xl">


        <form method="POST"
        action="{{ route('cabang.update', $cabang->id_cabang) }}">


            @csrf
            @method('PUT')





            <!-- Nama Cabang -->
            <div class="mb-5">


                <label class="block mb-2 font-semibold text-gray-700">
                    Nama Cabang
                </label>


                <input
                type="text"
                name="nama_cabang"
                value="{{ $cabang->nama_cabang }}"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">


            </div>






            <!-- Alamat -->
            <div class="mb-5">


                <label class="block mb-2 font-semibold text-gray-700">
                    Alamat
                </label>


                <textarea
                name="alamat"
                rows="3"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">{{ $cabang->alamat }}</textarea>


            </div>







            <!-- Status -->
            <div class="mb-6">


                <label class="block mb-2 font-semibold text-gray-700">
                    Status
                </label>


                <select
                name="is_aktif"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">


                    <option value="1"
                    {{ $cabang->is_aktif == 1 ? 'selected' : '' }}>

                        Aktif

                    </option>



                    <option value="0"
                    {{ $cabang->is_aktif == 0 ? 'selected' : '' }}>

                        Tidak Aktif

                    </option>


                </select>


            </div>






            <!-- Button -->
            <div class="flex justify-end gap-3">


                <a href="{{ route('cabang.index') }}"
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