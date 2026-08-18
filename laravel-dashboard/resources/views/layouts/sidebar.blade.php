@php
    $role = auth()->user()->role->nama_role ?? null;

    $menuClass = function ($active) {
        return $active
            ? 'flex items-center gap-3 px-3 py-2 rounded-lg bg-sky-100 text-sky-700 font-semibold transition'
            : 'flex items-center gap-3 px-3 py-2 rounded-lg text-gray-800 font-medium hover:bg-sky-100 hover:text-sky-700 transition';
    };

    $iconClass = function ($active) {
        return $active
            ? 'w-5 h-5 text-sky-700'
            : 'w-5 h-5 text-gray-500';
    };
@endphp

<aside class="w-64 shrink-0 bg-white border-r h-screen font-sans">
    <div class="p-4 flex items-center gap-3 border-b">
        <img src="{{ asset('build/assets/img/logo.png') }}"
             alt="Logo"
             class="w-10 h-10 object-contain">

        <div>
            <div class="font-bold text-sm text-gray-800 leading-snug">
                Sistem Informasi Gudang Bahan
            </div>
            <div class="text-xs text-gray-500">
                PT. DPSA
            </div>
        </div>
    </div>

    <nav class="px-4 py-3 space-y-1 text-[14px]">

        <p class="text-[11px] uppercase tracking-wider text-gray-400 mt-4 mb-1 font-semibold">
            DASHBOARD
        </p>

        <a href="{{ route('dashboard') }}"
           class="{{ $menuClass(request()->routeIs('dashboard')) }}">
            <x-heroicon-o-home class="{{ $iconClass(request()->routeIs('dashboard')) }}" />
            <span>Dashboard</span>
        </a>

        @if(in_array($role, ['Super Admin', 'Kepala Gudang', 'Pembelian']))
            <p class="text-[11px] uppercase tracking-wider text-gray-400 mt-4 mb-1 font-semibold">
                PREDIKSI
            </p>

            <a href="{{ route('prediksi.index') }}"
               class="{{ $menuClass(request()->routeIs('prediksi.*')) }}">
                <x-heroicon-o-chart-bar class="{{ $iconClass(request()->routeIs('prediksi.*')) }}" />
                <span>Prediksi</span>
            </a>
        @endif

        @if(in_array($role, ['Super Admin', 'Kepala Gudang', 'Anggota Gudang', 'Pembelian']))
            <p class="text-[11px] uppercase tracking-wider text-gray-400 mt-4 mb-1 font-semibold">
                BAHAN
            </p>

            @if($role === 'Super Admin')
                <a href="{{ route('master-bahan.index') }}"
                   class="{{ $menuClass(request()->routeIs('master-bahan.*')) }}">
                    <x-heroicon-o-cube class="{{ $iconClass(request()->routeIs('master-bahan.*')) }}" />
                    <span>Master Bahan</span>
                </a>
            @endif

            @if(in_array($role, ['Super Admin', 'Kepala Gudang']))
                <a href="{{ route('stok-bahan.index') }}"
                   class="{{ $menuClass(request()->routeIs('stok-bahan.*')) }}">
                    <x-heroicon-o-archive-box class="{{ $iconClass(request()->routeIs('stok-bahan.*')) }}" />
                    <span>Stok Bahan</span>
                </a>
            @endif

            @if(in_array($role, ['Anggota Gudang', 'Pembelian']))
                <a href="{{ route('lihat-bahan.index') }}"
                   class="{{ $menuClass(request()->routeIs('lihat-bahan.*')) }}">
                    <x-heroicon-o-cube class="{{ $iconClass(request()->routeIs('lihat-bahan.*')) }}" />
                    <span>Master Bahan</span>
                </a>
            @endif
        @endif

        @if(in_array($role, ['Super Admin', 'Kepala Gudang', 'Pembelian', 'Anggota Gudang']))
            <p class="text-[11px] uppercase tracking-wider text-gray-400 mt-4 mb-1 font-semibold">
                LAPORAN
            </p>

            @if(in_array($role, ['Super Admin', 'Kepala Gudang', 'Anggota Gudang']))
                <a href="{{ route('permintaan-bahan.index') }}"
                   class="{{ $menuClass(request()->routeIs('permintaan-bahan.*')) }}">
                    <x-heroicon-o-document-text class="{{ $iconClass(request()->routeIs('permintaan-bahan.*')) }}" />
                    <span>Dokubah</span>
                </a>
            @endif

            @if(in_array($role, ['Super Admin', 'Kepala Gudang', 'Pembelian']))
                <a href="{{ route('bahan-masuk.index') }}"
                   class="{{ $menuClass(request()->routeIs('bahan-masuk.*')) }}">
                    <x-heroicon-o-arrow-down-tray class="{{ $iconClass(request()->routeIs('bahan-masuk.*')) }}" />
                    <span>Bahan Masuk</span>
                </a>
            @endif

            @if(in_array($role, ['Super Admin', 'Kepala Gudang', 'Anggota Gudang']))
                <a href="{{ route('bahan-keluar.index') }}"
                   class="{{ $menuClass(request()->routeIs('bahan-keluar.*')) }}">
                    <x-heroicon-o-arrow-up-tray class="{{ $iconClass(request()->routeIs('bahan-keluar.*')) }}" />
                    <span>Bahan Keluar</span>
                </a>
            @endif
        @endif

        @if($role === 'Super Admin')
            <p class="text-[11px] uppercase tracking-wider text-gray-400 mt-4 mb-1 font-semibold">
                PENGATURAN
            </p>

            <a href="{{ route('users.index') }}"
                class="{{ $menuClass(request()->routeIs('users.*')) }}">
                <x-heroicon-o-user-group class="{{ $iconClass(request()->routeIs('users.*')) }}" />
                <span>Manajemen User</span>
            </a>
            <a href="{{ route('management-model.index') }}"
                class="{{ $menuClass(request()->routeIs('model.*')) }}">
                <x-heroicon-o-cpu-chip class="{{ $iconClass(request()->routeIs('model.*')) }}" />
                <span>Manajemen Model</span>
            </a>
        @endif

    </nav>
</aside>