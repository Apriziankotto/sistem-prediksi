<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-200 flex items-center justify-center">

<div class="w-[88%] max-w-5xl bg-white rounded-[32px] px-12 py-10 flex items-center">

    <!-- Bagian Kiri -->
    <div class="w-1/2 flex flex-col items-center justify-center border-r border-gray-200 pr-14">

        <img
            src="{{ asset('build/assets/img/Logo.png') }}"
            alt="Logo Perusahaan"
            class="w-40 mb-6"
        >

        <h1 class="text-center text-sky-700 font-bold text-xl tracking-wide leading-8">
            PT. DHARMA PUTRA <br>
            SEJAHTERA ABADI
        </h1>
    </div>

    <!-- Bagian Kanan -->
    <div class="w-1/2 pl-16">

        <h2 class="text-4xl font-bold text-gray-800 text-center mb-8">
            Welcome Back!
        </h2>

        <h3 class="text-3xl font-bold text-gray-800 text-center mb-8">
            Log In
        </h3>

        <!-- Status Session -->
        <x-auth-session-status
            class="mb-4"
            :status="session('status')"
        />

        <!-- ERROR LOGIN -->
        @if ($errors->has('email'))
            <div class="mb-5 rounded-lg border border-red-300 bg-red-100 px-4 py-3 text-sm text-red-700">
                Email atau password yang Anda masukkan salah.
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="mb-5">

                <div class="flex items-center border border-gray-300 px-3 bg-white rounded-md">

                    <i class="fa-regular fa-envelope text-gray-500 mr-3"></i>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Email"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full border-0 focus:ring-0 py-3 text-gray-700"
                    >
                </div>

            </div>

            <!-- Password -->
            <div class="mb-5">

                <div class="flex items-center border border-gray-300 px-3 bg-white rounded-md">

                    <i class="fa-solid fa-lock text-gray-500 mr-3"></i>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Password"
                        required
                        autocomplete="current-password"
                        class="w-full border-0 focus:ring-0 py-3 text-gray-700"
                    >

                </div>


            </div>

            <!-- Lupa Password -->
            <div class="text-right mb-8">
                @if (Route::has('password.request'))
                    <a
                        href="{{ route('password.request') }}"
                        class="text-xs text-sky-500 hover:underline"
                    >
                        Lupa Password?
                    </a>
                @endif
            </div>

            <!-- Tombol Login -->
            <button
                type="submit"
                class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 rounded-md transition"
            >
                Login
            </button>

        </form>

    </div>

</div>

</body>
</html>