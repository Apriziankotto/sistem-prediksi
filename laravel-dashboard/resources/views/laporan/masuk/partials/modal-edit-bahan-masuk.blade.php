<!-- ================= MODAL EDIT BARANG MASUK ================= -->
<div id="modalEdit"
     class="hidden fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-lg rounded-xl p-6">

        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    Edit Barang Masuk
                </h2>

                <p class="text-sm text-gray-500">
                    Ubah bahan, jumlah masuk, dan keterangan.
                </p>
            </div>

            <button type="button"
                    onclick="closeModalEdit()"
                    class="text-gray-400 hover:text-gray-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEdit" method="POST">
            @csrf
            @method('PUT')

            <!-- Pilih bahan -->
            <div class="mb-3">
                <label class="text-sm text-gray-600">
                    Pilih Bahan
                </label>

                <select id="edit_master_bahan_id"
                        name="master_bahan_id"
                        required
                        class="w-full border rounded-lg p-2 mt-1 focus:outline-none focus:ring-2 focus:ring-sky-500">

                    <option value="">
                        -- Pilih Bahan --
                    </option>

                    @foreach($masterBahan as $bahan)
                        <option value="{{ $bahan->id }}">
                            {{ $bahan->kode_bahan }} - {{ $bahan->nama_bahan }} | {{ $bahan->satuan }}
                        </option>
                    @endforeach

                </select>
            </div>

            <!-- Jumlah masuk -->
            <div class="mb-3">
                <label class="text-sm text-gray-600">
                    Jumlah Masuk
                </label>

                <input id="edit_jumlah"
                       type="number"
                       name="jumlah"
                       min="0.01"
                       step="0.01"
                       required
                       class="w-full border rounded-lg p-2 mt-1 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <!-- Keterangan -->
            <div class="mb-4">
                <label class="text-sm text-gray-600">
                    Keterangan
                </label>

                <textarea id="edit_keterangan"
                          name="keterangan"
                          rows="3"
                          class="w-full border rounded-lg p-2 mt-1 focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
            </div>

            <div class="flex justify-end gap-2">

                <button type="button"
                        onclick="closeModalEdit()"
                        class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                    Batal
                </button>

                <button type="submit"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded">
                    Update
                </button>

            </div>

        </form>

    </div>

</div>

<script>
    function openModalEdit(id, masterBahanId, jumlah, keterangan) {
        const modal = document.getElementById('modalEdit');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('edit_master_bahan_id').value = masterBahanId;
        document.getElementById('edit_jumlah').value = jumlah;
        document.getElementById('edit_keterangan').value = keterangan ?? '';

        document.getElementById('formEdit').action = "{{ url('laporan/bahan-masuk') }}/" + id;
    }

    function closeModalEdit() {
        const modal = document.getElementById('modalEdit');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>