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
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
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
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror

            </div>

                        <!-- Password -->
            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Password
                </label>

                <div class="relative">

                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-green-700"
                        placeholder="Minimal 6 karakter"
                        required>

                    <button
                        type="button"
                        id="toggle-password"
                        aria-label="Tampilkan atau sembunyikan password"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2F593E] transition">

                        <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>

                        <svg id="icon-eye-off" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>

                    </button>

                </div>

                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror

            </div>

            <!-- Role -->
            <div class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Role
                </label>

                <select
                    id="role"
                    name="role"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700"
                    required>

                    <option value="">Pilih Role</option>

                    <option value="pemilik" {{ old('role', $user->role) == 'pemilik' ? 'selected' : '' }}>
                        Pemilik
                    </option>

                    <option value="barista" {{ old('role', $user->role) == 'barista' ? 'selected' : '' }}>
                        Barista
                    </option>

                </select>

                @error('role')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror

            </div>

                        <!-- Cabang (hanya untuk barista) -->
            <div id="wrap-cabang" class="mb-5">

                <label class="block mb-2 font-semibold text-gray-700">
                    Cabang
                </label>

                <select
                    id="id_cabang"
                    name="id_cabang"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-700">

                    <option value="">Pilih Cabang</option>

                    @foreach($cabangs as $cabang)
                        <option
                            value="{{ $cabang->id_cabang }}"
                            {{ old('id_cabang', $user->id_cabang) == $cabang->id_cabang ? 'selected' : '' }}>
                            {{ $cabang->nama_cabang }}
                        </option>
                    @endforeach

                </select>

                @error('id_cabang')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
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

                    <option value="1" {{ old('is_aktif', $user->is_aktif) == 1 ? 'selected' : '' }}>
                        Aktif
                    </option>

                    <option value="0" {{ old('is_aktif', $user->is_aktif) == 0 ? 'selected' : '' }}>
                        Tidak Aktif
                    </option>

                </select>

                @error('is_aktif')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
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

<script>
    const role   = document.getElementById('role');
    const wrap   = document.getElementById('wrap-cabang');
    const cabang = document.getElementById('id_cabang');

    function toggleCabang() {
        const isBarista = role.value === 'barista';
        wrap.classList.toggle('hidden', !isBarista);
        cabang.required = isBarista;
        if (!isBarista) cabang.value = '';
    }

    role.addEventListener('change', toggleCabang);
    toggleCabang();


    const pwInput  = document.getElementById('password');
    const pwToggle = document.getElementById('toggle-password');
    const iconEye  = document.getElementById('icon-eye');
    const iconOff  = document.getElementById('icon-eye-off');

    pwToggle.addEventListener('click', () => {
        const show = pwInput.type === 'password';
        pwInput.type = show ? 'text' : 'password';
        iconEye.classList.toggle('hidden', show);
        iconOff.classList.toggle('hidden', !show);
    });
</script>

@endsection