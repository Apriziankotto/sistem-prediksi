<x-admin-layout>

    <!-- Flatpickr CSS (Untuk format tampilan tanggal dd/mm/yyyy) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- ================= FLASH MESSAGE ================= -->
    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif


    <!-- ================= HEADER HALAMAN ================= -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Bahan Masuk
        </h1>
    </div>


    <!-- ================= CARD RINGKASAN ================= -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <!-- Total transaksi -->
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center gap-3">

                <div class="w-10 h-10 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center">
                    <i class="fa-solid fa-receipt"></i>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Total Transaksi</p>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ $totalTransaksi }}
                    </h3>
                </div>

            </div>
        </div>

        <!-- Total jumlah masuk -->
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center gap-3">

                <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Total Jumlah Masuk</p>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ number_format($totalJumlahMasuk, 2, ',', '.') }}
                    </h3>
                </div>

            </div>
        </div>

        <!-- Transaksi hari ini -->
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center gap-3">

                <div class="w-10 h-10 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Transaksi Hari Ini</p>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ $totalHariIni }}
                    </h3>
                </div>

            </div>
        </div>

    </div>


    <!-- ================= FILTER TANGGAL ================= -->
    <div class="bg-white p-6 rounded-xl border border-gray-200 mb-6">

        <label class="text-sm font-medium text-gray-700">
            Filter Barang Masuk Berdasarkan Tanggal
        </label>

        <form method="GET"
              action="{{ route('bahan-masuk.index') }}"
              class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3">

            <!-- Menjaga search agar tidak hilang saat filter tanggal -->
            <input type="hidden" name="search" value="{{ request('search') }}">

            <!-- Menjaga per_page agar tidak hilang saat filter tanggal -->
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <div>
                <label class="text-xs text-gray-500 block mb-1">Tanggal Mulai</label>
                <div class="relative">
                    <input type="text"
                           id="tanggal_mulai"
                           name="tanggal_mulai"
                           value="{{ request('tanggal_mulai') }}"
                           placeholder="dd/mm/yyyy"
                           class="datepicker-input w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 cursor-pointer">
                    <i class="fa-regular fa-calendar absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-sm"></i>
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-500 block mb-1">Tanggal Selesai</label>
                <div class="relative">
                    <input type="text"
                           id="tanggal_selesai"
                           name="tanggal_selesai"
                           value="{{ request('tanggal_selesai') }}"
                           placeholder="dd/mm/yyyy"
                           class="datepicker-input w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 cursor-pointer">
                    <i class="fa-regular fa-calendar absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-sm"></i>
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md text-sm h-[38px] flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </button>

                @if(request('tanggal_mulai') || request('tanggal_selesai'))
                    <a href="{{ route('bahan-masuk.index', ['search' => request('search'), 'per_page' => request('per_page')]) }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-2 rounded-md text-sm h-[38px] flex items-center justify-center transition-colors"
                       title="Reset Filter Tanggal">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>

        </form>

    </div>


    <!-- ================= CARD TABEL ================= -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <form method="GET"
              action="{{ route('bahan-masuk.index') }}"
              class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

            <!-- Menjaga filter tanggal agar tidak hilang saat search/per_page -->
            <input type="hidden" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
            <input type="hidden" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}">

            <!-- Dropdown tampilkan data -->
            <div>
                <select name="per_page"
                        onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-2 text-sm h-11 focus:outline-none focus:ring-2 focus:ring-sky-500">

                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>
                        10
                    </option>

                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>
                        25
                    </option>

                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>
                        50
                    </option>

                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>
                        100
                    </option>

                </select>
            </div>

            <!-- Search dan tombol tambah -->
            <div class="flex items-center gap-3">

                <div class="relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search..."
                           class="border border-gray-400 rounded-lg pl-10 pr-4 py-2 text-sm w-64 h-11 focus:outline-none focus:ring-2 focus:ring-sky-500">

                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                </div>

                <button type="button"
                        onclick="openModalCreate()"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-md text-sm h-11">
                    <i class="fa-solid fa-plus mr-1"></i>
                    Tambah Masuk
                </button>

            </div>

        </form>


        <!-- ================= TABEL BARANG MASUK ================= -->
        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left">

                <thead class="border-b text-gray-600">
                    <tr>
                        <th class="py-3 w-12">No</th>
                        <th class="py-3">Tanggal</th>
                        <th class="py-3">Kode Bahan</th>
                        <th class="py-3">Nama Bahan</th>
                        <!-- <th class="py-3">Kategori</th>
                        <th class="py-3">Satuan</th> -->
                        <th class="py-3 text-center">Jumlah Masuk</th>
                        <th class="py-3">Keterangan</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">

                    @forelse($barangMasuk as $item)

                        <tr class="border-b hover:bg-gray-50">

                            <!-- Nomor pagination -->
                            <td class="py-4">
                                {{ $barangMasuk->firstItem() + $loop->index }}
                            </td>

                            <td class="py-4 text-gray-500">
                                {{ $item->created_at ? $item->created_at->format('d/m/Y') : '-' }}
                            </td>

                            <td class="py-4 font-medium text-sky-600">
                                {{ $item->masterBahan->kode_bahan ?? '-' }}
                            </td>

                            <td class="py-4">
                                {{ $item->masterBahan->nama_bahan ?? '-' }}
                            </td>

                            <!-- <td class="py-4">
                                {{ $item->masterBahan->kategori_bahan ?? '-' }}
                            </td>

                            <td class="py-4">
                                {{ $item->masterBahan->satuan ?? '-' }}
                            </td> -->

                            <td class="py-4 text-center font-semibold">
                                {{ number_format($item->jumlah, 2, ',', '.') }}
                            </td>

                            <td class="py-4">
                                {{ $item->keterangan ?? '-' }}
                            </td>

                            <td class="py-4 text-center">

                                <div class="flex items-center justify-center gap-2">

                                    <!-- Tombol edit -->
                                    <button type="button"
                                            onclick="openModalEdit(
                                                @js($item->id),
                                                @js($item->master_bahan_id),
                                                @js($item->jumlah),
                                                @js($item->keterangan)
                                            )"
                                            class="w-8 h-8 flex items-center justify-center rounded-md bg-sky-100 text-sky-600 hover:bg-sky-200"
                                            title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </button>

                                    <!-- Tombol hapus -->
                                    <form action="{{ route('bahan-masuk.destroy', $item->id) }}"
                                          method="POST"
                                          class="inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                onclick="return confirm('Hapus data barang masuk ini? Stok bahan akan ikut berkurang.')"
                                                class="w-8 h-8 flex items-center justify-center rounded-md bg-red-100 text-red-600 hover:bg-red-200"
                                                title="Hapus">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="text-center py-8 text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-box-open text-3xl text-gray-300"></i>
                                    <span>Data barang masuk belum tersedia.</span>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <!-- ================= PAGINATION ================= -->
        <div class="mt-5">
            {{ $barangMasuk->links() }}
        </div>

    </div>

    @include('laporan.masuk.partials.modal-add-bahan-masuk')
    @include('laporan.masuk.partials.modal-edit-bahan-masuk')

    <!-- ================= SCRIPT FLATPICKR ================= -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const config = {
                locale: 'id',
                altInput: true,
                altFormat: 'd/m/Y',     // Tampilan ke user: dd/mm/yyyy
                dateFormat: 'Y-m-d',     // Nilai submit ke backend: yyyy-mm-dd
                allowInput: true,
            };

            flatpickr('#tanggal_mulai', config);
            flatpickr('#tanggal_selesai', config);
        });
    </script>

</x-admin-layout>