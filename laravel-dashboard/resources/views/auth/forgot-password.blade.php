<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-200 flex items-center justify-center">

    <div class="w-full max-w-5xl bg-white rounded-[32px] px-16 py-20 flex items-center">

        <!-- Bagian Kiri -->
        <div class="w-1/2 flex flex-col items-center justify-center border-r border-gray-200 pr-14">

            <img 
                src="{{ asset('build/assets/img/logo.png') }}" 
                alt="Logo Perusahaan" 
                class="w-40 mb-6"
            >

            <h1 class="text-center text-sky-700 font-bold text-lg tracking-wide leading-8">
                PT. DHARMA PUTRA <br>
                SEJAHTERA ABADI
            </h1>
        </div>

        <!-- Bagian Kanan -->
        <div class="w-1/2 pl-14">

            <h2 class="text-4xl font-bold text-gray-800 text-center mb-3">
                Forgot Password?
            </h2>

            <p class="text-center text-gray-500 mb-8 text-sm leading-6">
                Masukkan alamat email akun Anda, link untuk membuat password baru akan dikirimkan.
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <div class="flex items-center border border-gray-300 px-3 bg-white rounded-md">
                        <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>

                        <input 
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Masukkan email"
                            required
                            autofocus
                            class="w-full border-0 focus:ring-0 py-3 text-gray-700"
                        >
                    </div>

                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Button -->
                <button 
                    type="submit"
                    class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 rounded-md transition"
                >
                    Kirim Link Reset Password
                </button>

                <!-- Link Login -->
                <div class="text-center mt-5">
                    <span class="text-sm text-gray-600">
                        Kembali ke halaman login?
                    </span>

                    <a 
                        href="{{ route('login') }}" 
                        class="text-sm text-sky-600 font-semibold hover:underline"
                    >
                        Login
                    </a>
                </div>
            </form>
        </div>

    </div>

</body>
</html>