<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Pengguna</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 min-h-screen">

    <!-- HEADER -->
    <div class="max-w-7xl mx-auto px-6 pt-8">

        <!-- Tombol Back -->
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-sky-600 font-medium transition mb-6">

            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 19l-7-7 7-7" />
            </svg>

            <span>Kembali ke Dashboard</span>
        </a>

        <h1 class="text-2xl font-bold text-gray-800">
            Profile Pengguna
        </h1>
    </div>

    <!-- CONTENT -->
    <div class="max-w-7xl mx-auto px-6 py-6">

        <!-- ================= NOTIFIKASI SUKSES ================= -->
        @if (session('status') === 'profile-updated' || session('status') === 'password-updated')
            <div class="mb-6 p-4 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 text-sm flex items-center justify-between">
                <span>
                    @if(session('status') === 'profile-updated')
                        Informasi profil Anda berhasil diperbarui.
                    @else
                        Password Anda berhasil diperbarui.
                    @endif
                </span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 font-bold hover:text-emerald-900 ml-4">
                    &times;
                </button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- INFORMASI PROFILE -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-bold text-gray-800">
                        Informasi Profil
                    </h2>

                    <p class="text-xs text-gray-500 mt-1">
                        Perbarui nama dan email Anda.
                    </p>
                </div>

                @include('profile.partials.update-profile-information-form')
            </div>


            <!-- PASSWORD -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">

                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-bold text-gray-800">
                        Ubah Password
                    </h2>

                    <p class="text-xs text-gray-500 mt-1">
                        Gunakan password yang kuat untuk menjaga keamanan akun.
                    </p>
                </div>

                @include('profile.partials.update-password-form')
            </div>

        </div>

    </div>

</body>

</html>