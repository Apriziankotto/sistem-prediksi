<!-- ================= MODAL TAMBAH BARANG MASUK ================= -->
<div id="modalCreate"
     class="hidden fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-2xl rounded-xl shadow-lg max-h-[90vh] overflow-hidden">

        <!-- HEADER MODAL -->
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <div>
                <h2 class="text-lg font-bold text-gray-800">
                    Tambah Barang Masuk
                </h2>

                <p class="text-xs text-gray-500">
                    Cari bahan, pilih bahan, lalu masukkan jumlah barang masuk.
                </p>
            </div>

            <button type="button"
                    onclick="closeModalCreate()"
                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- BODY MODAL SCROLL -->
        <div class="p-5 overflow-y-auto max-h-[calc(90vh-80px)]">

            <form action="{{ route('bahan-masuk.store') }}" method="POST">
                @csrf

                <input type="hidden"
                       name="master_bahan_id"
                       id="selectedMasterBahanId"
                       required>

                <!-- SEARCH BAHAN -->
                <div class="mb-3">
                    <label class="text-sm text-gray-600">
                        Cari Bahan
                    </label>

                    <input type="text"
                           id="searchMasterBahan"
                           onkeyup="filterMasterBahan()"
                           placeholder="Cari kode atau nama bahan..."
                           class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- LIST BAHAN -->
                <div class="mb-3">
                    <label class="text-sm text-gray-600">
                        Pilih Bahan
                    </label>

                    <div class="max-h-48 overflow-y-auto border rounded-lg mt-1">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b text-gray-600 sticky top-0 z-10">
                                <tr>
                                    <th class="py-2 px-3 w-10"></th>
                                    <th class="py-2 px-3 text-left w-28">Kode</th>
                                    <th class="py-2 px-3 text-left">Nama Bahan</th>
                                    <th class="py-2 px-3 text-left w-24">Satuan</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($masterBahan as $bahan)
                                    <tr class="border-b bahan-masuk-row hover:bg-sky-50 cursor-pointer"
                                        onclick="selectMasterBahan('{{ $bahan->id }}', this)">

                                        <td class="py-2 px-3 text-center">
                                            <input type="radio"
                                                   name="radio_master_bahan"
                                                   value="{{ $bahan->id }}"
                                                   onclick="selectMasterBahan('{{ $bahan->id }}', this.closest('tr'))">
                                        </td>

                                        <td class="py-2 px-3 font-medium text-sky-600 bahan-kode">
                                            {{ $bahan->kode_bahan }}
                                        </td>

                                        <td class="py-2 px-3 bahan-nama">
                                            {{ $bahan->nama_bahan }}
                                        </td>

                                        <td class="py-2 px-3 text-gray-500 bahan-satuan">
                                            {{ $bahan->satuan ?? '-' }}
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-400">
                                            Data bahan belum tersedia.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <p id="selectedBahanText" class="text-xs text-gray-500 mt-2">
                        Belum ada bahan yang dipilih.
                    </p>
                </div>

                <!-- JUMLAH MASUK -->
                <div class="mb-3">
                    <label class="text-sm text-gray-600">
                        Jumlah Masuk
                    </label>

                    <input type="number"
                           name="jumlah"
                           min="0.01"
                           step="0.01"
                           required
                           placeholder="Contoh: 10"
                           class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- KETERANGAN -->
                <div class="mb-4">
                    <label class="text-sm text-gray-600">
                        Keterangan
                    </label>

                    <textarea name="keterangan"
                              rows="2"
                              placeholder="Contoh: Restock gudang / barang dari supplier"
                              class="w-full border rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                </div>

                <!-- BUTTON -->
                <div class="flex justify-end gap-2 sticky bottom-0 bg-white pt-3 border-t">

                    <button type="button"
                            onclick="closeModalCreate()"
                            class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-md text-sm">
                        Batal
                    </button>

                    <button type="submit"
                            id="btnSimpanBarangMasuk"
                            disabled
                            class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        Simpan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>
    function openModalCreate() {
        const modal = document.getElementById('modalCreate');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModalCreate() {
        const modal = document.getElementById('modalCreate');

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        resetModalCreateBarangMasuk();
    }

    function filterMasterBahan() {
        const input = document.getElementById('searchMasterBahan').value.toLowerCase();
        const rows = document.querySelectorAll('.bahan-masuk-row');

        rows.forEach(row => {
            const kode = row.querySelector('.bahan-kode').innerText.toLowerCase();
            const nama = row.querySelector('.bahan-nama').innerText.toLowerCase();
            const satuan = row.querySelector('.bahan-satuan').innerText.toLowerCase();

            row.style.display =
                kode.includes(input) || nama.includes(input) || satuan.includes(input)
                    ? ''
                    : 'none';
        });
    }

    function selectMasterBahan(id, row) {
        document.getElementById('selectedMasterBahanId').value = id;
        document.getElementById('btnSimpanBarangMasuk').disabled = false;

        document.querySelectorAll('.bahan-masuk-row').forEach(r => {
            r.classList.remove('bg-sky-100');
        });

        row.classList.add('bg-sky-100');

        const radio = row.querySelector('input[type="radio"]');

        if (radio) {
            radio.checked = true;
        }

        const kode = row.querySelector('.bahan-kode').innerText;
        const nama = row.querySelector('.bahan-nama').innerText;
        const satuan = row.querySelector('.bahan-satuan').innerText;

        document.getElementById('selectedBahanText').innerText =
            'Dipilih: ' + kode + ' - ' + nama + ' | ' + satuan;
    }

    function resetModalCreateBarangMasuk() {
        const searchInput = document.getElementById('searchMasterBahan');
        const selectedInput = document.getElementById('selectedMasterBahanId');
        const selectedText = document.getElementById('selectedBahanText');
        const submitButton = document.getElementById('btnSimpanBarangMasuk');

        if (searchInput) {
            searchInput.value = '';
        }

        if (selectedInput) {
            selectedInput.value = '';
        }

        if (selectedText) {
            selectedText.innerText = 'Belum ada bahan yang dipilih.';
        }

        if (submitButton) {
            submitButton.disabled = true;
        }

        document.querySelectorAll('.bahan-masuk-row').forEach(row => {
            row.style.display = '';
            row.classList.remove('bg-sky-100');
        });

        document.querySelectorAll('input[name="radio_master_bahan"]').forEach(radio => {
            radio.checked = false;
        });
    }
</script>