<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Sistem Informasi Gudang</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 flex items-center justify-center px-6 py-10">

    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-xl overflow-hidden grid grid-cols-1 md:grid-cols-2">

        <!-- KIRI -->
        <div class="bg-sky-700 px-10 py-12 flex flex-col items-center justify-center text-white">

            <div class="bg-white rounded-2xl p-5 shadow-md mb-6">
                <img src="{{ asset('build/assets/img/logo.png') }}"
                     alt="Logo Perusahaan"
                     class="w-32 h-32 object-contain">
            </div>

            <h1 class="text-center text-2xl font-bold leading-snug">
                Sistem Informasi Gudang
            </h1>

            <p class="text-center text-sky-100 mt-2 text-sm">
                PT. Dharma Putra Sejahtera Abadi
            </p>

            <div class="mt-8 w-full max-w-sm bg-white/10 rounded-2xl p-5 border border-white/20">
                <p class="text-sm text-center leading-6 text-sky-50">
                    Buat password baru untuk mengakses kembali akun sistem gudang Anda.
                </p>
            </div>
        </div>


        <!-- KANAN -->
        <div class="px-10 py-12 md:px-14">

            <div class="mb-8 text-center">
                <h2 class="text-3xl font-bold text-gray-800">
                    Reset Password
                </h2>

                <p class="text-gray-500 text-sm mt-2">
                    Masukkan email dan password baru Anda
                </p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>

                    <div class="flex items-center border border-gray-300 rounded-xl px-3 bg-white focus-within:ring-2 focus-within:ring-sky-500">
                        <x-heroicon-o-envelope class="w-5 h-5 text-gray-400 mr-3" />

                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email', $request->email) }}"
                               placeholder="Masukkan email"
                               required
                               autofocus
                               autocomplete="username"
                               class="w-full border-0 focus:ring-0 py-3 text-gray-700">
                    </div>

                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password Baru
                    </label>

                    <div class="flex items-center border border-gray-300 rounded-xl px-3 bg-white focus-within:ring-2 focus-within:ring-sky-500">
                        <x-heroicon-o-lock-closed class="w-5 h-5 text-gray-400 mr-3" />

                        <input id="password"
                               type="password"
                               name="password"
                               placeholder="Masukkan password baru"
                               required
                               autocomplete="new-password"
                               class="w-full border-0 focus:ring-0 py-3 text-gray-700">
                    </div>

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Konfirmasi Password
                    </label>

                    <div class="flex items-center border border-gray-300 rounded-xl px-3 bg-white focus-within:ring-2 focus-within:ring-sky-500">
                        <x-heroicon-o-shield-check class="w-5 h-5 text-gray-400 mr-3" />

                        <input id="password_confirmation"
                               type="password"
                               name="password_confirmation"
                               placeholder="Ulangi password baru"
                               required
                               autocomplete="new-password"
                               class="w-full border-0 focus:ring-0 py-3 text-gray-700">
                    </div>

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button type="submit"
                        class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3 rounded-xl transition shadow-sm">
                    Simpan Password Baru
                </button>

                <div class="text-center pt-2">
                    <span class="text-sm text-gray-500">
                        Sudah ingat password?
                    </span>

                    <a href="{{ route('login') }}"
                       class="text-sm text-sky-600 font-semibold hover:underline">
                        Login
                    </a>
                </div>
            </form>

        </div>
    </div>

</body>
</html>