@php
    $user = auth()->user();
    $namaLengkap = $user->name;
    $namaDepan = explode(' ', trim($user->name))[0];

    // Jika relasi role sudah ada di model User
    $roleName = optional($user->role)->nama_role ?? 'User';
@endphp

<div class="bg-white shadow-sm px-4 md:px-6 py-3 flex items-center justify-between">

    <!-- LEFT: HAMBURGER + SEARCH -->
    <div class="flex items-center gap-4 w-full">

        <!-- BUTTON SIDEBAR MOBILE -->
        <button
            type="button"
            @click="$dispatch('toggle-sidebar')"
            class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border text-gray-600 hover:bg-gray-100 focus:outline-none"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-6 h-6"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- SEARCH -->
        <form method="GET" action="{{ url()->current() }}" class="relative w-full max-w-md">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari data..."
                class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
            >

            <span class="absolute left-3 top-2.5 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                </svg>
            </span>
        </form>

    </div>

    <!-- RIGHT: USER DROPDOWN -->
    <div class="relative ml-4" x-data="{ open: false }">

        <!-- BUTTON USER -->
        <button
            type="button"
            @click="open = !open"
            class="flex items-center gap-2 text-gray-700 focus:outline-none"
        >

            <div class="w-9 h-9 bg-sky-600 text-white rounded-full flex items-center justify-center font-bold uppercase">
                {{ strtoupper(substr($namaLengkap, 0, 1)) }}
            </div>

            <div class="hidden md:block text-left">
                <div class="text-sm font-medium">
                    {{ $namaDepan }}
                </div>
            </div>

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4 text-gray-500 hidden md:block"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7" />
            </svg>

        </button>

        <!-- DROPDOWN -->
        <div
            x-show="open"
            x-transition
            @click.away="open = false"
            class="absolute right-0 mt-3 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden"
        >

            <!-- USER INFO -->
            <div class="px-4 py-3 border-b bg-gray-50">
                <div class="font-semibold text-gray-800">
                    {{ $namaLengkap }}
                </div>

                <div class="text-sm text-gray-500">
                    {{ $roleName }}
                </div>
            </div>

            <!-- PROFILE -->
            <a href="{{ route('profile.edit') }}"
            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">

                <x-heroicon-o-user-circle class="w-5 h-5 text-gray-500" />

                <span>Profile</span>
            </a>

            <!-- LOGOUT -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
                    </svg>
                    Logout
                </button>
            </form>

        </div>

    </div>

</div>