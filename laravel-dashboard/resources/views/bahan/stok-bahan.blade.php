<x-admin-layout>

    <!-- ================= HEADER HALAMAN ================= -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Stok Opname
        </h1>

        <!-- Tombol print opsional -->
        {{-- 
        <a href="#"
           class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fa-solid fa-file-pdf mr-1"></i>
            Print PDF
        </a> 
        --}}
    </div>

    <!-- ================= NOTIFIKASI SUKSES ================= -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 font-bold hover:text-emerald-900 ml-4">
                &times;
            </button>
        </div>
    @endif


    <!-- ================= FILTER STOK ATAS ================= -->
    <div class="bg-white p-6 rounded-xl border border-gray-200 mb-8">

        <label class="text-sm font-medium text-gray-600">
            Filter Stok Berdasarkan
        </label>

        <form method="GET" action="{{ route('stok-bahan.index') }}" class="mt-3">

            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <select name="filter"
                    onchange="this.form.submit()"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">

                <option value="semua" {{ request('filter', 'semua') == 'semua' ? 'selected' : '' }}>
                    Semua Status Stok
                </option>

                <option value="aman" {{ request('filter') == 'aman' ? 'selected' : '' }}>
                    Stok Aman
                </option>

                <option value="menipis" {{ request('filter') == 'menipis' ? 'selected' : '' }}>
                    Stok Menipis
                </option>

                <option value="habis" {{ request('filter') == 'habis' ? 'selected' : '' }}>
                    Stok Habis
                </option>

            </select>

        </form>

    </div>


    <!-- ================= CARD TABEL ================= -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <form method="GET" action="{{ route('stok-bahan.index') }}"
              class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

            <input type="hidden" name="filter" value="{{ request('filter', 'semua') }}">


            <!-- ================= KIRI: DROPDOWN TAMPILKAN DATA ================= -->
            <div>
                <select name="per_page"
                        onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-2 text-sm text-gray-700 h-11 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">

                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100</option>

                </select>
            </div>


            <!-- ================= KANAN: SEARCH ================= -->
            <div class="flex items-center gap-3">

                <div class="relative">

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search..."
                           class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 text-sm text-gray-700 w-64 h-11 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">

                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>

                </div>

            </div>

        </form>


        <!-- ================= TABEL DATA STOK ================= -->
        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left">

                <thead class="border-b text-gray-600 font-medium">
                    <tr>
                        <th class="py-3 w-12">No</th>
                        <th class="py-3">Kode Barang</th>
                        <th class="py-3">Nama Barang</th>
                        <th class="py-3">Kategori</th>
                        <th class="py-3">Ukuran</th>
                        <th class="py-3">Satuan</th>
                        <th class="py-3 text-center">Stok</th>
                        <th class="py-3 text-center">Status</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700 divide-y divide-gray-100">

                    @forelse($data as $item)

                        <tr class="hover:bg-gray-50 transition">

                            <td class="py-4">
                                {{ $data->firstItem() + $loop->index }}
                            </td>

                            <td class="py-4 font-medium text-sky-600">
                                {{ $item['kode_bahan'] ?? '-' }}
                            </td>

                            <td class="py-4 font-medium text-gray-800">
                                {{ $item['nama_bahan'] ?? '-' }}
                            </td>

                            <td class="py-4">
                                {{ $item['kategori_bahan'] ?? '-' }}
                            </td>

                            <!-- ================= KOLOM UKURAN ================= -->
                            <td class="py-4 text-gray-600">
                                @if(isset($item['bahan_jadi']) && $item['bahan_jadi'] == 0 && !empty($item['ukuran']))
                                    <span class="inline-block bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded text-xs font-medium border border-slate-200">
                                        {{ $item['ukuran'] }}
                                    </span>
                                @elseif(!empty($item['ukuran']))
                                    <span class="inline-block bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded text-xs font-medium border border-slate-200">
                                        {{ $item['ukuran'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="py-4">
                                {{ $item['satuan'] ?? '-' }}
                            </td>

                            <!-- Stok dibuat lebih menonjol -->
                            <td class="py-4 text-center font-semibold text-gray-800">
                                {{ $item['stok'] }}
                            </td>

                            <!-- Status stok berdasarkan jumlah stok -->
                            <td class="py-4 text-center">

                                @if($item['stok'] <= 0)

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-600">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        Habis
                                    </span>

                                @elseif($item['stok'] <= 5)

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        Menipis
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">
                                        <i class="fa-solid fa-circle-check"></i>
                                        Aman
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-box-open text-4xl text-gray-300 mb-1"></i>
                                    <span class="text-sm font-medium">Data stok tidak tersedia</span>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <!-- ================= PAGINATION ================= -->
        <div class="mt-5">
            {{ $data->links() }}
        </div>

    </div>

</x-admin-layout>