<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Font Awesome untuk icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans overflow-x-hidden" style="font-family: 'Poppins', sans-serif;">

<div class="flex min-h-screen bg-gray-100 overflow-x-hidden">

    <!-- Sidebar jangan boleh mengecil -->
    <div class="shrink-0">
        @include('layouts.sidebar')
    </div>

    <!-- Konten utama boleh mengecil sesuai sisa layar -->
    <div class="flex-1 min-w-0 flex flex-col">

        @include('layouts.topbar')

        <!-- 
            overflow-x-hidden di sini agar halaman utama tidak ikut scroll kanan-kiri.
            Nanti scroll horizontal hanya muncul di div tabel saja.
        -->
        <main class="flex-1 min-w-0 p-6 overflow-x-hidden">
            {{ $slot }}
        </main>

    </div>

</div>

</body>
</html>