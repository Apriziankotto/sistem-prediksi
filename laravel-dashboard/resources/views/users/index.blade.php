<x-admin-layout>

<div class="space-y-6">

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg text-sm">
            <p class="font-semibold mb-1">Data gagal disimpan.</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-800">
            Manajemen User
        </h1>
    </div>


    <!-- SUMMARY CARD -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Total User</p>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ $totalUser }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                    <i class="fa-solid fa-user-shield"></i>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Super Admin</p>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ $totalAdmin }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center">
                    <i class="fa-solid fa-id-card"></i>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Total Role</p>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ $totalRole }}
                    </h3>
                </div>
            </div>
        </div>

    </div>


    <!-- ================= TABLE USER ================= -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <!-- Header Tabel -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
            <h2 class="text-lg font-bold text-gray-800">
                Daftar User
            </h2>

            <form method="GET"
                action="{{ route('users.index') }}"
                class="flex items-center gap-3">

                <!-- Search -->
                <div class="relative">

                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari user..."
                        class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 text-sm w-64 h-11 focus:outline-none focus:ring-2 focus:ring-sky-500">

                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>

                </div>

                <!-- Tombol Tambah User -->
                <button type="button"
                        onclick="openAddUserModal()"
                        class="h-11 bg-sky-600 hover:bg-sky-700 text-white px-5 rounded-lg text-sm inline-flex items-center gap-2">

                    <i class="fa-solid fa-plus"></i>
                    Tambah User

                </button>

            </form>

        </div>

        <!-- ================= TABEL ================= -->
        <div class="overflow-x-auto border rounded-xl">

            <table class="w-full text-sm text-left whitespace-nowrap">

                <thead class="border-b text-gray-600 bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 w-16 text-center">No</th>
                        <th class="py-3 px-4">Nama User</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Tanggal Ditambahkan</th>
                        <th class="py-3 px-4 text-center w-24">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">

                    @forelse($users as $user)

                        <tr class="border-b hover:bg-gray-50">

                            <td class="py-4 px-4 text-center">
                                {{ ($users->firstItem() ?? 1) + $loop->index }}
                            </td>

                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                                        {{ strtoupper(substr($user->name,0,1)) }}
                                    </div>

                                    <div>

                                        <div class="font-semibold text-gray-800">
                                            {{ $user->name }}
                                        </div>

                                        @if(auth()->id() === $user->id)
                                            <div class="text-xs text-sky-600">
                                                Akun sedang digunakan
                                            </div>
                                        @endif

                                    </div>

                                </div>
                            </td>

                            <td class="py-4 px-4 text-gray-600">
                                {{ $user->email }}
                            </td>

                            <td class="py-4 px-4">

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                                    {{ optional($user->role)->nama_role === 'Super Admin'
                                        ? 'bg-purple-100 text-purple-700'
                                        : 'bg-gray-100 text-gray-700' }}">

                                    {{ optional($user->role)->nama_role ?? '-' }}

                                </span>

                            </td>

                            <td class="py-4 px-4 text-gray-500">
                                {{ $user->created_at?->format('d-m-Y H:i') }}
                            </td>

                            <td class="py-4 px-4 text-center">

                                @if(auth()->id() !== $user->id)

                                    <form action="{{ route('users.destroy',$user->id) }}"
                                        method="POST"
                                        class="inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                onclick="return confirm('Hapus user ini?')"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-red-100 text-red-600 hover:bg-red-200">

                                            <i class="fa-solid fa-trash text-sm"></i>

                                        </button>

                                    </form>

                                @else

                                    <span class="text-xs text-gray-400">
                                        -
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center py-8 text-gray-400">

                                Belum ada data user.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-5">
            {{ $users->links() }}
        </div>

    </div>

</div>

@include('users.partials.modal-add-user')

</x-admin-layout>