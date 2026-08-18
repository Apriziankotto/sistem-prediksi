<!-- ================= MODAL EDIT BAHAN KELUAR ================= -->
<div id="modalEdit"
     class="hidden fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-lg rounded-xl p-6">

        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    Edit Bahan Keluar
                </h2>

                <p class="text-sm text-gray-500">
                    Ubah tanggal, jumlah keluar, dan keterangan bahan keluar.
                </p>
            </div>

            <button type="button"
                    onclick="closeEditModal()"
                    class="text-gray-400 hover:text-gray-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEdit" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="text-sm text-gray-600">
                    Tanggal Keluar
                </label>

                <input id="edit_tanggal_aktual"
                       type="date"
                       name="tanggal_aktual"
                       required
                       class="w-full border rounded-lg p-2 mt-1 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <div class="mb-3">
                <label class="text-sm text-gray-600">
                    Jumlah Keluar
                </label>

                <input id="edit_jumlah_aktual"
                       type="number"
                       name="jumlah_aktual"
                       min="0.01"
                       step="0.01"
                       required
                       class="w-full border rounded-lg p-2 mt-1 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

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
                        onclick="closeEditModal()"
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
    function openEditModal(id, tanggal, jumlah, keterangan) {
        const modal = document.getElementById('modalEdit');

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('edit_tanggal_aktual').value = tanggal ?? '';
        document.getElementById('edit_jumlah_aktual').value = jumlah ?? '';
        document.getElementById('edit_keterangan').value = keterangan ?? '';

        document.getElementById('formEdit').action = "{{ url('laporan/bahan-keluar') }}/" + id;
    }

    function closeEditModal() {
        const modal = document.getElementById('modalEdit');

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>