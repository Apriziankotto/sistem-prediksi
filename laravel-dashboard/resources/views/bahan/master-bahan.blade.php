<x-admin-layout>

    <!-- ================= HEADER HALAMAN ================= -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Master Bahan
        </h1>
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


    <!-- ================= FILTER KATEGORI ATAS ================= -->
    <div class="bg-white p-6 rounded-xl border border-gray-200 mb-8">

        <label class="text-sm text-gray-600 font-medium">
            Filter Berdasarkan Kategori
        </label>

        <form method="GET" action="{{ route('master-bahan.index') }}" class="mt-3">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <select name="kategori"
                    onchange="this.form.submit()"
                    class="w-full border border-gray-400 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">

                <option value="semua" {{ request('kategori', 'semua') == 'semua' ? 'selected' : '' }}>
                    Semua
                </option>

                @foreach($kategoriList as $kategori)
                    <option value="{{ $kategori }}"
                        {{ request('kategori') == $kategori ? 'selected' : '' }}>
                        {{ $kategori }}
                    </option>
                @endforeach

            </select>

        </form>

    </div>


    <!-- ================= CARD TABEL ================= -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <form method="GET" action="{{ route('master-bahan.index') }}"
              class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

            <input type="hidden" name="kategori" value="{{ request('kategori', 'semua') }}">

            <!-- ================= KIRI: DROPDOWN TAMPILKAN DATA ================= -->
            <div>
                <select name="per_page"
                        onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-2 text-sm h-11 focus:outline-none focus:ring-2 focus:ring-sky-500">

                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100</option>

                </select>
            </div>


            <!-- ================= KANAN: SEARCH DAN TAMBAH ================= -->
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
                        onclick="openModal()"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2 rounded-md text-sm h-11">
                    + Tambah
                </button>

            </div>

        </form>


        <!-- ================= TABEL DATA MASTER BAHAN ================= -->
        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left">

                <thead class="border-b text-gray-700">
                    <tr>
                        <th class="py-3 w-12">No</th>
                        <th class="py-3">Kode Bahan</th>
                        <th class="py-3">Nama Bahan</th>
                        <th class="py-3">Kategori</th>
                        <th class="py-3">Ukuran</th>
                        <th class="py-3">Satuan</th>
                        <th class="py-3">Tipe</th>
                        <th class="py-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">

                    @forelse($masterBahan as $item)

                        <tr class="border-b hover:bg-gray-50">

                            <td class="py-4">
                                {{ $masterBahan->firstItem() + $loop->index }}
                            </td>

                            <td class="py-4 font-medium text-sky-600">
                                {{ $item->kode_bahan }}
                            </td>

                            <td class="py-4 font-medium text-gray-800">
                                {{ $item->nama_bahan }}
                            </td>

                            <td class="py-4">
                                {{ $item->kategori_bahan }}
                            </td>

                            <!-- Ukuran Bahan -->
                            <td class="py-4">
                                @if($item->bahan_jadi == 0 && !empty($item->ukuran))
                                    <span class="inline-block bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded text-xs font-medium border border-slate-200">
                                        {{ $item->ukuran }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="py-4">
                                {{ $item->satuan }}
                            </td>

                            <!-- Status Barang Jadi / Mentah -->
                            <td class="py-4">
                                @if($item->bahan_jadi == 1)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Barang Jadi
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        Bahan Mentah
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi Edit & Hapus -->
                            <td class="py-4 text-center">

                                <div class="flex items-center justify-center gap-2">

                                    <button type="button"
                                            onclick="openEditModal(
                                                @js($item->id),
                                                @js($item->kode_bahan),
                                                @js($item->nama_bahan),
                                                @js($item->kategori_bahan),
                                                @js($item->satuan),
                                                @js($item->bahan_jadi ?? 0),
                                                @js($item->ukuran ?? '')
                                            )"
                                            class="w-8 h-8 flex items-center justify-center rounded-md bg-sky-100 text-sky-600 hover:bg-sky-200 hover:text-sky-700 transition"
                                            title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </button>

                                    <form action="{{ route('master-bahan.destroy', $item->id) }}"
                                          method="POST"
                                          class="inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                onclick="return confirm('Hapus data master bahan ini?')"
                                                class="w-8 h-8 flex items-center justify-center rounded-md bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-700 transition"
                                                title="Hapus">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center py-6 text-gray-500">
                                Data master bahan tidak ditemukan.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <!-- ================= PAGINATION ================= -->
        <div class="mt-5">
            {{ $masterBahan->links() }}
        </div>

    </div>


    <!-- ================= MODAL TAMBAH DATA ================= -->
    <div id="modalCreate"
        class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">

        <div class="bg-white w-full max-w-lg rounded-xl p-6 shadow-xl max-h-[90vh] overflow-y-auto">

            <h2 class="text-xl font-bold mb-4 text-gray-800">
                Tambah Master Bahan
            </h2>

            <form action="{{ route('master-bahan.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Kode bahan otomatis -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kode Bahan</label>
                    <input class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-500 focus:outline-none"
                        value="Kode bahan otomatis"
                        readonly>
                </div>

                <!-- Input nama bahan -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bahan <span class="text-red-500">*</span></label>
                    <input class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500"
                        name="nama_bahan"
                        placeholder="Masukkan nama bahan"
                        required>
                </div>

                <!-- Input Tipe: Apakah Barang Jadi? -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Bahan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-2.5 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="bahan_jadi" value="0" checked onchange="toggleUkuranCreate(this.value)" class="text-sky-600 focus:ring-sky-500">
                            <span class="text-sm text-gray-700 font-medium">Bukan Barang Jadi</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="bahan_jadi" value="1" onchange="toggleUkuranCreate(this.value)" class="text-sky-600 focus:ring-sky-500">
                            <span class="text-sm text-gray-700 font-medium">Barang Jadi</span>
                        </label>
                    </div>
                </div>

                <!-- Input Ukuran Bahan (Muncul jika Bukan Barang Jadi) -->
                <div id="wrapper_ukuran_create">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ukuran Bahan <span class="text-red-500">*</span></label>
                    <input type="text"
                           id="create_ukuran"
                           name="ukuran"
                           placeholder="Contoh: 122 x 244 cm, Tebal 3 mm, dsb"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500"
                           required>
                </div>

                <!-- Input kategori bahan -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Bahan</label>
                    <select id="kategoriSelect"
                            name="kategori_bahan"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500">

                        <option value="">Pilih Kategori</option>

                        @foreach($kategoriList as $kategori)
                            <option value="{{ $kategori }}">
                                {{ $kategori }}
                            </option>
                        @endforeach

                        <option value="lainnya">+ Tambah Kategori Baru</option>

                    </select>

                    <input type="text"
                        id="kategoriBaru"
                        name="kategori_baru"
                        placeholder="Masukkan kategori baru"
                        class="hidden w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 mt-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- Input satuan bahan -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Satuan <span class="text-red-500">*</span></label>
                    <input class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500"
                        name="satuan"
                        placeholder="Contoh: Pcs, Lembar, Meter"
                        required>
                </div>

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button"
                            onclick="closeModal()"
                            class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg text-sm transition">
                        Batal
                    </button>

                    <button type="submit"
                            class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- ================= MODAL EDIT DATA ================= -->
    <div id="modalEdit"
         class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">

        <div class="bg-white w-full max-w-lg rounded-xl p-6 shadow-xl max-h-[90vh] overflow-y-auto">

            <h2 class="text-xl font-bold mb-4 text-gray-800">
                Edit Master Bahan
            </h2>

            <form id="formEdit" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Input kode bahan edit -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kode Bahan</label>
                    <input id="edit_kode"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-500 focus:outline-none"
                           name="kode_bahan" readonly>
                </div>

                <!-- Input nama bahan edit -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bahan <span class="text-red-500">*</span></label>
                    <input id="edit_nama"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500"
                           name="nama_bahan" required>
                </div>

                <!-- Input Tipe Edit: Apakah Barang Jadi? -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Bahan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-2.5 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" id="edit_bahan_jadi_0" name="bahan_jadi" value="0" onchange="toggleUkuranEdit(this.value)" class="text-sky-600 focus:ring-sky-500">
                            <span class="text-sm text-gray-700 font-medium">Bukan Barang Jadi</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" id="edit_bahan_jadi_1" name="bahan_jadi" value="1" onchange="toggleUkuranEdit(this.value)" class="text-sky-600 focus:ring-sky-500">
                            <span class="text-sm text-gray-700 font-medium">Barang Jadi</span>
                        </label>
                    </div>
                </div>

                <!-- Input Ukuran Edit (Muncul jika Bukan Barang Jadi) -->
                <div id="wrapper_ukuran_edit">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ukuran Bahan <span class="text-red-500">*</span></label>
                    <input type="text"
                           id="edit_ukuran"
                           name="ukuran"
                           placeholder="Contoh: 122 x 244 cm, Tebal 3 mm, dsb"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- Input kategori bahan edit (Dropdown) -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Bahan</label>
                    <select id="edit_kategori_select"
                            name="kategori_bahan"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500">

                        <option value="">Pilih Kategori</option>

                        @foreach($kategoriList as $kategori)
                            <option value="{{ $kategori }}">
                                {{ $kategori }}
                            </option>
                        @endforeach

                        <option value="lainnya">+ Tambah Kategori Baru</option>

                    </select>

                    <input type="text"
                           id="edit_kategori_baru"
                           name="kategori_baru"
                           placeholder="Masukkan kategori baru"
                           class="hidden w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 mt-2 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- Input satuan bahan edit -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Satuan <span class="text-red-500">*</span></label>
                    <input id="edit_satuan"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-sky-500"
                           name="satuan" required>
                </div>

                <div class="flex justify-end gap-2 pt-3">

                    <button type="button"
                            onclick="closeEditModal()"
                            class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg text-sm transition">
                        Batal
                    </button>

                    <button type="submit"
                            class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm transition">
                        Update
                    </button>

                </div>

            </form>

        </div>
    </div>


    <!-- ================= SCRIPT MODAL & TOGGLE UKURAN ================= -->
    <script>
        // Logika toggle ukuran di Modal Tambah
        function toggleUkuranCreate(val) {
            const wrapper = document.getElementById('wrapper_ukuran_create');
            const input = document.getElementById('create_ukuran');
            if (val === '1' || val === 1) {
                wrapper.classList.add('hidden');
                input.required = false;
                input.value = '';
            } else {
                wrapper.classList.remove('hidden');
                input.required = true;
            }
        }

        // Logika toggle ukuran di Modal Edit
        function toggleUkuranEdit(val) {
            const wrapper = document.getElementById('wrapper_ukuran_edit');
            const input = document.getElementById('edit_ukuran');
            if (val === '1' || val === 1) {
                wrapper.classList.add('hidden');
                input.required = false;
                input.value = '';
            } else {
                wrapper.classList.remove('hidden');
                input.required = true;
            }
        }

        // Membuka modal tambah data
        function openModal() {
            document.getElementById('modalCreate').classList.remove('hidden');
            // Reset ke default: Bukan Barang Jadi (0)
            const radioDefault = document.querySelector('input[name="bahan_jadi"][value="0"]');
            if (radioDefault) radioDefault.checked = true;
            toggleUkuranCreate(0);
        }

        // Menutup modal tambah data
        function closeModal() {
            document.getElementById('modalCreate').classList.add('hidden');
        }

        // Membuka modal edit dan mengisi data
        function openEditModal(id, kode, nama, kategori, satuan, bahanJadi, ukuran) {
            document.getElementById('modalEdit').classList.remove('hidden');

            document.getElementById('edit_kode').value = kode;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_satuan').value = satuan;

            // Set Radio Bahan Jadi
            if (bahanJadi == 1) {
                document.getElementById('edit_bahan_jadi_1').checked = true;
                toggleUkuranEdit(1);
            } else {
                document.getElementById('edit_bahan_jadi_0').checked = true;
                document.getElementById('edit_ukuran').value = ukuran || '';
                toggleUkuranEdit(0);
            }

            // Kategori
            let editSelect = document.getElementById('edit_kategori_select');
            let editInputBaru = document.getElementById('edit_kategori_baru');
            editSelect.value = kategori;
            editInputBaru.classList.add('hidden');
            editInputBaru.required = false;
            editInputBaru.value = '';

            document.getElementById('formEdit').action = "{{ url('master-bahan') }}/" + id;
        }

        // Menutup modal edit
        function closeEditModal() {
            document.getElementById('modalEdit').classList.add('hidden');
        }
    </script>

    <!-- Script toggle kategori baru modal Tambah -->
    <script>
    document.getElementById('kategoriSelect')
        .addEventListener('change', function () {
            let inputBaru = document.getElementById('kategoriBaru');
            if (this.value === 'lainnya') {
                inputBaru.classList.remove('hidden');
                inputBaru.required = true;
            } else {
                inputBaru.classList.add('hidden');
                inputBaru.required = false;
            }
        });
    </script>

    <!-- Script toggle kategori baru modal Edit -->
    <script>
    document.getElementById('edit_kategori_select')
        .addEventListener('change', function () {
            let inputBaru = document.getElementById('edit_kategori_baru');
            if (this.value === 'lainnya') {
                inputBaru.classList.remove('hidden');
                inputBaru.required = true;
            } else {
                inputBaru.classList.add('hidden');
                inputBaru.required = false;
            }
        });
    </script>

</x-admin-layout>