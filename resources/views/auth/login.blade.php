<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Owner - Matcha Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg w-96 border-t-8 border-[#2F593E]">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-[#2F593E]">Matcha Indonesia</h2>
            <p class="text-sm text-gray-500">Sistem Manajemen Operasional</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2F593E]" required value="{{ old('username') }}">
                @error('username') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">PIN / Kata Sandi</label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2F593E]" required>
            </div>

            <button type="submit" class="w-full bg-[#2F593E] text-white font-bold py-2 px-4 rounded-lg hover:bg-green-800 transition duration-300">
                Masuk ke Dashboard
            </button>
        </form>
    </div>

</body>
</html>