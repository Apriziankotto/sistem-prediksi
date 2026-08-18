<!-- ================= MODAL EDIT SPK ================= -->
<div id="modalEditSpk"
     class="hidden fixed inset-0 bg-black bg-opacity-40 items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-lg rounded-xl p-6 relative">

        <button type="button"
                onclick="closeEditSpkModal()"
                class="absolute top-3 right-3 text-gray-500 hover:text-sky-600">
            ✕
        </button>

        <h2 class="text-xl font-bold mb-5 text-gray-800">
            Edit Dokubah / SPK
        </h2>

        <form id="formEditSpk" method="POST">
            @csrf
            @method('PUT')

            <label class="text-sm text-gray-600">Nomor SPK</label>
            <input id="edit_nomor_spk"
                   class="w-full border border-gray-300 rounded-lg p-2 mb-3 bg-gray-100 text-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                   name="nomor_spk"
                   readonly>

            <label class="text-sm text-gray-600">Nama Project</label>
            <input id="edit_nama_proyek"
                   class="w-full border border-gray-300 rounded-lg p-2 mb-3 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                   name="nama_proyek"
                   required>

            <label class="text-sm text-gray-600">Tanggal Mulai</label>
            <input id="edit_tanggal_mulai"
                   type="date"
                   class="w-full border border-gray-300 rounded-lg p-2 mb-3 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                   name="tanggal_mulai"
                   required>

            <label class="text-sm text-gray-600">Tanggal Selesai</label>
            <input id="edit_tanggal_selesai"
                   type="date"
                   class="w-full border border-gray-300 rounded-lg p-2 mb-4 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                   name="tanggal_selesai">

            <div class="flex justify-end gap-2">

                <button type="button"
                        onclick="closeEditSpkModal()"
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