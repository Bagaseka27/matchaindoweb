@extends('layouts.app')

@section('title', 'Kelola Shift')

@section('content')

<div class="space-y-6">


    <!-- Header -->
    <div>

        <h2 class="text-2xl font-bold text-gray-800">
            Kelola Shift
        </h2>

        <p class="text-gray-500 text-sm mt-1">
            Pengaturan jam kerja cabang {{ $cabang->nama_cabang }}
        </p>

    </div>




    <!-- Form Tambah Shift -->
    <div class="bg-white rounded-xl shadow-md p-6 w-full">


        <h3 class="text-lg font-bold text-gray-800 mb-6">
            Tambah Shift
        </h3>



        <form method="POST"
        action="{{ route('cabang.shift.store', $cabang->id_cabang) }}">


            @csrf



            <div class="grid grid-cols-3 gap-5">


                <!-- Nama Shift -->
                <div>

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Shift
                    </label>


                    <input
                    type="text"
                    name="nama_shift"
                    placeholder="Contoh: Shift Pagi"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">


                </div>




                <!-- Jam Mulai -->
                <div>

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jam Mulai
                    </label>


                    <input
                    type="time"
                    name="jam_mulai"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">


                </div>





                <!-- Jam Selesai -->
                <div>

                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Jam Selesai
                    </label>


                    <input
                    type="time"
                    name="jam_selesai"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">


                </div>


            </div>




            <div class="mt-6">

                <button
            type="submit"
            class="bg-[#2F593E] text-white px-6 py-3 rounded-lg hover:bg-green-800 transition">

                Simpan

            </button>

            </div>



        </form>


    </div>






    <!-- Daftar Shift -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">


        <div class="px-6 py-4 border-b">

            <h3 class="text-lg font-bold text-gray-800">
                Daftar Shift {{ $cabang->nama_cabang }}
            </h3>

        </div>





        <table class="w-full text-left">


            <thead class="bg-[#2F593E] text-white">


                <tr>


                    <th class="px-6 py-4">
                        Nama Shift
                    </th>


                    <th class="px-6 py-4">
                        Jam Mulai
                    </th>


                    <th class="px-6 py-4">
                        Jam Selesai
                    </th>


                </tr>


            </thead>





            <tbody>


            @forelse($shift as $s)


                <tr class="border-b hover:bg-gray-50">


                    <td class="px-6 py-4 font-semibold text-gray-800">

                        {{ $s->nama_shift }}

                    </td>



                    <td class="px-6 py-4 text-gray-600">

                        {{ $s->jam_mulai }}

                    </td>



                    <td class="px-6 py-4 text-gray-600">

                        {{ $s->jam_selesai }}

                    </td>



                </tr>



            @empty


                <tr>

                    <td colspan="3"
                    class="text-center py-10 text-gray-400">

                        Belum ada data shift

                    </td>

                </tr>


            @endforelse



            </tbody>


        </table>


    </div>



</div>


@endsection