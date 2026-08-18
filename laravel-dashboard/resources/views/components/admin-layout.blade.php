<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans">

<div class="flex h-screen">

    <!-- SIDEBAR -->
    @include('layouts.sidebar')

    <!-- MAIN AREA -->
    <div class="flex flex-col flex-1">

        <!-- TOPBAR -->
        @include('layouts.topbar')

        <!-- CONTENT -->
        <main class="p-6 overflow-y-auto">
            {{ $slot }}
        </main>

    </div>

</div>

</body>
</html>