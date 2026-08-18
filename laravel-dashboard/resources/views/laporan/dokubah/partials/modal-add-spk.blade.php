<!-- ================= MODAL TAMBAH SPK ================= -->
<div id="modalAddSpk"
     class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50 px-4">

    <div class="bg-white w-full max-w-lg rounded-xl p-6 relative">

        <button type="button"
                onclick="closeAddSpkModal()"
                class="absolute top-3 right-3 text-gray-500 hover:text-sky-600">
            ✕
        </button>

        <h2 class="text-xl font-bold mb-5 text-gray-800">
            Tambah Dokubah / SPK
        </h2>

        <form id="formAddSpk"
              method="POST"
              action="{{ route('spk.store') }}">
            @csrf

            <label class="text-sm text-gray-600">Nomor SPK</label>
            <input type="text"
                   value="Dibuat otomatis"
                   readonly
                   class="w-full border border-gray-300 rounded-lg p-2 mb-3 bg-gray-100 text-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">

            <label class="text-sm text-gray-600">Nama Project</label>
            <input name="nama_proyek"
                   class="w-full border border-gray-300 rounded-lg p-2 mb-3 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                   required>

            <label class="text-sm text-gray-600">Tanggal Mulai</label>
            <input type="date"
                   name="tanggal_mulai"
                   class="w-full border border-gray-300 rounded-lg p-2 mb-3 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                   required>

            <label class="text-sm text-gray-600">Tanggal Selesai</label>
            <input type="date"
                   name="tanggal_selesai"
                   class="w-full border border-gray-300 rounded-lg p-2 mb-3 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">

            <div class="flex justify-end gap-2 mt-4">

                <button type="button"
                        onclick="closeAddSpkModal()"
                        class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                    Batal
                </button>

                <button type="submit"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded">
                    Simpan
                </button>

            </div>

        </form>

    </div>

</div>